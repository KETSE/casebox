<?php
namespace CB;

use CB\DataModel as DM;

$cron_id = 'check_core_email';
$execution_timeout = 60; //default is 60 seconds

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

$coreName = Config::get('core_name');

require_once 'Zend/Loader/StandardAutoloader.php';

require_once 'mail_functions.php';

/** DURING INSTANTIATION **/
$loader = new \Zend\Loader\StandardAutoloader(
    array(
        // absolute directory
        'Zend' => ZEND_PATH.'Zend'
    )
);
/** AFTER INSTANTIATION **/
$loader = new \Zend\Loader\StandardAutoloader();

// the path can be absolute or relative below:
$loader->registerNamespace('Zend', ZEND_PATH.'Zend');

/** TO START AUTOLOADING */
$loader->register();

$mail_requirements = "
Mail requirements are:
    1. Subject of email should contain target folder in the following format: <Message title> (/target/folder)
    2. target folder should exist in the database.
    3. Your email address should be specified in your casebox user profile.

    If at least one condition is not satisfied then the email would not be processed and is deleted automatically.
";

// skip core if no email is set in config
if (!defined('config\mail_user')) {
    exit();
}

echo " (".config\mail_user.") ...";

$cd = prepareCron($cron_id, $execution_timeout);
if (!$cd['success']) {
    echo "\nFailed to prepare cron\n";
    exit(); //skip this core if cron preparation fails
}

/* check if this core has an email template defined */
$email_template_id = false;

$res = DB\dbQuery('SELECT id FROM templates WHERE `type` = \'email\'');
if ($r = $res->fetch_assoc()) {
    $email_template_id = $r['id'];
}
$res->close();

if (!$email_template_id) {
    echo " there is no Email template defined in this core.\n";
    continue;
}
/* end of check if this core has an email template defined */

try {
    $mailbox = new Zend\Mail\Storage\Imap(
        array(
            'host' => config\mail_host
            ,'port' => Util\coalesce(config\mail_port, 993)
            ,'ssl' =>  (config\mail_ssl == true)
            ,'user' => config\mail_user
            ,'password' => config\mail_password
        )
    );
} catch (\Exception $e) {
    System::notifyAdmin('Casebox: check mail Exception for core' . $coreName, $e->getMessage());
    echo " Error connecting to email\n";
    exit(); // skip this core if mail cannot be accesed
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$files = new Files();

$mail_count = $mailbox->countMessages();
echo ' Mail count: '.$mail_count."\n";

$delete_ids = array ();
$processed_ids = array ();
$i = 0;
foreach ($mailbox as $k => $mail) {
    $i++;
    echo $i.' ';

    if ($mail->hasFlag(Zend\Mail\Storage::FLAG_SEEN)) {
        continue;
    }

    $subject = decodeSubject($mail->subject);
    $subject = str_replace('Fwd: ', '', $subject);
    $pid = false;
    /* try to get target folder from subject*/
    $path = false; //case_nr

    /* try to find user from database that corresponds to this mail.
    Ex: Kell <kellaagnya@gmail.com> */
    $email = false;
    if (preg_match_all('/^[^<]*<?([^>]+)>?/i', $mail->from, $results)) {
        $email = $results[1][0];
    }
    if ($email == false) {
        $delete_ids[] = $mailbox->getUniqueId($k);
        echo "\rcannot find senders email for: $subject ... skipping";
        mail(
            $mail->from,
            'Error processing your email: '.$subject,
            '. We didn\'t find '.
            'your email in received message. '.$mail_requirements,
            'From: '.$core['mail_user'] . "\n\r"
        );
        continue;
    }

    $user_id = DM\Users::getIdByEmail($email);

    if (!empty($user_id)) {
        if (empty($test_user_id)) {
            $delete_ids[] = $mailbox->getUniqueId($k);
            mail(
                $mail->from,
                'Error processing your email: '.$subject,
                '. We didn\'t find your'.
                ' email address in our users database, please update your email '.
                'address in your user profile of casebox and resend your mail. '.
                'Wrong messages are deleted automatically.'.$mail_requirements,
                'From: '.$core['mail_user'] . "\n\r"
            );

            echo "\rcannot find corresponding user in our database '.
                'for email $email from message: $subject ... skipping";
            continue;
        } else {
            $user_id = $test_user_id;
        }
    }
    /* end of try to find user from database that corresponds to this mail */

    if (preg_match('/(\([\s]*(.+)[\s]*\))\s*$/i', $subject, $matches)) {
        $subject = str_replace($matches[0], '', $subject);
        $path = $matches[2];
    } else {
        /*STORE IN /<USER_ID>/Emails folder*/
        die('obsolete email destination detection.');
        // $pid = User::getEmailFolderId($user_id);
    }

    /* end of try to get target folder from subject*/
    /* locate the corresponding folder in our database */
    if (empty($pid)) {
        echo 'processing path '.$path;
        $path = explode('/', $path);

        $rootFolderId = Browser::getRootFolderId();
        $rootFolderName = null;
        $sql = 'SELECT name FROM tree WHERE id = $1';
        $res = DB\dbQuery($sql, $rootFolderId);
        if ($r = $res->fetch_assoc()) {
            $rootFolderName = $r['name'];
        }
        $res->close();
        while (!empty($path) && empty($path[0])) {
            array_shift($path);
        }
        while (!empty($path) && empty($path[sizeof($path)-1])) {
            array_pop($path);
        }

        //check if first folder name in specified path is equal to root name from tree
        if (empty($path)) {
            $pid = $rootFolderId;
        } else {
            $found = false;
            $lastPid = $rootFolderId;

            //trying to get path by excluding first element because it's equal to root foldre name
            if ($path[0] == $rootFolderName) {
                $found = true;
                $i = 1;
                while ($found && ($i < sizeof($path))) {
                    if (!empty($path[$i])) {
                        $sql = 'SELECT id FROM tree WHERE pid = $1 AND name = $2';
                        $res = DB\dbQuery($sql, array($lastPid, $path[$i]));
                        if ($r = $res->fetch_assoc()) {
                            $lastPid = $r['id'];
                        } else {
                            $found = false;
                        }
                        $res->close();
                    }
                    $i++;
                }
            } else {
                echo "\r root name is not equal to first element";
            }
            if (!$found) {
                $found = true;
                $lastPid = $rootFolderId;
                $i = 0;
                while ($found && ($i < sizeof($path))) {
                    if (!empty($path[$i])) {
                        $sql = 'SELECT id FROM tree WHERE pid = $1 AND name = $2';
                        $res = DB\dbQuery($sql, array($lastPid, $path[$i]));
                        if ($r = $res->fetch_assoc()) {
                            $lastPid = $r['id'];
                        } else {
                            $found = false;
                        }
                        $res->close();
                    }
                    $i++;
                }
            }
            if (!$found) {
                $delete_ids[] = $mailbox->getUniqueId($k);
                mail(
                    $mail->from,
                    'Error processing your email: '.$subject,
                    '. We didn\'t find the specified target folder, from the '.
                    'subject of your mail, in our database, please correct '.
                    'the subject and resend your email. Wrong messages are '.
                    'deleted automatically.'.$mail_requirements,
                    'From: '.$core['mail_user'] . "\n\r"
                );

                echo "\rcannot find corresponding folder in our database for: $subject ... skipping";
                continue;
            } else {
                $pid = $lastPid;
            }

        }

    }
    /* end of locate the corresponding folder in our database */

    /* get email date. Ex: Thu, 24 Feb 2011 22:22:10 +0300 /**/
    $time = strtotime($mail->date);
    $time = date('Y-m-d H:i:s', $time);
    /* end of get email date /**/

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

    /* creating email object in corresponding case and adding attachments if any */
    $obj = Objects::getCustomClassByType('email');
    $objectId = $obj->create(
        array(
            'pid' => $pid
            ,'user_id' => $user_id
            ,'name' => $subject
            ,'template_id' => $email_template_id
            ,'date' => $time
            ,'cid' => $user_id
            ,'data' => array(
                '_title' => $subject
                ,'_date_start' => $time
                ,'_content' => $content
                ,'from' => $mail->from
            )
            ,'sys_data' => array(
                'old_id' => $mailbox->getUniqueId($k)
            )
        )
    );

    if (!empty($attachments)) {
        saveObjectAttachments($objectId, $attachments);
    }

    /* end of creating email object in corresponding case and adding attachments if any */

    $mailbox->setFlags($k, array(Zend\Mail\Storage::FLAG_SEEN));
    $processed_ids[] = $mailbox->getUniqueId($k);

    /* keep alive each 10 messages*/
    if ($i == 10) {
        $i=0;
        $mailbox->noop(); // keep alive
    }
    /*end of keep alive each 10 messages*/
    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}

/* moving read messages from inbox to All Mail folder*/
$i = 0;
foreach ($processed_ids as $uniq_id) {
    $i ++;
    if ($i % 5 == 0) {
        $mailbox->noop(); // keep alive
    }
    $mailbox->moveMessage($mailbox->getNumberByUniqueId($uniq_id), '[Gmail]/All Mail'); //
    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}

// if ($i > 0) {
//     Solr\Client::runCron();
// }
/* end of moving read messages from inbox to All Mail folder*/

/* deleting unprocessed messages from inbox*/
$i = 0;
foreach ($delete_ids as $uniq_id) {
    $i ++;
    if ($i % 5 == 0) {
        $mailbox->noop(); // keep alive
    }
    $mailbox->moveMessage($mailbox->getNumberByUniqueId($uniq_id), '[Gmail]/Trash'); //
    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
}
/* end of moving read messages from inbox to All Mail folder*/

// closeCron($cron_id);
