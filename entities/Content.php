<?php
/**
 * File Content.php
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
use sweelix\yii1\ext\db\ar\Content as ActiveRecordContent;
use sweelix\yii1\ext\db\dao\Content as DaoContent;
use sweelix\yii1\ext\db\CriteriaBuilder;
use sweelix\yii1\ext\components\RouteEncoder;

/**
 * Class Content
 *
 * This is the model class for table "contents".
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
 * @property string   $contentSignature
 * @property boolean  $contentStartDateActive;
 * @property boolean  $contentEndDateActive;
 * @property mixed    $contentElements
 * @property Meta[]   $metas
 * @property Tag[]    $tags
 * @property Author   $author
 * @property Language $language
 * @property Node     $node
 * @property Template $template
 *
 * @method mixed   prop()                  prop(string $property, array $arrayCell=null) fetch a subproperty without crashing
 * @method string  getCompositeTemplate()  getCompositeTemplate(integer $templateId)     fetch composite template
 * @method string  getSubProperties()      getSubProperties(integer $templateId)         retrieve list of subproperties
 * @method array   getTemplateDefinition() getTemplateDefinition(integer $templateId)    get full template definition
 * @method boolean getIsSubProperty()      getIsSubProperty(string $name)                check if subproperty exists
 */
class Content extends ActiveRecordContent {
	/**
	 * @var array, store sub elements
	 */
	private $_contentElements = null;
	/**
	 * @var array, store tag checked to avoid multiple requests
	 */
	private $_hasTags = array();
	/**
	 * @var array, store group checked to avoid multiple requests
	 */
	private $_hasGroups = array();

	/**
	 * @var string current signature
	 */
	private $_contentSignature;

	/**
	 * Retrieve signature for current content
	 *
	 * @return string
	 * @since  1.9.0
	 */
	public function getContentSignature() {
		if($this->_contentSignature === null) {
			$this->_contentSignature = $this->getSignature();
		}
		return $this->_contentSignature;
	}

	/**
	 * Force signature
	 *
	 * @param string $signature
	 *
	 * @return void
	 * @since  1.9.0
	 */
	public function setContentSignature($signature) {
		$this->_contentSignature = $signature;
	}

	/**
	 * Get current signature
	 *
	 * @return string
	 */
	public function getSignature() {
		$attributes = $this->attributes;
		unset($attributes['contentCreateDate']);
		unset($attributes['contentUpdateDate']);
		return sha1(serialize($attributes));
	}

	/**
	 * check current signature against the one in database
	 *
	 * @return void
	 * @since  1.9.0
	 */
	public function checkSignature() {
		$currentContent = Content::model()->findByPk($this->contentId);
		if($currentContent instanceof Content) {
			$dbSignature = $currentContent->getSignature();
		}
		if($dbSignature != $this->getContentSignature()) {
			$this->addError('contentSignature', \Yii::t('sweelix', 'Content has been updated by {firstname} {lastname}', array('{firstname}' => $currentContent->author->authorFirstname, '{lastname}' =>$currentContent->author->authorLastname)));
		}
	}

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Content the static model class
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
					'elasticStorage' => 'contentData',
					'templateConfig' => array($this, 'getRawTemplate'),
					'pathParameters' => array('{nodeId}' => 'nodeId', '{contentId}' => 'contentId'),
				),
 				'indexer' => array(
 					'class' => 'sweelix\yii1\ext\behaviors\Token',
 					'type' => 'content',
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
		$route = RouteEncoder::encode($this->contentId);
		if(empty($action) === false) {
			$route = $route.'/'.$action;
		}
		return $route;
	}

	/**
	 * Set content elements to blob data
	 *
	 * @param mixed $data data structure to store
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setContentElements($data) {
		$this->_contentElements = $data;
		$this->contentData = \CJSON::encode($data);
	}

	/**
	 * Retrieve content elements from blob data
	 *
	 * @param boolean $asArray decode element as array instead of object
	 *
	 * @return mixed
	 * @since  1.0.0
	 */
	public function getContentElements($asArray = true) {
		if($this->_contentElements === null) {
			$this->_contentElements = \CJSON::decode($this->contentData, $asArray);
		}
		return $this->_contentElements;
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
	 * Move a content in the tree structure
	 *
	 * Move current content with $mode (top, bottom, up, down, after, before) the targetId
	 * node in the tree structure
	 *
	 * @param string  $where	   authorized elements 'top', 'bottom', 'up', 'down'
	 * @param integer $targetContentId target contentId
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function move($where = 'top' ,$targetContentId=null) {
		try {
			\Yii::trace('Move content '.$this->contentId.' '.$where.' contentId : '.$targetContentId, 'sweelix.yii1.ext.entities');
			if($this->getIsNewRecord()!==true) {
				$where = strtolower($where);
				switch($where) {
					case 'top' :
					case 'bottom' :
					case 'up' :
					case 'down' :
					case 'before' :
					case 'after' :
						break;
					default :
						throw new \Exception('where parameter is mandatory');
					break;
				}
				$result = DaoContent::move($this, $targetContentId, $where);
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
	 * Entity rules
	 *
	 * @return array validation rules for model attributes.
	 * @since  1.0.0
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contentTitle, contentUrl', 'required', 'on'=>'updateDetail'),
			array('contentUrl', 'checkUrlAvailable', 'mode'=>'update', 'on'=>'updateDetail'),
			array('contentSubtitle, contentData', 'safe', 'on'=>'updateDetail'),
			array('contentSignature', 'checkSignature', 'on' => 'updateDetail'),
			array('contentViewed', 'numerical', 'on'=>'updateProperty'),
			array('templateId', 'required', 'on'=>'updateProperty'),
			array('contentStartDate, contentEndDate', 'checkDate', 'on'=>'updateProperty'),
			array('nodeId', 'safe', 'on'=>'updateProperty'),
			array('contentViewed', 'numerical', 'on'=>'createStep1'),
			array('templateId', 'required', 'on'=>'createStep1'),
			array('contentStartDate, contentEndDate', 'checkDate', 'on'=>'createStep1'),
			array('nodeId', 'safe', 'on'=>'createStep1'),
			array('contentTitle, contentUrl, templateId, nodeId', 'required', 'on'=>'createStep2'),
			array('contentUrl', 'checkUrlAvailable', 'on'=>'createStep2'),
			array('contentSubtitle, contentData', 'safe', 'on'=>'createStep2'),
			array('contentViewed', 'numerical', 'on'=>'createStep2'),
			array('contentStartDate, contentEndDate', 'checkDate', 'on'=>'createStep2'),
		);
	}

	/**
	 * attributes labels
	 *
	 * @return array customized attribute labels (name=>label)
	 * @since  1.0.0
	 */
	public function attributeLabels() {
		return \CMap::mergeArray(
			parent::attributeLabels(),
			array(
				'contentStartDateActive' => \Yii::t('sweelix', 'Enable start date'),
				'contentEndDateActive' => \Yii::t('sweelix', 'Enable end date'),
			)
		);
	}

	/**
	 * Check if date timeframe is valid
	 *
	 * @param string $attribute name of the attribute to be validated
	 * @param array  $params    options specified in the validation rule
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function checkDate($attribute, $params) {
		if((\CPropertyValue::ensureBoolean($this->contentStartDateActive) === true)
			&& (\CPropertyValue::ensureBoolean($this->contentEndDateActive) === true)) {
			$startDate = \CDateTimeParser::parse($this->contentStartDate, \Yii::app()->getLocale()->getDateFormat('short'));
			$endDate = \CDateTimeParser::parse($this->contentEndDate, \Yii::app()->getLocale()->getDateFormat('short'));
			if($startDate > $endDate) {
				if($attribute === 'contentStartDate') {
					$this->addError('contentStartDate', \Yii::t('sweelix', 'Start date is incorrect'));
				} elseif($attribute === 'contentEndDate') {
					$this->addError('contentEndDate', \Yii::t('sweelix', 'End date is incorrect'));
				}
			}
		}
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
				':elementType' => 'content',
				':elementId' => $this->contentId,
				':urlValue' => $this->contentUrl,
			);
			$url = Url::model()->find($criteria);
			if($url !== null) {
				$this->addError('contentUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		} else {
			$url = Url::model()->findByPk($this->contentUrl);
			if($url !== null) {
				$this->addError('contentUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		}
	}

	/**
	 * Prepare data before saving content.
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function beforeSave() {
		if(\CPropertyValue::ensureBoolean($this->contentStartDateActive) === false) {
			$this->contentStartDate = null;
		} elseif(is_string($this->contentStartDate) === true) {
			$this->contentStartDate = \Yii::app()->getLocale()->dateFormatter->format('yyyy-MM-dd', \CDateTimeParser::parse($this->contentStartDate, \Yii::app()->getLocale()->getDateFormat('short')));
		}
		if(\CPropertyValue::ensureBoolean($this->contentEndDateActive) === false) {
			$this->contentEndDate = null;
		} else if(is_string($this->contentEndDate) === true) {
			$this->contentEndDate = \Yii::app()->getLocale()->dateFormatter->format('yyyy-MM-dd', \CDateTimeParser::parse($this->contentEndDate, \Yii::app()->getLocale()->getDateFormat('short')));
		}
		if($this->isNewRecord === true) {
			$this->contentCreateDate = new \CDbExpression('NOW()');
		} else {
			if(empty($this->contentCreateDate) === true) {
				$this->contentCreateDate  = new \CDbExpression('NOW()');
			}
			$this->contentUpdateDate = new \CDbExpression('NOW()');
		}
		return parent::beforeSave();
	}
	/**
	 * Convert date using current local
	 *
	 * @return void
	 * @since  1.0.0
	 */
	private function transcodeDates() {
		if(($this->contentStartDate !== null) && (is_string($this->contentStartDate) === true)) {
			$this->contentStartDate = \Yii::app()->getLocale()->dateFormatter->format(\Yii::app()->getLocale()->getDateFormat('short'), \CDateTimeParser::parse($this->contentStartDate, 'yyyy-MM-dd HH:mm:ss'));
		}
		if(($this->contentEndDate !== null) && (is_string($this->contentEndDate) === true)) {
			$this->contentEndDate = \Yii::app()->getLocale()->dateFormatter->format(\Yii::app()->getLocale()->getDateFormat('short'), \CDateTimeParser::parse($this->contentEndDate, 'yyyy-MM-dd HH:mm:ss'));
		}
	}

	/**
	 * Transcode date after save
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function afterSave() {
		parent::afterSave();
		$this->transcodeDates();
		Url::store($this->contentUrl, 'content', $this->contentId);
		$this->_contentSignature = $this->getSignature();
	}

	/**
	 * Transcode date after find
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function afterFind() {
		parent::afterFind();
		$this->transcodeDates();
	}
	/**
	 * Prepare properties to be conform with DB structure
	 *
	 * @return boolean validation status
	 * @since  1.0.0
	 */
	public function beforeValidate() {
		$status = parent::beforeValidate();
		if(\CPropertyValue::ensureBoolean($this->contentStartDateActive) === false) {
			$this->contentStartDate = null;
		}
		if(\CPropertyValue::ensureBoolean($this->contentEndDateActive) === false) {
			$this->contentEndDate = null;
		}
		return $status;
	}

	/**
	 * Date start active getter
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function getContentStartDateActive() {
		if(($this->contentStartDate === null) || (empty($this->contentStartDate) === true)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Date end active getter
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function getContentEndDateActive() {
		if(($this->contentEndDate === null) || (empty($this->contentEndDate) === true)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if tagId is affected to current content
	 *
	 * @param integer $tagId tagId to check
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function hasTag($tagId) {
		if(isset($this->_hasTags[$tagId]) === false) {
			$criteriaBuilder = new CriteriaBuilder('content');
			$criteriaBuilder->filterBy('contentId', $this->contentId);
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
	 * Check if current content can be published (check dates)
	 *
	 * @param boolean $checkStatus use the status with date to know if content can be published
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function isPublishable($checkStatus=true) {
		$isPublishable = true;
		if(($checkStatus===true) && ($this->contentStatus !== 'online')) {
			$isPublishable = false;
		}
		if(($isPublishable === true) && ($this->contentStartDateActive === true)) {
			$startDate = \CDateTimeParser::parse($this->contentStartDate, \Yii::app()->getLocale()->getDateFormat('short'));
			if(time() < $startDate) {
				$isPublishable = false;
			}
		}
		if(($isPublishable === true) && ($this->contentEndDateActive === true)) {
			$endDate = \CDateTimeParser::parse($this->contentEndDate, \Yii::app()->getLocale()->getDateFormat('short'));
			if(time() > $endDate) {
				$isPublishable = false;
			}
		}
		return $isPublishable;
	}
	/**
	 * Check if groupId is affected to current content
	 *
	 * @param integer $groupId groupId to check
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function hasGroup($groupId) {
		if(isset($this->_hasGroups[$groupId]) === false) {
			$criteriaBuilder = new CriteriaBuilder('content');
			$criteriaBuilder->filterBy('contentId', $this->contentId);
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
	 * The followings are the available model relations:
	 *
	 * @return array relational rules.
	 * @since  1.0.0
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'metas' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Meta', 'contentMeta(contentId, metaId)'),
			'tags' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Tag', 'contentTag(contentId, tagId)'),
			'author' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Author', 'authorId'),
			'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Language', 'languageId'),
			'node' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Node', 'nodeId'),
			'template' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Template', 'templateId'),
		);
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
