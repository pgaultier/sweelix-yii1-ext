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
 * @category  entities
 * @package   sweelix.yii1.ext.db.entities
 */

namespace sweelix\yii1\ext\entities;
use sweelix\yii1\ext\db\ar\Template as ActiveRecordTemplate;

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
 * @category  entities
 * @package   sweelix.yii1.ext.db.entities
 * @since     1.0.0
 *
 * @property Content[] $contents
 * @property Group[]   $groups
 * @property Node[]    $nodes
 * @property Tag[]     $tags
 */
class Template extends ActiveRecordTemplate {
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
	 * The followings are the available model relations:
	 *
	 * @return array relational rules.
	 * @since  1.0.0
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'contents' => array(self::HAS_MANY, 'sweelix\yii1\ext\entities\Content', 'templateId'),
			'groups' => array(self::HAS_MANY, 'sweelix\yii1\ext\entities\Group', 'templateId'),
			'nodes' => array(self::HAS_MANY, 'sweelix\yii1\ext\entities\Node', 'templateId'),
			'tags' => array(self::HAS_MANY, 'sweelix\yii1\ext\entities\Tag', 'templateId'),
		);
	}
}