<?php

namespace Sabre\HTTP;

/**
 * HTTP utility methods
 *
 * @copyright Copyright (C) 2009-2014 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @author Paul Voegler
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Util {

    /**
     * Parses a RFC2616-compatible date string
     *
     * This method returns false if the date is invalid
     *
     * @param string $dateHeader
     * @return bool|DateTime
     */
    static function parseHTTPDate($dateHeader) {

        //RFC 2616 section 3.3.1 Full Date
        //Only the format is checked, valid ranges are checked by strtotime below
        $month = '(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
        $weekday = '(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)';
        $wkday = '(Mon|Tue|Wed|Thu|Fri|Sat|Sun)';
        $time = '[0-2]\d(\:[0-5]\d){2}';
        $date3 = $month . ' ([1-3]\d| \d)';
        $date2 = '[0-3]\d\-' . $month . '\-\d\d';
        //4-digit year cannot begin with 0 - unix timestamp begins in 1970
        $date1 = '[0-3]\d ' . $month . ' [1-9]\d{3}';

        //ANSI C's asctime() format
        //4-digit year cannot begin with 0 - unix timestamp begins in 1970
        $asctime_date = $wkday . ' ' . $date3 . ' ' . $time . ' [1-9]\d{3}';
        //RFC 850, obsoleted by RFC 1036
        $rfc850_date = $weekday . ', ' . $date2 . ' ' . $time . ' GMT';
        //RFC 822, updated by RFC 1123
        $rfc1123_date = $wkday . ', ' . $date1 . ' ' . $time . ' GMT';
        //allowed date formats by RFC 2616
        $HTTP_date = "($rfc1123_date|$rfc850_date|$asctime_date)";

        //allow for space around the string and strip it
        $dateHeader = trim($dateHeader, ' ');
        if (!preg_match('/^' . $HTTP_date . '$/', $dateHeader))
            return false;

        //append implicit GMT timezone to ANSI C time format
        if (strpos($dateHeader, ' GMT') === false)
            $dateHeader .= ' GMT';


        $realDate = strtotime($dateHeader);
        //strtotime can return -1 or false in case of error
        if ($realDate !== false && $realDate >= 0)
            return new \DateTime('@' . $realDate, new \DateTimeZone('UTC'));

    }

    /**
     * Transforms a DateTime object to HTTP's most common date format.
     *
     * We're serializing it as the RFC 1123 date, which, for HTTP must be
     * specified as GMT.
     *
     * @param \DateTime $dateTime
     * @return string
     */
    static function toHTTPDate(\DateTime $dateTime) {

        // We need to clone it, as we don't want to affect the existing
        // DateTime.
        $dateTime = clone $dateTime;
        $dateTime->setTimeZone(new \DateTimeZone('GMT'));
        return $dateTime->format('D, d M Y H:i:s \G\M\T');

    }

    /**
     * This method can be used to aid with content negotiation.
     *
     * It takes 2 arguments, the $acceptHeaderValue, which may come from Accept,
     * Accept-Language, Accept-Charset, Accept-Encoding, and an array of items
     * that the server can support.
     *
     * The result of this function will be the 'best possible option'. If no
     * best possible option could be found, null is returned.
     *
     * When it's null you can according to the spec either return a default, or
     * you can choose to emit 406 Not Acceptable.
     *
     * The method also accepts sending 'null' for the $acceptHeaderValue,
     * implying that no accept header was sent.
     *
     * @param string|null $acceptHeader
     * @param array $availableOptions
     * @return string|null
     */
    static function negotiate($acceptHeaderValue, array $availableOptions) {

        if (!$acceptHeaderValue) {
            // Grabbing the first in the list.
            return reset($availableOptions);
        }

        $proposals = explode(',' , $acceptHeaderValue);

        /**
         * This function loops through every element, and creates a new array
         * with 3 elements per item:
         * 1. mimeType
         * 2. quality (contents of q= parameter)
         * 3. index (the original order in the array)
         */
        array_walk(
            $proposals,

            function(&$value, $key) {

                $parts = explode(';', $value);
                $mimeType = trim($parts[0]);
                if (isset($parts[1]) && substr(trim($parts[1]),0,2)==='q=') {
                    $quality = substr(trim($parts[1]),2);
                } else {
                    $quality = 1;
                }

                $value = [$mimeType, $quality, $key];

            }
        );

        /**
         * This sorts the array based on quality first, and key-index second.
         */
        usort(
            $proposals,

            function($a, $b) {

                // If quality is identical, we compare the original index.
                if ($a[1]===$b[1]) {
                    // Indexes are ints, so we can just subtract
                    return $a[2] - $b[2];
                } else {
                    // Quality are floats, so we need to make sure we're
                    // returning the correct integers.
                    return $a[1] > $b[1]?-1:1;
                }

            }

        );

        // Now we're left with a correctly ordered Accept: header, so we can
        // compare it to the available mimetypes.
        foreach($proposals as $proposal) {

            // If it's */* it means 'anything will wdo'
            if ($proposal[0] === '*/*') {
                return reset($availableOptions);
            }

            foreach($availableOptions as $availableItem) {
                if ($availableItem===$proposal[0]) {
                    return $availableItem;
                }
            }

        }

    }

}
