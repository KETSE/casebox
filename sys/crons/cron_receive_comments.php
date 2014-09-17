<?php
namespace CB;

/**
 * this script is intended to be executed directly and it processes all cores at once
 * retreiving all comment mails from common or particulat mail for each core
 */

//init
ini_set('max_execution_time', 300);

error_reporting(E_ALL);

$_SERVER['REMOTE_ADDR'] = 'localhost';

$_SESSION['user'] = array(
    'id' => 1
    ,'name' => 'system'
);

$site_path = realpath(
    dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.
    DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'httpsdocs'
).DIRECTORY_SEPARATOR;

include $site_path.DIRECTORY_SEPARATOR.'config_platform.php';

require_once 'mail_functions.php';

$cfg = Cache::get('platformConfig');

$mail_requirements = 'Mail requirements are:
    1. Subject of email : [$coreName #$nodeId] Comment: $nodeTitle ($nodePath)
    2. target nodeId should exist in the database.
    3. email address should be specified in casebox user profile.

    If at least one condition is not satisfied then the email would not be processed and deleted automatically.';

$mailServers = array();
$commonEmail = false;

// check if we have a common email server defined for all cores on this host
// i.e. casebox.config table should contain comments_config with "common": true

$res = DB\dbQuery(
    'SELECT `value`
    FROM casebox.config
    WHERE param = $1',
    'comments_config'
) or die(DB\dbQueryError());
if ($r = $res->fetch_assoc()) {
    $cfg = json_decode($r['value']);
    if (!empty($t['common'])) {
        $commonEmail = $true;

        $mailServerId = $cfg['host'] . ' / ' . $cfg['email'];
        $mailServers[$mailServerId] = $cfg;
    }
}
$res->close();

// iterate cores and group cores by mail server

$res = DB\dbQuery(
    'SELECT id, name
    FROM `casebox`.cores
    WHERE `active` = 1',
    array()
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    if (!$commonEmail) {
        $_GET['core'] = $r['name'];

        $_SERVER['SERVER_NAME'] = $r['name'].'.casebox.org';

        include $site_path.DIRECTORY_SEPARATOR.'config.php';

        $cfg = Config::get('comments_config');

        if (empty($cfg)) {
            continue;
        }
    }

    $mailServerId = $cfg['host'] . ' / ' . $cfg['email'];
    if (empty($mailServers[$mailServerId])) {
        $mailServers[$mailServerId] = $cfg;
    }

    $mailServers[$mailServerId]['cores'][$r['name']] = array();
}
$res->close();

// collect all new mails per each core

foreach ($mailServers as &$cfg) {
    try {
        $user = empty($cfg['user'])
            // ? array_shift($user)
            ? $cfg['email']
            : $cfg['user'];

        $mailConf = array(
            'host' => $cfg['host']
            ,'port' => Util\coalesce(@$cfg['port'], 993)
            ,'ssl' =>  (@$cfg['ssl'] == true)
            ,'user' => $cfg['email']
            ,'password' => $cfg['pass']
        );

        $mailbox = new \Zend\Mail\Storage\Imap($mailConf);

        $mailCount = $mailbox->countMessages();
        echo $user . ' on ' . $mailConf['host'] . '. mail count: '.$mailCount."\n";

        if ($mailCount > 0) {
            $cfg['mailbox'] = &$mailbox;
            processMails($cfg);
        }

    } catch (\Exception $e) {
        // notifyAdmin('Casebox: check mail Exception for core' . $coreName, $e->getMessage());
        echo " Error connecting to email\n".$e->getMessage();
    }

}

// iterate each core and add comment items if there is smth

foreach ($mailServers as $mailConf) {
    $deleteMailIds = array();

    foreach ($mailConf['cores'] as $coreName => $core) {
        if (empty($core['mails'])) {
            continue;
        }

        $_GET['core'] = $coreName;

        $_SERVER['SERVER_NAME'] = $coreName.'.casebox.org';

        include $site_path.DIRECTORY_SEPARATOR.'config.php';
        include $site_path.DIRECTORY_SEPARATOR.'language.php';

        $templateIds = Templates::getIdsByType('comment');

        if (empty($templateIds)) {
            \CB\debug('receive comments cron: no comment template defined');
            continue;
        }

        $templateId = array_shift($templateIds);

        $commentsObj = Objects::getCustomClassByType('comment');

        foreach ($core['mails'] as $mail) {
            if (!Objects::idExists($mail['pid'])) {
                \CB\debug('receive comments cron: target id not found for mail "'.$mail['subject'].'"');
                continue;
            }

            $userId = getCoreUserByMail($mail['from']);
            $_SESSION['user'] = array('id' => $userId);

            $data = array(
                'id' => null
                ,'pid' => $mail['pid']
                ,'oid' => $userId
                ,'cid' => $userId
                ,'template_id' => $templateId
                ,'data' => array(
                    '_title' => removeContentExtraBlock($mail['content'], $mail['to'])
                )
                ,'sys_data' => array(
                    'mailId' => $mail['id']
                )
            );
            try {
                $commentsObj->create($data);
            } catch (Exception $e) {
                \CB\debug('Cannot create comment from ' . $mail['from'], $data);
            }

            $deleteMailIds[] = $mail['id'];
        }
    }

    if (!empty($mailConf['mailbox'])) {
        deleteMails($mailConf['mailbox'], $deleteMailIds);
    }

    \CB\Solr\Client::runBackgroundCron();
}

function processMails(&$mailServer)
{
    $dids = array (); //array for deleted ids

    $i = 0;
    echo "process mail .. \n";
    //iterate and process each mail
    foreach ($mailServer['mailbox'] as $k => $mail) {
        $i++;
        echo $i.' ';

        try {
            if ($mail->hasFlag(\Zend\Mail\Storage::FLAG_SEEN) || empty($mail->subject)) {
                continue;
            }
        } catch (\InvalidArgumentException $e) {
            echo "Cant read this mail, probably empty subject.\n";
            continue;
        }

        //Re: [dev #2841] New task: listeners when eupload file on casebox (/1/3-1/3-3/3-assignee/3-au_3)
        $subject = decodeSubject($mail->subject);

        preg_match('/(Re:\s*)?\[([^\s]+)\s+#(\d+)\]/', $subject, $matches);
        // $matches[2] - core name
        // $matches[3] - item id

        //if found core name and core name is registered for this mail server
        //then add email to "mails" per core, to be inserted later all together
        if (!empty($matches[2]) && isset($mailServer['cores'][$matches[2]])) {

            $date = strtotime($mail->date);
            $date = date('Y-m-d H:i:s', $date);

            /* get contents and attachments */
            $parts = getMailContentAndAtachment($mail);
            $content = null;
            $attachments = array();
            foreach ($parts as $p) {
                //content, filename, content-type
                if (!$p['attachment'] && !$content) {
                    $content = $p['content'];
                } else {
                    $attachments[] = $p;
                }
            }
            /* end of get contents and attachments */

            $mailServer['cores'][$matches[2]]['mails'][] = array(
                'id' => $mailServer['mailbox']->getUniqueId($k)
                ,'pid' => $matches[3]
                ,'from' => $mail->from
                ,'to' => $mail->to
                ,'date' => $date
                ,'subject' => $subject
                ,'content' => $content
                ,'attachments' => $attachments
            );
        } else {
            $dids[] = $mailServer['mailbox']->getUniqueId($k);
        }
    }

    deleteMails($mailServer['mailbox'], $dids);
}

/**
 * remove "reply to" extra block from mail message
 * as well al signature block delimited by /\n--\n/
 * @param  varchar $content
 * @param  varchar $mail
 * @return varchar
 */
function removeContentExtraBlock($content, $mail)
{
    //remove block that starts with email (that replied to)
    $idx = strpos($content, $mail);
    if ($idx == false) {
        return $content;
    }

    $content = substr($content, 0, $idx);
    $idx = strrpos($content, "\n");
    if ($idx !== false) {
        $content = substr($content, 0, $idx);
    } else {
        $idx = strrpos($content, "\r");
        if ($idx !== false) {
            $content = substr($content, 0, $idx);
        }
    }
    //remove quoted block
    $content = preg_replace('/(^\w.+:[\r\n])?(^>.*(\n|\r|$)){2,}/miu', '', $content);

    //remove signature block
    $content = preg_replace('/[\n\r]+--[\n\r]+.*/i', '', $content);

    return trim($content);
}

function getCoreUserByMail($email)
{
    /* try to find user from database that corresponds to given mail.
    Ex: Kell <kellaagnya@gmail.com> */

    if (preg_match_all('/^[^<]*<?([^>]+)>?/i', $email, $results)) {
        $email = $results[1][0];
    }

    $rez = false;

    $res = DB\dbQuery(
        'SELECT id
        FROM users_groups
        WHERE (`email` LIKE $1)
            OR (`email` LIKE $2)
            OR (`email` LIKE $3)',
        array(
            $email
            ,'%,'.$email
            ,$email.',%'
        )
    ) or die(DB\dbQueryError());

    if ($r = $res->fetch_assoc()) {
        $rez = $r['id'];
    }
    $res->close();

    return $rez;
}

function deleteMails(&$mailBox, $idsArray)
{
    if (empty($mailBox)) {
        throw new \Exception("Error Processing Request", 1);
    }

    if (empty($idsArray)) {
        return;
    }

    foreach ($idsArray as $id) {
        try {
            $mailBox->removeMessage($id);
        } catch (\Exception $e) {
            try {
                //$mailBox->getNumberByUniqueId()
                $mailBox->moveMessage($id, 'Trash');
            } catch (\Exception $e) {
                echo " cant delete message $id\n";
            }
        }
    }
}
