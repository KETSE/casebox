<?php
namespace CB;

$cron_id = 'check_core_email';
$execution_timeout = 60; //default is 60 seconds

include 'init.php';

require_once ZEND_PATH.'/Zend/Loader/StandardAutoloader.php';

/** DURING INSTANTIATION **/
$loader = new \Zend\Loader\StandardAutoloader(
    array(
        // absolute directory
        'Zend' => ZEND_PATH.'/Zend'
    )
);
/** AFTER INSTANTIATION **/
$loader = new \Zend\Loader\StandardAutoloader();

// the path can be absolute or relative below:
$loader->registerNamespace('Zend', ZEND_PATH.'/Zend');

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

$res = DB\dbQuery('SELECT id FROM templates WHERE `type` = 8') or die(DB\dbQueryError());
if ($r = $res->fetch_row()) {
    $email_template_id = $r[0];
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
} catch (Exception $e) {
    notifyAdmin('Casebox: check mail Exception for core'.CORENAME, $e->getMessage());
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

    $user_id = false;
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
    if ($r = $res->fetch_row()) {
        $user_id = $r[0];
    }
    $res->close();
    if ($user_id == false) {
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
        $pid = User::getEmailFolderId($user_id);
    }

    /* end of try to get target folder from subject*/
    /* locate the corresponding folder in our database */
    if (empty($pid)) {
        echo 'processing path '.$path;
        $path = explode('/', $path);

        $rootFolderId = Browser::getRootFolderId();
        $rootFolderName = null;
        $sql = 'SELECT name FROM tree WHERE id = $1';
        $res = DB\dbQuery($sql, $rootFolderId) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $rootFolderName = $r[0];
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
                        $sql = 'select id from tree where pid = $1 and name = $2';
                        $res = DB\dbQuery($sql, array($lastPid, $path[$i])) or die(DB\dbQueryError());
                        if ($r = $res->fetch_row()) {
                            $lastPid = $r[0];
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
                        $sql = 'select id from tree where pid = $1 and name = $2';
                        $res = DB\dbQuery($sql, array($lastPid, $path[$i])) or die(DB\dbQueryError());
                        if ($r = $res->fetch_row()) {
                            $lastPid = $r[0];
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
        if (!$p['attachment']&&!$content) {
            $content = $p['content'];
        } else {
            $attachments[] = $p;
        }
    }
    /* end of get contents and attachments */

    /* creating email object in corresponding case and adding attachments if any */
    DB\dbQuery(
        'INSERT INTO tree (
            old_id
            ,pid
            ,user_id
            ,`type`
            ,name
            ,`date`
            ,cid
            ,uid
            ,template_id)
        VALUES ($1
              , $2
              , $3
              , 8
              , $4
              , $5
              , $3
              , $3
              , $4)',
        array(
            $mailbox->getUniqueId($k)
            ,$pid
            ,$user_id
            ,$subject
            ,$time
            ,config\default_folder_template
        )
    ) or die(DB\dbQueryError());

    $object_id = DB\last_insert_id();

    DB\dbQuery(
        'INSERT INTO objects (
            id
            ,`title`
            ,`custom_title`
            ,template_id
            ,date_start
            ,cid
            ,uid)
        VALUES ($1
              ,$2
              ,$2
              ,$3
              ,$4
              ,$5
              ,$5)',
        array(
            $object_id
            ,$subject
            ,$email_template_id
            ,$time
            ,$user_id
        )
    ) or die(DB\dbQueryError());

    $sql = 'INSERT INTO objects_data (
            object_id
            ,field_id
            ,duplicate_id
            ,`value`)
        SELECT $1
             , id
             , 0
             , $2
        FROM templates_structure
        WHERE template_id = $3
            AND name = $4';
    DB\dbQuery(
        $sql,
        array(
            $object_id
            ,$subject
            ,$email_template_id
            ,'_title'
        )
    ) or die(DB\dbQueryError());
    DB\dbQuery(
        $sql,
        array(
            $object_id
            ,$time
            ,$email_template_id
            ,'_date_start'
        )
    ) or die(DB\dbQueryError());
    DB\dbQuery(
        $sql,
        array(
            $object_id
            ,$content
            ,$email_template_id
            ,'_content'
        )
    ) or die(DB\dbQueryError());
    DB\dbQuery(
        $sql,
        array(
            $object_id
            ,$mail->from
            ,$email_template_id
            ,'from'
        )
    ) or die(DB\dbQueryError());

    if (!empty($attachments)) {
        foreach ($attachments as $a) {
            if (!$a['attachment']) {
                continue;
            }
                $tmp_name = tempnam(sys_get_temp_dir(), 'cbEml');
                file_put_contents($tmp_name, $a['content']);
                $f = array(
                    'tmp_name' => $tmp_name
                    ,'size' => filesize($tmp_name)
                    ,'date' => Util\date_mysql_to_iso($time)
                    ,'type' =>  finfo_file($finfo, $tmp_name)
                );
                $files->storeContent($f, FILES_PATH.$core['name'].DIRECTORY_SEPARATOR);

                DB\dbQuery(
                    'INSERT INTO tree (
                        pid
                        ,`name`
                        ,`type`
                        ,`date`
                        ,cid
                        ,uid
                        ,template_id)
                    VALUES($1
                         , $2
                         , 5
                         , $3
                         , $4
                         , $4
                         , $5) ',
                    array(
                        $object_id
                        ,$a['filename']
                        ,$time
                        ,$user_id
                        ,config\default_file_template
                    )
                ) or die(DB\dbQueryError());
                $file_id = DB\last_insert_id();

                DB\dbQuery(
                    'INSERT INTO files (
                        id
                        ,content_id
                        ,`date`
                        ,`name`
                        ,`title`
                        ,cid
                        ,uid
                        ,cdate
                        ,udate)
                    VALUES (
                        $1
                        ,$2
                        ,$3
                        ,$4
                        ,$5
                        ,$6
                        ,$6
                        ,CURRENT_TIMESTAMP
                        ,CURRENT_TIMESTAMP)',
                    array(
                        $file_id
                        ,$f['content_id']
                        ,$time
                        ,$a['filename']
                        ,''
                        ,$user_id
                    )
                ) or die(DB\dbQueryError());
        }
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

if ($i > 0) {
    SolrClient::runCron();
}
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
DB\dbQuery(
    'UPDATE crons
    SET last_end_time = CURRENT_TIMESTAMP, execution_info = $2
    WHERE cron_id = $1',
    array(
        $cron_id
        ,'ok'
    )
) or die(DB\dbQueryError());

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

//----------------------------------------------------------------------------------------------------------------------
function getMailContentAndAtachment($message)
{
    $foundParts = array();

    if ($message->isMultipart()) {
        foreach (new RecursiveIteratorIterator($message) as $part) {
            $headers = $part->getHeaders()->toArray();
            $datapart = array('content-type' => $part->getHeaderField('content-type'));
            try {
                $datapart['attachment'] = true;
                try {
                    $datapart['filename'] = decodeSubject($part->getHeaderField('content-disposition', 'filename'));
                    $datapart['filename'] = ($datapart['filename'] ? $datapart['filename'] : decodeSubject($part->getHeaderField('content-type', 'name')));
                } catch (Exception $e) {
                    $datapart['attachment'] = false;
                }
                // decode content
                $datapart['content'] = $part->getContent();

                if (isset($headers['Content-Transfer-Encoding'])) {
                    switch ($headers['Content-Transfer-Encoding']) {
                        case 'base64':
                            $datapart['content'] = base64_decode($datapart['content']);
                            break;
                        case 'quoted-printable':
                            $datapart['content'] = quoted_printable_decode($datapart['content']);
                            break;
                    }
                }
                //find the charset
                $charset = $part->getHeaderField('content-type', 'charset');
                if ($charset) {
                    $datapart['content'] = iconv($charset, 'UTF-8', $datapart['content']); //convert to utf8
                }
                array_push($foundParts, $datapart);
            } catch (Zend_Mail_Exception $e) {
                echo '' . $e;
                Zend_Debug::dump($e);
            }
        }
    } else {
        try {
            $headers = $message->getHeaders()->toArray();
            $datapart = array( 'attachment' => false, 'content' => $message->getContent() );
            // decode content
            if (isset($headers['Content-Transfer-Encoding'])) {
                switch ($headers['Content-Transfer-Encoding']) {
                    case 'base64':
                        $datapart['content'] = base64_decode($datapart['content']);
                        break;
                    case 'quoted-printable':
                        $datapart['content'] = quoted_printable_decode($datapart['content']);
                        break;
                }
            }
            //find the charset
            $charset = $message->getHeaderField('content-type', 'charset');
            if ($charset) {
                $datapart['content'] = iconv($charset, 'UTF-8', $datapart['content']); //convert to utf8
            }
            array_push($foundParts, $datapart);
        } catch (Zend_Mail_Exception $e) {
            echo '' . $e;
            Zend_Debug::dump($e);
        }
    }

    return $foundParts;
}
//----------------------------------------------------------------------------------------------------------------------
