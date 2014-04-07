<?php
/**
 * File Token.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 */

namespace sweelix\yii1\ext\db\ar;

/**
 * This is the model class for table "tokens".
 *
 * The followings are the available columns in table 'tokens':
 * @property string  $elementProperty
 * @property string  $elementId
 * @property string  $elementType
 * @property string  $elementTitle
 * @property string  $tokenKey
 * @property float   $tokenNumeric
 * @property string  $contentId
 * @property string  $nodeId
 * @property string  $tagId
 * @property string  $groupId
 * @property integer $tokenWeight
 * @property string $tokenDateCreate
 *
 * The followings are the available model relations:
 * @property Tags $tag
 * @property Contents $content
 * @property Groups $group
 * @property Nodes $node
 */
class Token extends \CActiveRecord {
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
	 * Define table name
	 *
	 * @return string the associated database table name
	 * @since  1.0.0
	 */
	public function tableName() {
		if($this->getDbConnection()->tablePrefix === '') {
			return 'tokens';
		} else {
			return '{{tokens}}';
		}
	}

	/**
	 * Business rules related to database
	 *
	 * @return array validation rules for model attributes.
	 * @since  1.0.0
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('elementProperty, elementId, elementType, tokenKey, tokenWeight, tokenDateCreate', 'required'),
			array('tokenWeight', 'numerical', 'integerOnly'=>true),
			array('tokenNumeric', 'numerical'),
			array('elementProperty, elementType, tokenKey', 'length', 'max'=>255),
			array('elementId, contentId, nodeId, tagId, groupId', 'length', 'max'=>20),
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
			'tag' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Tag', 'tagId'),
			'content' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Content', 'contentId'),
			'group' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Group', 'groupId'),
			'node' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Node', 'nodeId'),
		);
	}

	/**
	 * attributes labels
	 *
	 * @return array customized attribute labels (name=>label)
	 * @since  1.0.0
	 */
	public function attributeLabels() {
		return array(
			'elementProperty' => \Yii::t('sweelix', 'Element Property'),
			'elementId' => \Yii::t('sweelix', 'Element'),
			'elementType' => \Yii::t('sweelix', 'Element Type'),
			'elementTitle' => \Yii::t('sweelix', 'Title'),
			'tokenKey' => \Yii::t('sweelix', 'Keyword'),
			'tokenNumeric' => \Yii::t('sweelix', 'Numeric'),
			'contentId' => \Yii::t('sweelix', 'Content'),
			'nodeId' => \Yii::t('sweelix', 'Node'),
			'tagId' => \Yii::t('sweelix', 'Tag'),
			'groupId' => \Yii::t('sweelix', 'Group'),
			'tokenWeight' => \Yii::t('sweelix', 'Keyword Weight'),
			'tokenDateCreate' => \Yii::t('sweelix', 'Keyword Date Create'),
		);
	}
}