<?php
/**
 * File Author.php
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
 * Class Author
 *
 * This is the model class for table "authors".
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
 * @property integer   $authorId
 * @property string    $authorEmail
 * @property string    $authorPassword
 * @property string    $authorFirstname
 * @property string    $authorLastname
 * @property string    $authorLastLogin
 * @property string    $languageId
 * @property Language  $language
 * @property Content[] $contents
 * @property Node[]    $nodes
 */
class Author extends \CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className entity classname automatically set
     *
     * @return Author the static model class
     * @since  1.0.0
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Define table name
     *
     * @return string the associated database table name
     * @since  1.0.0
     */
    public function tableName()
    {
        if ($this->getDbConnection()->tablePrefix === null) {
            return 'authors';
        } else {
            return '{{authors}}';
        }
    }

    /**
     * Business rules related to database
     *
     * @return array validation rules for model attributes.
     * @since  1.0.0
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('authorEmail, authorPassword', 'required'),
            array('authorEmail', 'length', 'max' => 255),
            array('authorPassword', 'length', 'max' => 64),
            array('authorFirstname, authorLastname', 'length', 'max' => 45),
            array('languageId', 'length', 'max' => 8),
            array('authorLastLogin', 'safe'),
        );
    }

    /**
     * The followings are the available model relations:
     *
     * @return array relational rules.
     * @since  1.0.0
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'language' => array(self::BELONGS_TO, 'sweelix\yii1\ext\db\ar\Language', 'languageId'),
            'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Content', 'authorId'),
            'nodes' => array(self::HAS_MANY, 'sweelix\yii1\ext\db\ar\Node', 'authorId'),
        );
    }

    /**
     * attributes labels
     *
     * @return array customized attribute labels (name=>label)
     * @since  1.0.0
     */
    public function attributeLabels()
    {
        return array(
            'authorId' => \Yii::t('sweelix', 'Author'),
            'authorEmail' => \Yii::t('sweelix', 'Author Email'),
            'authorPassword' => \Yii::t('sweelix', 'Author Password'),
            'authorFirstname' => \Yii::t('sweelix', 'Author Firstname'),
            'authorLastname' => \Yii::t('sweelix', 'Author Lastname'),
            'authorLastLogin' => \Yii::t('sweelix', 'Author Last Login'),
            'languageId' => \Yii::t('sweelix', 'Language'),
        );
    }
}