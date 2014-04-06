<?php
/**
 * Router.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     XXX
 */

namespace sweelix\yii1\ext\behaviors;
// use sweelix\yii2\ext\components\CmsMapper;
use CBehavior;
use Yii;

/**
 * This Router override the controllerMap to allow controller handling
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     XXX
 */
class Router extends CBehavior {

	/**
	 * @var string cms controller name space. if not set, default namespace will be used
	 */
	public $controllerNamespace;

	/**
	 * Attach the router to before request
	 *
	 * @return array
	 * @since  XXX
	 */
	public function events() {
		return [
			'onBeginRequest' => 'beginRequest',
		];
	}

	/**
	 * Before request is run, application controller mapper is upgraded to hook the
	 * cms controller mapper into the application
	 *
	 * @param Event $event current event triggered
	 *
	 * @return void
	 * @since  XXX
	 */
	public function beginRequest($event) {
		$currentApp = $event->sender;
		$currentApp->controllerMap = Yii::createComponent([
			'class' => 'sweelix\yii1\ext\components\CmsMapper',
			'controllerNamespace' => ($this->controllerNamespace === null)?$currentApp->controllerNamespace:$this->controllerNamespace ,
			'additionalMap' => $currentApp->controllerMap,
		]);
	}
}
