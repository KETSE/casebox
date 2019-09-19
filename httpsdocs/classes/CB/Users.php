<?php
namespace CB;

use CB\DataModel as DM;

class Users
{
    /**
     * get user colors
     * @return array associative array of userId => color
     */
    public static function getColors()
    {
        $rez = array();

        $uid = User:: getId();

        $recs = DM\UsersGroups::readAll();

        $noColors = array();

        foreach ($recs as &$r) {
            if (empty($r['cfg']['color']) && ($r['type'] == 2)) {
                $noColors[] = &$r;
            }
        }

        if (!empty($noColors)) {
            $colors = \Colors\RandomColor::many(
                sizeof($noColors),
                array(
                    'luminosity'=>'random'
                    ,'hue'=>'random'
                )
            );

            foreach ($noColors as &$r) {
                $r['cfg']['color'] = array_shift($colors);
                User::setUserConfigParam('color', $r['cfg']['color'], $r['id']);
            }
            unset($r);
        }

        foreach ($recs as $r) {
            $rez[$r['id']] = empty($r['cfg']['customColor'][$uid])
                ? @$r['cfg']['color']
                : $r['cfg']['customColor'][$uid];
        }

        return $rez;
    }
}
