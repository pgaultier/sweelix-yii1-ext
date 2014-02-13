<?php
/**
 * File Language.php
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
 * Class Language
 *
 * This is the model class for table "languages".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @since     1.0.0
 *
 * @property string    $languageId
 * @property string    $languageTitle
 * @property integer   $languageIsActive
 * @property Author[]  $authors
 * @property Content[] $contents
 * @property Group[]   $groups
 * @property Tag[]     $tags
 */
class Language extends \CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Language the static model class
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
			return 'languages';
		} else {
			return '{{languages}}';
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
			array('languageTitle', 'required'),
			array('languageIsActive', 'numerical', 'integerOnly'=>true),
			array('languageId', 'length', 'max'=>8),
			array('languageTitle', 'length', 'max'=>255),
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
			'authors' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Author', 'languageId'),
			'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Content', 'languageId'),
			'groups' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Group', 'languageId'),
			'tags' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Tag', 'languageId'),
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
			'languageId' => \Yii::t('sweelix', 'Language'),
			'languageTitle' => \Yii::t('sweelix', 'Language Title'),
			'languageIsActive' => \Yii::t('sweelix', 'Language Is Active'),
		);
	}
}