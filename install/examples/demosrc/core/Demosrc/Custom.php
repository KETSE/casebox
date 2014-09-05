<?php
namespace Demosrc;

class Custom
{
    private $categories = array(
        716 => 2,
        717 => 5665,
        718 => 13332,
        719 => 16265,
        720 => 16784,
        721 => 18936,
        722 => 19087,
        723 => 21386);

    public function beforeNodeDbCreate(&$p)
    {
        return ;
        // should be reviewed if needed. We rely on templates, not on type field.
        if ($p['type'] != 6) {

            $path = @$p['path'];
            $path = explode('/', trim($path, '/'));

            if (count($path)<=1) {
                return;
            }
            if ($category = array_search($path[1], $this->categories)) {

                switch ($p['template_id']) {
                    case 91:
                        // action
                        $program_field = 303;
                        break;
                }
            }

            if (isset($program_field)) {
                $p['gridData']['values']['f'.$program_field.'_0'] = array('value'=>$category, 'info'=>'', 'file'=>'');
            }
        }
    }
}
