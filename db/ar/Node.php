<?php
/**
 * File Node.php
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
 * Class Node
 *
 * This is the model class for table "nodes".
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
 * @property integer   $nodeId
 * @property string    $nodeTitle
 * @property string    $nodeUrl
 * @property string    $nodeData
 * @property string    $nodeDisplayMode
 * @property integer   $nodeRedirection
 * @property integer   $nodeLeftId
 * @property integer   $nodeRightId
 * @property integer   $nodeLevel
 * @property string    $nodeStatus
 * @property integer   $nodeViewed
 * @property datetime  $nodeCreateDate
 * @property datetime  $nodeUpdateDate
 * @property integer   $authorId
 * @property integer   $templateId
 * @property integer   $languageId
 * @property Content[] $contents
 * @property Meta[]    $metas
 * @property Tag[]     $tags
 * @property Author    $author
 * @property Node      $redirection
 * @property Node[]    $nodes
 * @property Template  $template
 */
class Node extends \CActiveRecord {
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
	 * Define table name
	 *
	 * @return string the associated database table name
	 * @since  1.0.0
	 */
	public function tableName() {
		if($this->getDbConnection()->tablePrefix === null) {
			return 'nodes';
		} else {
			return '{{nodes}}';
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
			array('nodeTitle', 'required'),
			array('nodeUrl', 'length', 'max'=>255),
			array('nodeDisplayMode, languageId', 'length', 'max'=>8),
			array('nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeViewed, authorId, templateId', 'length', 'max'=>20),
			array('nodeStatus', 'length', 'max'=>7),
			array('nodeData, nodeCreateDate, nodeUpdateDate, nodeLeftId, nodeRightId, nodeLevel', 'safe'),
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
			'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Content', 'nodeId'),
			'metas' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Meta', 'nodeMeta(nodeId, metaId)'),
			'tags' => array(self::MANY_MANY, 'sweelix\yii1\ext\db\ar\Tag', 'nodeTag(nodeId, tagId)'),
			'author' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Author', 'authorId'),
			'redirection' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Node', 'nodeRedirection'),
			'nodes' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Node', 'nodeRedirection'),
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
			'nodeId' => \Yii::t('sweelix', 'Node'),
			'nodeTitle' => \Yii::t('sweelix', 'Node Title'),
			'nodeUrl' => \Yii::t('sweelix', 'Node Url'),
			'nodeData' => \Yii::t('sweelix', 'Node Data'),
			'nodeCreateDate' => \Yii::t('sweelix', 'Node Create Date'),
			'nodeUpdateDate' => \Yii::t('sweelix', 'Node Update Date'),
			'nodeDisplayMode' => \Yii::t('sweelix', 'Node Display Mode'),
			'nodeRedirection' => \Yii::t('sweelix', 'Node Redirection'),
			'nodeLeftId' => \Yii::t('sweelix', 'Node Left'),
			'nodeRightId' => \Yii::t('sweelix', 'Node Right'),
			'nodeLevel' => \Yii::t('sweelix', 'Node Level'),
			'nodeStatus' => \Yii::t('sweelix', 'Node Status'),
			'nodeViewed' => \Yii::t('sweelix', 'Node Viewed'),
			'authorId' => \Yii::t('sweelix', 'Author'),
			'templateId' => \Yii::t('sweelix', 'Template'),
			'languageId' => \Yii::t('sweelix', 'Language'),
		);
	}
}