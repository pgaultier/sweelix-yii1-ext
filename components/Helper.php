<?php
/**
 * File Helper.php
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

/**
 * Class Helper
 *
 * This is static class allow easy conversion between
 * different data structures
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweelix.yii1.ext.components
 * @since     1.0.0
 */
class Helper {
	/**
	 * Converts interval db structure to parent - child
	 * array.
	 *
	 * @param array $nodes must be ordered by nodeLeftId
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function linearizeNodesToArray($nodes) {
		$newNodes = array();
		while(($node = array_shift($nodes)) !== null) {
			$children = ($node->nodeRightId - $node->nodeLeftId - 1) / 2;
			if($children > 0) {
				$childrenNodes =array_splice($nodes, 0, $children);
				$newNodes[] = array('node'=>$node, 'children'=>self::linearizeNodesToArray($childrenNodes));
			} else {
				$newNodes[] = array('node'=>$node, 'children'=>null);
			}
		}
		return $newNodes;
	}
	/**
	 * Converts interval db structure to parent - child
	 * array.
	 *
	 * @param mixed  $nodes     must be ordered by nodeLeftId
	 * @param array  $initial   initial data
	 * @param string $separator separator string
	 *
	 * @return array
	 * @since  1.0.0
	 */
	public static function linearizeNodesToDropDownList($nodes, $initial=array(), $separator=' - ') {
		if($nodes instanceof \CActiveDataProvider) {
			foreach($nodes->getData() as $node) {
				$initial[$node->nodeId] = str_repeat($separator, $node->nodeLevel).$node->nodeTitle;
			}
		} else {
			foreach($nodes as $node) {
				$initial[$node->nodeId] = str_repeat($separator, $node->nodeLevel).$node->nodeTitle;
			}
		}
		return $initial;
	}

}