<?php
/**
 * File CmsUrlRule.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii2.ext.web
 */

namespace sweelix\yii1\ext\web;

use sweelix\yii1\ext\components\RouteEncoder;
use CBaseUrlRule;
use Yii;

/**
 * This class allow transcoding from database data to ecnoded and vice versa
 *
 * <code>
 *    'components' => array(
 *        ...
 *        'urlManager' => array(
 *            'enablePrettyUrl' => true,
 *            'suffix' => '.html',
 *            'rules' => array(
 *                array(
 *                    'class' => 'sweelix\yii1\ext\web\CmsUrlRule',
 *                ),
 *            ),
 *        ),
 *        ...
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii2.ext.web
 * @since     3.1.0
 */
class CmsUrlRule extends CBaseUrlRule
{

    /**
     * @var string  suffix used for faked url
     */
    public $urlSuffix;

    /**
     * @var string table used for urls
     */
    public $table = '{{urls}}';

    /**
     * @var bool check if we allow action while searching. can cause problems with nested URLs.
     */
    public $allowActions = false;

    /**
     * Retrieve an url from an encoded route return pretty url if found
     * false otherwise
     *
     * @param \CUrlManager $manager current url manager
     * @param string $route route to check
     * @param array $params additional parameters
     *
     * @return mixed
     * @since  3.1.0
     */
    public function createUrl($manager, $route, $params, $ampersand)
    {
        $prettyUrl = false;
        $newRoute = preg_split('#/#', $route, -1, PREG_SPLIT_NO_EMPTY);
        $route = $newRoute[0];
        $action = isset($newRoute[1]) ? $newRoute[1] : null;
        if (($decodedRoute = RouteEncoder::decode($route)) !== false) {
            $url = null;
            list($contentId, $nodeId, $tagId, $groupId) = $decodedRoute;
            if ($contentId !== null) {
                $elementId = $contentId;
                $elementType = 'content';
            } elseif ($nodeId !== null) {
                $elementId = $nodeId;
                $elementType = 'node';
            } elseif ($tagId !== null) {
                $elementId = $tagId;
                $elementType = 'tag';
            } elseif ($groupId !== null) {
                $elementId = $groupId;
                $elementType = 'group';
            }
            if (isset($elementType) === true) {
                if ($this->urlSuffix === null) {
                    $this->urlSuffix = $manager->urlSuffix;
                }
                $url = Yii::app()->db->createCommand()
                    ->select('urlElementId, urlElementType, urlValue')
                    ->from($this->table)
                    ->where('urlElementType = :urlElementType AND urlElementId = :urlElementId',
                        array(':urlElementType' => $elementType, ':urlElementId' => $elementId))
                    ->queryRow();
                if ($url !== false) {
                    if ($action !== null) {
                        $prettyUrl = $url['urlValue'] . '/' . $action . $this->urlSuffix;
                    } else {
                        $prettyUrl = $url['urlValue'] . $this->urlSuffix;
                    }
                    if ((empty($params) === false) && (($query = http_build_query($params, '', $ampersand)) !== '')) {
                        $prettyUrl .= '?' . $query;
                    }

                }
            }
        }
        return $prettyUrl;
    }

    /**
     * Rebuild the encoded route from a pretty url. Retrun string if found
     * false otherwise
     *
     * @param \CUrlManager $manager current url manager
     * @param \CHttpRequest $request current request
     *
     * @return mixed
     * @since  3.1.0
     */
    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        $prettyUrl = false;
        if (empty($pathInfo) === false) {
            if ($this->urlSuffix === null) {
                $this->urlSuffix = $manager->urlSuffix;
            }
            $suffix = (string)$this->urlSuffix;
            if ($suffix !== '' && $pathInfo !== '') {
                $n = strlen($this->urlSuffix);
                if (substr($pathInfo, -$n) === $this->urlSuffix) {
                    $pathInfo = substr($pathInfo, 0, -$n);
                }
            }
            if ($pathInfo !== '') {
                $url = Yii::app()->db->createCommand()
                    ->select('urlElementId, urlElementType, urlValue')
                    ->from($this->table)
                    ->where('urlValue = :urlValue', array(':urlValue' => $pathInfo))
                    ->queryRow();
                if (($url === false) && ($this->allowActions === true)) {
                    $newPathinfo = preg_split('#/#', $pathInfo, -1, PREG_SPLIT_NO_EMPTY);
                    $action = array_pop($newPathinfo);
                    $pathInfo = implode('/', $newPathinfo);
                    $url = Yii::app()->db->createCommand()
                        ->select('urlElementId, urlElementType, urlValue')
                        ->from($this->table)
                        ->where('urlValue = :urlValue', array(':urlValue' => $pathInfo))
                        ->queryRow();
                }
                if ($url !== false) {
                    $contentId = null;
                    $nodeId = null;
                    $tagId = null;
                    $groupId = null;
                    switch ($url['urlElementType']) {
                        case 'content':
                            $contentId = $url['urlElementId'];
                            break;
                        case 'node' :
                            $nodeId = $url['urlElementId'];
                            break;
                        case 'tag':
                            $tagId = $url['urlElementId'];
                            break;
                        case 'group':
                            $groupId = $url['urlElementId'];
                            break;
                    }
                    $prettyUrl = RouteEncoder::encode($contentId, $nodeId, $tagId, $groupId);
                    if (($this->allowActions === true) && (isset($action) === true)) {
                        $prettyUrl = $prettyUrl . '/' . $action;
                    }
                }
            }
        }
        return $prettyUrl;  // this rule does not apply
    }
}
