<?php
namespace Demosrc;

use CB\Util;
use CB\Objects;
use CB\Path;

class Graph
{
    public $colors = array(
        'blue' => '#9DB7E8'
        ,'gray' => '#BFBFBF'
        ,'green' => '#78D168'
        ,'maroon' => 'maroon'
        ,'olive' => '#C6D2B0'
        ,'orange' => '#F9BA89'
        ,'brown' => 'brown'
        ,'peach' => 'peach'
        ,'purple' => '#B5A1E2'
        ,'red' => '#E7A1A2'
        ,'steel' => '#DAD9DC'
        ,'teal' => '9FDCC9'
        ,'yellow' => '#FCFA90'
        ,'' => '#BFBFBF'
    );

    public $labelField = 'name';
    public $hintField = 'hint';
    public $dateField = 'date';

    protected $links;

    /* search and return nodes from solr */
    private function getGraphNodes($id)
    {
        $acceptTemplates = Util\toNumericArray(\CB\getOption('action_templates'));
        if (empty($acceptTemplates)) {
            return null;
        }

        $p = array(
            'templates' => $acceptTemplates
            ,'template_types' => array('object')
            ,'pids' => $id
            ,'fl' => 'id,name,date,template_id' //returned field list
            ,'sort' => 'date'
        );
        $s = new \CB\Search();
        $srez = $s->query($p);
        unset($s);

        return $srez['data'];
    }

    private function switchNodeShowTitles(&$node)
    {
        /* switching label with hint if $_GET['title'] == 1*/
        if (empty($_GET['titles']) && empty($this->params['titles'])) {/* parameter titles is sent from the client when switching the nodes display mode */
            $t = Util\coalesce($node[$this->hintField], $node[$this->labelField]);
            $node[$this->hintField] = $node[$this->labelField];
            $node[$this->labelField] = $t;
        }
    }

    /**
     * This function is designed to prepeare all necessary nodes properties for graph rendering.
     *
     * used properties for graph:
     * id
     * ,$this->labelField - will be used as node label
     * ,hint = $this->hintField
     * ,date = $this->dateField
     * ,shape - node shape
     * ,style - custom node style
     * ,fillcolor
     * ,margin
     * ,penwith
     * ,leftSideNodes - for decisions with associated violations
     * ,rightSideNodes
     */
    private function prepareGraphNodes(&$nodesArray)
    {

        for ($i=0; $i < sizeof($nodesArray); $i++) {
            /* define a reference for easy use */
            $node = &$nodesArray[$i];

            /* adjust title field if needed */
            $t = trim($node[$this->labelField]);
            $node[$this->hintField] = nl2br($t);
            $node[$this->labelField] = (strlen($t) > 30)
                ? substr($t, 0, 30) . ' ...'
                : $t;

            /* adjusting date field format */
            if (!empty($node[$this->dateField])) {
                 $node[$this->dateField] = substr($node[$this->dateField], 0, 10);
                 $node[$this->dateField] = implode('.', array_reverse(explode('-', $node[$this->dateField])));
            }
            /* end of adjusting date field format */

            /* SETTING NODE STYLE (SHAPE AND COLOR) */

            /* getting node object */
            $o = Objects::getCachedObject($node['id']);

            $node['style'] = 'filled';
            $color = $o->getFieldValue('color', 0)['value'];
            if (!empty($color)) {
                $t = $o->getTemplate();
                $color = $t->formatValueForDisplay($t->getField('color'), $color, false);
                $node['fillcolor'] = @$this->colors[$color];
            } else {
                $node['fillcolor'] = $this->colors['gray'];
            }

            $inLinks = Util\toNumericArray($o->getFieldValue('in_links', 0)['value']);
            $outLinks = Util\toNumericArray($o->getFieldValue('out_links', 0)['value']);

            foreach ($inLinks as $inNode) {
                $this->links[$inNode][$node['id']] = 1;
            }
            foreach ($outLinks as $outNode) {
                $this->links[$node['id']][$outNode] = 1;
            }

            /* setting node shape */
            $node['shape'] = "box"; //set default shape

            // $this->switchNodeShowTitles($node);
        }
    }

    //it is supposed that nodesArray is sorted ascending by date property
    private function setGraphTimeline(&$nodesArray)
    {
        $this->timeline = array();
        $lastTime = -1;
        $timeNodeindex = -1;
        for ($i=0; $i < sizeof($nodesArray); $i++) {
            if ($lastTime != @$nodesArray[$i][$this->dateField]) {
                $lastTime = @$nodesArray[$i][$this->dateField];
                $this->timeline[] = $lastTime;
                $timeNodeindex++;
            }
            $nodesArray[$i]['t'] = 't'.$timeNodeindex; //timeline index for the node
        }
    }

    private function getNodeLinks($nodeId)
    {
        $rez = array();

        if (empty($this->links[$nodeId])) {
            return $rez;
        }

        return array_keys($this->links[$nodeId]);
    }

    public function load($p)
    {
        $rez = array(
            'success'=> true
            ,'data'=> array(
                'html' => '<div class="msg">Please select a case ...</div>'
            )
        );

        if (empty($p['path'])) {
            return $rez;
        }

        $this->params = $p;

        // classes refactored, this should be reviewed
        // $this->pathProperties = Path::getPathProperties($p['path']);
        // $pp = &$this->pathProperties;

        // if (empty($pp['case_id'])) {
            return $rez;
        // }

        /* define graph title */
        $graphTitle = empty($pp['name'])
            ? 'Graph'
            : $pp['name'];

        $graphTitle = str_replace(array(' ', '.', '\'', '"', '-', '/', '\\', '~'), '_', $graphTitle);
        /* end of define graph title */

        $solrNodes = $this->getGraphNodes($pp['case_id']);
        if (empty($solrNodes)) {
            return array(
                'success'=> true
                ,'data'=> array(
                    'html' => '<div class="msg">Nothing to display.</div>'
                )
            );
        }
        $graphNodes = $solrNodes;

        $this->prepareGraphNodes($graphNodes);

        $this->setGraphTimeline($graphNodes);

        $rez = '';

        /* print graph timeline nodes */
        $rez .= "{\n node [shape=plaintext, fontsize=7, fontcolor=\"gray\", tooltip=\"timeline\"];\n /* the time-line graph */ \n";
        $rez .= 't'.implode(' -> t', array_keys($this->timeline))." [dir=none, color=\"gray\"];\n}\n";
        for ($i=0; $i < sizeof($this->timeline); $i++) {
            $rez .= 't'.$i.'[label="'.$this->timeline[$i].'"];'."\n";
        }
        /* end of print graph timeline nodes */

        /* align graph nodes to corresponding timeline node */
        foreach ($graphNodes as $node) {
            $rez .= "\n{rank=same; ".$node['t']."; n".$node['id']."; }";
        }
        /* end of align graph nodes to corresponding timeline node */

        /* defining nodes */
        foreach ($graphNodes as $node) {
            /* check if its a composite node with left and/or right sides
            and replace it's label with corresponding nodes table */
            $leftSide = '';
            if (!empty($node['leftSideNodes'])) {
                foreach ($node['leftSideNodes'] as $subnode) {
                    $leftSide .= '<td href="#'.$subnode['id'].'"'.
                        ' bgcolor="'.$subnode['bgcolor'].'"'.
                        ' PORT="n'.$subnode['id'].'_'.$node['id'].'"'.
                        (empty($subnode[$this->hintField]) ? '' : ' tooltip="'.$subnode[$this->hintField].'"').
                        ' valign="middle">'.
                        $subnode[$this->labelField].'</td>';
                }

            }
            $rightSide = '';
            if (!empty($node['rightSideNodes'])) {
                foreach ($node['rightSideNodes'] as $subnode) {
                    $rightSide .= '<td href="#'.$subnode['id'].'"'.
                        ' bgcolor="'.$subnode['bgcolor'].'"'.
                        ' PORT="n'.$subnode['id'].'_'.$node['id'].'"'.
                        (empty($subnode[$this->hintField]) ? '' : ' tooltip="'.$subnode[$this->hintField].'"').
                        ' valign="middle">'.
                        $subnode[$this->labelField].'</td>';
                }
            }
            if (!empty($leftSide) || !empty($rightSide)) {
                $node[$this->labelField] = '< <table border="0" cellborder="1" cellspacing="0" cellpadding="4" '.
                'style="border: 0 !important; border-collapse: collapse !important"><tr>'.
                $leftSide.'<td bgcolor="'.$this->colors['yellow'].'"'.
                    ' PORT="n'.$node['id'].'"'.
                    ' valign="middle"'.
                    (empty($node[$this->hintField]) ? : ' tooltip="'.addslashes($node[$this->hintField]).'"').
                    '>'.$node[$this->labelField].'</td>'.
                    $rightSide.'</tr></table> >';
            } else {
                 $node[$this->labelField] = '"'.addslashes($node[$this->labelField]).'"';
            }

            /* end of check if its a composite node with left and/or right sides
            and replace it's label with corresponding nodes table */

            $rez .= "\n n".$node['id'].' [shape="'.($node['shape'] ? $node['shape'] : 'ellipse').'"'.
                    (empty($node['style']) ? '' : ', style="'.$node['style'].'"').
                    (empty($node['fillcolor']) ? '' : ', fillcolor="'.$node['fillcolor'].'"').
                    (isset($node['margin']) ? ', margin="'.$node['margin'].'"' : '').
                    (isset($node['penwidth']) ? ', penwidth="'.$node['penwidth'].'"' : '').
                    (empty($node[$this->hintField]) ? '' : ', tooltip="'.addslashes($node[$this->hintField]).'"').
                    ', label='.$node[$this->labelField].', URL="#'.$node['id'].'"];';
            $linkedNodeIds = $this->getNodeLinks($node['id']);
            foreach ($linkedNodeIds as $ln) {
                $rez .= "\n".'n'.$ln.' -> n'.$node['id'].';';
            }
        }

        // enclosing all nodes data in header and footer
        $rez = 'digraph "'.$graphTitle.'" {
            spline=false;
            node [margin="0.05,0.05", shape=plaintext, fontname="tahoma", fontsize=8, height=0.2, width=0.2];
            edge [arrowsize=0.5, fontsize=7];

            ranksep=0.25;
            nodesep=0.25;
            '.$rez."\n}";

        /* save result to a temporary file for passing it to graphviz */
        $t = tempnam(sys_get_temp_dir(), 'gv');
        file_put_contents($t, $rez);
        //also store gv file in core files path for any case

        /* if parametter "d" is passed in GET, then save graph is requested.
        Generate png file and write it directly to output */
        if (@$_GET['d'] == 1) {
            $cmd = "dot  -Tpng -o".$t.".png ".$t;
            exec($cmd);
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="'.date('Y-m-d').' - Граф дела '.addslashes($graphTitle).'.png"');
            echo file_get_contents($t.'.png');
        } else { //generate and return svg
            $cmd = "dot  -Tsvg -o".$t.".svg ".$t;
            exec($cmd);
            $rez = file_get_contents($t.'.svg');
        }

        /* delete temporary files */
        unlink($t);
        unlink($t.'.svg');
        /* end of delete temporary files */

        return array(
            'success' => true
            ,'data'=> array(
                'html' => $rez
                ,'nodes' => $solrNodes
            )
        );

    }
}
