<?php
namespace CB\Objects;

use CB\DataModel as DM;
use CB\Objects;
use CB\User;
use CB\Util;
use CB\Log;

/**
 * class for casebox files objects
 */
class File extends Object
{

    /**
     * create method
     * @return void
     */
    public function create($p = false)
    {
        //disable default log from parent Object class
        //we'll set comments add as comment action for parent

        $disableLogFlag = \CB\Config::getFlag('disableActivityLog');

        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::create($p);

        \CB\Config::setFlag('disableActivityLog', $disableLogFlag);

        $p = &$this->data;

        $this->parentObj = Objects::getCachedObject($p['pid']);

        $this->updateParentFollowers();

        $this->logAction(
            'file_upload',
            array(
                'file' => array(
                    'id' => $p['id'],
                    'name' => $p['name']
                )
            )
        );

        return $rez;
    }

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        DM\Files::create(
            array(
                'id' => $this->id
                ,'content_id' => @$this->data['content_id']
                ,'date' => @$this->data['date']
                ,'name' => @$this->data['name']
                ,'cid' => @$this->data['cid']
            )
        );
    }

    /**
     * load custom data for $this->id
     *
     * @return void
     */
    protected function loadCustomData()
    {

        parent::loadCustomData();

        $d = &$this->data;

        $cd = DM\Files::getContentData($this->id);

        if (!empty($cd)) {
            $d['content_id'] = $cd['id'];
            $d['size'] = $cd['size'];
            $d['pages'] = $cd['pages'];
            $d['content_type'] = $cd['type'];
            $d['content_path'] = $cd['path'];
            $d['md5'] = $cd['md5'];
        }

        $this->data['versions'] = DM\FilesVersions::getFileVersions($this->id);
    }

    /**
     * update file
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        //disable default log from parent Object class
        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::update($p);

        \CB\Config::setFlag('disableActivityLog', false);

        $p = &$this->data;

        $this->logAction(
            'file_update',
            array(
                'file' => array(
                    'id' => $p['id'],
                    'name' => $p['name']
                )
            )
        );

        return $rez;

    }

    /**
     * update objects custom data
     * @return void
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

        $updated = DM\Files::update(
            array(
                'id' => $this->id
                ,'content_id' => @$this->data['content_id']
                ,'date' => @$this->data['date']
                ,'name' => @$this->data['name']
                ,'cid' => @$this->data['cid']
                ,'uid' => User::getId()
            )
        );

        //create record if doesnt exist yet
        if (!$updated) {
            DM\Files::create(
                array(
                    'id' => $this->id
                    ,'content_id' => @$this->data['content_id']
                    ,'date' => @$this->data['date']
                    ,'name' => @$this->data['name']
                    ,'cid' => @$this->data['cid']
                )
            );
        }
    }

    /**
     * method to collect solr data from object data
     * according to template fields configuration
     * and store it in sys_data onder "solr" property
     * @return void
     */
    protected function collectSolrData()
    {
        parent::collectSolrData();

        $sd = &$this->data['sys_data']['solr'];

        $r = DM\Files::getSolrData($this->id);

        if (!empty($r)) {
            $sd['size'] = $r['size'];
            $sd['versions'] = intval($r['versions']);
        }
    }

    /**
     * copy costom files data to targetId
     * @param  int  $targetId
     * @return void
     */
    protected function copyCustomDataTo($targetId)
    {
        DM\Files::copy(
            $this->id,
            $targetId
        );
    }

    /**
     * function to update parent followers when uploading a file
     * with this user
     * @return void
     */
    protected function updateParentFollowers()
    {
        $posd = $this->parentObj->getSysData();

        $newUserIds = array();

        $wu = empty($posd['wu'])
            ? array()
            : $posd['wu'];
        $uid = User::getId();

        if (!in_array($uid, $wu)) {
            $newUserIds[] = intval($uid);
        }

        //update only if new users added
        if (!empty($newUserIds)) {
            $wu = array_merge($wu, $newUserIds);
            $wu = Util\toNumericArray($wu);

            $posd['wu'] = array_unique($wu);

            $this->parentObj->updateSysData($posd);
        }
    }
}
