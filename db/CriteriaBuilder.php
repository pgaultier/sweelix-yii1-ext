<?php
/**
 * CriteriaBuilder.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 */

namespace sweelix\yii1\ext\db;
use sweelix\yii1\ext\db\Mapper;

/**
 * CriteriaBuilder allow easy creation of CDbCriteria for Sweelix Content
 * database.
 *
 * <code>
 * 	$scCriteria = new CriteriaBuilder('content'); // we are targeting contents
 * 	$scCriteria->filterBy('nodeId', 1); // get contents for node Id 1
 * 	$scCriteria->orderBy('contentOrder'); // set ordering stuff
 * 	$cdbCriteria = $scCriteria->getCriteria(false); // get criteria and keep order in it
 *  $activeDataProvider = new CActiveDataProvider($scCriteria->dataModel, array(
 *  	'criteria' => $scCriteria->getCriteria(false),
 *  ));
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 * @since     2.0.0
 *
 * @property string      $modelClass
 * @property string      $defaultOrder
 * @property CDbCriteria $criteria
 * @property array       $sortAttributes
 */
class CriteriaBuilder extends \CComponent {

	public $connectionId='db';

	/**
	 * @var string class for current model
	 */
	private $_modelClass;
	/**
	 * @var string main table for current model
	 */
	private $_targetTable;

	// builder vars
	private $_joinedTables;
	private $_joinClause;
	private $_availableFields;
	private $_filters;
	private $_orders;
	private $_defaultOrder;
	private $_removeSortFromCriteria;
	private $_criteria;
	private $_sortAttributes;
	private $_currentSortOrderPart;

	/**
	 * Construct an CriteriaBuilder. The CriteriaBuilder is a wrapper around sweelix
	 * database.
	 *
	 * @param string $target      target element. Can be content|node|tag|group|language|author|meta|template
	 * @param string $wantedClass if we should use another kind of object pass the class name (including namespace)
	 *
	 * @return CriteriaBuilder
	 * @since  1.11.0
	 */
	public function __construct($target, $wantedClass=null) {
		if(preg_match('/^(content|node|tag|group|language|author|meta|template)s?$/i', $target, $matches)>0) {
			$base = strtolower($matches[1]);
			if($wantedClass === null) {
				$this->_modelClass = 'sweelix\\yii1\\ext\\entities\\'.ucfirst($base);
			} else {
				$this->_modelClass = $wantedClass;
			}

			$this->_targetTable = '{{'.$base.'s}}';
		} else {
			throw new \Exception('Target object '.$target.' is unknown');
		}
		$this->_joinedTables[$this->_targetTable] = array($this->_targetTable);
		$this->addAvailableFields($this->_targetTable);
	}

	/**
	 * Get current modelClass
	 *
	 * @return string
	 * @since  1.11.0
	 */
	public function getModelClass() {
		return $this->_modelClass;
	}

	/**
	 * return current database connection
	 *
	 * @return CDbConnection
	 * @since  1.11.0
	 */
	public function getDb() {
		return \Yii::app()->{$this->connectionId};
	}
	/**
	 * Return correct CDbCriteria with order part.
	 * When used with CGridView or similar widget, retrieve the criteria
	 * without the ordering part to allow automatic ordering
	 *
	 * @param boolean $withoutOrderPart remove the 'order' part of the criteria
	 *
	 * @return CDbCriteria
	 * @since  1.11.0
	 */
	public function getCriteria($withoutOrderPart=false) {
		if(($this->_criteria === null) || ($this->_currentSortOrderPart !== $withoutOrderPart)) {
			$this->rebuildCriteria($withoutOrderPart);
		}
		return new \CDbCriteria($this->_criteria);
	}

	/**
	 * Retrieve original ordering part. Usefull to populate
	 * CSort in CGridView or similar widget
	 *
	 * @return string
	 * @since  1.11.0
	 */
	public function getDefaultOrder() {
		return $this->_defaultOrder;
	}

	/**
	 * Retrieve all sortable attributes
	 *
	 * @return array
	 * @since  1.11.0
	 */
	public function getSortAttributes() {
		if($this->_sortAttributes === null) {
			$this->_sortAttributes = array();
			foreach($this->_availableFields as $key => $tables) {
				$this->_sortAttributes[$key] = array(
						'asc' => $this->_sortAttributes,
						'desc' => $this->_sortAttributes.' DESC',
				);
			}
		}
		return $this->_sortAttributes;
	}

	/**
	 * Add ordering filter
	 *
	 * @param string $field fieldname used
	 * @param string $way   ASCending orDESCending
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function orderBy($field, $way="ASC") {
		$this->_criteria = null;
		if(isset($this->_availableFields[$field]) === false) {
			// join needed table
			$this->addJoin($this->_targetTable, Mapper::findTableByField($field));
		}
		$table = $this->_availableFields[$field][0];
		$this->_orders[] = array(
				'table' => $table,
				'field' => $field,
				'way' => $way
		);
	}

	/**
	 * Add new filter to the sql statement
	 *
	 * @param string $field    field name
	 * @param mixed  $value    value to check
	 * @param string $operator operator
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function filterBy($field, $value, $operator='=') {
		$this->_criteria = null;
		if(isset($this->_availableFields[$field]) === false) {
			// join needed table
			$this->addJoin($this->_targetTable, Mapper::findTableByField($field));
		}
		$table = $this->_availableFields[$field][0];
		$type = Mapper::getFieldDatatype($table, $field);
		// override operator to handle lists
		if(is_array($value) === true) {
			$operator = 'in';
		}
		list($op, $bind) = Mapper::castOperator($operator, $type, $value);
		$this->_filters[] = array(
				'table' => $table,
				'field' => $field,
				'value' => $value,
				'operator' => $op,
				'binder' => $bind,
				'type' => $type,
		);
	}

	/**
	 * Perform grouping
	 *
	 * @param string $operator operator to use between groups
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function beginGroup($operator='or') {
		$this->_criteria = null;
		$this->_filters[] = array('startGroup'=>$operator);
	}

	/**
	 * Perform grouping
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function endGroup() {
		$this->_criteria = null;
		$this->_filters[] = array('endGroup'=>true);
	}

	/**
	 * Fetch only published elements.
	 * This method is valid for contents and for nodes
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function published() {
		$this->_criteria = null;
		if($this->_targetTable === '{{contents}}') {
			$this->filterBy('nodeStatus', 'online');
			$this->filterBy('contentStatus', 'online');
			$this->beginGroup();
			$this->filterBy('contentStartDate', null);
			$this->filterBy('contentStartDate', new \CDbExpression('NOW()'), '<=');
			$this->endGroup();
			$this->beginGroup();
			$this->filterBy('contentEndDate', null);
			$this->filterBy('contentEndDate', new \CDbExpression('NOW()'), '>=');
			$this->endGroup();
		} elseif ($this->_targetTable === '{{nodes}}') {
			$this->filterBy('nodeStatus', 'online');
		}
	}

	/**
	 * Return an active record list for current request
	 *
	 * @return CActiveRecord[]
	 * @since  1.11.0
	 */
	public function findAll() {
		$modelClass = $this->_modelClass;
		return $modelClass::model()->findAll($this->getCriteria());
	}

	/**
	 * Return the first active record for current request
	 *
	 * @return CActiveRecord
	 * @since  1.11.0
	 */
	public function find() {
		$modelClass = $this->_modelClass;
		return $modelClass::model()->find($this->getCriteria());
	}

	/**
	 * Count the number of row
	 *
	 * @return integer
	 * @since  2.0.0
	 */
	public function count() {
		$modelClass = $this->_modelClass;
		return $modelClass::model()->count($this->getCriteria());
	}
	/**
	 * Return ActiveProvider for current request
	 *
	 * @param array $config basic CActiveDataProvider configuration
	 *
	 * @return CActiveDataProvider
	 * @since  1.11.0
	 */
	public function getActiveDataProvider($config=array()) {
		$config['criteria'] = $this->getCriteria(true);
		if(isset($config['sort']) === true) {
			if((is_array($config['sort']) === true) && (isset($config['sort']['defaultOrder']) === false)) {
				$config['sort']['defaultOrder'] = $this->getDefaultOrder();
			} elseif(($config['sort'] instanceof \CSort) && ($config['sort']->defaultOrder == null)) {
				$config['sort']->defaultOrder = $this->getDefaultOrder();
			}
		} else {
			$config['sort']['defaultOrder'] = $this->getDefaultOrder();
		}

		return new \CActiveDataProvider($this->modelClass, $config);

	}


	/**
	 * Add fields from included tables to avoid
	 * further request
	 *
	 * @param string $table table name
	 *
	 * @return void
	 * @since  1.11.0
	 */
	protected function addAvailableFields($table) {
		$newFields = Mapper::getTableFields($table);
		foreach($newFields as $newField) {
			if(isset($this->_availableFields[$newField]) === true) {
				$this->_availableFields[$newField][] = $table;
			} else {
				$this->_availableFields[$newField] = array($table);
			}
		}
	}

	/**
	 * Perform join between two tables
	 *
	 * @param string $sourceTable source table name
	 * @param string $targetTable target table name
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	protected function addJoin($sourceTable, $targetTable) {
		$result = true;
		if((isset($this->_joinedTables[$sourceTable]) === false) && ($this->_targetTable !== $sourceTable)) {
			throw new \Exception('origin table must be defined');
		}
		if(isset($this->_joinedTables[$targetTable]) === false) {
			//XXX: perform join stuff
			if(Mapper::getDistance($sourceTable, $targetTable) === 0) {
				//XXX: table can be joined directly
				$this->_joinedTables[$targetTable] = array($sourceTable, $targetTable);
				$this->_joinClause[] = Mapper::getJoin($sourceTable, $targetTable);
				$this->addAvailableFields($targetTable);
			} else {
				//XXX: we must join multiple tables
				$listTables = Mapper::getPath($sourceTable, $targetTable);
				$prevTable = $sourceTable;
				foreach($listTables as $newTable) {
					if($this->addJoin($prevTable, $newTable) === true) {
						//we are recursing here so everything should be fine
						$prevTable = $newTable;
					} else {
						//something bad happened
						throw new \Exception('was not able to perform correct join');
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Rebuild the criteria. This is a wrapper around the original builder
	 *
	 * @param boolean $withoutOrderPart remove the 'order' part of the criteria
	 *
	 * @return void
	 * @since  1.11.0
	 */
	protected function rebuildCriteria($withoutOrderPart) {
		$this->_currentSortOrderPart = $withoutOrderPart;
		$criteria = array();
		$prefix = $this->getDb()->tablePrefix;
		if(is_array($this->_joinClause) === true) {
			$criteria['join'] = '';
			foreach($this->_joinClause as $clause) {
				$criteria['join'] .= " INNER JOIN  ".$clause['targetTable']." ON ".$clause['fromTable'].".".$clause['fromField']." = ".$clause['targetTable'].".".$clause['targetField']." ";
			}
			$criteria['join'] = str_replace($this->_targetTable, 't', $criteria['join'] );
			if($prefix===null) {
				$criteria['join'] = str_replace(array('{{', '}}'), array('', ''), $criteria['join'] );
			}

		}
		if(is_array($this->_filters) === true) {
			$params = null;
			$count = 0;
			$where = Mapper::buildClause($this->_filters, $params, $count);
			if(preg_match('/^\s+\(\s+\)\s+$/', $where) === 0) {
				$criteria['condition'] = $where;
				foreach($params as $param) {
					$criteria['params'][$param['field']] = $param['value'];
				}
				$criteria['condition'] = str_replace($this->_targetTable, 't', $criteria['condition'] );
				if($prefix===null) {
					$criteria['condition'] = str_replace(array('{{', '}}'), array('', ''), $criteria['condition'] );
				}
			}
		}
		if(is_array($this->_orders) === true) {
			$orderClause = array();
			foreach($this->_orders as $order) {
				$clause = $order['table'].".".$order['field']." ".$order['way'];
				$clause = str_replace($this->_targetTable, 't', $clause );
				$orderClause[] = $clause;
			}
			if($withoutOrderPart === true) {
				$this->_defaultOrder = implode(', ', $orderClause);
			} else {
				$criteria['order'] = implode(', ', $orderClause);
				if($prefix===null) {
					$criteria['order'] = str_replace(array('{{', '}}'), array('', ''), $criteria['order'] );
				}
			}
		}
		$this->_criteria = $criteria;
	}
}
