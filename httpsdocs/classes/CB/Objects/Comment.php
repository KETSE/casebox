<?php
namespace CB\Objects;

use CB\Util;
use CB\User;
use CB\Objects;

class Comment extends Object
{

    /**
     * internal function used by create method for creating custom data
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
        return parent::create($p);
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

        //replace object references with links
        if (in_array('object', $replacements) && preg_match_all('/(.?)#(\d+)(.?)/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // check if not a html code
                if (($match[1] == '&') && ($match[3] == ';')) {
                    continue;
                }

                $templateId = Objects::getTemplateId($match[2]);
                $name = Objects::getName($match[2]);
                $name = (strlen($name) > 30)
                    ? substr($name, 0, 30) . '&hellip;'
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

        //replace users with their names
        if (in_array('user', $replacements) &&preg_match_all('/@([\w\.\-]+[\w])/', $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $userId = User::exists($match[1]);
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

        return $message;
    }
}
