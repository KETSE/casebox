<?php
namespace DisplayColumns;

use CB\Objects;

class FormEditor extends Base
{

    protected $fromParam = 'formEditor';

    /**
     * get display columns for field
     * @return array
     */
    public function getDC()
    {

        $rez = array();

        if (empty($this->params['inputParams']['fieldId'])) {
            return $rez;
        }

        $fieldId = $this->params['inputParams']['fieldId'];

        $field = new Objects\TemplateField($fieldId, false);

        $fieldData = $field->load();

        if (!empty($fieldData['cfg']['DC'])) {
            $rez = $fieldData['cfg']['DC'];
        }

        return array(
            'data' => $rez
        );
    }
}
