<?php
namespace CB;

class BrowserTree extends Browser
{
    public function getChildren($p)
    {
        $rez = parent::getChildren($p);

        $sql = 'SELECT count(*) `has_childs`
            FROM tree
            WHERE pid = $1
                AND dstatus = 0'.
            ( empty($p['showFoldersContent']) ?
                ' AND `template_id` IN (0'.implode(',', Config::get('folder_templates')).')'
                : ''
            );

        foreach ($rez['data'] as &$d) {
            if (is_numeric($d['nid']) && !isset($d['loaded'])) {
                $res = DB\dbQuery($sql, $d['nid']) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $d['has_childs'] = !empty($r['has_childs']);
                }
                $res->close();
            }

            $d['loaded'] = empty($d['has_childs']);
        }

        return $rez['data'];
    }
}
