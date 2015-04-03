<?php
/**
 * File Controller.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
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
use CController;

/**
 * This Controller class handle everything to retrieve the elements
 * IDs from the current request
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii1.ext.web
 * @since     1.0.0
 */
class Controller extends CController
{
    /**
     * @var integer contentId or null
     */
    public $contentId;
    /**
     * @var integer nodeId or null
     */
    public $nodeId;
    /**
     * @var integer groupId or null
     */
    public $groupId;
    /**
     * @var integer tagId or null
     */
    public $tagId;

    /**
     * @var bool define if the controller was generated by the cms
     */
    public $cmsGenerated = false;

    public $cmsId;


    /**
     * Returns the directory containing view files for this controller.
     * The default implementation returns 'protected/views/ControllerID'.
     * Child classes may override this method to use customized view path.
     * If the controller belongs to a module, the default view path
     * is the {@link CWebModule::getViewPath module view path} appended with the controller ID.
     * @return string the directory containing the view files for this controller. Defaults to 'protected/views/ControllerID'.
     */
    public function getViewPath()
    {
        if (($module = $this->getModule()) === null) {
            $module = Yii::app();
        }
        return $module->getViewPath() . DIRECTORY_SEPARATOR . (($this->cmsId !== null) ? $this->cmsId : $this->getId());
    }

    /**
     * @var \sweelix\yii1\ext\entities\Content content object
     */
    private $content = null;

    /**
     * Return content if it exists
     *
     * @return \sweelix\yii1\ext\entities\Content
     * @since  1.0.0
     */
    public function getContent()
    {
        if (($this->content === null) && ($this->contentId !== null)) {
            $this->content = Content::model()->findByPk($this->contentId);
        }
        return $this->content;
    }

    /**
     * @var \sweelix\yii1\ext\entities\Node node object
     */
    private $node = null;

    /**
     * Return node if it exists
     *
     * @return \sweelix\yii1\ext\entities\Node
     * @since  1.0.0
     */
    public function getNode()
    {
        if (($this->node === null) && ($this->nodeId !== null)) {
            $this->node = Node::model()->findByPk($this->nodeId);
        }
        return $this->node;
    }

    /**
     * @var \sweelix\yii1\ext\entities\Tag tag object
     */
    private $tag = null;

    /**
     * Return tag if it exists
     *
     * @return \sweelix\yii1\ext\entities\Tag
     * @since  1.0.0
     */
    public function getTag()
    {
        if (($this->tag === null) && ($this->tagId !== null)) {
            $this->tag = Tag::model()->findByPk($this->tagId);
        }
        return $this->tag;
    }

    /**
     * @var \sweelix\yii1\ext\entities\Group group object
     */
    private $group = null;

    /**
     * Return group if it exists
     *
     * @return \sweelix\yii1\ext\entities\Group
     * @since  1.0.0
     */
    public function getGroup()
    {
        if (($this->group === null) && ($this->groupId !== null)) {
            $this->group = Group::model()->findByPk($this->groupId);
        }
        return $this->group;
    }

    /**
     * Override @see CController::createUrl() to handle correct
     * controller context and path
     *
     * @param string $route route
     * @param array $params url parameters
     * @param string $ampersand change parameters separator
     *
     * @return string
     * @since  1.6.0
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        if (is_array($route) === true) {
            // we are in cms and we want to override the generation
            $contentId = isset($route['content']) ? $route['content'] : null;
            $nodeId = isset($route['node']) ? $route['node'] : null;
            $tagId = isset($route['tag']) ? $route['tag'] : null;
            $groupId = isset($route['group']) ? $route['group'] : null;
            $encodedRoute = RouteEncoder::encode($contentId, $nodeId, $tagId, $groupId);
            if (isset($route['action']) === true) {
                $encodedRoute = $encodedRoute . '/' . $route['action'];
            }
            return Yii::app()->createUrl($encodedRoute, $params, $ampersand);
        } else {
            if ($this->cmsGenerated === true) {
                $id = RouteEncoder::encode($this->contentId, $this->nodeId, $this->tagId, $this->groupId);
            } else {
                $id = $this->getId();
            }
            if ($route === '') {
                $route = $id . '/' . $this->getAction()->getId();
            } else {
                if (strpos($route, '/') === false) {
                    $route = $id . '/' . $route;
                }
            }
            if ($route[0] !== '/' && ($module = $this->getModule()) !== null) {
                $route = $module->getId() . '/' . $route;
            }
            return Yii::app()->createUrl(trim($route, '/'), $params, $ampersand);
        }
    }
}
