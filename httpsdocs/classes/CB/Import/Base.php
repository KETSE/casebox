<?php
namespace CB\Import;

use CB\Cache;
use CB\DB;
use CB\Objects;
use CB\Solr;
use CB\Util;
use CB\Import\BareBoneModel as BBM;

/**
 * base class to reflect main logic for import external data into a casebox core
 *
 */

class Base
{
    protected $cfg = array(
        'db_user' => 'root'
        ,'db_pass' => ''
        ,'db_name' => 'none'

        ,'source_db_name' => 'unknown'

        ,'overwrite_existing_core_db' => 'y' // overwrite target db on each import
        ,'core_solr_overwrite' => 'n'  // skip recreating solr core
        ,'core_solr_reindex' => 'n'   //skip reindexing solr, we'll do it at the end
    );

    public function __construct($config = false)
    {
        //define path to barebone sql file
        $this->docRootDirectory = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR;

        $cbRoot = dirname($this->docRootDirectory) . DIRECTORY_SEPARATOR;

        $this->binDirectory = $cbRoot . 'bin' . DIRECTORY_SEPARATOR;

        $this->bareBoneCoreSql = $cbRoot . 'install/mysql/bare_bone_core.sql';

        if (!empty($config)) {
            $this->cfg = array_merge($this->cfg, $config);
        }
    }

    /**
     * method to implement custom logic and create final config
     * @param  array $cfg optional
     * @return array
     */
    protected function getConfig($cfg = false)
    {
        $rez = $this->cfg;

        if ($cfg !== false) {
            $rez = array_merge($rez, $cfg);
        }

        $platformConfig = Cache::get('platformConfig');

        $rez = array_merge($platformConfig, $rez);

        //here we can ask for su_db_user and pass
        //
        //we are considering that source and target db are on the same
        //mysql server, otherwise source db options should be set to be used later
        //
        return $rez;
    }

    /**
     * prepare target db or create it from barebone
     * @return void
     */
    protected function prepareTargetDb()
    {
        $cfg = $this->cfg;

        if (!empty($cfg['importSql'])) {
            Cache::set('RUN_SETUP_CFG', $this->cfg);

            $options = array(
                'core' => $cfg['core_name']
                ,'sql' => $cfg['importSql']
            );

            $options = $options; //dummy codacy assignment

            include $this->binDirectory . 'core_create.php';
        }
    }

    /**
     * init cb with created core
     * @return void
     */
    public function initTargetCore()
    {
        $cfg = $this->cfg;

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $_GET['core'] = $cfg['core_name'];

        $_SESSION['user'] = array('id' => 1);

        include $this->docRootDirectory . 'config.php';

        ini_set('max_execution_time', 0);

        error_reporting(E_ALL);

        require_once $this->docRootDirectory . 'lib/language.php';

        \CB\L\initTranslations();
    }

    /**
     * connect to source db
     *
     * we consider source db available on the same server with target db
     *
     * @return void
     */
    protected function connectSourceDb()
    {
    }

    /**
     * init importing process
     * @return void
     */
    protected function init()
    {
        $this->cfg = $this->getConfig();

        echo "preparing target DB: \n";
        $this->prepareTargetDb();
        echo "Ok\n";

        echo "\nInitializing target core ..";
        $this->initTargetCore();
        echo " Ok\n";

        $this->connectSourceDb();
    }

    /**
     * executing preimporting changes to target core
     * @return void
     */
    protected function prepare()
    {

    }

    /**
     * executing import
     * @return void
     */
    protected function execute()
    {
    }

    /**
     * executing postimporting adjustments to target core
     * @return void
     */
    protected function adjust()
    {
    }

    /**
     * reindex solr core if needed
     * @return void
     */
    protected function reindex()
    {
        $solr = new Solr\Client;
        $solr->updateTree(
            array(
                'all' => true
                ,'nolimit' => true
            )
        );
    }

    /**
     * start importing process
     * @return void
     */
    public function import()
    {
        \CB\Config::setFlag('disableSolrIndexing', true);
        \CB\Config::setFlag('disableActivityLog', true);
        \CB\Config::setFlag('disableTriggers', true);

        DB\startTransaction();

        echo "\nInitializing .. \n____________________________\n";

        $this->init();
        echo "\nOk\n";

        echo "\nPreparing .. \n____________________________\n";
        $this->prepare();
        echo "\nOk\n";

        echo "\nExecuting .. \n____________________________\n";
        $this->execute();
        echo "\nOk\n";

        echo "\nAdjusting .. \n____________________________\n";
        $this->adjust();
        echo "\nOk\n";

        \CB\Config::setFlag('disableTriggers', false);
        \CB\Config::setFlag('disableActivityLog', false);
        \CB\Config::setFlag('disableSolrIndexing', false);

        DB\commitTransaction();

        echo "\nReindexing .. \n____________________________\n";
        $this->reindex();
        echo "\nOk\n";
    }

    /**
     * create templates defined in config
     * By default this method is not colled in this base class
     * Call it in descendant classes if needed
     * @return void
     */
    protected function addCustomTemplates()
    {
        $o = new \CB\Objects\Template();
        $tf = new \CB\Objects\TemplateField();

        foreach ($this->cfg['templates'] as $k => $v) {
            echo "creating template '$k' .. ";

            $v['id'] = null;
            $v['pid'] = BBM::$cfg['templatesFolderId'];
            $v['template_id'] = BBM::$cfg['templatesTemplateId'];

            //create correct data
            if (empty($v['name'])) {
                $v['name'] = $k;
            }

            $type = empty($v['type'])
                ? 'object'
                : $v['type'];

            $data = array(
                '_title' => $k
                ,'en' => $v['name']
                ,'type' => $type
                ,'visible' => 1
            );

            if (!empty($v['iconCls'])) {
                $data['iconCls'] = $v['iconCls'];
            }
            if (!empty($v['cfg'])) {
                $data['cfg'] = $v['cfg'];
            }
            if (!empty($v['title_template'])) {
                $data['title_template'] = $v['title_template'];
            }

            $v['data'] = $data;

            $fields = empty($v['fields'])
                ? array()
                : $v['fields'];

            unset($v['fields']);

            $id = $o->create($v);

            $this->templateIds[$k] = $id;

            // analize fields
            // ,'country' => array(
            //             'en' => 'Country'
            //             ,'type' => '_objects'
            //             ,'cfg' => array(
            //                 'source' => 'tree'
            //                 ,'scope' => '/Country'
            //             )
            //         )

            $i = 1;
            foreach ($fields as $fn => $fv) {
                //check if we have field reference
                if (is_scalar($fv)) {
                    $fn = $fv;
                    $fv = $this->cfg['templateFields'][$fn];
                }

                $fv['id'] = null;
                $fv['pid'] = $id;
                $fv['template_id'] = BBM::$cfg['fieldTemplateId'];

                $name = empty($fv['en'])
                    ? $fn
                    : $fv['en'];

                $type = empty($fv['type'])
                    ? 'varchar'
                    : $fv['type'];

                $order = empty($fv['order'])
                    ? $i++
                    : $fv['order'];

                $data = array(
                    'name' => $fn
                    ,'_title' => $fn
                    ,'en' => $name
                    ,'type' => $type
                    ,'order' => $order
                );

                if (!empty($fv['solr_column_name'])) {
                    $data['solr_column_name'] = $fv['solr_column_name'];
                }

                $cfg = empty($fv['cfg'])
                    ? array()
                    : $fv['cfg'];

                if (!empty($cfg['scope']) && substr($cfg['scope'], 0, 1) == '/') {
                    $cfg['scope'] = $this->thesauriIds[$cfg['scope']]['id'];
                }

                if (!empty($cfg)) {
                    $fv['cfg'] = $cfg;
                    $data['cfg'] = Util\jsonEncode($cfg);
                }

                $fv['name'] = $fn;
                $fv['type'] = $type;
                $fv['order'] = $order;
                $fv['data'] = $data;

                $tf->create($fv);
            }

            /*
            {"_title":"assigned"
            ,"en":"Assigned"
            ,"type":"_objects"
            ,"order":7
            ,"cfg":"{
                 \"editor\": \"form\"
                ,\"source\": \"users\"
                ,\"renderer\": \"listObjIcons\"
                 ,\"autoLoad\": true
                 ,\"multiValued\": true
                 ,\"hidePreview\": true\n}"
            }
             */

            echo "Ok\n";
        }
    }

    protected function getMenuSeparatorId()
    {
        return Objects::getChildId(
            1,
            array('Tree', 'System', 'Templates', 'Built-in', 'Menu', '- Menu separator -')
        );

    }
}
