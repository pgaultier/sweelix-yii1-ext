<?php
/**
 * File Tag.php
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
 * Class Tag
 *
 * This is the model class for table "tags".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 * @since     1.0.0
 *
 * @property integer   $tagId
 * @property string    $tagTitle
 * @property string    $tagUrl
 * @property string    $tagData
 * @property datetime  $tagCreateDate
 * @property datetime  $tagUpdateDate
 * @property integer   $groupId
 * @property integer   $templateId
 * @property integer   $languageId
 * @property Content[] $contents
 * @property Node[]    $nodes
 * @property Group     $group
 * @property Language  $language
 * @property Template  $template
 */
class Tag extends \CActiveRecord {
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
	 * Define table name
	 *
	 * @return string the associated database table name
	 * @since  1.0.0
	 */
	public function tableName() {
		if($this->getDbConnection()->tablePrefix === null) {
			return 'tags';
		} else {
			return '{{tags}}';
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
			array('tagTitle', 'required'),
			array('tagUrl', 'length', 'max'=>255),
			array('groupId, templateId', 'length', 'max'=>20),
			array('languageId', 'length', 'max'=>8),
			array('tagData, tagCreateDate, tagUpdateDate', 'safe'),
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
			'contents' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Content', 'contentTag(tagId, contentId)'),
			'nodes' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Node', 'nodeTag(tagId, nodeId)'),
			'group' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Group', 'groupId'),
			'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Language', 'languageId'),
			'template' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Template', 'templateId'),
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
			'tagId' => \Yii::t('sweelix', 'Tag'),
			'tagTitle' => \Yii::t('sweelix', 'Tag Title'),
			'tagUrl' => \Yii::t('sweelix', 'Tag Url'),
			'tagData' => \Yii::t('sweelix', 'Tag Data'),
			'tagCreateDate' => \Yii::t('sweelix', 'Tag Create Date'),
			'tagUpdateDate' => \Yii::t('sweelix', 'Tag Update Date'),
			'groupId' => \Yii::t('sweelix', 'Group'),
			'templateId' => \Yii::t('sweelix', 'Template'),
			'languageId' => \Yii::t('sweelix', 'Language'),
		);
	}
}