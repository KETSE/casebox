<?php
namespace Util;

use CB\DB as DB;

class Templates
{
    /**
     * get main Template id (Template for templates)
     * @return int || null
     */
    public static function getMainTemplateId()
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT t.id
            FROM tree t
            JOIN templates tt
             ON t.`template_id` = tt.id
             AND tt.type = \'template\'
             AND tt.name = \'TemplatesTemplate\'
            WHERE t.template_id = t.id'
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }
}
