<?php
namespace CB\Objects;

use CB\Util;
use CB\User;
use CB\Objects;
use CB\Log;
use CB\DataModel as DM;

class Comment extends Object
{

    /**
     * create method
     * @return void
     */
    public function create($p = false)
    {
        if ($p === false) {
            $p = &$this->data;
        }

        // if (!empty($p['data']['_title'])) {
        //     //all data is html escaped when indexed in solr
        //     //so no need to encode it here
        //     $msg = $this->processAndFormatMessage($p['data']['_title']);
        //     $p['name'] = $msg;
        //     $p['data']['_title'] = $msg;
        // }

        //disable default log from parent Object class
        //we'll set comments add as comment action for parent
        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::create($p);

        \CB\Config::setFlag('disableActivityLog', false);

        $this->updateParentFollowers();

        $this->logAction(
            'comment',
            array(
                'new' => $this->getParentObject()
                ,'comment' => $p['data']['_title']
                ,'mentioned' => $this->lastMentionedUserIds
            )
        );

        return $rez;
    }

    /**
     * update comment
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        //disable default log from parent Object class
        //we'll set comments add as comment action for parent
        \CB\Config::setFlag('disableActivityLog', true);

        $rez = parent::update($p);

        \CB\Config::setFlag('disableActivityLog', false);

        $p = &$this->data;

        $this->updateParentFollowers();

        $this->logAction(
            'comment_update',
            array(
                'new' => Objects::getCachedObject($p['pid'])
                ,'comment' => $p['data']['_title']
                ,'mentioned' => $this->lastMentionedUserIds
            )
        );

        return $rez;

    }

    /**
     * method to collect solr data from object data
     * according to template fields configuration
     * and store it in sys_data onder "solr" property
     * @return void
     */
    protected function collectSolrData()
    {
        $rez = array();

        // parent::collectSolrData();
        //
        if (!empty($this->data['data']['_title'])) {
            $rez['content'] = $this->data['data']['_title'];

        }

        $this->data['sys_data']['solr'] = $rez;
    }

    /**
     * function to update parent followers when adding a comment
     * with this user and referenced users from comment
     * @return void
     */
    protected function updateParentFollowers()
    {
        $p = &$this->data;

        $po = $this->getParentObject();
        $posd = $po->getSysData();

        $newUserIds = array();

        $posd['lastComment'] = array(
            'user_id' => User::getId()
            ,'date' => Util\dateMysqlToISO('now')
        );

        $wu = empty($posd['wu'])
            ? array()
            : $posd['wu'];
        $uid = User::getId();

        if (!in_array($uid, $wu)) {
            $newUserIds[] = intval($uid);
        }

        //analize comment text and get referenced users
        $this->lastMentionedUserIds = Util\getReferencedUsers($p['data']['_title']);
        foreach ($this->lastMentionedUserIds as $uid) {
            if (!in_array($uid, $wu)) {
                $newUserIds[] = $uid;
            }
        }

        //update only if new users added
        if (!empty($newUserIds)) {
            $wu = array_merge($wu, $newUserIds);
            $wu = Util\toNumericArray($wu);

            $posd['wu'] = array_unique($wu);

        }

        //always update sys_data to change lastComment date
        $po->updateSysData($posd);
    }

    /**
     * process a message:
     *     - replace urls with links
     *     - replace object references with links
     * @param varchar $message
     */
    public static function processAndFormatMessage($message, $replacements = 'user,object,url')
    {
        if (empty($message)) {
            return $message;
        }

        $replacements = Util\toTrimmedArray($replacements);

        // replace urls with links
        if (in_array('url', $replacements)) {
            $message = \Kwi\UrlLinker::getInstance()->linkUrlsAndEscapeHtml($message);
        }

        //replace users with their names
        //doing replace before object reference replacements because object titles can contain user refs
        if (in_array('user', $replacements) &&preg_match_all('/@([\w\.\-]+[\w])/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $userId = DM\Users::getIdByName($match[1]);
                if (is_numeric($userId)) {
                    $userName = $match[1];
                    $message = str_replace(
                        $match[0],
                        '<span class="cDB user-ref" title="' . User::getDisplayName($userId) . '">@' . $userName . '</span>',
                        $message
                    );
                }
            }
        }

        //replace object references with links
        if (in_array('object', $replacements) && preg_match_all('/(.?)#(\d+)(.?)/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // check if not a html code
                if (($match[1] == '&') && ($match[3] == ';')) {
                    continue;
                }

                $templateId = Objects::getTemplateId($match[2]);
                $obj = Objects::getCachedObject($match[2]);

                $name = empty($obj)
                    ? ''
                    : $obj->getHtmlSafeName();

                $name = (strlen($name) > 30)
                    ? mb_substr($name, 0, 30) . '&hellip;'
                    : $name;

                $message = str_replace(
                    $match[0],
                    $match[1] .
                    '<a class="click obj-ref" itemid="' . $match[2] .
                    '" templateid= "' . $templateId .
                    '" title="' . $name . '"' .
                    '>#' . $match[2] . '</a>' .
                    $match[3],
                    $message
                );
            }
        }

        return $message;
    }
}
