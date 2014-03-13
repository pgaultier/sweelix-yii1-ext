<?php
/**
 * File Token.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 */

namespace sweelix\yii1\ext\entities;
use sweelix\yii1\ext\db\ar\Token as ActiveRecordToken;

/**
 * Class Token.php
 *
 * This is the model class for table "search".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 *
 * @property Author[]  $authors
 * @property Content[] $contents
 * @property Group[]   $groups
 * @property Tag[]     $tags
 */
class Token extends ActiveRecordToken {

	/**
	 * @var integer $weight sum of a few weight words.
	 */
	public $weight;

	/**
	 * @var string $searchByWords Is the textfield typed for the research
	 */
	public $searchByWords;
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Token the static model class
	 * @since  1.0.0
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	/**
	 *
	 * This function create the criteria variable for the research.
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @param boolean $detail If is it the js call or not
	 *
	 * @return CDbCriteria
	 * @since  2.0.0
	 */
	function searchCriteria($limit, $offset, $detail) {
		$split = strtolower(preg_replace('/[^a-z0-9\-áàâäéèêëíìîïóòôöúùûüç]+/i', ' ', $this->searchByWords));
		$split = preg_split('/[\s]+/', $split);
		$split = array_unique($split);

		$criteria = new \CDbCriteria();

		$criteria->select = 'SUM(tokenWeight) AS weight, elementId, elementType, tokenKey, elementProperty, elementTitle, contentId, nodeId, groupId, tagId, tokenDateCreate';
		$criteria->addInCondition('tokenKey', $split, 'AND');

		$criteria->compare('elementProperty',$this->elementProperty,true);
		$criteria->compare('elementId',$this->elementId,true);
		$criteria->compare('elementType',$this->elementType,true);
		$criteria->compare('elementTitle',$this->elementTitle,true);
		$criteria->compare('tokenKey',$this->tokenKey,true);
		$criteria->compare('contentId',$this->contentId,true);
		$criteria->compare('nodeId',$this->nodeId,true);
		$criteria->compare('tagId',$this->tagId,true);
		$criteria->compare('groupId',$this->groupId,true);

		if ($this->tokenWeight !== 0) {
			$criteria->compare('tokenWeight',$this->tokenWeight);
		}
		$criteria->compare('tokenDateCreate',$this->tokenDateCreate,true);
		if ($detail === true) {
			$criteria->group = 'elementId, elementType, elementProperty, tokenKey';
		} else {
			$criteria->group = 'elementId, elementType, elementTitle';
		}
		$criteria->limit = $limit;
		$criteria->offset = $offset;
		$criteria->order = 'weight DESC';

		return $criteria;
	}

	/**
	 *
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @param boolean $detail if is it the js call or not
	 * @param integer $offset the offset
	 * @param boolean $getCriteria if we want criteria or dataprovider
	 *
	 * @return mixed CActiveDataProvider if getCriteria is false whether CDbCriteria, the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($detail=false, $limit=10, $offset=0, $getCriteria=false) {
		$criteria = $this->searchCriteria($limit, $offset, $detail);
		if ($getCriteria === true) {
			//requete a execute
			$obj = $criteria;
		} else {
			// liste de tous les objets
			$obj = new \CActiveDataProvider($this, array('criteria' => $criteria));
		}
		return $obj;
	}

	/**
	 * Find the author of selected element
	 *
	 * @return Author or null if not find.
	 * @since  3.0.0
	 */
	public function getAuthor() {
		$id = null;

		if ((isset($this->node->authorId) === true) && ($this->node->authorId !== null)) {
			$id = $this->node->authorId;
		} else if ((isset($this->content->authorId) === true) && ($this->content->authorId !== null)) {
			$id = $this->content->authorId;
		}
		return Author::model()->findByPk($id);
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
			'content' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Content', 'contentId'),
			'group' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Group', 'groupId'),
			'tag' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Tag', 'tagId'),
			'node' => array(self::BELONGS_TO, 'sweelix\yii1\ext\entities\Node', 'nodeId'),
		);
	}
}
