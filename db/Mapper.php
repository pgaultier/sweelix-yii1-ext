<?php
/**
 * File Mapper.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 */

namespace sweelix\yii1\ext\db;

/**
 * Class Mapper
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 * @since     2.0.0
 */
class Mapper {
	/**
	 * get list of available fields for selected table
	 *
	 * @param string $table table name
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function getTableFields($table) {
		if(isset(self::$_tables[$table])===false) {
			throw new \Exception('table '.$table.' is not defined');
		}
		return array_keys(self::$_tables[$table]);
	}
	/**
	 * @var array full database datastructure.
	 */
	private static $_tables = array(
		'{{authors}}' => array(
			'authorId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'authorEmail' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>true ),
			'authorPassword' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'authorFirstname' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'authorLastname' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'authorLastLogin' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>false,'unique'=>false ),
			'languageId' => array('pkey'=>false,'type'=>'text','fkey'=>'{{languages}}','index'=>true,'unique'=>false ),
		),
		'{{contentMeta}}' => array(
			'contentId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{contents}}','index'=>true,'unique'=>false ),
			'metaId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{metas}}','index'=>true,'unique'=>false ),
			'contentMetaValue' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
		),
		'{{contents}}' => array(
			'contentId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'contentTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentSubTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentUrl' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentData' => array('pkey'=>false,'type'=>'longblob','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentOrder' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentStartDate' => array('pkey'=>false,'type'=>'date','fkey'=>false,'index'=>true,'unique'=>false ),
			'contentEndDate' => array('pkey'=>false,'type'=>'date','fkey'=>false,'index'=>true,'unique'=>false ),
			'contentCreateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'contentUpdateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'contentStatus' => array('pkey'=>false,'type'=>'string','fkey'=>false,'index'=>true,'unique'=>false ),
			'contentViewed' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{nodes}}','index'=>true,'unique'=>false ),
			'authorId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{authors}}','index'=>true,'unique'=>false ),
			'templateId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{templates}}','index'=>true,'unique'=>false ),
			'languageId' => array('pkey'=>false,'type'=>'text','fkey'=>'{{languages}}','index'=>true,'unique'=>false ),
		),
		'{{contentTag}}' => array(
			'contentId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{contents}}','index'=>true,'unique'=>false ),
			'tagId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{metas}}','index'=>true,'unique'=>false ),
		),
		'{{groups}}' => array(
			'groupId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'groupTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'groupType' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'groupUrl' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'groupData' => array('pkey'=>false,'type'=>'longblob','fkey'=>false,'index'=>false,'unique'=>false ),
			'groupCreateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'groupUpdateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'templateId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{templates}}','index'=>true,'unique'=>false ),
			'languageId' => array('pkey'=>false,'type'=>'text','fkey'=>'{{languages}}','index'=>true,'unique'=>false ),
		),
		'{{languages}}' => array(
			'languageId' => array('pkey'=>true,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>true ),
			'languageTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'languageIsActive' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>false,'unique'=>false ),
		),
		'{{metas}}' => array(
			'metaId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'metaDefaultValue' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
		),
		'{{nodeMeta}}' => array(
			'nodeId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{nodes}}','index'=>true,'unique'=>false ),
			'metaId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{metas}}','index'=>true,'unique'=>false ),
			'nodeMetaValue' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
		),
		'{{nodes}}' => array(
			'nodeId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'nodeTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeUrl' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeData' => array('pkey'=>false,'type'=>'longblob','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeDisplayMode' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeRedirection' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{nodes}}','index'=>true,'unique'=>false ),
			'nodeLeftId' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>false ),
			'nodeRightId' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>false ),
			'nodeLevel' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>false ),
			'nodeStatus' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeViewed' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>false,'unique'=>false ),
			'nodeCreateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'nodeUpdateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'authorId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{authors}}','index'=>true,'unique'=>false ),
			'templateId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{templates}}','index'=>true,'unique'=>false ),
			'languageId' => array('pkey'=>false,'type'=>'text','fkey'=>'{{languages}}','index'=>true,'unique'=>false ),
		),
		'{{nodeTag}}' => array(
			'nodeId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{nodes}}','index'=>true,'unique'=>false ),
			'tagId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{tags}}','index'=>true,'unique'=>false ),
		),
		'{{tags}}' => array(
			'tagId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'tagTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'tagUrl' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'tagData' => array('pkey'=>false,'type'=>'longblob','fkey'=>false,'index'=>false,'unique'=>false ),
			'groupId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{groups}}','index'=>true,'unique'=>false ),
			'tagCreateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'tagUpdateDate' => array('pkey'=>false,'type'=>'datetime','fkey'=>false,'index'=>true,'unique'=>false ),
			'templateId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{templates}}','index'=>true,'unique'=>false ),
			'languageId' => array('pkey'=>false,'type'=>'text','fkey'=>'{{languages}}','index'=>true,'unique'=>false ),
		),
		'{{templates}}' => array(
			'templateId' => array('pkey'=>true,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>true ),
			'templateTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'templateDefinition' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'templateController' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'templateAction' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'templateComposite' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
			'templateType' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>false,'unique'=>false ),
		),
		'{{urls}}' => array(
			'urlValue' => array('pkey'=>true,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>true ),
			'urlElementType' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>false ),
			'urlElementId' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>false ),
		),
		'{{tokens}}' => array(
			'elementProperty' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>false ),
			'elementId' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>true,'unique'=>false ),
			'elementType' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>false ),
			'elementTitle' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>false ),
			'tokenKey' => array('pkey'=>false,'type'=>'text','fkey'=>false,'index'=>true,'unique'=>false ),
			'tokenNumeric' => array('pkey'=>false,'type'=>'float','fkey'=>false,'index'=>false,'unique'=>false ),
			'contentId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{contents}}','index'=>true,'unique'=>false ),
			'nodeId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{nodes}}','index'=>true,'unique'=>false ),
			'tagId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{tags}}','index'=>true,'unique'=>false ),
			'groupId' => array('pkey'=>false,'type'=>'integer','fkey'=>'{{groups}}','index'=>true,'unique'=>false ),
			'tokenWeight' => array('pkey'=>false,'type'=>'integer','fkey'=>false,'index'=>false,'unique'=>false ),
			'tokenDateCreate' => array('pkey'=>false,'type'=>'date','fkey'=>false,'index'=>true,'unique'=>false ),
		),
	);
	/**
	 * @var array links between tables
	*/
	private static $_path = array(
			'{{authors}}' => array(
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'authorId', 'targetField' => 'authorId'),
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'authorId', 'targetField' => 'authorId'),
			),
			'{{contentMeta}}' => array(
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
				'{{metas}}' => array('tables' => array('{{metas}}'), 'fromField' => 'metaId', 'targetField' => 'metaId'),
			),
			'{{contents}}' => array(
				'{{authors}}' => array('tables' => array('{{authors}}'), 'fromField' => 'authorId', 'targetField' => 'authorId'),
				'{{contentTag}}' => array('tables' => array('{{contentTag}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
				'{{groups}}' => array('tables' => array('{{contentTag}}','{{tags}}','{{groups}}')),
				'{{languages}}' => array('tables' => array('{{languages}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{metas}}' => array('tables' => array('{{contentMeta}}','{{metas}}')),
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{tags}}' => array('tables' => array('{{contentTag}}','{{tags}}')),
				'{{templates}}' => array('tables' => array('{{templates}}'), 'fromField' => 'templateId', 'targetField' => 'templateId'),
				'{{tokens}}' => array('tables' => array('{{tokens}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
			),
			'{{contentTag}}' => array(
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
			),
			'{{groups}}' => array(
				'{{contents}}' => array('tables' => array('{{tags}}', '{{contentTag}}', '{{contents}}')),
				'{{contentTag}}' => array('tables' => array('{{tags}}', '{{contentTag}}')),
				'{{languages}}' => array('tables' => array('{{languages}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{nodes}}' => array('tables' => array('{{tags}}', '{{nodeTag}}', '{{nodes}}')),
				'{{nodeTag}}' => array('tables' => array('{{tags}}', '{{nodeTag}}')),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'groupId', 'targetField' => 'groupId'),
				'{{templates}}' => array('tables' => array('{{templates}}'), 'fromField' => 'templateId', 'targetField' => 'templateId'),
				'{{tokens}}' => array('tables' => array('{{tokens}}'), 'fromField' => 'groupId', 'targetField' => 'groupId'),
			),
			'{{languages}}' => array(
				'{{authors}}' => array('tables' => array('{{authors}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{groups}}' => array('tables' => array('{{groups}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
			),
			'{{metas}}' => array(
				'{{contents}}' => array('tables' => array('{{contentMeta}}', '{{contents}}')),
				'{{contentMeta}}' => array('tables' => array('{{contentMeta}}'), 'fromField' => 'metaId', 'targetField' => 'metaId'),
				'{{nodes}}' => array('tables' => array('nodeMeta', '{{nodes}}')),
				'nodeMeta' => array('tables' => array('nodeMeta'), 'fromField' => 'metaId', 'targetField' => 'metaId'),
			),
			'{{nodeMeta}}' => array(
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{metas}}' => array('tables' => array('{{metas}}'), 'fromField' => 'metaId', 'targetField' => 'metaId'),
			),
			'{{nodes}}' => array(
				'{{authors}}' => array('tables' => array('{{authors}}'), 'fromField' => 'authorId', 'targetField' => 'authorId'),
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{nodeTag}}' => array('tables' => array('{{nodeTag}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{groups}}' => array('tables' => array('{{nodeTag}}','{{tags}}','{{groups}}')),
				'{{languages}}' => array('tables' => array('{{languages}}'), 'fromField' => 'languageId', 'targetField' => 'languageId'),
				'{{metas}}' => array('tables' => array('nodeMeta','{{metas}}')),
				'{{tags}}' => array('tables' => array('{{nodeTag}}','{{tags}}')),
				'{{templates}}' => array('tables' => array('{{templates}}'), 'fromField' => 'templateId', 'targetField' => 'templateId'),
				'{{tokens}}' => array('tables' => array('{{tokens}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
			),
			'{{nodeTag}}' => array(
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
			),
			'{{tags}}' => array(
				'{{contents}}' => array('tables' => array('{{contentTag}}','{{contents}}')),
				'{{contentTag}}' => array('tables' => array('{{contentTag}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
				'{{groups}}' => array('tables' => array('{{groups}}'), 'fromField' => 'groupId', 'targetField' => 'groupId'),
				'{{nodes}}' => array('tables' => array('{{nodeTag}}','{{nodes}}')),
				'{{nodeTag}}' => array('tables' => array('{{nodeTag}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
				'{{templates}}' => array('tables' => array('{{templates}}'), 'fromField' => 'templateId', 'targetField' => 'templateId'),
				'{{tokens}}' => array('tables' => array('{{tokens}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
			),
			'{{templates}}' => array(
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
				'{{groups}}' => array('tables' => array('{{groups}}'), 'fromField' => 'groupId', 'targetField' => 'groupId'),
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
			),
			'{{tokens}}' => array(
				'{{nodes}}' => array('tables' => array('{{nodes}}'), 'fromField' => 'nodeId', 'targetField' => 'nodeId'),
				'{{contents}}' => array('tables' => array('{{contents}}'), 'fromField' => 'contentId', 'targetField' => 'contentId'),
				'{{tags}}' => array('tables' => array('{{tags}}'), 'fromField' => 'tagId', 'targetField' => 'tagId'),
				'{{groups}}' => array('tables' => array('{{groups}}'), 'fromField' => 'groupId', 'targetField' => 'groupId'),
			),
	);

	/**
	 * @var array reversed table (automatically calculated)
	*/
	private static $_reversedTables = null;

	/**
	 * @var array primary keys (automatically calculated)
	 */
	private static $_primaries = null;

	private static $_primary = null;

	/**
	 * Retrieve the primary key for current table
	 *
	 * @param string $table table name
	 *
	 * @return string
	 * @since  1.0.0
	 */
	private static function getPrimary($table) {
		if(self::$_primary === null) {
			foreach(self::$_tables as $tableName => $tableData ) {
				foreach($tableData as $columnName => $columnData) {
					if($columnData['pkey'] === true) {
						self::$_primary[$tableName] = $columnName;
					}
				}
			}
		}
		return self::$_primary[$table];
	}

	/**
	 * Build the reversed data structure
	 *
	 * @return void
	 * @since  1.0.0
	 */
	private static function buildExtendedData() {
		foreach(self::$_tables as $tableName => $tableData ) {
			foreach($tableData as $columnName => $columnData) {
				if(isset(self::$_reversedTables[$columnName]) === true) {
					if(in_array($tableName, self::$_reversedTables[$columnName])=== false) {
						self::$_reversedTables[$columnName][] = $tableName;
					}
				} else {
					self::$_reversedTables[$columnName] = array($tableName);
				}
				if($columnData['pkey'] === true) {
					self::$_primaries[$columnName] = $tableName;
				}
			}
		}
	}

	/**
	 * find table corresponding to specific pkey
	 *
	 * @param string $pkey primary key
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function findTableByPkey($pkey) {
		if(self::$_primaries === null) {
			self::buildExtendedData();
		}
		if(isset(self::$_primaries[$pkey]) === false) {
			throw new \Exception('table for pkey '.$pkey.' not found');
		}
		return self::$_primaries[$pkey];
	}

	/**
	 * find table corresponding to specific field.
	 *
	 * @param string $field field name
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function findTableByField($field) {
		if((self::$_primaries === null) || (self::$_reversedTables  === null)) {
			self::buildExtendedData();
		}
		if(isset(self::$_primaries[$field]) === false) {
			if(isset(self::$_reversedTables[$field]) === false) {
				throw new \Exception('table for field '.$field.' not found');
			} else {
				//XXX: we get the first table because field name should be unique among the whole DB
				$result = self::$_reversedTables[$field][0];
			}
		} else {
			$result = self::$_primaries[$field];
		}
		return $result;
	}

	/**
	 * get the distance between two tables
	 *
	 * @param string $sourceTable name of the source table
	 * @param string $targetTable name of the target table
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public static function getDistance($sourceTable, $targetTable) {
		if(isset(self::$_path[$sourceTable][$targetTable])===false) {
			throw new \Exception('tables '.$sourceTable.' and '.$targetTable.' cannot be joined');
		}
		return (count(self::$_path[$sourceTable][$targetTable]['tables'])-1);
	}

	/**
	 * Try to join two tables
	 *
	 * @param string $sourceTable name of the source table
	 * @param string $targetTable name of the target table
	 *
	 * @throws Exception
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public static function getJoin($sourceTable, $targetTable) {
		if(self::getDistance($sourceTable, $targetTable) !== 0) {
			throw new \Exception('tables '.$sourceTable.' and '.$targetTable.' cannot be joined directly');
		}
		return array(
				'fromTable' => $sourceTable,
				'targetTable' => $targetTable,
				'fromField'=> self::$_path[$sourceTable][$targetTable]['fromField'],
				'targetField'=> self::$_path[$sourceTable][$targetTable]['targetField']
		);

		return array($sourceTable.'.'.self::$_path[$sourceTable][$targetTable]['field'],$targetTable.'.'.self::$_path[$sourceTable][$targetTable]['field']);
		return (count(self::$_path[$sourceTable][$targetTable]['tables'])-1);
	}
	/**
	 * expand table list to perform correct join
	 *
	 * @param string $sourceTable source table
	 * @param string $targetTable target table
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function getPath($sourceTable, $targetTable) {
		if(isset(self::$_path[$sourceTable][$targetTable])===false) {
			throw new \Exception('tables '.$sourceTable.' and '.$targetTable.' cannot be joined');
		}
		return self::$_path[$sourceTable][$targetTable]['tables'];
	}

	/**
	 * Prepare the operator and the bind method
	 *
	 * @param string $operator what to do
	 	* @param string $type     value type
	 * @param mixed  $value    value to check
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function castOperator($operator, $type, $value) {
		$operator = strtolower($operator);
		switch($type) {
			case 'string' :
			case 'blob' :
			case 'longblob' :
			case 'text' :
				switch($operator) {
					case '!=' :
					case 'not like' :
						if($value === null) {
							return array('is not null', -1);
						} else {
							return array('not like', \PDO::PARAM_STR);
						}
						break;
					case '=' :
					case 'like' :
						if($value === null) {
							return array('is null', -1);
						} else {
							return array('like', \PDO::PARAM_STR);
						}
						break;
					case 'in' :
					case 'not in' :
						return array($operator, \PDO::PARAM_STR);
						break;
					default :
						throw new \Exception('Operator "'.$operator.'" not implemented for type"'.$type.'"');
						break;
				}
				break;
			case 'float' :
			case 'date' :
			case 'datetime' :
				switch($operator) {
					case '=' :
					case '>' :
					case '>=' :
					case '<' :
					case '<=' :
					case '!=' :
					case 'in' :
					case 'not in' :
						if(($value === null) && ($operator === '=')) {
							return array('is null', -1);
						} elseif(($value === null) && ($operator == '!=')) {
							return array('is not null', -1);
						} else {
							return array($operator, \PDO::PARAM_STR);
						}
						break;
					case 'like' :
						return array('=', \PDO::PARAM_STR);
						break;
					case 'not like' :
						return array('!=', \PDO::PARAM_STR);
						break;
					default :
						throw new \Exception('Operator "'.$operator.'" not implemented for type"'.$type.'"');
						break;
				}
				break;
			case 'integer' :
				switch($operator) {
					case '=' :
					case '>' :
					case '<' :
					case '>=' :
					case '<=' :
					case '!=' :
					case 'in' :
					case 'not in' :
						if(($value === null) && ($operator === '=')) {
							return array('is null', -1);
						} elseif(($value === null) && ($operator == '!=')) {
							return array('is not null', -1);
						} else {
							return array($operator, \PDO::PARAM_INT);
						}
						break;
					case 'like' :
						return array('=', \PDO::PARAM_INT);
						break;
					case 'not like' :
						return array('!=', \PDO::PARAM_INT);
						break;
					default :
						throw new \Exception('Operator "'.$operator.'" not implemented for type"'.$type.'"');
						break;
				}
				break;
			default:
				throw new \Exception('Type"'.$type.'" is unknown');
				break;
		}
	}

	/**
	 * Find datatype of selected field
	 *
	 * @param string $table table name
	 * @param string $field target field
	 *
	 * @throws Exception
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function getFieldDatatype($table, $field) {
		if(isset(self::$_tables[$table][$field]['type']) === false ) {
			throw new \Exception('Type for field '.$table.'.'.$field.' is not defined');
		}
		return self::$_tables[$table][$field]['type'];
	}

	/**
	 * build the select statement.
	 *
	 * @param boolean $distinct    distinct results
	 * @param string  $targetTable name of the target table
	 * @param array   $joinClause  join elements
	 * @param array   $filters     filters to apply
	 * @param array   $orders      order by elements
	 * @param array   $limit       limit element for lazy loading
	 *
	 * @return array array(sqlData, parameters)
	 * @since  1.0.0
	 */
	public static function buildSelect($distinct, $targetTable, $joinClause, $filters, $orders, $limit) {
		if($distinct===true) {
			$sql = "SELECT DISTINCT ".$targetTable.".* FROM ".$targetTable." ";
		} else {
			$sql = "SELECT ".$targetTable.".* FROM ".$targetTable." ";
		}
		foreach($joinClause as $clause) {
			$sql = $sql." INNER JOIN  ".$clause['targetTable']." ON ".$clause['fromTable'].".".$clause['fromField']." = ".$clause['targetTable'].".".$clause['targetField']." ";
		}
		$params = null;
		$count = 0;
		$where = self::buildClause($filters, $params, $count);
		if($where !== " (  ) ") {
			$sql = $sql." WHERE ".$where;
		}
		$orderCount = true;
		foreach($orders as $order) {
			if($orderCount === true) {
				$orderCount = false;
				$sql .= " ORDER BY ";
			} else {
				$sql .= ", ";
			}
			$sql .= $order['table'].".".$order['field']." ".$order['way']." ";
		}
		$sql .= self::buildLimit($limit);
		return array($sql, $params);
	}

	/**
	 * Prepare the where clause of the sql statement
	 *
	 * @param array   &$filters array of the filters to apply
	 * @param array   &$params  array of the parameters values
	 * @param integer &$count   counting var
	 * @param string  $operator operator to apply
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function buildClause(&$filters, &$params, &$count, $operator='and') {
		$sql = "";
		$operator = strtoupper($operator);
		if(is_array($params) === false) { $params = array(); }
		$start = true;
		while(count($filters)>0) {
			$filter = array_shift($filters);
			if(isset($filter['startGroup']) === true) {
				if($count>0) {
					$sql .= " ".$operator." ";
				}
				$sql .= self::buildClause($filters, $params, $count, $filter['startGroup']);
				$start = false;
			} elseif(isset($filter['endGroup']) === true) {
				break;
			} else {
				if($start === true) {
					$start = false;
				} else {
					$sql .= " ".$operator." ";
				}
				//XXX: filtering is here
				if($filter['value'] instanceof \CDbExpression) {
					$sql .= " ".$filter['table'].".".$filter['field']." ".$filter['operator']." ".$filter['value'];
				} elseif ($filter['binder'] === -1) {
					$sql .= " ".$filter['table'].".".$filter['field']." ".$filter['operator']." ";
				} else {
					if (is_array($filter['value']) === true) {
						$sql .= " ".$filter['table'].".".$filter['field']." ".$filter['operator']." ".'('.implode(',', $filter['value']).')';
					} else {
						$value = $filter['value'];
						$count++;
						$params[] = array('field'=>':f'.$count, 'binder'=>$filter['binder'], 'value'=> $value);
						$sql .= " ".$filter['table'].".".$filter['field']." ".$filter['operator']." :f".$count." ";

					}

				}
			}
		}
		return " ( ".$sql." ) ";
	}

	/**
	 * Add limit element to the statement
	 *
	 * @param array $limit limit parameter array('offset'=>xx,'length'=>yy)
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function buildLimit($limit=null) {
		if($limit!==null) {
			$sql = " LIMIT ".$limit['offset'].", ".$limit['length']." ";
			// $sql = " LIMIT ".$length." OFFSET ".$offset." ";
		} else {
			$sql = "";
		}
		return $sql;
	}
	/**
	 * build the select statement to count potential results.
	 *
	 * @param boolean $distinct    distinct results
	 * @param string  $targetTable name of the target table
	 * @param array   $joinClause  join elements
	 * @param array   $filters     filters to apply
	 * @param array   $orders      order by elements
	 *
	 * @return array array(sqlData, parameters)
	 * @since  1.0.0
	 */
	public static function buildCount($distinct, $targetTable, $joinClause, $filters, $orders) {
		if($distinct===true) {
			$sql = "SELECT COUNT(DISTINCT ".$targetTable.".".self::getPrimary($targetTable).") AS cnt FROM ".$targetTable." ";
		} else {
			$sql = "SELECT COUNT(".$targetTable.".".self::getPrimary($targetTable).") AS cnt FROM ".$targetTable." ";
		}
		foreach($joinClause as $clause) {
			$sql = $sql." INNER JOIN  ".$clause['targetTable']." ON ".$clause['fromTable'].".".$clause['fromField']." = ".$clause['targetTable'].".".$clause['targetField']." ";
		}
		$params = null;
		$count = 0;
		$where = self::buildClause($filters, $params, $count);
		if($where !== " (  ) ") {
			$sql = $sql." WHERE ".$where;
		}
		$orderCount = true;
		foreach($orders as $order) {
			if($orderCount === true) {
				$orderCount = false;
				$sql .= " ORDER BY ";
			} else {
				$sql .= ", ";
			}
			$sql .= $order['table'].".".$order['field']." ".$order['way']." ";
		}
		return array($sql, $params);
	}
}
