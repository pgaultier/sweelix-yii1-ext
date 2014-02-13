<?php
/**
 * File ContentMeta.php
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
use sweelix\yii1\ext\db\ar\ContentMeta as ActiveRecordContentMeta;

/**
 * Class ContentMeta
 *
 * This is the model class for table "contentMeta".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.db.entities
 * @since     1.0.0
 */
class ContentMeta extends ActiveRecordContentMeta {
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return ContentMeta the static model class
	 * @since  1.0.0
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
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
		);
	}
}