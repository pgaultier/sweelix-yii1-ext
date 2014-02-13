<?php
/**
 * Content.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  dao
 * @package   sweelix.yii1.ext.db.dao
 */

namespace sweelix\yii1\ext\db\dao;
use sweelix\yii1\ext\entities\Content as EntityContent;

/**
 * Class Content
 *
 * This is the AO class for table "contents".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  dao
 * @package   sweelix.yii1.ext.db.dao
 * @since     1.0.0
 */
class Content extends \CComponent {
	/**
	 * Move content
	 *
	 * Move content from one place to another
	 *
	 * @param EntityContent &$content        content object to move
	 * @param integer       $targetContentId id of target node
	 * @param enum          $where           moving mode can be top, bottom, up, down
	 *
	 * @return boolean
	 * @since  1.0.0
	 */
	public static function move(EntityContent &$content, $targetContentId=null, $where='top') {
		try {
			\Yii::trace('Move content '.$content->contentId.' '.$where.' contentId : '.$targetContentId, 'sweelix.yii1.ext.db.dao');
			$sql = 'CALL spContentMove(:contentId, :where, :targetId)';
			$where = strtolower($where);
			switch($where) {
				case 'top':
				case 'bottom':
				case 'up':
				case 'down':
				case 'before':
				case 'after':
					// do nothing we are ok
				break;
				default :
					$where = 'top';
				break;
			}
			$cmd = $content->dbConnection->createCommand($sql);
			$cmd->bindValue ( ':contentId', $content->contentId, \PDO::PARAM_INT );
			$cmd->bindValue ( ':where', $where, \PDO::PARAM_STR );
			$cmd->bindValue ( ':targetId', $targetContentId, \PDO::PARAM_INT );
			$data = $cmd->queryRow();
			$content->contentOrder = $data['contentOrder'];
			$result = true;
		} catch ( \Exception $e ) {
			\Yii::log('Error in '.__METHOD__.'():'.$e->getMessage(), \CLogger::LEVEL_ERROR, 'sweelix.yii1.ext.db.dao');
			$result = false;
		}
		return $result;
	}
}
