<?php
/**
 * ExportDatabaseCommand.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   sweelix.yii1.ext.commands
 */

namespace sweelix\yii1\ext\commands;

/**
 * This command dump the database into a migration
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   sweelix.yii1.ext.commands
 * @since     2.0.0
 */
class ExportDatabaseCommand extends \CConsoleCommand {
	public static $exportDir = 'application.migrations';
	public static $targetTables = array(
			'authors',
			'contentMeta',
			'contents',
			'contentTag',
			'groups',
			'languages',
			'metas',
			'nodeMeta',
			'nodes',
			'nodeTag',
			'tags',
			'templates',
			'urls',
			'swauthAssignment',
			'swauthItem',
			'swauthItemChild',
			//TODO: add conditional creation of 'tokens'
	);
	private $_directory;

	/**
	 * Create the database dump
	 *
	 * @return void
	 * @since  2.0.0
	 */
    public function actionIndex() {
    	try {
			\Yii::trace(__METHOD__.'()', 'sweelix.yii1.ext.commands');
			$this->_directory = \Yii::getPathOfAlias(self::$exportDir);
    		if((is_dir($this->_directory) == false) || (is_writable($this->_directory) == false)) {
	    		throw new \CException('Directory '.$this->_directory.' must be writable');
	    	}
			$migrationFile = $this->_dumpToFile();
			echo "File ".$migrationFile." was successfully created in ".self::$exportDir.".\n";
    	} catch (\Exception $e) {
			\Yii::log('Error in '.__METHOD__.'():'.$e->getMessage(), \CLogger::LEVEL_ERROR, 'sweelix.yii1.ext.commands');
    		throw $e;
    	}
    }

	private function _dumpToFile() {
		$db = \Yii::app()->getDb();
		$tplFile =<<<EOTPL
<?php
/**
 * m{date}_upgradeCms.php
 *
 * PHP version 5.4+
 *
 * recreate the whole cms data
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  migrations
 * @package   application.migrations
 */
class m{date}_upgradeCms extends \CDbMigration {
	/**
	 * Apply current migration
	 *
	 * @return void
	 */
	public function safeUp() {
		\$this->getDbConnection()->getSchema()->checkIntegrity(false);
{migration}
		\$this->getDbConnection()->getSchema()->checkIntegrity(true);
	}
}
EOTPL;
    	$tplColumn = "\t\t\t'{columnName}' => {columnValue},";
    	$tplInsert = "\t\t\$this->insert('{table}', array({columns}\n\t\t));\n";
    	$tplTruncate = "\t\t\$this->truncateTable('{table}');\n";
    	$migration = '';
		foreach(self::$targetTables as $tableName) {
    		$rs = $db->createCommand()->select('*')->from($tableName)->queryAll();
    		$migration .= str_replace(array('{table}'), array($tableName), $tplTruncate);
    		foreach($rs as $key => $val) {
    			$columns = '';
    			foreach($val as $colName => $colValue) {
    				if($colValue == null) {
	    				$columns .= "\n".str_replace(array('{columnName}', '{columnValue}'), array($colName, 'null'), $tplColumn);
    				} else {
	    				$columns .= "\n".str_replace(array('{columnName}', '{columnValue}'), array($colName, "'".str_replace("'", "\\'", $colValue)."'"), $tplColumn);
    				}
    			}
    			$migration .= str_replace(array('{table}', '{columns}'), array($tableName, $columns),$tplInsert);
    		}
    	}
    	$date = date('ymd_His');
    	$file = str_replace(array('{date}', '{migration}'), array($date, $migration),$tplFile);
		$fp = fopen($this->_directory.'/m'.$date.'_upgradeCms.php', 'w');
    	fwrite($fp, $file);
    	fclose($fp);
    	return 'm'.$date.'_upgradeCms.php';
    }
}