<?php
/**
 * File Controller.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii1.ext.web
 */

namespace sweelix\yii1\ext\web;
use sweelix\yii1\ext\entities\Node;
use sweelix\yii1\ext\entities\Content;
use sweelix\yii1\ext\entities\Group;
use sweelix\yii1\ext\entities\Tag;
use sweelix\yii1\ext\components\RouteEncoder;

/**
 * This Controller class handle everything to retrieve the elements
 * IDs from the current request
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii1.ext.web
 * @since     1.0.0
 */
class Controller extends \CController {
	/**
	 * @var integer contentId
	 */
	protected $_contentId=null;
	/**
	 * Return contentId if it exists
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getContentId() {
		if(($this->_contentId === null) && (\Yii::app()->getUrlManager()->hasProperty('contentId') === true )) {
			$this->_contentId = \Yii::app()->getUrlManager()->contentId;
		}
		return $this->_contentId;
	}
	/**
	 * @var Content content object
	 */
	protected $_content=null;
	/**
	 * Return content if it exists
	 *
	 * @return Content
	 * @since  1.0.0
	 */
	public function getContent() {
		if(($this->_content === null) && ($this->getContentId() !== null)) {
			$this->_content = Content::model()->findByPk($this->getContentId());
		}
		return $this->_content;
	}
	/**
	 * @var integer nodeId
	 */
	protected $_nodeId=null;
	/**
	 * Return nodeId if it exists
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getNodeId() {
		if(($this->_nodeId === null) && (\Yii::app()->getUrlManager()->hasProperty('nodeId') === true )) {
			$this->_nodeId = \Yii::app()->getUrlManager()->nodeId;
		}
		return $this->_nodeId;
	}
	/**
	 * @var Node node object
	 */
	protected $_node=null;
	/**
	 * Return node if it exists
	 *
	 * @return Node
	 * @since  1.0.0
	 */
	public function getNode() {
		if(($this->_node === null) && ($this->getNodeId() !== null)) {
			$this->_node = Node::model()->findByPk($this->getNodeId());
		}
		return $this->_node;
	}
	/**
	 * @var integer tagId
	 */
	protected $_tagId=null;
	/**
	 * Return tagId if it exists
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getTagId() {
		if(($this->_tagId === null) && (\Yii::app()->getUrlManager()->hasProperty('tagId') === true )) {
			$this->_tagId = \Yii::app()->getUrlManager()->tagId;
		}
		return $this->_tagId;
	}
	/**
	 * @var Tag tag object
	 */
	protected $_tag=null;
	/**
	 * Return tag if it exists
	 *
	 * @return Tag
	 * @since  1.0.0
	 */
	public function getTag() {
		if(($this->_tag === null) && ($this->getTagId() !== null)) {
			$this->_tag = Tag::model()->findByPk($this->getTagId());
		}
		return $this->_tag;
	}
	/**
	 * @var integer groupId
	 */
	protected $_groupId=null;
	/**
	 * Return groupId if it exists
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getGroupId() {
		if(($this->_groupId === null) && (\Yii::app()->getUrlManager()->hasProperty('groupId') === true )) {
			$this->_groupId = \Yii::app()->getUrlManager()->groupId;
		}
		return $this->_groupId;
	}
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
		if(($this->_group === null) && ($this->getGroupId() !== null)) {
			$this->_group = Group::model()->findByPk($this->getGroupId());
		}
		return $this->_group;
	}
	private $_cmsActive = null;
	/**
	 * Return if cms is active
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public function getCmsActive() {
		if(($this->_cmsActive === null) && (\Yii::app()->getUrlManager()->hasProperty('cmsActive') === true )) {
			$this->_cmsActive = \Yii::app()->getUrlManager()->cmsActive;
		}
		return $this->_cmsActive;
	}
	/**
	 * Override @see CController::createUrl() to handle correct
	 * controller context and path
	 *
	 * @param string $route     route
	 * @param array  $params    url parameters
	 * @param string $ampersand change parameters separator
	 *
	 * @return string
	 * @since  1.6.0
	 */
	public function createUrl($route, $params=array(), $ampersand='&') {
		if(is_array($route) === true) {
			// we are in cms and we want to override the generation
			return \Yii::app()->createUrl($route, $params, $ampersand);
		} else {
			if($this->cmsActive === true) {
				$id = RouteEncoder::encode($this->contentId, $this->nodeId, $this->tagId, $this->groupId);
			} else {
				$id = $this->getId();
			}
			if($route==='') {
				$route=$id.'/'.$this->getAction()->getId();
			}else if(strpos($route, '/')===false) {
				$route=$id.'/'.$route;
			}
			if($route[0]!=='/' && ($module=$this->getModule())!==null) {
				$route=$module->getId().'/'.$route;
			}
			return \Yii::app()->createUrl(trim($route, '/'), $params, $ampersand);
		}
	}
}
