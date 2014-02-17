<?php
namespace Util\JSON;

/**
 * converting old objects data format to new JSON format
 */
use CB\DB;
use CB\Objects;

class Transform
{
    public function execute()
    {
        //we should exclude template for fields from processing

        $fieldTemplates = array();

        DB\startTransaction();
        $res = DB\dbQuery(
            "SELECT id FROM templates WHERE `type` in ( 'field', 'template')"
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $fieldTemplates[] = $r['id'];
        }
        $res->close();
        if (empty($fieldTemplates)) {
            $fieldTemplates = '';
        } else {
            $fieldTemplates = ' WHERE template_id NOT IN ('.implode(',', $fieldTemplates).')';
        }

        echo "Start processing objects :\n";

        $res = DB\dbQuery('SELECT id FROM tree '.$fieldTemplates) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            echo '.';
            $obj = Objects::getCustomClassByObjectId($r['id']);
            $obj->load();
            //tasks are loading as needed
            if ($obj->getType() !== 'task') {
                $obj->loadOldGridDataToNewFormat();
            }
            $obj->update();
        }
        $res->close();

        echo "\n Done\n\nProcessing Users data:\n";

        $user = new \CB\User();
        $res = DB\dbQuery('SELECT id FROM users_groups WHERE `type` = 2') or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            echo $r['id'].' ';
            @$data = $user->getProfileData($r['id']);
            @$user->saveProfileData($data);
        }
        $res->close();
        echo "\n commiting transaction ... \n";
        DB\commitTransaction();
    }
}
