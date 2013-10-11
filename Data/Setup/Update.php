<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Setup;

use Silex\Application;

class Update
{
    protected $app = null;

    /**
     * Check if the give column exists in the table
     *
     * @param string $table
     * @param string $column_name
     * @return boolean
     */
    protected function columnExists($table, $column_name)
    {
        try {
            $query = $this->app['db']->query("DESCRIBE `$table`");
            while (false !== ($row = $query->fetch())) {
                if ($row['Field'] == $column_name) return true;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Release 0.36
     */
    protected function release_036()
    {
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'basic_extension_catalog', 'release_status')) {
            // add release_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."basic_extension_catalog` ADD `release_status` VARCHAR(64) NOT NULL DEFAULT 'undefined' AFTER `release`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[BASIC Update] Add field `release_status` to table `basic_extension_catalog`');
        }

        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'basic_extension_register', 'release_status')) {
            // add field release_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."basic_extension_register` ADD `release_status` VARCHAR(64) NOT NULL DEFAULT 'undefined' AFTER `release`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('[BASIC Update] Add field `release_status` to table `basic_extension_register`');
        }
    }

    /**
     * Release 0.42
     */
    public function release_042(Application $app)
    {
        if (!file_exists(CMS_PATH.'/modules/kit_framework_search/VERSION')) {
            // the VERSION file exists since kitframework_search 0.11
            $app['filesystem']->copy(
                MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.42/search.php',
                CMS_PATH.'/modules/kit_framework_search/search.php',
                true);
            file_put_contents(CMS_PATH.'/modules/kit_framework_search/VERSION', '0.10.1');
            $app['monolog']->addInfo('BASIC Update] Changed kit_framework_search and added VERSION file');
        }
        if (file_exists(CMS_PATH.'/modules/kit_framework/VERSION')) {
            if (false === ($version = trim(file_get_contents(CMS_PATH.'/modules/kit_framework/VERSION')))) {
                throw new \Exception('Missing kit_framework VERSION file!');
            }
            if (version_compare('0.28', trim($version), '<=')) {
                $app['filesystem']->copy(
                    MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.42/output_interface.php',
                    CMS_PATH.'/modules/kit_framework/output_interface.php',
                    true);
            }
        }

    }

    /**
     * Update the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        $this->release_036();
        $this->release_042($app);

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}