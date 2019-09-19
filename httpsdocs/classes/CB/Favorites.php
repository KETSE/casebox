<?php
namespace CB;

use CB\DataModel as DM;

class Favorites
{
    public function create($p)
    {
        $rez = array(
            'succes' => false
            ,'data' => array()
        );

        if (empty($p['node_id']) || empty($p['data'])) {
            return $rez;
        }

        $data = array(
            'name' => Purify::filename($p['data']['name'])
            ,'path' => $p['data']['path']
            ,'pathText' => empty($p['data']['pathText'])
                ? ''
                : $p['data']['pathText']
        );

        if (is_numeric($p['node_id'])) {
            $data['template_id'] = Objects::getTemplateId($p['node_id']);
            $data['iconCls'] = Browser::getIcon($data);

        } elseif (!empty($p['data']['iconCls'])) {
            $data['iconCls'] = $p['data']['iconCls'];
        }

        $d = array(
            'user_id' => User::getId()
            ,'node_id' => $p['node_id']
            ,'data' => Util\jsonEncode($data)
        );

        $id = DM\Favorites::create($d);

        $rez = array(
            'success' => true
            ,'data' => array(
                'id' => $id
                ,'node_id' => $d['node_id']
                ,'data' => $data
            )
        );

        return $rez;
    }

    public function read($p)
    {
        $p = $p; //dummy codacy assignment
        $rez = array(
            'succes' => true
            ,'data' => array()
        );

        $rez['data'] = DM\Favorites::readAll();

        return $rez;
    }

    public function delete($nodeId)
    {
        $rez = array(
            'success' => DM\Favorites::deleteByNodeId($nodeId)
            ,'node_id' => $nodeId
        );

        return $rez;
    }
}
