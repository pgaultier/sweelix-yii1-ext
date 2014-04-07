<?php
/**
 * File Node.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 */

namespace sweelix\yii1\ext\entities;
use sweelix\yii1\ext\db\ar\Node as ActiveRecordNode;
use sweelix\yii1\ext\db\dao\Node as DaoNode;
use sweelix\yii1\ext\db\CriteriaBuilder;
use sweelix\yii1\ext\components\RouteEncoder;


/**
 * Class Node
 *
 * This is the model class for table "nodes".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 * @since     1.0.0
 *
 * @property mixed     $nodeElements
 * @property string    $nodeSignature
 * @property Content[] $contents
 * @property Meta[]    $metas
 * @property Tag[]     $tags
 * @property Author    $author
 * @property Node      $redirection
 * @property Node[]    $nodes
 * @property Template  $template
 *
 * @method mixed   prop()	               prop(string $property, array $arrayCell=null) fetch a subproperty without crashing
 * @method string  getCompositeTemplate()  getCompositeTemplate(integer $templateId)     fetch composite template
 * @method string  getSubProperties()      getSubProperties(integer $templateId)         retrieve list of subproperties
 * @method array   getTemplateDefinition() getTemplateDefinition(integer $templateId)    get full template definition
 * @method boolean getIsSubProperty()      getIsSubProperty(string $name)                check if subproperty exists
 */
class Node extends ActiveRecordNode {
	/**
	 * @var array, store sub elements
	 */
	private $_nodeElements = null;
	/**
	 * @var array, store tag checked to avoid multiple requests
	 */
	private $_hasTags = array();
	/**
	 * @var array, store group checked to avoid multiple requests
	 */
	private $_hasGroups = array();
	/**
	 * @var Node handle parent node info
	 */
	private $_parent;

	/**
	 * @var string current signature
	 */
	private $_nodeSignature;

	/**
	 * Retrieve signature for current node
	 *
	 * @return string
	 * @since  1.9.0
	 */
	public function getNodeSignature() {
		if($this->_nodeSignature === null) {
			$this->_nodeSignature = $this->getSignature();
		}
		return $this->_nodeSignature;
	}

	/**
	 * Force signature
	 *
	 * @param string $signature
	 *
	 * @return void
	 * @since  1.9.0
	 */
	public function setNodeSignature($signature) {
		$this->_nodeSignature = $signature;
	}

	/**
	 * Get current signature
	 *
	 * @return string
	 */
	public function getSignature() {
		$attributes = $this->attributes;
		unset($attributes['nodeCreateDate']);
		unset($attributes['nodeUpdateDate']);
		return sha1(serialize($attributes));
	}

	/**
	 * check current signature against the one in database
	 *
	 * @return void
	 * @since  1.9.0
	 */
	public function checkSignature() {
		$currentNode = Node::model()->findByPk($this->nodeId);
		if($currentNode instanceof Node) {
			$dbSignature = $currentNode->getSignature();
		}
		if($dbSignature != $this->getNodeSignature()) {
			$this->addError('nodeSignature', \Yii::t('sweelix', 'Node has been updated by {firstname} {lastname}', array('{firstname}' => $currentNode->author->authorFirstname, '{lastname}' =>$currentNode->author->authorLastname)));
		}
	}

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Node the static model class
	 * @since  1.0.0
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Attach behavior to current model
	 * @see CModel::behaviors()
	 *
	 * @return array
	 * @since  1.6.0
	 */
	public function behaviors() {
		return \CMap::mergeArray(
			parent::behaviors(),
			array(

				'template' => 'sweelix\yii1\ext\behaviors\Template',
				'transliterate' => 'sweelix\yii1\ext\behaviors\Url',
				'elasticProperties' => array(
					'class' => 'sweelix\yii1\behaviors\ElasticModel',
					'exceptScenarios' => 'updateProperty',
					'elasticStorage' => 'nodeData',
					'templateConfig' => array($this, 'getRawTemplate'),
					'pathParameters' => array('{nodeId}' => 'nodeId'),
				),
 				'indexer' => array(
 					'class' => 'sweelix\yii1\ext\behaviors\Token',
 					'type' => 'node',
 				),
			)
		);
	}

	/**
	 * This function is executed on runtime.
	 * It is used by the behavior elasticProperties (can't call getTemplateDefinition with params)
	 *
	 * @return array -> templateConfig
	 *
	 * @since  2.0.0
	 */
	public function getRawTemplate() {
		return $this->getTemplateDefinition($this->templateId);
	}

	/**
	 * Override __get to handle magic getter for our magic extended
	 * properties
	 * @see CActiveRecord::__get()
	 *
	 * @param string $name property name
	 *
	 * @return mixed
	 * @since  1.6.0
	 */
	public function __get($name){
		try {
			return parent::__get($name);
		} catch(\Exception $e) {
			if($this->getIsSubProperty($name) === true) {
				return $this->prop($name);
			} else {
				throw($e);
			}
		}
	}

	/**
	 * Generate an url for specific element using Yii constructUrl
	 *
	 * @param string $action    action if needed
	 * @param array  $params    query parameters
	 * @param string $ampersand query separator
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function getUrl($action=null, $params=array(), $ampersand='&') {
		return \Yii::app()->getUrlManager()->createUrl(
			$this->getRoute($action),
			$params,
			$ampersand);
	}

	/**
	 * Prepare a route which will be usable in
	 * @see CHtml::normalizeUrl()
	 *
	 * @param string $action target action if needed
	 *
	 * @return array
	 * @since  1.6.0
	 */
	public function getRoute($action=null) {
		$route = RouteEncoder::encoder(null, $this->nodeId);
		if(empty($action) === false) {
			$route = $route.'/'.$action;
		}
		return $route;
	}

	/**
	 * Set node elements to blob data
	 *
	 * @param mixed $data data structure to store
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setNodeElements($data) {
		$this->_nodeElements = $data;
		$this->nodeData = \CJSON::encode($data);
	}

	/**
	 * Retrieve node elements from blob data
	 *
	 * @param boolean $asArray decode element as array instead of object
	 *
	 * @return mixed
	 * @since  1.0.0
	 */
	public function getNodeElements($asArray = true) {
		if($this->_nodeElements === null) {
			$this->_nodeElements = \CJSON::decode($this->nodeData, $asArray);
		}
		return $this->_nodeElements;
	}

	/**
	 * Return avaliable values to fill the displayMode field
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function getAvailableDisplayModes() {
		return array(
			'first'=>\Yii::t('sweelix', 'first'),
			'list'=>\Yii::t('sweelix', 'list'),
			'redirect'=>\Yii::t('sweelix', 'redirect'),
		);
	}

	/**
	 * Return avaliable values to fill the status field
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function getAvailableStatus() {
		return array(
			'draft'=>\Yii::t('sweelix', 'draft'),
			'online'=>\Yii::t('sweelix', 'online'),
			'offline'=>\Yii::t('sweelix', 'offline'),
		);
	}

	/**
	 * Check if url is valid
	 *
	 * @param string $attribute name of the attribute to be validated
	 * @param array  $params    options specified in the validation rule
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function checkUrlAvailable($attribute, $params) {
		if((isset($params['mode']) === true) && ($params['mode'] === 'update')) {
			$criteria = new \CDbCriteria();
			$criteria->condition = '(urlElementType <> :elementType or urlElementId <> :elementId) and urlValue = :urlValue';
			$criteria->params = array(
				':elementType' => 'node',
				':elementId' => $this->nodeId,
				':urlValue' => $this->nodeUrl,
			);
			$url = Url::model()->find($criteria);
			if($url !== null) {
				$this->addError('nodeUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		} else {
			$url = Url::model()->findByPk($this->nodeUrl);
			if($url !== null) {
				$this->addError('nodeUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		}
	}

	/**
	 * Upgraded save method to handle business logic (stored procedures)
	 * this is specific to tree management @see CActiveRecord::save()
	 *
	 * @param boolean $runValidation perform validation
	 * @param array   $attributes    attributes to save
	 * @param integer $targetNodeId  nodeId where the current node will be saved
	 *
	 * @return boolean
	 * @since  1.0.0
	 * @todo   Create a better signature with targetNodeId at the end to be compliant with Ar
	 */
	public function save($runValidation=true, $attributes=null, $targetNodeId=null) {
		if(($runValidation !== true) || ($this->validate($attributes) === true)) {
			if(($this->getIsNewRecord()===true) && ($targetNodeId !== null)) {
				return $this->insert($attributes, $targetNodeId);
			} elseif(($this->getIsNewRecord()===true) && ($targetNodeId === null)) {
				throw new \CException(\Yii::t('sweelix', 'NodeId is mandatory to create a new node'));
			} else {
				return $this->update($attributes);
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Insert a nodes
	 *
	 * Insert current node into the tree structure
	 *
	 * @param array   $attributes   attributes to save
	 * @param integer $targetNodeId nodeId where the current node will be saved
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function insert($attributes=null, $targetNodeId=null) {
		if($this->getIsNewRecord() !== true) {
			throw new \CDbException(\Yii::t('yii', 'The active record cannot be inserted to database because it is not new.'));
		}
		if($this->beforeSave() === true) {
			\Yii::trace('Insert node into nodeId : '.$targetNodeId, 'sweelix.yii1.ext.entities');
			if($targetNodeId === null) {
				$targetNodeId = 0;
			}
			$insert = DaoNode::insert($this, $targetNodeId);
			if($insert === true) {
				$this->_pk=$this->getPrimaryKey();
				$this->afterSave();
				$this->setIsNewRecord(false);
				$this->setScenario('update');
				return true;
			}
		}
		return false;
	}

	/**
	 * Move a node in the tree structure
	 *
	 * Move current node with $mode (in, after, before) the targetId
	 * node in the tree structure
	 *
	 * @param string  $where	'in','after','before'
	 * @param integer $targetNodeId target nodeId
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function move($where = 'in', $targetNodeId) {
		try {
			\Yii::trace('Move node '.$this->nodeId.' '.$where.' nodeId : '.$targetNodeId, 'sweelix.yii1.ext.entities');
			if($this->getIsNewRecord()!==true) {
				$where = strtolower($where);
				switch($where) {
					case 'first':
					case 'last':
					case 'in' :
					case 'before' :
					case 'after' :
					break;
					default :
						throw new \Exception('where parameter is mandatory');
					break;
				}
				$result = DaoNode::move($this, $targetNodeId, $where);
			} else {
				$result = false;
			}
		} catch ( \Exception $e ) {
			$result = false;
			\Yii::log('Error in '.__METHOD__.'():'.$e->getMessage(), \CLogger::LEVEL_ERROR, 'sweelix.yii1.ext.entities');
		}
		return $result;
	}

	/**
	 * Delete method Override the delete method to delete all sub
	 * nodes @see CActiveRecord::deleteByPk()
	 *
	 * @param integer $pk	nodeId (primary key)
	 * @param string  $condition sql condition
	 * @param array   $params    parameters to apply to the condition
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function deleteByPk($pk, $condition='', $params=array()) {
		\Yii::trace(__METHOD__.'()', 'sweelix.yii1.ext.entities');
		$deletedNodes = null;
		$result = DaoNode::deleteByPk(self::getDbConnection(), $pk, $deletedNodes);
		return count($result);
	}

	/**
	 * Get parent node of current node.
	 *
	 * @return Node|false
	 * @since  1.0.0
	 */
	public function getParent() {
		if($this->_parent === null) {
			$criteria = new \CDbCriteria();
			$criteria->condition = 'nodeLeftId < :nodeLeftId and nodeRightId > :nodeRightId and nodeLevel = :nodeLevel';
			$criteria->params = array(
				':nodeLeftId'=>$this->nodeLeftId,
				':nodeRightId'=>$this->nodeRightId,
				':nodeLevel'=>($this->nodeLevel - 1)
			);
			$this->_parent = Node::model()->find($criteria);
			if($this->_parent === null) {
				$this->_parent = false;
			}
		}
		return $this->_parent;
	}

	/**
	 * Reorder contents for current node
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function reOrder() {
		\Yii::trace(__METHOD__.'()', 'sweelix.yii1.ext.entities');
		$result = DaoNode::reOrder($this);
		return $result;
	}

	/**
	 * Check if tagId is affected to current node
	 *
	 * @param integer $tagId tagId to check
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function hasTag($tagId) {
		if(isset($this->_hasTags[$tagId]) === false) {
			$criteriaBuilder = new CriteriaBuilder('node');
			$criteriaBuilder->filterBy('nodeId', $this->nodeId);
			$criteriaBuilder->filterBy('tagId', $tagId);
			$result = $criteriaBuilder->count();
			if($result > 0) {
				$this->_hasTags[$tagId] = true;
			} else {
				$this->_hasTags[$tagId] = false;
			}
		}
		return $this->_hasTags[$tagId];
	}

	/**
	 * Check if groupId is affected to current node
	 *
	 * @param integer $groupId groupId to check
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function hasGroup($groupId) {
		if(isset($this->_hasGroups[$groupId]) === false) {
			$criteriaBuilder = new CriteriaBuilder('node');
			$criteriaBuilder->filterBy('nodeId', $this->nodeId);
			$criteriaBuilder->filterBy('groupId', $groupId);
			$result = $criteriaBuilder->count();
			if($result > 0) {
				$this->_hasGroups[$groupId] = true;
			} else {
				$this->_hasGroups[$groupId] = false;
			}
		}
		return $this->_hasGroups[$groupId];
	}

	/**
	 * Entity rules
	 *
	 * @return array validation rules for model attributes.
	 * @since  1.0.0
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('nodeTitle, nodeUrl, authorId', 'required', 'on'=>'updateDetail'),
			array('nodeUrl', 'checkUrlAvailable', 'mode'=>'update', 'on'=>'updateDetail'),
			array('nodeData', 'safe', 'on'=>'updateDetail'),
			array('nodeSignature', 'checkSignature', 'on' => 'updateDetail'),
			array('nodeStatus, nodeDisplayMode', 'required', 'on'=>'updateProperty'),
			array('nodeRedirection, templateId', 'safe', 'on'=>'updateProperty'),
			array('nodeStatus, nodeDisplayMode', 'required', 'on'=>'createStep1'),
			array('nodeRedirection, templateId', 'safe', 'on'=>'createStep1'),
			array('nodeTitle, nodeStatus, nodeDisplayMode', 'required', 'on'=>'createStep2'),
			array('nodeData, nodeUrl, nodeRedirection, templateId', 'safe', 'on'=>'createStep2'),
			array('nodeUrl', 'checkUrlAvailable', 'on'=>'createStep2'),
		);
	}

	/**
	 * Prepare properties to be conform with DB structure
	 *
	 * @return boolean validation status
	 * @since  1.0.0
	 */
	public function beforeValidate() {
		$status = parent::beforeValidate();
		switch($this->nodeDisplayMode) {
			case 'list' :
				$this->nodeRedirection = null;
				break;
			case 'redirect' :
				// $this->templateId = null;
				break;
			case 'first' :
			default :
				$this->nodeRedirection = null;
				// $this->templateId = null;
				break;
		}
		return $status;
	}

	/**
	 * Thandle urls after save
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function afterSave() {
		parent::afterSave();
		Url::store($this->nodeUrl, 'node', $this->nodeId);
		$this->_nodeSignature = $this->getSignature();
	}

	public function beforeSave() {
		if($this->isNewRecord === true) {
			$this->nodeCreateDate = new \CDbExpression('NOW()');
		} else {
			if(empty($this->nodeCreateDate) === true) {
				$this->nodeCreateDate  = new \CDbExpression('NOW()');
			}
			$this->nodeUpdateDate = new \CDbExpression('NOW()');
		}
		return parent::beforeSave();
	}

	/**
	 * attributes labels
	 *
	 * @return array customized attribute labels (name=>label)
	 * @since  1.0.0
	 */
	public function attributeLabels() {
		return array(
			'nodeId' => \Yii::t('sweelix', 'Id'),
			'nodeTitle' => \Yii::t('sweelix', 'Title'),
			'nodeUrl' => \Yii::t('sweelix', 'URL'),
			'nodeData' => \Yii::t('sweelix', 'Data'),
			'nodeCreateDate' => \Yii::t('sweelix', 'Create Date'),
			'nodeUpdateDate' => \Yii::t('sweelix', 'Update Date'),
			'nodeDisplayMode' => \Yii::t('sweelix', 'Display Mode'),
			'nodeRedirection' => \Yii::t('sweelix', 'Redirection'),
			'nodeLeftId' => \Yii::t('sweelix', 'Left id'),
			'nodeRightId' => \Yii::t('sweelix', 'Right id'),
			'nodeLevel' => \Yii::t('sweelix', 'Level'),
			'nodeStatus' => \Yii::t('sweelix', 'Status'),
			'nodeViewed' => \Yii::t('sweelix', 'Viewed'),
			'authorId' => \Yii::t('sweelix', 'Author'),
			'templateId' => \Yii::t('sweelix', 'Template'),
			'languageId' => \Yii::t('sweelix', 'Language'),
		);
	}

	/**
	 * The followings are the available model relations:
	 *
	 * @return array relational rules.
	 * @since  1.0.0
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\entities\Content', 'nodeId'),
			'metas' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Meta', 'nodeMeta(nodeId, metaId)'),
			'tags' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Tag', 'nodeTag(nodeId, tagId)'),
			'author' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Author', 'authorId'),
			'redirection' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Node', 'nodeRedirection'),
			'template' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Template', 'templateId'),
		);
	}

	/**
	 * Return children contents of current node
	 *
	 * @return     Content[]
	 * @since      2.0.0
	 */
	public function getContents($published = true, $reverse = false) {
		return $this->getContentsBuilder($published, $reverse)->findAll();
	}

	/**
	 * Return criteria builder to obtain children of current node
	 *
	 * @return     Node[]
	 * @since      1.0.0
	 */
	public function getChildren($published = true, $reverse = false) {
		return $this->getChildrenBuilder($published, $reverse)->findAll();
	}

	/**
	 * Return criteria builder to obtain children of current node
	 *
	 * @return CriteriaBuilder
	 * @since  2.0.0
	 */
	public function getChildrenBuilder($published = true, $reverse = false) {
		$criteriaBuilder = new CriteriaBuilder('node');
		$criteriaBuilder->filterBy('nodeLeftId', $this->nodeLeftId, '>');
		$criteriaBuilder->filterBy('nodeRightId', $this->nodeRightId, '<');
		$criteriaBuilder->filterBy('nodeLevel', ($this->nodeLevel + 1));
		if($reverse === true) {
			$criteriaBuilder->orderBy('nodeLeftId', 'DESC');
		} else {
			$criteriaBuilder->orderBy('nodeLeftId');
		}

		if ($published === true)
			$criteriaBuilder->published();
		return $criteriaBuilder;
	}

	/**
	 * Return criteria builder to obtain children contents of current node
	 *
	 * @return CriteriaBuilder
	 * @since  2.0.0
	 */
	public function getContentsBuilder($published = true, $reverse = false) {
		$criteriaBuilder = new CriteriaBuilder('content');
		$criteriaBuilder->filterBy('nodeId', $this->nodeId);
		if($reverse === true) {
			$criteriaBuilder->orderBy('contentOrder', 'DESC');
		} else {
			$criteriaBuilder->orderBy('contentOrder');
		}

		if ($published === true)
			$criteriaBuilder->published();
		return $criteriaBuilder;
	}
	/**
	 * Check if current node can be published
	 *
	 * @return boolean
	 * @since  1.9.0
	 */
	public function isPublishable() {
		$isPublishable = true;
		if($this->nodeStatus !== 'online') {
			$isPublishable = false;
		}
		return $isPublishable;
	}

	/**
	 * Massive setter
	 *
	 * (non-PHPdoc)
	 * @see CModel::setAttributes()
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function setAttributes($values, $safeOnly=true) {
		if(($this->asa('elasticProperties') !== null) && ($this->asa('elasticProperties')->getEnabled() === true)) {
			$this->asa('elasticProperties')->setAttributes($values, $safeOnly);
			$values = $this->asa('elasticProperties')->filterOutElasticAttributes($values);
		}
		parent::setAttributes($values, $safeOnly);
	}

	/**
	 * Massive getter
	 *
	 * (non-PHPdoc)
	 * @see CActiveRecord::getAttributes()
	 *
	 * @return array
	 * @since  2.0.0
	 */
	public function getAttributes($names=true) {
		$attributes = parent::getAttributes($names);
		if(($this->asa('elasticProperties') !== null) && ($this->asa('elasticProperties')->getEnabled() === true)) {
			$elasticAttributes = $this->asa('elasticProperties')->getAttributes($names);
			$attributes = \CMap::mergeArray($attributes, $elasticAttributes);
		}
		return $attributes;
	}
}
