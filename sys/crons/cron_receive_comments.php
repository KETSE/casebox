<?php
namespace CB;

use CB\DataModel as DM;

/**
 * this script is intended to be executed directly and it processes all cores at once
 * retreiving all comment mails from common or particulat mail for each core
 */

//init
ini_set('max_execution_time', 300);

error_reporting(E_ALL);

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$_SESSION['user'] = array(
    'id' => 1
    ,'name' => 'system'
);

$site_path = realpath(
    dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.
    DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'httpsdocs'
).DIRECTORY_SEPARATOR;

include $site_path.DIRECTORY_SEPARATOR.'config_platform.php';

require_once 'php-imap/src/PhpImap/IncomingMail.php';
require_once 'php-imap/src/PhpImap/Mailbox.php';

$cfg = Cache::get('platformConfig');

$mail_requirements = 'Mail requirements are:
    1. Subject of email : [$coreName #$nodeId] Comment: $nodeTitle ($nodePath)
    2. target nodeId should exist in the database.
    3. email address should be specified in casebox user profile.

    If at least one condition is not satisfied then the email would not be processed and deleted automatically.';

$mailServer = array();
$cfg = array();
$commonEmail = false;

// check if we have a common email server defined for all cores on this host

$platformConfig =  Cache::get('platformConfig');
if (!empty($platformConfig['comments_email'])) {
    $mailServer = array(
        'email' => $platformConfig['comments_email']
        ,'host' => $platformConfig['comments_host']
        ,'port' => $platformConfig['comments_port']
        ,'ssl' =>  in_array($platformConfig['comments_ssl'], array(true, 'true', 1, 'y', 'yes'), true)
        ,'user' => @$platformConfig['comments_user']
        ,'pass' => $platformConfig['comments_pass']
        // ,'common' => $platformConfig['comments_common'] // if we moved it to config.ini then its common by default
    );
} else { //backward compatibile check
    $res = DB\dbQuery(
        'SELECT `value`
        FROM `' . PREFIX . '_casebox`.config
        WHERE param = $1',
        'comments_config'
    );
    if ($r = $res->fetch_assoc()) {
        $mailServer = Util\jsonDecode($r['value']);
    }
    $res->close();
}

// select active cores
$res = DB\dbQuery(
    'SELECT id, name
    FROM `' . PREFIX . '_casebox`.cores
    WHERE `active` = 1',
    array()
);

while ($r = $res->fetch_assoc()) {
    $mailServer['cores'][$r['name']] = array();
}
$res->close();

$mailServers = array($mailServer);
// collect all new mails per each core
foreach ($mailServers as &$cfg) {
    try {
        $user = empty($cfg['user'])
            ? $cfg['email']
            : $cfg['user'];

        $mc = array(
            'host' => $cfg['host']
            ,'port' => $cfg['port']//Util\coalesce(@$cfg['port'], 993)
            ,'ssl' =>  (@$cfg['ssl'] == true)
            ,'user' => $user
            ,'password' => $cfg['pass']
        );

        $mailbox = new \PhpImap\Mailbox(
            '{' . $mc['host'] . ':' . $mc['port'] . '/imap' .
            ($mc['ssl'] ? '/ssl' : '') .'}INBOX',
            $user,
            $mc['password'],
            TEMP_DIR
        );

        // $mailCount = $mailbox->countMessages();
        $cfg['mailIds'] = $mailbox->searchMailbox('UNSEEN'); //NEW
        $mailCount = sizeof($cfg['mailIds']);

        if ($mailCount > 0) {
            $cfg['mailbox'] = &$mailbox;

            $rez = processMails($cfg);

            if (!empty($rez)) {
                echo $user . ' on ' . $mc['host'] . '. mail count: ' . $mailCount . $rez;
            }
        }

    } catch (\Exception $e) {
        // System::notifyAdmin('Casebox: check mail Exception for core' . $coreName, $e->getMessage());
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
        include $site_path.DIRECTORY_SEPARATOR.'lib/language.php';

        $templateIds = DM\Templates::getIdsByType('comment');

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

            // $emailTo = extractEmailFromText($mail['to']);  // <comments@casebox.org>
            $mailsTo = array_keys($mail['to']);
            $emailTo = array_shift($mailsTo);

            $userId = DM\Users::getIdByEmail($mail['fromAddress']);

            $_SESSION['user'] = array('id' => $userId);

            $data = array(
                'id' => null
                ,'pid' => $mail['pid']
                ,'oid' => $userId
                ,'cid' => $userId
                ,'template_id' => $templateId
                ,'data' => array(
                    '_title' => removeContentExtraBlock($mail['content'], $mail['fromAddress'], $emailTo)
                )
                ,'sys_data' => array(
                    'mailId' => $mail['id']
                )
            );

            try {
                $commentId = $commentsObj->create($data);

                //add attachments
                if (!empty($mail['attachments'])) {
                    saveObjectAttachments($commentId, $mail['attachments']);
                }
            } catch (Exception $e) {
                \CB\debug('Cannot create comment from ' . $mail['fromAddress'], $data);
            }

            $deleteMailIds[] = $mail['id'];
        }
    }

    if (!empty($mailConf['mailbox'])) {
        deleteMails($mailConf['mailbox'], $deleteMailIds);
    }

    // \CB\Solr\Client::runBackgroundCron();
}

function processMails(&$mailServer)
{
    $rez = '';

    $dids = array (); //array for deleted ids

    $newMails = 0;

    //iterate and process each mail
    foreach ($mailServer['mailIds'] as $id) {
         $mail = $mailServer['mailbox']->getMail($id);
        try {
            if (empty($mail->subject)) {
                continue;
            }
        } catch (\Exception $e) {
            $rez .= "Cant read this mail, probably empty subject.\n";
            continue;
        }

        $newMails++;

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
            $content = $mail->textPlain;
            $attachments = $mail->getAttachments();

            $mailServer['cores'][$matches[2]]['mails'][] = array(
                'id' => $id //$mailServer['mailbox']->getUniqueId($k)
                ,'pid' => $matches[3]
                ,'fromName' => $mail->fromName //$mail->from
                ,'fromAddress' => $mail->fromAddress //$mail->from
                ,'to' => $mail->to
                ,'toString' => $mail->toString  //$mail->to
                ,'date' => $date
                ,'subject' => $subject
                ,'content' => $content
                ,'attachments' => $attachments
            );
        } else {
            $dids[] = $id; //$mailServer['mailbox']->getUniqueId($k);
        }
    }

    $rez .= (
        ($newMails > 0)
        ? ("\nnew mails: " . $newMails . "\n")
        : ''
    );

    $rez .= deleteMails($mailServer['mailbox'], $dids);

    return $rez;
}

/**
 * remove "reply to" extra block from mail message
 * as well al signature block delimited by /\n--\n/
 * @param  varchar $content
 * @param  varchar $mailFrom  user email
 * @param  varchar $mailTo   CB comments email <comments@casebox.org>
 * @return varchar
 */
function removeContentExtraBlock($content, $emailFrom, $emailTo)
{
    $marker = 'W3HK8jpPmwaGCv';

    // quotation: > ...
    $content = preg_replace('/(^\w.+:\n)?(^>.*(\n|$))+/mi', '', $content);

    // quotation: "On Dec 5, 2014 2:06 AM, "John Doe" <comments@casebox.org> wrote:"
    // remove all starting with th line that contains the email in '<' '>'
    // Do this for both emails: From & To
    // because the user may hit "Reply" to his own email that he just sent
    // Example: he replies to <comments@casebox.org> and then again
    // hits reply to his own email, it will have <user@domain.com> in the reply text
    $content = preg_replace('/^(.+)\<' . preg_quote($emailFrom) . '\>/m', $marker, $content);
    $content = preg_replace('/^(.+)\<' . preg_quote($emailTo) . '\>/m', $marker, $content);

    // signature block
    $content = preg_replace('/^--(\s+)?$/m', $marker, $content);

    // remove everything starting with $marker
    $content = preg_replace('/' . $marker . '(.*)/s', '', $content);

    return trim($content);
}

// email: John Doe <user@domain.com>
// @return varchar; // just email
function extractEmailFromText($email)
{
    if (preg_match_all('/^[^<]*<?([^>]+)>?/i', $email, $results)) {
        $email = $results[1][0];
    }

    return $email;
}

function deleteMails(&$mailBox, $idsArray)
{
    $rez = '';
    if (empty($mailBox)) {
        throw new \Exception("Error Processing Request", 1);
    }

    if (empty($idsArray)) {
        return;
    }

    foreach ($idsArray as $id) {
        try {
            $mailBox->deleteMail($id);
        } catch (\Exception $e) {
            try {
                //$mailBox->getNumberByUniqueId()
                $mailBox->moveMessage($id, 'Trash');
            } catch (\Exception $e) {
                $rez .= " cant delete message $id\n";
            }
        }
    }

    $mailBox->expungeDeletedMails();

    return $rez;
}

//**********************************************************************************************************************

/**
 * save attachments array for a given object id
 * @param  int $objectId
 * @param  array &$attachments attachments array info
 * @return void
 */
function saveObjectAttachments($objectId, &$attachments)
{
    /**
     *   array (
            '1682242924670150914' =>
            PhpImap\IncomingMailAttachment::__set_state(array(
               'id' => '1682242924670150914',
               'name' => '120x150_bulls_on_parade.jpg',
               'filePath' => 'D:\\devel\\www\\casebox\\data\\tmp\\289_1682242924670150914_120x150_bulls_on_parade.jpg',
               'disposition' => 'attachment',
            )),
          ),
    */

    $filesApiObject = new \CB\Api\Files();

    foreach ($attachments as $d) {
        if (empty($d['disposition']) || ($d['disposition'] !== 'attachment')) {
            continue;
        }

        //call the api method
        $filesApiObject->upload(
            array(
                'pid' => $objectId
                ,'localFile' => $d['filePath']
                ,'oid' => \CB\User::getId()
                ,'filename' => $d['name']
                // ,'content-type' => $d['content-type']
                ,'fileExistAction' => 'autorename'
            )
        );

        unlink($d['name']);
    }
}

//**********************************************************************************************************************
function decodeSubject($str)
{
    preg_match_all("/=\?([^\?]*?)\?B\?([^\?]+)\?=(?:\s+)?/i", $str, $arr);
    for ($i=0; $i < count($arr[1]); $i++) {
        if (isset($arr[1][$i])&&$arr[1][$i]) {
            $CHARSET = $arr[1][$i];
            $str = str_replace(
                $arr[0][$i],
                iconv(
                    $CHARSET,
                    'UTF-8',
                    base64_decode($arr[2][$i])
                ),
                $str
            );
        }
    }

    return $str;
}
