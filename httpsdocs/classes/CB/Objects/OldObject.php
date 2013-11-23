<?php
namespace CB\Objects;

use CB\DB as DB;

/**
 * class for converting old object format to new JSON format
 *
 * This class will be deleted after all cores data migration
 */

class OldObject
{
    /**
     * load grid data from old format and converts it to new format
     * in current data property
     * @return void
     */
    public function loadOldGridDataToNewFormat($objectType = 'objects')
    {
        $this->oldValues = array();
        $this->oldDuplicates = array();

        /* load duplicates */
        $res = DB\dbQuery(
            'SELECT id
                ,pid
                ,field_id
            FROM '.$objectType.'_duplicates
            WHERE '.$this->getObjectIdField($objectType).' = $1
            ORDER BY id',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $this->oldDuplicates[$r['field_id']][$r['id']] = $r['pid'];
        }
        $res->close();

        /* load data */
        $res = DB\dbQuery(
            'SELECT
                field_id
                ,duplicate_id
                ,`value`
                ,info
                '.($objectType == 'users_groups' ? '' : ',files,private_for_user `pfu`').'
            FROM '.$objectType.'_data
            WHERE '.$this->getObjectIdField($objectType).' = $1',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $this->oldValues[$r['field_id']][] = $r;
        }
        $res->close();

        $this->data['data'] = $this->convertOldData();

        /* converting obtained data to new format */
        /** @param array $p{ object properties
        *       @type int $id id of the object to be updated or null for crating a new object
        *       @type int $pid parent id
        *       @type int $template_id
        *       @type int $oid owner id
        *       @type array $data {
        *             <field_name> => <scalar value> | array( //single field
        *                 'value' => 'field value'
        *                 ,'info' => 'field info'
        *                 ,'childs' => array(
        *                     <field_name> =>
        *                     ...
        *                 )
        *             )
        *             ,<field_name> => <scalar value> | array( // multiplied field
        *                 <scalar value> | array(
        *                     'value' => 'field value'
        *                     ,'info' => 'field info'
        *                     ,'childs' => array(
        *
        *                     )
        *                 )
        *                 ,<scalar value> | array(
        *                     'value' => 'field value'
        *                     ,'info' => 'field info'
        *                     ,'childs' => array(
        *
        *                     )
        *                 )
        *             )
        **/
    }

    protected function convertOldData()
    {
        $rez = array();
        // iterate all values and select first level values
        foreach ($this->oldValues as $fieldId => $fieldValues) {
            // our old values are stored in an array
            for ($i=0; $i < sizeof($fieldValues); $i++) {
                $f = &$fieldValues[$i];
                $templateField = $this->template->getField($f['field_id']);
                // check if this field belongs to our $duplicatePid
                if (($f['duplicate_id'] == 0) && !is_null($templateField) &&
                    ($templateField['pid'] == $this->data['template_id'])
                ) {
                    $value = array(
                        'value' => $f['value']
                        ,'info' => $f['info']
                        ,'files' => $f['files']
                        ,'childs' => $this->getOldChilds($f['duplicate_id'], $f['field_id'])
                    );
                    $value = $this->formatOldValue($value);
                    $duplicates = $this->getOldDuplicates($f['duplicate_id'], $f['field_id']);
                    if (empty($duplicates)) {
                        if (!is_null($value)) {
                            $rez[$templateField['name']] = $value;
                        }
                    } else {
                        if (!is_null($value)) {
                            $rez[$templateField['name']][] = $value;
                        }
                        foreach ($duplicates as $duplicate) {
                            if (!is_null($duplicate)) {
                                $rez[$templateField['name']][] = $duplicate;
                            }
                        }
                        if (!empty($rez[$templateField['name']]) && (sizeof($rez[$templateField['name']]) == 1)) {
                            $rez[$templateField['name']] = $rez[$templateField['name']][0];
                        }
                    }
                }
            }
        }

        return $rez;
    }

    protected function getOldDuplicates($duplicatePid = 0, $fieldId = null)
    {
        $rez = array();
        // iterate all values and select duplicate values
        foreach ($this->oldValues as $fieldId => $fieldValues) {
            // our old values are stored in an array
            for ($i=0; $i < sizeof($fieldValues); $i++) {
                $f = &$fieldValues[$i];
                $templateField = $this->template->getField($f['field_id']);
                // check if this field belongs to our $duplicatePid
                if (!empty($f['duplicate_id']) &&
                    (@$this->oldDuplicates[$f['duplicate_id']] == $duplicatePid) &&
                    ($templateField['id'] == $fieldId)
                ) {
                    $value = array(
                        'value' => $f['value']
                        ,'info' => $f['info']
                        ,'files' => $f['files']
                        ,'childs' => $this->getOldChilds($f['duplicate_id'], $f['field_id'])
                    );
                    $value = $this->formatOldValue($value);
                    $duplicates = $this->getOldDuplicates($f['duplicate_id'], $f['field_id']);
                    if (empty($duplicates)) {
                        if (!is_null($value)) {
                            $rez[$templateField['name']] = $value;
                        }
                    } else {
                        if (!is_null($value)) {
                            $rez[$templateField['name']][] = $value;
                        }
                        foreach ($duplicates as $duplicate) {
                            if (!is_null($duplicate)) {
                                $rez[$templateField['name']][] = $duplicate;
                            }
                        }
                        if (!empty($rez[$templateField['name']]) && (sizeof($rez[$templateField['name']]) == 1)) {
                            $rez[$templateField['name']] = $rez[$templateField['name']][0];
                        }
                    }
                }
            }
        }

        return $rez;
    }

    protected function getOldChilds($duplicateId = 0, $fieldId = null)
    {
        $rez = array();
        // iterate all values and select child values
        foreach ($this->oldValues as $fieldId => $fieldValues) {
            // our old values are stored in an array
            for ($i=0; $i < sizeof($fieldValues); $i++) {
                $f = &$fieldValues[$i];
                $templateField = $this->template->getField($f['field_id']);

                // check if this field belongs to our $duplicatePid
                if (($f['duplicate_id'] == $duplicateId) &&
                    ($templateField['pid'] == $fieldId)
                ) {
                    $value = array(
                        'value' => $f['value']
                        ,'info' => $f['info']
                        ,'files' => $f['files']
                        ,'childs' => $this->getOldChilds($f['duplicate_id'], $f['field_id'])
                    );
                    $value = $this->formatOldValue($value);
                    $duplicates = $this->getOldDuplicates($f['duplicate_id'], $f['field_id']);
                    if (empty($duplicates)) {
                        if (!is_null($value)) {
                            $rez[$templateField['name']] = $value;
                        }
                    } else {
                        if (!is_null($value)) {
                            $rez[$templateField['name']][] = $value;
                        }
                        foreach ($duplicates as $duplicate) {
                            if (!is_null($duplicate)) {
                                $rez[$templateField['name']][] = $duplicate;
                            }
                        }
                        if (!empty($rez[$templateField['name']]) && (sizeof($rez[$templateField['name']]) == 1)) {
                            $rez[$templateField['name']] = $rez[$templateField['name']][0];
                        }
                    }
                }
            }
        }

        return $rez;
    }

    protected function formatOldValue($value)
    {
        if (empty($value['files'])) {
            unset($value['files']);
        }
        if (empty($value['info'])) {
            unset($value['info']);
        }
        if (empty($value['childs'])) {
            unset($value['childs']);
        }
        if (($value['value'] == '0000-00-00') ||
            ($value['value'] == '0000-00-00 00:00:00') ||
            (
                is_string($value['value']) &&
                empty($value['value'])
            )
        ) {
            $value['value'] = null;
        }

        if (array_key_exists('value', $value) && (sizeof($value) == 1)) {
            $value = $value['value'];
        }

        return $value;
    }

    protected function getObjectIdField($objectType)
    {
        switch ($objectType) {
            case 'users_groups':
                return 'user_id';
                break;
            default:
                return substr($objectType, 0, strlen($objectType) -1).'_id';
        }
    }
}
