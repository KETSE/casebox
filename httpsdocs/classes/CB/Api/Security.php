<?php
namespace CB\Api;

class Security
{
    public function getNodeDirectAcl($p)
    {
        $sec = new \CB\Security();
        $rez = $sec->getObjectDirectAcl((object) array('id' => $p['node_id']));

        return $rez;
    }

    public function updateNodeAccess($p)
    {
        $sec = new \CB\Security();
        $rez = $sec->updateObjectAccess(
            (object) array(
                'id' => $p['node_id']
                ,'data' => (object) array(
                    'id' => $p['user_group_id']
                    ,'allow' => $p['allow']
                    ,'deny' => $p['deny']
                )
            )
        );

        return $rez;
    }

    public function deleteNodeAccess($p)
    {
        $sec = new \CB\Security();
        $rez = $sec->destroyObjectAccess(
            (object) array(
                'id' => $p['node_id']
                ,'data' => $p['user_group_id']
            )
        );

        return $rez;
    }
}
