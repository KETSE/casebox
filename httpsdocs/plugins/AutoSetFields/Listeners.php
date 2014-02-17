<?php
namespace AutoSetFields;

class Listeners
{
    /**
     * autoset fields
     * @param  object $o
     * @return void
     */
    public function onNodeDbCreateOrUpdate($o)
    {
        if (!is_object($o)) {
            return;
        }

        $objData = $o->getData();

        $title = @$o->getFieldValue('_title', 0)['value'];
        if (empty($title)) {
            $template = $o->getTemplate();
            if (!empty($template)) {
                $templateData = $template->getData();
                if (!empty($templateData['title_template'])) {
                    $title = $this->getAutoTitle($o);
                }
            }
        }
        if (!empty($title)) {
            $objData['name'] = $title;
        }

        $date = @$o->getFieldValue('_date_start', 0)['value'];
        if (!empty($date)) {
            $objData['date'] = $date;
        }

        $date = @$o->getFieldValue('_date_end', 0)['value'];
        if (!empty($date)) {
            $objData['date_end'] = $date;
        }
        $o->setData($objData);
    }

    /**
     * generate title string using given object data and titleTemplate
     * @param  object  $object
     * @return varchar
     */
    protected function getAutoTitle($object)
    {
        $rez = '';

        if (!is_object($object)) {
            return $rez;
        }

        $template = $object->getTemplate();

        if (empty($template)) {
            return $rez;
        }

        $templateData = $template->getData();
        $fields = array(); //used from php templates of title
        $rez = str_replace(
            '{template_title}',
            @$templateData['title'],
            $templateData['title_template']
        );

        $ld = $object->getLinearData();
        /* replace field values */
        foreach ($ld as $field) {
            $tf = $template->getField($field['name']);
            $v = $template->formatValueForDisplay($tf, @$field['value'], false);
            if (is_array($v)) {
                $v = implode(',', $v);
            }
            $v = addcslashes($v, '\'');
            $rez = str_replace('{'.$field['name'].'}', $v, $rez);
            $fields[$field['name']] = $v;
        }

        //replacing field titles into object title variable
        foreach ($templateData['fields'] as $fk => $fv) {
            $rez = str_replace('{f'.$fv['name'].'t}', $fv['title'], $rez);

        }
        // evaluating the title if contains php code
        if (strpos($rez, '<?php') !== false) {
            eval(' ?>'.$rez.'<?php ');
            if (!empty($title)) {
                $rez = $title;
            }
        }
        //replacing any remained field placeholder from the title
        $rez = preg_replace('/\{[^\}]+\}/', '', $rez);
        $rez = stripslashes($rez);

        return $rez;
    }
}
