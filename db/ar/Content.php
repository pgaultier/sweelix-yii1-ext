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
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 */

namespace sweelix\yii1\ext\db\ar;

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
 * @category  ar
 * @package   sweelix.yii1.ext.db.ar
 * @since     1.0.0
 *
 * @property integer  $contentId
 * @property string   $contentTitle
 * @property string   $contentSubtitle
 * @property string   $contentUrl
 * @property string   $contentData
 * @property integer  $contentOrder
 * @property datetime $contentStartDate
 * @property datetime $contentEndDate
 * @property string   $contentStatus
 * @property integer  $contentViewed
 * @property datetime $contentCreateDate
 * @property datetime $contentUpdateDate
 * @property integer  $nodeId
 * @property integer  $authorId
 * @property integer  $templateId
 * @property string   $languageId
 * @property Meta[]   $metas
 * @property Tag[]    $tags
 * @property Author   $author
 * @property Language $language
 * @property Node     $node
 * @property Template $template
 */
class Content extends \CActiveRecord {
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
		if($this->getDbConnection()->tablePrefix === null) {
			return 'contents';
		} else {
			return '{{contents}}';
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
			array('contentTitle', 'required'),
			array('contentOrder', 'numerical', 'integerOnly'=>true),
			array('contentUrl', 'length', 'max'=>255),
			array('contentStatus', 'length', 'max'=>7),
			array('contentViewed, nodeId, authorId, templateId', 'length', 'max'=>20),
			array('languageId', 'length', 'max'=>8),
			array('contentSubtitle, contentData, contentStartDate, contentEndDate, contentCreateDate, contentUpdateDate', 'safe'),
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
			'metas' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Meta', 'contentMeta(contentId, metaId)'),
			'tags' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Tag', 'contentTag(contentId, tagId)'),
			'author' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Author', 'authorId'),
			'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Language', 'languageId'),
			'node' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Node', 'nodeId'),
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
			'contentId' => \Yii::t('sweelix', 'Content'),
			'contentTitle' => \Yii::t('sweelix', 'Content Title'),
			'contentSubtitle' => \Yii::t('sweelix', 'Content Subtitle'),
			'contentUrl' => \Yii::t('sweelix', 'Content Url'),
			'contentData' => \Yii::t('sweelix', 'Content Data'),
			'contentOrder' => \Yii::t('sweelix', 'Content Order'),
			'contentStartDate' => \Yii::t('sweelix', 'Content Start Date'),
			'contentEndDate' => \Yii::t('sweelix', 'Content End Date'),
			'contentStatus' => \Yii::t('sweelix', 'Content Status'),
			'contentViewed' => \Yii::t('sweelix', 'Content Viewed'),
			'contentCreateDate' => \Yii::t('sweelix', 'Content Create Date'),
			'contentUpdateDate' => \Yii::t('sweelix', 'Content Update Date'),
			'nodeId' => \Yii::t('sweelix', 'Node'),
			'authorId' => \Yii::t('sweelix', 'Author'),
			'templateId' => \Yii::t('sweelix', 'Template'),
			'languageId' => \Yii::t('sweelix', 'Language'),
		);
	}
}