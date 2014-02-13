<?php
/**
 * File Group.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 */

namespace sweelix\yii1\ext\db\ar;

/**
 * Class Group
 *
 * This is the model class for table "groups".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 * @since     1.0.0
 *
 * @property integer  $groupId
 * @property string   $groupTitle
 * @property string   $groupType
 * @property string   $groupUrl
 * @property string   $groupData
 * @property datetime $groupCreateDate
 * @property datetime $groupUpdateDate
 * @property integer  $templateId
 * @property string   $languageId
 * @property Language $language
 * @property Template $template
 * @property Tag[]    $tags
 */
class Group extends \CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Group the static model class
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
		if($this->getDbConnection()->tablePrefix === null) {
			return 'groups';
		} else {
			return '{{groups}}';
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
			array('groupTitle, groupType', 'required'),
			array('groupType, languageId', 'length', 'max'=>8),
			array('groupUrl', 'length', 'max'=>255),
			array('templateId', 'length', 'max'=>20),
			array('groupData, groupCreateDate, groupUpdateDate', 'safe'),
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
			'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Language', 'languageId'),
			'template' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Template', 'templateId'),
			'tags' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Tag', 'groupId'),
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
			'groupId' => \Yii::t('sweelix', 'Group'),
			'groupTitle' => \Yii::t('sweelix', 'Group Title'),
			'groupType' => \Yii::t('sweelix', 'Group Type'),
			'groupUrl' => \Yii::t('sweelix', 'Group Url'),
			'groupData' => \Yii::t('sweelix', 'Group Data'),
			'groupCreateDate' => \Yii::t('sweelix', 'Group Create Date'),
			'groupUpdateDate' => \Yii::t('sweelix', 'Group Update Date'),
			'templateId' => \Yii::t('sweelix', 'Template'),
			'languageId' => \Yii::t('sweelix', 'Language'),
		);
	}
}