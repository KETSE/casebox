<?php
namespace CB;

include 'PreviewExtractor.php';

class PreviewExtractorOffice extends PreviewExtractor
{
    public function execute()
    {
        $this->init();

        $processing = false;

        $res = DB\dbQuery(
            'SELECT count(*) `count`
            FROM file_previews
            WHERE `status` = 2
                AND `group` = $1',
            'office'
        ) or die( DB\dbQueryError() );

        if ($r = $res->fetch_assoc()) {
            $processing = ($r['count'] > 0);
        }
        $res->close();

        if ($processing) {
            exit(0);
        }

        $filesPreviewDir = Config::get('files_preview_dir');

        $sql = 'SELECT c.id `content_id`, c.path, p.status
                    ,(SELECT name
                        FROM files f
                        WHERE f.content_id = c.id LIMIT 1
                    ) `name`
                FROM file_previews p
                LEFT JOIN files_content c ON p.id = c.id
                WHERE p.`status` = 1
                    AND `group` = \'office\'
                ORDER BY p.cdate';

        $res = DB\dbQuery($sql) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            //start the transaction so that the file status would not change on script fail
            DB\startTransaction();

            DB\dbQuery(
                'UPDATE file_previews
                SET `status` = 2
                WHERE id = $1',
                $r['content_id']
            ) or die(DB\dbQueryError());
            $ext = explode('.', $r['name']);
            $ext = array_pop($ext);
            $ext = strtolower($ext);
            $fn = Config::get('files_dir') . $r['path'].DIRECTORY_SEPARATOR.$r['content_id'];
            $nfn = $filesPreviewDir . $r['content_id'] . '_.' . $ext;
            $pfn = $filesPreviewDir . $r['content_id'] . '_.html';

            copy($fn, $nfn);
            file_put_contents($pfn, '');
            $cmd = Config::get('UNOCONV') . ' -v -f html -o '.$pfn.' '.$nfn; //.' >> ' . Config::get('debug_log') . ' 2>&1';

            exec($cmd, $output, $returnStatus); //returnStatus should be 0 if no error

            //we cant delete the file right away
            //because command can execute in background and could take some time
            //unlink($nfn);

            if (empty($returnStatus) && file_exists($pfn)) {
                file_put_contents(
                    $pfn,
                    '<div style="padding: 5px">'.$this->purify(
                        file_get_contents($pfn),
                        array(
                            'URI.Base' => '/' . Config::get('core_name') . '/preview/'
                            ,'URI.MakeAbsolute' => true
                        )
                    ).'</div>'
                );

                DB\dbQuery(
                    'UPDATE file_previews
                    SET `status` = 0
                        ,`filename` = $2
                        ,`size` = $3
                    WHERE id = $1',
                    array(
                        $r['content_id']
                        ,$r['content_id'].'_.html'
                        ,filesize($pfn)
                    )
                )  or die(DB\dbQueryError());
                $res->close();
            } else {
                //preview not generated for some reason, probably unoconv service not started
                \CB\debug(
                    'UNOCONV execution error, please check if python accesible through command line'.
                    ' and if correctly specified in config.ini: '.$cmd
                );

                DB\dbQuery(
                    'UPDATE file_previews
                    SET `status` = 3
                    WHERE id = $1',
                    $r['content_id']
                )  or die(DB\dbQueryError());
            }

            DB\commitTransaction();

            $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        }
        $res->close();
    }
}
$extractor = new PreviewExtractorOffice();
$extractor->execute();
