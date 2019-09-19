<?php
namespace CB\data;

/**
 * sorters for item arrays using a sortField for sorting
 */
class Sorter
{
    // this property (field) should be set before call a sorter function
    public static $sortField = null;

    // asDate - Converts the value into Unix epoch time
    public static function asDateAsc($a, $b)
    {
        // if supposed to be mysql date strings then we can compare them as strings
        return Sorter::asStringAsc($a, $b);
    }

    public static function asDateDesc($a, $b)
    {
        return Sorter::asDateAsc($b, $a);
    }

    // asFloat - Converts the value to a floating point number
    public static function asFloatAsc($a, $b)
    {
        $a = @floatval($a[Sorter::$sortField]);
        $b = @floatval($b[Sorter::$sortField]);

        if ($a < $b) {
            return -1;
        }

        if ($a > $b) {
            return 1;
        }

        return 0;
    }

    public static function asFloatDesc($a, $b)
    {
        return Sorter::asFloatAsc($b, $a);
    }

    // asInt - Converts the value to an integer number
    public static function asIntAsc($a, $b)
    {
        $a = @intval($a[Sorter::$sortField]);
        $b = @intval($b[Sorter::$sortField]);

        if ($a < $b) {
            return -1;
        }

        if ($a > $b) {
            return 1;
        }

        return 0;
    }

    public static function asIntDesc($a, $b)
    {
        return Sorter::asIntAsc($b, $a);
    }

    // asText - Removes any tags and converts the value to a string
    public static function asTextAsc($a, $b)
    {
        $f = Sorter::$sortField;
        $a[$f] = @strip_tags($a[$f]);
        $b[$f] = @strip_tags($b[$f]);

        return Sorter::asStringAsc($a, $b);
    }

    public static function asTextDesc($a, $b)
    {
        return Sorter::asTextAsc($b, $a);
    }

    // asUCText - Removes any tags and converts the value to an uppercase string
    public static function asUCTextAsc($a, $b)
    {
        $f = Sorter::$sortField;
        $a[$f] = mb_strtoupper(@strip_tags($a[$f]));
        $b[$f] = mb_strtoupper(@strip_tags($b[$f]));

        return Sorter::asStringAsc($a, $b);
    }

    public static function asUCTextDesc($a, $b)
    {
        return Sorter::asUCTextAsc($b, $a);
    }

    public static function asStringAsc($a, $b)
    {
        $a = @$a[Sorter::$sortField];
        $b = @$b[Sorter::$sortField];

        if ($a < $b) {
            return -1;
        }

        if ($a > $b) {
            return 1;
        }

        return 0;
    }

    public static function asStringDesc($a, $b)
    {
        return Sorter::asStringAsc($b, $a);
    }

    // asUCString - Converts the value to an uppercase string
    public static function asUCStringAsc($a, $b)
    {
        $f = Sorter::$sortField;
        $a[$f] = @mb_strtoupper($a[$f]);
        $b[$f] = @mb_strtoupper($b[$f]);

        $rez = Sorter::asStringAsc($a, $b);

        return $rez;
    }

    public static function asUCStringDesc($a, $b)
    {
        return Sorter::asUCStringAsc($b, $a);
    }
}
