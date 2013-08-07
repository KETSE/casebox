<?php
namespace CB;

include 'PreviewExtractor.php';

class PreviewExtractorOffice extends PreviewExtractor
{
    public function execute()
    {
        $this->init();
        $sql = 'SELECT count(*)
            FROM file_previews
            WHERE `status` = 2
                AND `group` = \'office\'';
        $res = DB\dbQuery($sql) or die( DB\dbQueryError() );
        $processing = false;
        if ($r = $res->fetch_row()) {
            $processing = ($r[0] > 0);
        }
        $res->close();
        if ($processing) {
            exit(0);
        }

        $sql = 'SELECT c.id `content_id`, c.path, p.status
                  , (SELECT name
                     FROM files f
                     WHERE f.content_id = c.id LIMIT 1) `name`
                FROM file_previews p
                LEFT JOIN files_content c ON p.id = c.id
                WHERE p.`status` = 1
                    AND `group` = \'office\'
                ORDER BY p.cdate';

        $res = DB\dbQuery($sql) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            DB\dbQuery(
                'UPDATE file_previews
                SET `status` = 2
                WHERE id = $1',
                $r['content_id']
            ) or die(DB\dbQueryError());
            $ext = explode('.', $r['name']);
            $ext = array_pop($ext);
            $ext = strtolower($ext);
            $fn = FILES_PATH.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'];
            $nfn = FILES_PREVIEW_PATH.$r['content_id'].'_.'.$ext;
            $pfn = FILES_PREVIEW_PATH.$r['content_id'].'_.html';

            copy($fn, $nfn);
            file_put_contents($pfn, '');
            $cmd = UNOCONV.' -v -f html -o '.$pfn.' '.$nfn; //.' >> '.DEBUG_LOG.' 2>&1';
            exec($cmd);
            unlink($nfn);
            file_put_contents(
                $pfn,
                '<div style="padding: 5px">'.$this->purify(
                    file_get_contents($pfn),
                    array(
                        'URI.Base' => '/preview/'
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
            $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        }
        $res->close();
    }
}
$extractor = new PreviewExtractorOffice();
$extractor->execute();
