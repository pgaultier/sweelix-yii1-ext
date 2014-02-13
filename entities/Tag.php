<?php
/**
 * File Tag.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.db.entities
 */

namespace sweelix\yii1\ext\entities;
use sweelix\yii1\ext\db\ar\Tag as ActiveRecordTag;

/**
 * Class Tag
 *
 * This is the model class for table "tags".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.db.entities
 * @since     1.0.0
 *
 * @property mixed       $tagElements
 * @property Content[] $contents
 * @property Node[]    $nodes
 * @property Group     $group
 * @property Language  $language
 * @property Template  $template
 *
 * @method mixed   prop()		  prop(string $property, array $arrayCell=null) fetch a subproperty without crashing
 * @method string  getCompositeTemplate()  getCompositeTemplate(integer $templateId)     fetch composite template
 * @method string  getSubProperties()      getSubProperties(integer $templateId)	 retrieve list of subproperties
 * @method array   getTemplateDefinition() getTemplateDefinition(integer $templateId)    get full template definition
 * @method boolean getIsSubProperty()      getIsSubProperty(string $name)		check if subproperty exists
 */
class Tag extends ActiveRecordTag {
	public $tagSelected=false;

	/**
	 * @var array, store sub elements
	 */
	private $_tagElements = null;

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Tag the static model class
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
					'elasticStorage' => 'tagData',
					'templateConfig' => array($this, 'getRawTemplate'),
					'pathParameters' => array('{tagId}' => 'tagId'),
				),
 				'indexer' => array(
 					'class' => 'sweelix\yii1\ext\behaviors\Token',
 					'type' => 'tag',
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
		return array(
			'tag' => $this->tagId,
			'url' => $this->tagUrl,
			'action' => $action
		);
	}

	/**
	 * Set tag elements to blob data
	 *
	 * @param mixed $data data structure to store
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setTagElements($data) {
		$this->_tagElements = $data;
		$this->tagData = \CJSON::encode($data);
	}

	/**
	 * Retrieve tag elements from blob data
	 *
	 * @param boolean $asArray decode element as array instead of object
	 *
	 * @return mixed
	 * @since  1.0.0
	 */
	public function getTagElements($asArray = true) {
		if($this->_tagElements === null) {
			$this->_tagElements = \CJSON::decode($this->tagData, $asArray);
		}
		return $this->_tagElements;
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
			array('tagId', 'required', 'on'=>'updateTags'),
			array('tagSelected', 'safe', 'on'=>'updateTags'),
			array('tagId, groupId, templateId', 'required', 'on'=>'updateProperty'),
			array('tagId, tagTitle, tagUrl', 'required', 'on'=>'updateDetail'),
			array('tagUrl', 'checkUrlAvailable', 'mode'=>'update', 'on'=>'updateDetail'),
			array('tagData', 'safe', 'on'=>'updateDetail'),
			array('groupId, templateId', 'required', 'on'=>'createStep1'),
			array('tagTitle, groupId, templateId', 'required', 'on'=>'createStep2'),
			array('tagData, tagUrl', 'safe', 'on'=>'createStep2'),
			array('tagUrl', 'checkUrlAvailable', 'on'=>'createStep2'),
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
				':elementType' => 'tag',
				':elementId' => $this->tagId,
				':urlValue' => $this->tagUrl,
			);
			$url = Url::model()->find($criteria);
			if($url !== null) {
				$this->addError('tagUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		} else {
			$url = Url::model()->findByPk($this->tagUrl);
			if($url !== null) {
				$this->addError('tagUrl', \Yii::t('sweelix', 'Url already exists'));
			}
		}
	}

	/**
	 * Prepare properties to be conform with DB structure
	 *
	 * @return boolean validation status
	 * @since  1.0.0
	 */
	public function beforeValidate() {
		return parent::beforeValidate();
	}

	/**
	 * Thandle urls after save
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function afterSave() {
		parent::afterSave();
		Url::store($this->tagUrl, 'tag', $this->tagId);
	}

	/**
	 * Handle date before creation / update
	 * @see CActiveRecord::beforeSave()
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function beforeSave() {
		if($this->isNewRecord === true) {
			$this->tagCreateDate = new \CDbExpression('NOW()');
		} else {
			if(empty($this->tagCreateDate) === true) {
				$this->tagCreateDate  = new \CDbExpression('NOW()');
			}
			$this->tagUpdateDate = new \CDbExpression('NOW()');
		}
		return parent::beforeSave();
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
			'contents' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Content', 'contentTag(tagId, contentId)'),
			'nodes' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Node', 'nodeTag(tagId, nodeId)'),
			'group' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Group', 'groupId'),
			'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Language', 'languageId'),
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
