<?php
namespace CB\Api;

class Security
{
    /**
     * get access rules that are directly associated to passed node_id
     * @param  array $p array containing node_id value
     * @return json  array with a subarray "data" that list all accesses
     */
    public function getNodeDirectAcl($p)
    {
        $sec = new \CB\Security();
        $rez = $sec->getObjectDirectAcl(array('id' => $p['node_id']));

        return $rez;
    }
    /**
     * insert or update an existing access for a node
     * @param array $p array containig the following params:
     *                  node_id,
     *                  user_grop_id,
     *                  allow (read, write, modify, full_control),
     *                  deny (read, write, modify, full_control)
     * @return array array with boolean success propety
     */
    public function updateNodeAccess($p)
    {
        if (empty($p['allow'])) {
            $p['allow'] = '0,0,0,0,0,0,0,0,0,0,0,0';
        }
        if (empty($p['deny'])) {
            $p['deny'] = '0,0,0,0,0,0,0,0,0,0,0,0';
        }

        $p['allow'] = $this->convertTextToAccessString($p['allow']);
        $p['deny'] = $this->convertTextToAccessString($p['deny'], -1);

        /* validate access values */
        $a = array_filter(explode(',', $p['allow']), 'is_numeric');
        $b = array_filter(explode(',', $p['deny']), 'is_numeric');

        if ((sizeof($a) <> 12) || (sizeof($b) <> 12)) {
            return array(
                'success' => false
                ,'msg' => 'Wrong access value'
            );
        }
        /* end of validate access values */

        $sec = new \CB\Security();
        $rez = $sec->updateObjectAccess(
            array(
                'id' => $p['node_id']
                ,'data' => array(
                    'id' => $p['user_group_id']
                    ,'user_group_id' => $p['user_group_id']
                    ,'allow' => $p['allow']
                    ,'deny' => $p['deny']
                )
            )
        );

        return $rez;
    }
    /**
     * delete and access from a node
     * @param array $p array containing following params:
     *                  node_id,
     *                  user_group_id
     * @return array array with boolean success propety
     */
    public function deleteNodeAccess($p)
    {
        $sec = new \CB\Security();
        $rez = $sec->destroyObjectAccess(
            array(
                'id' => $p['node_id']
                ,'data' => $p['user_group_id']
            )
        );

        return $rez;
    }

    /**
     * set security inheritance for a node
     * @param array $p {
     *     @type int      $node_id    id of tree node
     *     @type boolean  $inherit    set inherit to true or false
     *     @type string   $copyRules   when removing inheritance ($inherit = false)
     *                                 then this value could be set to 'yes' or 'no'
     *                                 for copying inherited rules to current node
     * }
     * @return array array with boolean success propety
     */
    public function setInheritance($p)
    {
        /* check params */
        if (!is_numeric($p['node_id'])
            || !isset($p['inherit'])
        ) {
            return array('success' => false, 'msg' => 'Wrong params');
        }
        /* end of check params */

        $sec = new \CB\Security();
        $rez = $sec->setInheritance(
            array(
                'id' => $p['node_id']
                ,'inherit' => $p['inherit']
                ,'copyRules' => @$p['copyRules']
            )
        );

        return $rez;
    }

    /**
     * convert textual representation of accesses (read, write, modify, full_control)
     * to access specific string of bits
     * @param  varchar $access_string textual access representation
     * @param  integer $sign          bits sign
     * @return varchar comma separated bits string
     */
    private function convertTextToAccessString($access_string, $sign = 1)
    {
        if (is_array($access_string)) {
            $access_string = implode(',', $access_string);
        }

        $bit = 1 * $sign;
        switch ($access_string) {
            case 'read':
                $access_string = $bit.',0,0,0,0,'.$bit.',0,0,0,0,0,'.$bit;
                break;
            case 'write':
                $access_string = '0'.str_repeat(','.$bit, 4).',0,'.$bit.',0,0,0,0,0';
                break;
            case 'readwrite':
                $access_string = $bit.str_repeat(','.$bit, 6).',0,0,0,0,'.$bit;
                break;
            case 'modify':
                $access_string = $bit.str_repeat(','.$bit, 6).',0,'.$bit.',0,0,'.$bit;
                break;
            case 'full_control':
                $access_string = $bit.str_repeat(','.$bit, 11);
                break;
        }

        return $access_string;
    }
}
