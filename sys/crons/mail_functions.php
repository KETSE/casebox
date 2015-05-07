<?php

/**
 * save attachments array for a given object id
 * @param  int $objectId
 * @param  array &$attachments attachments array as from getMailContentAndAtachment
 * @return void
 */
function saveObjectAttachments($objectId, &$attachments)
{
    $filesApiObject = new \CB\Api\Files();

    foreach ($attachments as $d) {
        if (empty($d['attachment'])) {
            continue;
        }

        //safe content to a temporary file
        $tmpName = tempnam(sys_get_temp_dir(), 'cbMailAtt');
        file_put_contents($tmpName, $d['content']);

        //call the api method
        $filesApiObject->upload(
            array(
                'pid' => $objectId
                ,'localFile' => $tmpName
                ,'oid' => $_SESSION['user']['id']
                ,'filename' => $d['filename']
                ,'content-type' => $d['content-type']
                ,'fileExistAction' => 'autorename'
            )
        );
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
                } catch (\Exception $e) {
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
