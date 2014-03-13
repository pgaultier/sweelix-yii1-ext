<?php
/**
 * File UrlManager.php
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
use sweelix\yii1\ext\components\RouteEncoder;
use sweelix\yii1\ext\entities\Node;
use sweelix\yii1\ext\entities\Content;
use sweelix\yii1\ext\entities\Group;
use sweelix\yii1\ext\entities\Tag;
use sweelix\yii1\ext\db\CriteriaBuilder;

/**
 * Class UrlManager
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
class UrlManager extends \CUrlManager {
	const CACHE_KEY_PREFIX='sweelix.yii1.ext.web.UrlManager.';
	/**
	 * @var integer cache expiration
	 */
	private static $_expire=60;
	/**
	 * Get cache duration
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getExpire() {
		return self::$_expire;
	}
	/**
	 * Define cache duration
	 *
	 * @param integer $duration number of seconds to cache the value
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setExpire($duration) {
		self::$_expire = \CPropertyValue::ensureInteger($duration);
	}
	/**
	 * @var boolean cms in use
	 */
	private $_cmsActive = false;
	/**
	 * Check if we are in cms mode or not
	 *
	 * @return boolean
	 */
	public function getCmsActive() {
		return $this->_cmsActive;
	}
	/**
	 * @var array cms data
	 */
	private $_cmsData=array(null, null, null, null);
	/**
	 * Get current contentId, used by @see Controller
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getContentId() {
		return $this->_cmsData[0];
	}
	/**
	 * Get current nodeId, used by @see Controller
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getNodeId() {
		return $this->_cmsData[1];
	}
	/**
	 * Get current tagId, used by @see Controller
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getTagId() {
		return $this->_cmsData[2];
	}
	/**
	 * Get current groupId, used by @see Controller
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getGroupId() {
		return $this->_cmsData[3];
	}
	/**
	 * Override CUrlManage::parseUrl() to process CMS data
	 * if it applies
	 *
	 * @param CHttpRequest $request current request served
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function parseUrl($request) {
		if($this->getUrlFormat()===self::PATH_FORMAT) {
			//we are in mode pathinfo : check if url is cms
			// var_dump($request->getPathInfo());exit();
			$pathInfo = $this->removeUrlSuffix($request->getPathInfo(),$this->urlSuffix);
			// $urlElements = explode('/', $pathInfo);
			$this->_cmsData = RouteEncoder::decode($pathInfo, true);
			$overrideAction = null;
			if($this->_cmsData === false) {
				// check if we have overriden the action
				$urlElements = explode('/', $pathInfo);
				$overrideAction = array_pop($urlElements);
				$tmpPathInfo = implode('/', $urlElements);
				$this->_cmsData = RouteEncoder::decode($tmpPathInfo, true);
			}
			if($this->_cmsData !== false) {
				$this->_cmsActive = true;
				\Yii::beginProfile('sweelix.yii1.ext.web.UrlManager.findController');
				list($controller, $action) = self::findController($this->_cmsData);
				if($overrideAction !== null) {
					$action = $overrideAction;
				}
				$urlElements[0] = $controller;
				if((isset($urlElements[1]) === false) && ($action !== null)) {
					$urlElements[1] = $action;
				}
				\Yii::endProfile('sweelix.yii1.ext.web.UrlManager.findController');

				return implode('/', $urlElements);
			}
		} elseif(isset($_GET[$this->routeVar])===true) {
			$_GET[$this->routeVar] = $this->rewriteRoute($_GET[$this->routeVar]);
		} elseif(isset($_POST[$this->routeVar])===true) {
			$_POST[$this->routeVar] = $this->rewriteRoute($_POST[$this->routeVar]);
		}
		return parent::parseUrl($request);
	}
	/**
	 * Create url, fallback to default if unknwon
	 *
	 * @param mixed  $route     string for classic management array for cms
	 * @param array  $params    query string parameters
	 * @param string $ampersand ampersand separator
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function createUrl($route,$params=array(),$ampersand='&') {
		if((is_array($route) === true) && ($this->getUrlFormat()!==self::PATH_FORMAT)) {
			$contentId = isset($route['content'])?$route['content']:null;
			$nodeId = isset($route['node'])?$route['node']:null;
			$tagId = isset($route['tag'])?$route['tag']:null;
			$groupId = isset($route['group'])?$route['group']:null;
			$action = isset($route['action'])?'/'.$route['action']:null;
			$route = RouteEncoder::encode($contentId, $nodeId, $tagId, $groupId).$action;
		} elseif ((is_array($route) === true) && ($this->getUrlFormat()===self::PATH_FORMAT)) {
			if(isset($params['#'])) {
				$anchor='#'.$params['#'];
				unset($params['#']);
			} else
				$anchor='';
			$dbUrl = isset($route['url'])?$route['url']:null;
			if(isset($route['action']) && ($route['action'] !== null)) {
				$dbUrl = $dbUrl.'/'.$route['action'];
			}
			$url=rtrim($this->getBaseUrl().'/'.$dbUrl,'/');
			if($dbUrl!=='')
				$url.=$this->urlSuffix;
			$query=$this->createPathInfo($params,'=',$ampersand);
			return $query==='' ? $url.$anchor : $url.'?'.$query.$anchor;
		}
		return parent::createUrl($route, $params, $ampersand);
	}

	/**
	 * Check if there is some Cms information embedded in the route
	 *
	 * @param string $route route information
	 *
	 * @return string
	 * @since  1.0.0
	 */
	private function rewriteRoute($route) {
		//XXX: I don't know if we have to check the whole route or if it's delegated
		$elements = explode('/', $route);
		\Yii::beginProfile('sweelix.yii1.ext.web.UrlManager.rewriteRoute');
		$cacheKey = self::CACHE_KEY_PREFIX.'cmsdata.'.$elements[0];
		if((\Yii::app()->cache !== null) && (($cmsData = \Yii::app()->cache->get($cacheKey)) !== false)) {
			$this->_cmsData = $cmsData;
		} else {
			$this->_cmsData = RouteEncoder::decode($elements[0]);
			if(\Yii::app()->cache !== null) {
				\Yii::app()->cache->set($cacheKey, $this->_cmsData, self::$_expire );
			}
		}
		\Yii::endProfile('sweelix.yii1.ext.web.UrlManager.rewriteRoute');

		if($this->_cmsData !== false) {
			$this->_cmsActive = true;
			\Yii::beginProfile('sweelix.yii1.ext.web.UrlManager.findController');
			list($controller, $action) = self::findController($this->_cmsData);
			$elements[0] = $controller;
			if((isset($elements[1]) === false) && ($action !== null)) {
				$elements[1] = $action;
			}

			\Yii::endProfile('sweelix.yii1.ext.web.UrlManager.findController');
		}
		return implode('/', $elements);
	}

	/**
	 * Find controller from template
	 *
	 * @param Template $template template object
	 *
	 * @return string
	 * @since  2.0.0
	 */
	private static function buildRouteFromTemplate(&$template) {
		if($template->templateController === null) {
			throw new \CHttpException(404, \Yii::t('sweelix', 'Element not found'));
		}
		return array($template->templateController, $template->templateAction);
	}

	/**
	 * Find correct controller according to
	 * Cms data
	 *
	 * @param array $cmsData cms related information
	 *
	 * @return string
	 * @since  1.0.0
	 */
	private static function findController(&$cmsData) {
		$controllerRoute = null;
		$actionRoute = null;
		if($cmsData !== false) {
			// sample : c7df5a6a81c
			$cacheKey = self::CACHE_KEY_PREFIX.'route.'.$cmsData[0].'.'.$cmsData[1].'.'.$cmsData[2].'.'.$cmsData[3];
			if($cmsData[0] !== null) {
				if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get($cacheKey)) !== false)) {
					// we are using the cache
					$cmsData = $cachedData['cmsData'];
					$controllerRoute = $cachedData['controllerRoute'];
					$actionRoute = isset($cachedData['actionRoute'])?$cachedData['actionRoute']:null;
				} else {
					$element = Content::model()->findByPk($cmsData[0]);
					if($element !== null) {
						if($element->template !== null) {
							list($controllerRoute, $actionRoute) = self::buildRouteFromTemplate($element->template);
							if((\Yii::app()->cache !== null)) {
								\Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controllerRoute' => $controllerRoute, 'actionRoute' => $actionRoute), self::$_expire );
							}
						}
					}
				}
			} elseif($cmsData[1] !== null) {
				if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get($cacheKey)) !== false)) {
					// we are using the cache
					$cmsData = $cachedData['cmsData'];
					$controllerRoute = $cachedData['controllerRoute'];
					$actionRoute = isset($cachedData['actionRoute'])?$cachedData['actionRoute']:null;
				} else {
					$element = Node::model()->findByPk($cmsData[1]);
					if($element !== null) {
						do {
							switch($element->nodeDisplayMode) {
								case 'redirect':
									if($element->redirection instanceof Node) {
										if($element->nodeId == $element->redirection->nodeId) {
											throw new \CHttpException(404, \Yii::t('sweelix', 'Recursive redirection loop'));
										}
										$element = $element->redirection;
										$finished = false;
									} else {
										throw new \CHttpException(404, \Yii::t('sweelix', 'Element not found'));
									}
									break;
								case 'first':
									$finished = true;
									$cmsData[1] = $element->nodeId;

									$criteriaBuilder = new CriteriaBuilder('content');
									$criteriaBuilder->published();
									$criteriaBuilder->filterBy('nodeId', $element->nodeId);
									$criteriaBuilder->orderBy('contentOrder');

									if($criteriaBuilder->count()>0) {
										$content = $criteriaBuilder->find();
										if($content->template !== null) {
											list($controllerRoute, $actionRoute) = self::buildRouteFromTemplate($content->template);
											$cmsData[0] = $content->contentId;
											if((\Yii::app()->cache !== null)) {
												\Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controllerRoute' => $controllerRoute, 'actionRoute' => $actionRoute), self::$_expire );
											}
										}
									}
									break;
								case 'list':
								default:
									$finished = true;
									$cmsData[1] = $element->nodeId;
									if($element->template !== null) {
										list($controllerRoute, $actionRoute) = self::buildRouteFromTemplate($element->template);
											if((\Yii::app()->cache !== null)) {
												\Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controllerRoute' => $controllerRoute, 'actionRoute' => $actionRoute), self::$_expire );
											}
									}
									break;
							}
						} while($finished==false);
					}
				}
			} elseif($cmsData[2] !== null) {
				if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get($cacheKey)) !== false)) {
					// we are using the cache
					$cmsData = $cachedData['cmsData'];
					$controllerRoute = $cachedData['controllerRoute'];
					$actionRoute = isset($cachedData['actionRoute'])?$cachedData['actionRoute']:null;
				} else {
					$element = Tag::model()->findByPk($cmsData[2]);
					if($element !== null) {
						if($element->template !== null) {
							list($controllerRoute, $actionRoute) = self::buildRouteFromTemplate($element->template);
							if((\Yii::app()->cache !== null)) {
								\Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controllerRoute' => $controllerRoute, 'actionRoute' => $actionRoute), self::$_expire );
							}
						}
					}
				}
			} elseif($cmsData[3] !== null) {
				if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get($cacheKey)) !== false)) {
					// we are using the cache
					$cmsData = $cachedData['cmsData'];
					$controllerRoute = $cachedData['controllerRoute'];
					$actionRoute = isset($cachedData['actionRoute'])?$cachedData['actionRoute']:null;
				} else {
					$element = Group::model()->findByPk($cmsData[3]);
					if($element !== null) {
						if($element->template !== null) {
							list($controllerRoute, $actionRoute) = self::buildRouteFromTemplate($element->template);
							if((\Yii::app()->cache !== null)) {
								\Yii::app()->cache->set($cacheKey, array('cmsData'=>$cmsData, 'controllerRoute' => $controllerRoute, 'actionRoute' => $actionRoute), self::$_expire );
							}
						}
					}
				}
			}
			if($controllerRoute === null) {
				throw new \CHttpException(404, \Yii::t('sweelix', 'Element not found'));
			}
		}
		return array($controllerRoute, $actionRoute);
	}
}
