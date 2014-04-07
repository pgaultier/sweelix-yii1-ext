<?php
/**
 * File Controller.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii1.ext.web
 */

namespace sweelix\yii1\ext\web;
use sweelix\yii1\ext\entities\Node;
use sweelix\yii1\ext\entities\Content;
use sweelix\yii1\ext\entities\Group;
use sweelix\yii1\ext\entities\Tag;
use CController;

/**
 * This Controller class handle everything to retrieve the elements
 * IDs from the current request
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii1.ext.web
 * @since     1.0.0
 */
class Controller extends CController {
	/**
	 * @var integer contentId
	 */
	public $contentId;
	/**
	 * @var Content content object
	 */
	protected $_content;
	/**
	 * Return content if it exists
	 *
	 * @return Content
	 * @since  1.0.0
	 */
	public function getContent() {
		if(($this->_content === null) && ($this->contentId !== null)) {
			$this->_content = Content::model()->findByPk($this->contentId);
		}
		return $this->_content;
	}
	/**
	 * @var integer nodeId
	 */
	public $nodeId;
	/**
	 * @var Node node object
	 */
	protected $_node;
	/**
	 * Return node if it exists
	 *
	 * @return Node
	 * @since  1.0.0
	 */
	public function getNode() {
		if(($this->_node === null) && ($this->nodeId !== null)) {
			$this->_node = Node::model()->findByPk($this->nodeId);
		}
		return $this->_node;
	}
	/**
	 * @var integer tagId
	 */
	public $tagId;
	/**
	 * @var Tag tag object
	 */
	protected $_tag;
	/**
	 * Return tag if it exists
	 *
	 * @return Tag
	 * @since  1.0.0
	 */
	public function getTag() {
		if(($this->_tag === null) && ($this->tagId !== null)) {
			$this->_tag = Tag::model()->findByPk($this->tagId);
		}
		return $this->_tag;
	}
	/**
	 * @var integer groupId
	 */
	public $groupId;
	/**
	 * @var Group group object
	 */
	protected $_group=null;
	/**
	 * Return group if it exists
	 *
	 * @return Group
	 * @since  1.0.0
	 */
	public function getGroup() {
		if(($this->_group === null) && ($this->groupId !== null)) {
			$this->_group = Group::model()->findByPk($this->groupId);
		}
		return $this->_group;
	}
}
