<?php
/**
 * File CmsMapper.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweelix.yii1.ext.components
 */

namespace sweelix\yii1\ext\components;
use sweelix\yii1\ext\db\CriteriaBuilder;
use sweelix\yii1\ext\entities\Content;
use sweelix\yii1\ext\entities\Group;
use sweelix\yii1\ext\entities\Node;
use sweelix\yii1\ext\entities\Tag;
use ArrayAccess;
use CHttpException;
use CComponent;
use Yii;

/**
 * Class CmsMapper allow automatic mapping between cms data in database and
 * controllers
 * Cms mapper must be added in the application.
 *
 * <code>
 * 	'components' => [
 * 		...
 * 		'sweelix' => [
 * 			'class'=>'sweelix\yii2\ext\components\Config',
 * 		],
 * 		...
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweelix.yii2.ext.components
 * @since     3.1.0
 *
 * @property string urlPattern
 */
class CmsMapper extends CComponent implements ArrayAccess {

	CONST CACHE_KEY_PREFIX = 'sweelix.yii1.ext.components.cmsmapper';

	public static $CACHE_EXPIRE=3600;

	/**
	 * @var string controller namespace
	 */
	public $controllerNamespace;
	/**
	 * @var array original maps
	 */
	public $additionalMap = [];

	/**
	 * check if current route is handled by the mapper
	 *
	 * @param mixed $offset target route
	 *
	 * @return boolean
	 * @since  3.1.0
	 */
	public function offsetExists ($offset) {
		return (isset($this->additionalMap[$offset]) === true) || (RouteEncoder::decode($offset) !== false);
	}

	/**
	 * Retrieve controller for current route
	 * 1. if requested route is in the map, return controller definition
	 * 2. if requested route is defined in the cms return controller definitions and element ids
	 *
	 * @param mixed $offset target route
	 *
	 * @return mixed
	 * @since  3.1.0
	 */
	public function offsetGet ($offset) {
		$mappedController = null;
		if(isset($this->additionalMap[$offset]) === true) {
			$mappedController = $this->additionalMap[$offset];
		} elseif(($data = RouteEncoder::decode($offset)) !== false) {
			// list($contentId, $nodeId, $tagId, $groupId) = $data;
			list($controller, $action) = self::findController($data);
			if($this->controllerNamespace !== null) {
				$class = $this->controllerNamespace. '\\' . $controller . 'Controller';
			} else {
				$class = $controller . 'Controller';
			}
			$mappedController =  [
				'class' => $class,
				'contentId' => $data[0],
				'nodeId' => $data[1],
				'tagId' => $data[2],
				'groupId' => $data[3],
			];
			if(empty($action) === false) {
				$mappedController['defaultAction'] = $action;

			}
		}
		return $mappedController;
	}

	/**
	 * Allow backward compatibility with classic controllerMap
	 *
	 * @param mixed $offset route to add
	 * @param mixed $value  controller definition
	 *
	 * @return void
	 * @since  3.1.0
	 */
	public function offsetSet ($offset, $value) {
		$this->additionalMap[$offset] = $value;
	}

	/**
	 * Allow backward compatibility with classic controllerMap
	 *
	 * @param mixed $offset route to remove
	 *
	 * @return void
	 * @since  3.1.0
	 */
	public function offsetUnset ($offset) {
		unset($this->additionalMap[$offset]);
	}
	/**
	 * Find controller from template
	 *
	 * @param Template $template template object
	 *
	 * @return string
	 * @since  3.1.0
	 */
	private static function buildRouteFromTemplate(&$template) {
		if($template->templateController === null) {
			throw new CHttpException(404, Yii::t('sweelix', 'Element not found'));
		}
		return array($template->templateController, $template->templateAction);
	}

	private static function fetchControllerForElement($class, $elementId) {
		//TODO: protect access to offline / draft elements. Probably a good place to hook some "admin" stuff (preview,...)
		$controller = null;
		$action = null;
		if((Yii::app()->cache !== null) && (($cachedData = Yii::app()->cache->get($cacheKey)) !== false)) {
			// we are using the cache
			$controller = $cachedData['controller'];
			$action = isset($cachedData['action'])?$cachedData['action']:null;
		} else {
			$element = $class::model()->findByPk($elementId);
			if($element !== null) {
				if($element->template !== null) {
					list($controller, $action) = self::buildRouteFromTemplate($element->template);
					if((Yii::app()->cache !== null)) {
						Yii::app()->cache->set($cacheKey, ['cmsData' => $cmsData, 'controller' => $controller, 'action' => $action], self::$CACHE_EXPIRE );
					}
				}
			}
		}
		return [$controller, $action];
	}
	/**
	 * Find correct controller according to
	 * Cms data
	 *
	 * @param array $cmsData cms related information
	 *
	 * @return string
	 * @since  3.1.0
	 */
	private static function findController(&$cmsData) {
		$controller = null;
		$action = null;
		if($cmsData !== false) {
			// sample : c7df5a6a81c
			$cacheKey = self::CACHE_KEY_PREFIX.':'.$cmsData[0].'.'.$cmsData[1].'.'.$cmsData[2].'.'.$cmsData[3];
			if($cmsData[0] !== null) {
				list($controller, $action) = self::fetchControllerForElement('sweelix\yii1\ext\entities\Content', $cmsData[0]);
			} elseif($cmsData[1] !== null) {
				if((Yii::app()->cache !== null) && (($cachedData = Yii::app()->cache->get($cacheKey)) !== false)) {
					// we are using the cache
					$controller = $cachedData['controller'];
					$action = isset($cachedData['action'])?$cachedData['action']:null;
					$cmsData = $cachedData['cmsData'];
				} else {
					$element = Node::model()->findByPk($cmsData[1]);
					if($element !== null) {
						do {
							switch($element->nodeDisplayMode) {
								case 'redirect':
									if($element->redirection instanceof Node) {
										if($element->nodeId == $element->redirection->nodeId) {
											throw new CHttpException(500, Yii::t('sweelix', 'Recursive redirection loop'));
										}
										$element = $element->redirection;
										$finished = false;
									} else {
										throw new CHttpException(404, Yii::t('sweelix', 'Element not found'));
									}
									break;
								case 'first':
                                    $finished = true;
									$cmsData[1] = $element->nodeId;

									$criteriaBuilder = new CriteriaBuilder('content');
									$criteriaBuilder->published();
									$criteriaBuilder->filterBy('nodeLeftId', $element->nodeLeftId, '>=');
                                    $criteriaBuilder->filterBy('nodeRightId', $element->nodeRightId, '<=');
                                    $criteriaBuilder->orderBy('nodeLeftId');
                                    $criteriaBuilder->orderBy('contentOrder');

									if($criteriaBuilder->count()>0) {
										$content = $criteriaBuilder->find();
										if($content->template !== null) {
											list($controller, $action) = self::buildRouteFromTemplate($content->template);
											$cmsData[0] = $content->contentId;
											if((Yii::app()->cache !== null)) {
												Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controller' => $controllerRoute, 'action' => $actionRoute), self::$CACHE_EXPIRE );
											}
										}
									}
									break;
								case 'list':
								default:
									$finished = true;
									$cmsData[1] = $element->nodeId;
									if($element->template !== null) {
										list($controller, $action) = self::buildRouteFromTemplate($element->template);
										if((Yii::app()->cache !== null)) {
											Yii::app()->cache->set($cacheKey, ['cmsData'=>$cmsData, 'controller' => $controller, 'action' => $action], self::$CACHE_EXPIRE);
										}
									}
									break;
							}
						} while($finished === false);
					}
				}
			} elseif($cmsData[2] !== null) {
				list($controller, $action) = self::fetchControllerForElement('sweelix\yii1\ext\entities\Tag', $cmsData[2]);
			} elseif($cmsData[3] !== null) {
				list($controller, $action) = self::fetchControllerForElement('sweelix\yii1\ext\entities\Group', $cmsData[3]);
			}
			if($controller === null) {
				throw new CHttpException(404, Yii::t('sweelix', 'Element not found'));
			}
		}
		return array($controller, $action);
	}

}
