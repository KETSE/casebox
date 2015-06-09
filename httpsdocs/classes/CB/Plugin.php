<?php
namespace CB;

use CB\DB;

class Plugin implements Interfaces\Plugin
{
    protected $name = '';
    protected $config = array();

    public function __construct($name)
    {
        $this->name = $name;
        $this->config = $this->getConfig();
    }

    public function getConfig()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT cfg FROM ' . PREFIX . '_casebox.plugins WHERE name = $1',
            $this->name
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $rez = Util\toJSONArray($r['cfg']);
        }
        $res->close();

        return $rez;
    }

    public function setConfig($cfg)
    {
        $this->config = $cfg;
        DB\dbQuery(
            'UPDATE ' . PREFIX . '_casebox.plugins SET `cfg` = $2 WHERE name = $1',
            array(
                $this->name
                ,Util\jsonEncode($cfg)
            )
        ) or die(DB\dbQueryError());
    }

    public function install()
    {
        if ($this->isInstalled()) {
            return false;
        }

        DB\dbQuery(
            'INSERT INTO ' . PREFIX . '_casebox.plugins (name, cfg)
            VALUES($1, $2)',
            array(
                $this->name
                ,Util\jsonEncode($this->config)
            )
        ) or die(DB\dbQueryError());

        return true;
    }

    public function uninstall()
    {
        DB\dbQuery(
            'DELETE from ' . PREFIX . '_casebox.plugins where name = $1',
            $this->name
        ) or die(DB\dbQueryError());

    }

    public function isInstalled()
    {
        $rez = false;
        $res = DB\dbQuery(
            'SELECT name FROM ' . PREFIX . '_casebox.plugins WHERE name = $1',
            $this->name
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $rez = true;
        }
        $res->close();

        return $rez;
    }

    public function isActive()
    {
        $rez = false;
        $res = DB\dbQuery(
            'SELECT `active` FROM ' . PREFIX . '_casebox.plugins WHERE name = $1',
            $this->name
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $rez = ($r['active'] == 1);
        }
        $res->close();

        return $rez;
    }

    public function enable()
    {
        DB\dbQuery(
            'UPDATE ' . PREFIX . '_casebox.plugins SET active` = 1 WHERE name = $1',
            $this->name
        ) or die(DB\dbQueryError());
    }

    public function disable()
    {
        DB\dbQuery(
            'UPDATE ' . PREFIX . '_casebox.plugins SET active` = 0 WHERE name = $1',
            $this->name
        ) or die(DB\dbQueryError());
    }
}
