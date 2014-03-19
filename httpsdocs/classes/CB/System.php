<?php
namespace CB;

class System
{
    /**
     * get countries list with their phone codes
     *
     * this function returns an array of records for arrayReader
     *     first column is id
     *     second is name
     *     third is phone code
     * @return json response
     */
    public function getCountries()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT
                id
                ,name
                ,phone_codes
            FROM casebox.country_phone_codes
            ORDER BY name'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = array_values($r);
        }

        return array('success' => true, 'data' => $rez);
    }

    /**
     * get defined timezones
     *
     * returns an array of records for arrayReader
     * record contains two fields: caption, gmt offset
     * @return json response
     */
    public function getTimezones()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT caption, gmt_offset
            FROM casebox.zone
            ORDER BY gmt_offset, caption'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $offsetHours = floor(abs($r['gmt_offset'])/3600);
            $offsetMinutes = round((abs($r['gmt_offset']) - $offsetHours * 3600) / 60);
            if ($offsetMinutes == 60) {
                $offsetHours++;
                $offsetMinutes = 0;
            }
            $r['gmt_offset'] = ( ($r['gmt_offset'] < 0) ? '-': '+' )
                . ($offsetHours < 10 ? '0' : '') . $offsetHours
                . ':'
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
            $rez[] = array_values($r);
        }

        return array('success' => true, 'data' => $rez);
    }

    public static function getGmtOffset($timezone)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT gmt_offset
            FROM casebox.zone
            WHERE zone_name = $1',
            $timezone
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = intval($r['gmt_offset'] / 60);
        }
        $res->close();

        return $rez;
    }
}
