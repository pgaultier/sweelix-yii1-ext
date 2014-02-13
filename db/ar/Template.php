<?php
/**
 * File Template.php
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
 * Class Template
 *
 * This is the model class for table "templates".
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
 * @property integer   $templateId
 * @property string    $templateTitle
 * @property string    $templateDefinition
 * @property string    $templateController
 * @property string    $templateAction
 * @property string    $templateComposite
 * @property string    $templateType
 * @property Content[] $contents
 * @property Group[]   $groups
 * @property Node[]    $nodes
 * @property Tag[]     $tags
 */
class Template extends \CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Template the static model class
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
			return 'templates';
		} else {
			return '{{templates}}';
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
			array('templateDefinition, templateTitle, templateType', 'required'),
			array('templateDefinition, templateController, templateAction, templateComposite', 'length', 'max'=>255),
			array('templateType', 'length', 'max'=>6),
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
			'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Content', 'templateId'),
			'groups' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Group', 'templateId'),
			'nodes' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Node', 'templateId'),
			'tags' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Tag', 'templateId'),
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
			'templateId' => \Yii::t('sweelix', 'Template'),
			'templateTitle' => \Yii::t('sweelix', 'Template Title'),
			'templateDefinition' => \Yii::t('sweelix', 'Template Definition'),
			'templateController' => \Yii::t('sweelix', 'Template Controller'),
			'templateAction' => \Yii::t('sweelix', 'Template Action'),
			'templateComposite' => \Yii::t('sweelix', 'Template Composite'),
			'templateType' => \Yii::t('sweelix', 'Template Type'),
		);
	}
}