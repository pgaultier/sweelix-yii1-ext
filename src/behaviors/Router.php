<?php
/**
 * Router.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     3.1.0
 */

namespace sweelix\yii1\ext\behaviors;

use CBehavior;
use Yii;

/**
 * This Router override the controllerMap to allow controller handling
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     3.1.0
 */
class Router extends CBehavior
{

    /**
     * @var string cms controller name space. if not set, default namespace will be used
     */
    public $controllerNamespace;

    /**
     * Attach the router to before request
     *
     * @return array
     * @since  3.1.0
     */
    public function events()
    {
        return array(
            'onBeginRequest' => 'beginRequest',
        );
    }

    /**
     * Before request is run, application controller mapper is upgraded to hook the
     * cms controller mapper into the application
     *
     * @param Event $event current event triggered
     *
     * @return void
     * @since  3.1.0
     */
    public function beginRequest($event)
    {
        $currentApp = $event->sender;
        $currentApp->controllerMap = Yii::createComponent(array(
            'class' => 'sweelix\yii1\ext\components\CmsMapper',
            'controllerNamespace' => ($this->controllerNamespace === null) ? $currentApp->controllerNamespace : $this->controllerNamespace,
            'additionalMap' => $currentApp->controllerMap,
        ));
    }
}
