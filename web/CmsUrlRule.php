<?php
/**
 * File CmsUrlRule.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii2.ext.web
 */

namespace sweelix\yii1\ext\web;

use sweelix\yii1\components\RouteEncoder;
use CBaseUrlRule;
use Yii;

/**
 * This class allow transcoding from database data to ecnoded and vice versa
 *
 * <code>
 * 	'components' => [
 * 		...
 *		'urlManager' => [
 *			'enablePrettyUrl' => true,
 *			'suffix' => '.html',
 *			'rules' => [
 *				[
 *					'class' => 'sweelix\yii2\ext\web\CmsUrlRule',
 *				],
 *			],
 *		],
 * 		...
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweelix.yii2.ext.web
 * @since     XXX
 */
class CmsUrlRule extends CBaseUrlRule {

	/**
	 * @var string  suffix used for faked url
	 */
	public $urlSuffix;

	/**
	 * @var string  suffix used for faked url
	 */
	public $table = '{{urls}}';

	/**
	 * Retrieve an url from an encoded route return pretty url if found
	 * false otherwise
	 *
	 * @param UrlManager $manager current url manager
	 * @param string     $route   route to check
	 * @param array      $params  additional parameters
	 *
	 * @return mixed
	 * @since  XXX
	 */
	public function createUrl($manager, $route, $params, $ampersand) {
		$prettyUrl = false;
		if(($decodedRoute = RouteEncoder::decode($route)) !== false) {
			$url = null;
			list($contentId, $nodeId, $tagId, $groupId) = $decodedRoute;
			if($contentId !== null) {
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
			if(isset($elementType) === true) {
				if($this->urlSuffix === null) {
					$this->urlSuffix = $manager->urlSuffix;
				}
				$url = Yii::app()->db->createCommand()
						->select('urlElementId, urlElementType, urlValue')
						->from($this->table)
						->where('urlElementType = :urlElementType AND urlElementId = :urlElementId', [':urlElementType' => $elementType, ':urlElementId' => $elementId])
						->queryRow();
				if($url !== false) {
					$prettyUrl = $url['urlValue'].$this->urlSuffix;
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
	 * @param UrlManager $manager current url manager
	 * @param Request    $request current request
	 *
	 * @return mixed
	 * @since  XXX
	 */
	public function parseUrl($manager, $request, $pathInfo, $rawPathInfo) {
		$prettyUrl = false;
		if(empty($pathInfo) === false) {
			if($this->urlSuffix === null) {
				$this->urlSuffix = $manager->urlSuffix;
			}
			$suffix = (string) $this->urlSuffix;
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
						->where('urlValue = :urlValue', [':urlValue' => $pathInfo])
						->queryRow();
				if($url !== false) {
					$contentId = null;
					$nodeId = null;
					$tagId = null;
					$groupId = null;
					switch($url['urlElementType']) {
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
				}
            }
		}
		return $prettyUrl;  // this rule does not apply
	}
}
