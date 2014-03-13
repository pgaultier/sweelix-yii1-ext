<?php
/**
 * File RouteEncoder.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 */

namespace sweelix\yii1\ext\db;
use sweelix\yii1\ext\entities\Url;

/**
 * Class RouteEncoder
 *
 * This is static class allow easy conversion between
 * different data structures
 *
 * <code>
 *   $route = RouteEncoder::encode(12); //create a specific route for contentId 12
 *   $route = RouteEncoder::encode(12, 2); //create a specific route for contentId 12 and nodeId 2
 *   list($contentId, $nodeId, $tagId, $groupId) = RouteEncoder::decode($route);
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  db
 * @package   sweelix.yii1.ext.db
 * @since     1.0.0
 */
class RouteEncoder {
	/**
	 * Encode CMS parameters into a string. This function
	 * is mainly used by the url manager to encode parameters
	 * into the route
	 *
	 * @param integer $contentId content id (null if not known)
	 * @param integer $nodeId    node id (null if not known)
	 * @param integer $tagId     tag id (null if not known)
	 * @param integer $groupId   group id (null if not known)
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function encode($contentId=null, $nodeId=null, $tagId=null, $groupId=null) {
		$result = "";
		if($contentId !== null) {
			$url = Url::model()->find(
					'urlElementType = :type and urlElementId = :id',
					array(':type'=>'content', ':id'=>$contentId)
			);
		} elseif($nodeId !== null) {
			$url = Url::model()->find(
					'urlElementType = :type and urlElementId = :id',
					array(':type'=>'node', ':id'=>$nodeId)
			);
		} elseif($tagId !== null) {
			$url = Url::model()->find(
					'urlElementType = :type and urlElementId = :id',
					array(':type'=>'tag', ':id'=>$tagId)
			);
		}elseif($groupId !== null) {
			$url = Url::model()->find(
					'urlElementType = :type and urlElementId = :id',
					array(':type'=>'group', ':id'=>$groupId)
			);
		}
		$result = $url->urlValue;
		return $result;
	}

	/**
	 * Decode route into CMS parameters. This function
	 * is mainly used by the url manager to decode the route
	 * into parameters
	 *
	 * @param string  $route   route
	 * @param boolean $rewrite use url rewriting
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function decode($route='') {
		$contentId = null;
		$nodeId = null;
		$tagId = null;
		$groupId = null;

		$url = Url::model()->findByPk($route);
		if($url!==null) {
			switch($url->urlElementType) {
				case 'content':
					$contentId = $url->urlElementId;
					break;
				case 'node':
					$nodeId = $url->urlElementId;
					break;
				case 'tag':
					$tagId = $url->urlElementId;
					break;
				case 'group':
					$groupId = $url->urlElementId;
					break;
			}
			$result = array($contentId, $nodeId, $tagId, $groupId);
		} else {
			$result = false;
		}
		return $result;
	}
}

