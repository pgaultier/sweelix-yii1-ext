<?php
/**
 * File ContentMeta.php
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
 * Class ContentMeta
 *
 * This is the model class for table "contentMeta".
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
 * @property integer $contentId
 * @property integer $metaId
 * @property string  $contentMetaValue
 */
class ContentMeta extends \CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className entity classname automatically set
     *
     * @return ContentMeta the static model class
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
            return 'contentMeta';
        } else {
            return '{{contentMeta}}';
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
            array('contentId', 'required'),
            array('contentId', 'length', 'max' => 20),
            array('metaId', 'length', 'max' => 32),
            array('contentMetaValue', 'safe'),
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
        return array();
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
            'contentId' => \Yii::t('sweelix', 'Content'),
            'metaId' => \Yii::t('sweelix', 'Meta'),
            'contentMetaValue' => \Yii::t('sweelix', 'Content Meta Value'),
        );
    }
}