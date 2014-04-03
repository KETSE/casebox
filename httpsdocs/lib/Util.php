<?php
namespace CB\Util;

use CB\L as L;

function getIP()
{
    $ip = false;
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip != false) {
            array_unshift($ips, $ip);
            $ip = false;
        }
        $count = count($ips);
        // Exclude IP addresses that are reserved for LANs
        for ($i = 0; $i < $count; $i++) {
            if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    if (false == $ip && isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function getIPs()
{
    $ips = array();
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ips[] = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = array_merge($ips, explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ips[] = $_SERVER['REMOTE_ADDR'];
    }

    return implode('|', $ips);
}

function coalesce()
{
    $args = func_get_args();
    foreach ($args as $a) {
        if (!empty($a)) {
            return $a;
        }
    }

    return '';
}

/* date and time functions */

function formatPastTime($mysqlTime)
{
    if (empty($mysqlTime)) {
        return '';
    }
    $time = strtotime($mysqlTime);

    $time__ = date('j n Y', $time);

    if ($time__ == date('j n Y', time())) {
        return L\todayAt.' '.date('H:i', $time);
    } elseif ($time__ == date('j n Y', time()-3600 * 24)) {
        return L\yesterdayAt.' '.date('H:i', $time);
    } elseif ($time__ == date('j n Y', time()-3600 * 24 * 2)) {
        return L\beforeYesterdayAt.' '.date('H:i', $time);
    } else {
        return translateMonths(date('j M Y', $time).' '.L\at.' '.date(' H:i', $time));
    }
}

function formatAgoTime($mysqlTime)
{
    if (empty($mysqlTime)) {
        return '';
    }
    /*
    same day: few seconds ago/10 min ago /3 hours 30 min ago
    privous day: yesterday at 15:30
    same week: Tuesday at 12:20
    same year: November 8
    else: 2011, august 5

     */

    $AHOUR = 3600; // 60 seconds * 60 minutes
    $TODAY_START = strtotime('today');
    $YESTERDAY_START = strtotime('yesterday');
    $WEEK_START = strtotime('last Sunday');
    $YEAR_START = strtotime('1 January');

    $time = strtotime($mysqlTime);
    $interval = strtotime('now') - $time;//11003
    if ($interval < 0) {
        //it's a future time
        return L\fewSecondsAgo;
    }

    if ($interval < $AHOUR) {
        $m = intval($interval / 60);
        if ($m == 0) {
            return L\fewSecondsAgo;
        }
        if ($m < 2) {
            return $m.' '.L\minute.' '.L\ago;
        }

        return $m.' '.L\minutes.' '.L\ago;
    }
    if ($interval < ($time - $TODAY_START)) {
        $H = intval($interval/$AHOUR);
        if ($H < 2) {
            return $H.' '.L\hour.' '.L\ago;
        }

        return $H.' '.L\ofHours.' '.L\ago;
    }
    if ($interval < ($time - $YESTERDAY_START)) {
        return L\Yesterday.' '.L\at.' '.date('H:i', $time);
    }
    if ($interval < ($time - $WEEK_START)) {
        return translateDays(date('l', $time)).' '.L\at.' '.date('H:i', $time);
    }

    if ($interval < ($time - $YEAR_START)) {
        return translateMonths(date('d F', $time));
    }

    return translateMonths(date('Y, F d', $time));
}

function translateDays($dateString)
{
    /* replace long day names */
    $days_en = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    $days = explode(',', L\dayNames);
    $days = array_combine($days_en, $days);

    $dateString = strtr($dateString, $days);

    /* replace short day names */
    $days_en = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
    $days = explode(',', L\dayNamesShort);
    $days = array_combine($days_en, $days);

    $dateString = strtr($dateString, $days);

    return $dateString;
}

function translateMonths($dateString)
{
    /* replace long month names */
    $months_en = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $months = explode(',', L\monthNames);
    $months = array_combine($months_en, $months);

    $dateString = strtr($dateString, $months);

    /* replace short month names */
    $months_en = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $months = explode(',', L\monthNamesShort);
    $months = array_combine($months_en, $months);

    $dateString = strtr($dateString, $months);

    return $dateString;
}

function formatTaskTime($mysqlTime)
{
    $time = strtotime($mysqlTime);

    $time__ = date('j n Y', $time);
    $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

    if ($time == $today) {
        return '<span class="cM fwB">'.L\today.'</span>';
    } elseif ($today - $time > 3600 * 24 * 2) return translateMonths(date('j M Y', $time));
    elseif ($today - $time > 3600 * 24) return L\beforeYesterday;
    elseif ($today - $time > 0) return L\yesterday;
    elseif ($time - $today < 3600 * 24 * 2) return '<span class="cM fwB">'.L\tomorow.'</span>';
    elseif ($time - $today < 3600 * 24 * 6) return '<span class="cM fwB">'.(($time - $today) / (3600 * 24) ).' '.L\ofDays.'</span>';
    else{
        return translateMonths(date('j M Y', $time));
    }
}

/**
 * formats a dateTime period between two dates (without time). For ex.: Tue Apr 30, 2013 - 31
 * @param  varchar $fromDateTime mysql formated date
 * @param  varchar $toDateTime   mysql formated date
 * @return varchar               formated period
 */
function formatDatePeriod($fromDateTime, $toDateTime)
{
    $d1 = new \DateTime($fromDateTime);
    $d2 = new \DateTime($toDateTime);

    $rez = $d1->format('D M j, Y');

    $d2format = '';
    if ($d1->format('Y') != $d2->format('Y')) {
         $d2format = 'D M j, Y';
    } elseif ($d1->format('M') != $d2->format('M')) {
        $d2format = 'D M j';
    } elseif ($d1->format('j') != $d2->format('j')) {
        $d2format = 'D j';
    } elseif ($d1->format('D') != $d2->format('D')) {
        $d2format = 'D';
    }

    if (!empty($toDateTime) && !empty($d2format)) {
        $rez .= ' - '.$d2->format($d2format);
    }

    return $rez;
}

/**
 * formats a dateTime string according to user timezone from session for webdav
 * @param  varchar $fromDateTime mysql formated date
 * @return varchar               modified time if timezone present
 */
function UTCTimeToUserTimezone($dateTime)
{
    if (empty($dateTime) || empty($_SESSION['user']['cfg']['TZ'])) {
        return $dateTime;
    }

    $d = new \DateTime($dateTime);
    $d->setTimezone(new \DateTimeZone($_SESSION['user']['cfg']['TZ']));

    return $d->format('Y-m-d H:i:s');
}

/**
 * formats a dateTime string according to user timezone from session for webdav
 * @param  varchar $fromDateTime mysql formated date
 * @return varchar               modified time if timezone present
 */
function userTimeToUTCTimezone($dateTime)
{
    if (empty($dateTime) || empty($_SESSION['user']['cfg']['TZ'])) {
        return $dateTime;
    }

    $d = new \DateTime($dateTime, new \DateTimeZone($_SESSION['user']['cfg']['TZ']));
    $d->setTimezone('UTC');

    return $d->format('Y-m-d H:i:s');
}

/**
 * formats a dateTime period between two dates. For ex.: Tue Apr 30, 2013 00:10 - 01:10
 * @param  varchar $fromDateTime mysql formated date
 * @param  varchar $toDateTime   mysql formated date
 * @param  string $TZ           timezone
 * @return varchar               formated period
 */
function formatDateTimePeriod($fromDateTime, $toDateTime, $TZ = 'UTC')
{
    $d1 = new \DateTime($fromDateTime);
    if (empty($TZ)) {
        $TZ = 'UTC';
    }
    $d1->setTimezone(new \DateTimeZone($TZ));


    $rez = $d1->format('D M j, Y');
    $hourText = $d1->format('H:i');
    if ($hourText != '00:00') {
        $rez .= ' '.$hourText;
    }

    if (empty($toDateTime)) {
        return $rez;
    }
    $d2 = new \DateTime($toDateTime);
    $d2->setTimezone(new \DateTimeZone($TZ));

    $d2format = '';
    if ($d1->format('Y') != $d2->format('Y')) {
         $d2format = 'D M j, Y';
    } elseif ($d1->format('M') != $d2->format('M')) {
        $d2format = 'D M j';
    } elseif ($d1->format('j') != $d2->format('j')) {
        $d2format = 'D j';
    } elseif ($d1->format('D') != $d2->format('D')) {
        $d2format = 'D';
    }

    $hourText = $d2->format('H:i');

    if (($hourText != '00:00') || empty($d2format)) {
        $d2format .= (empty($d2format) ? '' : ', ').'H:i';
    }

    if (!empty($d2format)) {
        $rez .= ' - '.$d2->format($d2format);
    }

    return $rez;
}
function formatLeftDays($days_difference)
{
    if ($days_difference == 0) {
        return L\today;
    }
    if ($days_difference < 0) {
        return '';
    } elseif ($days_difference == 1) {
        return L\tomorow;
    } elseif ($days_difference <21) {
        return $days_difference.' '.L\ofDays;
    }

    return '';
}

function formatMysqlDate($date, $format = false, $TZ = 'UTC')
{
    if (empty($date)) {
        return '';
    }
    if (empty($TZ)) {
        $TZ = 'UTC';
    }
    if ($format == false) {
        $format = \CB\getOption('short_date_format');
    }

    $d1 = new \DateTime($date);

    $d1->setTimezone(new \DateTimeZone($TZ));


    $rez = $d1->format(str_replace('%', '', $format));

    return $rez;
}

function formatMysqlTime($date, $format = false)
{
    if (empty($date)) {
        return '';
    }
    if ($format == false) {
        $format = \CB\getOption('short_date_format').' '.\CB\getOption('time_format');
    }

    return date(str_replace('%', '', $format), strtotime($date));
}

function clientToMysqlDate($date)
{
    if (empty($date)) {
        return null;
    }
    $d = date_parse_from_format(
        str_replace(
            '%',
            '',
            \CB\getOption('short_date_format')
        ),
        $date
    );

    return $d['year'].'-'.$d['month'].'-'.$d['day'];
}
/* date and time functions */

function formatFileSize($v)
{
    if (!is_numeric($v)) {
        return '';
    }
    if ($v <= 0) {
        return  '0 KB';
    } elseif ($v < 1024) {
        return '1 KB';
    } elseif ($v < 1024 * 1024) {
        return round($v / 1024).' KB';
    } else {
        $n = $v / (1024 * 1024);

        return number_format($n, 2).' MB';
    }
}

function validId($id = false)
{
    return (!empty($id) && is_numeric($id) && ($id > 0));
}

function getLanguagesParams($post_params, &$result_params_array, &$values_string, &$on_duplicate_string, $default_text_value = null)
{
    if (is_array($post_params)) {
        $p = &$post_params;
    } else {
        $p = (array) $post_params;
    }
    $i = sizeof($result_params_array) + 1;
    for ($lidx=0; $lidx < sizeof($GLOBALS['languages']); $lidx++) {
        $l = 'l'.($lidx+1);
        $values_string .= (empty($values_string) ? '' : ',').'$'.$i;
        $on_duplicate_string .= (empty($on_duplicate_string) ? '' : ',').'`'.$l.'`=$'.$i++;
        $result_params_array[$l] = empty($p[$l]) ? $default_text_value: $p[$l];
    }
}

function adjustTextForDisplay($text)
{
    return htmlentities($text, ENT_COMPAT, 'UTF-8');
}

function dateISOToMysql($date_string)
{
    if (empty($date_string)) {
        return null;
    }
    $d = strtotime($date_string);

    return date('Y-m-d H:i:s.u', $d);
}

// function dateMysqlToISO($date_string)
// {
//     if (empty($date_string)) {
//         return null;
//     }

//     return str_replace(' ', 'T', $date_string).'Z';
// }

function dateMysqlToISO($date_string)
{
    if (empty($date_string)) {
        return null;
    }
    $d = strtotime($date_string);

    return date('Y-m-d\TH:i:s\Z', $d);
}

function getCoreHost($db_name = false)
{
    if ($db_name == false) {
        $db_name = \CB\CONFIG\DB_NAME;
    }
    $core = $db_name;
    if (substr($db_name, 0, 3) == 'cb_') {
        $core = substr($db_name, 3);
    }

    $server = 'https://'.
        (empty($_SERVER['SERVER_NAME'])
            ? 'casebox.org'
            : $_SERVER['SERVER_NAME']
        ).'/';

    $dev = \CB\isDevelServer() ? 'dev.' : '';

    $core = "https://$dev$server/$core/";

    return $core;
}

function toNumericArray($v)
{
    if (empty($v)) {
        return array();
    }
    if (!is_array($v)) {
        $v = explode(',', $v);
    }
    $v = array_filter($v, 'is_numeric');
    foreach ($v as $k => $w) {
        $w = trim($w);
        $iw = intval($w);
        if ($iw == $w) {
            $v[$k] = $iw;
        } else {
            $v[$k] = $w;
            settype($v[$k], 'float');
        }
    }

    return $v;
}

/**
 * convers a given variable to json array or empty array
 * @param  [type] $v [description]
 * @return [type]    [description]
 */
function toJSONArray($v)
{
    if (empty($v)) {
        return array();
    }
    if (is_array($v)) {
        return $v;
    }

    if (is_scalar($v)) {
        $v = json_decode($v, true);
    }
    if (empty($v)) {
        return array();
    }
    if (is_array($v)) {
        return $v;
    } elseif (is_object($v)) {
        $v = (Array) $v;
    }

    return $v;
}

function validISO8601Date($value)
{
    try {
        $timestamp = strtotime($value);
        $date = date(DATE_ISO8601, $timestamp);

        return ($date === $value);
    } catch (\Exception $e) {
        return false;
    }
}
