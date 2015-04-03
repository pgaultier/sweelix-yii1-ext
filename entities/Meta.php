<?php
/**
 * File Meta.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 */

namespace sweelix\yii1\ext\entities;

use sweelix\yii1\ext\db\ar\Meta as ActiveRecordMeta;

/**
 * Class Meta
 *
 * This is the model class for table "metas".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 * @since     1.0.0
 *
 * @property Content[] $contents
 * @property Node[]    $nodes
 */
class Meta extends ActiveRecordMeta
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className entity classname automatically set
     *
     * @return Meta the static model class
     * @since  1.0.0
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
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
            'contents' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Content', 'contentMeta(metaId, contentId)'),
            'nodes' => array(self::MANY_MANY, 'sweelix\yii1\ext\entities\Node', 'nodeMeta(metaId, nodeId)'),
        );
    }
}