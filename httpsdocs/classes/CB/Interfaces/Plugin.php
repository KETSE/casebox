<?php
namespace CB\Interfaces;

/**
 * CaseBox plugins interface
 */
interface Plugin
{

    /**
     * method for take actions on plugin installation
     *
     * create tables, prepeare directories, make compatibility checks etc.
     * @return void
     */
    public function install();

    /**
     * get plugin config
     *
     * method that should return an associative array with following defined peroperties:
     * css - an array of css files to include in client side
     * js - array of custom javascript files used by plugin
     * api - api methods that should be defined to be available on client side
     *     for accessing by custom js code
     * listeners - defined list of methods that should be called on specified events
     *
     * Note: all paths are considered relative to plugin root folder.
     *     Also double slash '//' could be used at the begining for casebox document root (httpsdocs).
     *
     *     Php classes that should be accessed by CaseBox for api and for events
     *     should be defined in correct namespace, according to PSR-0 standart.
     *     Main namespace should be exactly as plugin name (equal to plugin main folder name)
     *
     * Result Example:
     * array(
     *     'css' => array(
     *         '/plugin_subfolder/custom.css'
     *      )
     *      ,'js' => array(
     *          '/subfolder/custom_functionality.js'
     *          '/subfolder/init.js'
     *      )
     *      ,'api' => array(
     *          'PluginClass_with_full_namespace' => array(
     *              'methods' => array(
     *                  'methodName' => array('len' => 1) // number of params
     *              )
     *          )
     *      )
     *      ,"listeners" => array(
     *          'caseboxEventName' => array(
     *              'Namespace\\Subnamespace\\ClassName' => array('methodName')
     *          )
     *      )
     * )
     *
     * @return array
     */
    public function getConfig();

    public function isInstalled();
}
