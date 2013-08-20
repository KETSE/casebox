<?php
namespace CB\Util;

use CB\DB as DB;
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
        return ''; //it's a foture time
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
    //else
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
    //elseif ($time - $today < 3600 * 24) return '<span class="cM">'.L\today.'</span>';
    elseif ($time - $today < 3600 * 24 * 2) return '<span class="cM fwB">'.L\tomorow.'</span>';
    elseif ($time - $today < 3600 * 24 * 6) return '<span class="cM fwB">'.(($time - $today) / (3600 * 24) ).' '.L\ofDays.'</span>';
    else{
        return translateMonths(date('j M Y', $time));
    }
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

function formatMysqlDate($date, $format = false)
{
    if (empty($date)) {
        return '';
    }
    if ($format == false) {
        $format = $_SESSION['user']['cfg']['short_date_format'];
    }

    return date(str_replace('%', '', $format), strtotime($date));
    //return implode('.', array_reverse(explode('-', substr($date, 0, 10))));
}

function formatMysqlTime($date, $format = false)
{
    if (empty($date)) {
        return '';
    }
    if ($format == false) {
        $format = $_SESSION['user']['cfg']['short_date_format'].' '.$_SESSION['user']['cfg']['time_format'];
    }

    return date(str_replace('%', '', $format), strtotime($date));
    //return implode('.', array_reverse(explode('-', substr($date, 0, 10))));
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
            $_SESSION['user']['cfg']['short_date_format']
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

function getThesauriTitles($ids_string, $language_id = false)
{
    if (empty($ids_string)) {
        return '';
    }
    if ($language_id === false) {
        $language_id = \CB\USER_LANGUAGE_INDEX;
    }
    if (!is_array($ids_string)) {
        $a = explode(',', $ids_string);
    } else {
        $a = &$ids_string;
    }
    $a = array_filter($a, 'is_numeric');
    if (empty($a)) {
        return '';
    }
    $rez = array();
    foreach ($a as $id) {
        $var_name = "TH[$id]['name']";
        if (!Cache::exist($var_name)) {
            $res = DB\dbQuery(
                'SELECT l'.$language_id.'
                FROM tags
                WHERE id = $1',
                $id
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_row()) {
                Cache::set($var_name, $r[0]);
            }
            $res->close();
        }
        $rez[] = Cache::get($var_name);
    }

    if (sizeof($rez) == 1) {
        return $rez[0];
    }

    return $rez;
}

function getThesauryIcon($id)
{
    if (!is_numeric($id)) {
        return '';
    }

    $var_name = 'TH['.$id."]['icon']";

    if (!Cache::exist($var_name)) {
        $res = DB\dbQuery(
            'SELECT iconCls FROM tags WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            Cache::set($var_name, $r['iconCls']);
        }
        $res->close();
    }

    return Cache::get($var_name);
}

function getUsername($id)
{
    if (!is_numeric($id)) {
        return '';
    }
    $rez = '';
    $res = DB\dbQuery('select l'.\CB\USER_LANGUAGE_INDEX.' from users_groups where id = $1', $id) or die(DB\dbQueryError());
    if ($r = $res->fetch_row()) {
        $rez = $r[0];
    }
    $res->close();

    return $rez;
}

function dateISOToMysql($date_string)
{
    if (empty($date_string)) {
        return null;
    }
    //$date_string = '2004-02-12T15:19:21+00:00';
    $d = strtotime($date_string);

    return date('Y-m-d H:i:s.u', $d);
}

function dateMysqlToISO($date_string)
{
    if (empty($date_string)) {
        return null;
    }
    //$date_string = '2004-02-12T15:19:21+00:00';
    $d = strtotime($date_string);

    return date('Y-m-d\TH:i:s.u\Z', $d);
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
    switch ($core) {
        case 'cb2':
            $core = 'http://cb2.vvv.md/';
            break;
        default:
            $core = 'https://'.$core.'.casebox.org/';
            break;
    }

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

    return $v;
}
