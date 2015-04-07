<?php
/**
 * Node.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  dao
 * @package   sweelix.yii1.ext.db.dao
 */

namespace sweelix\yii1\ext\db\dao;

use sweelix\yii1\ext\entities\Node as EntityNode;

/**
 * Class Node
 *
 * This is the AO class for table "nodes".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  dao
 * @package   sweelix.yii1.ext.db.dao
 * @since     1.0.0
 */
class Node extends \CComponent
{

    /**
     * Delete extends method
     *
     * Delete node and subnodes
     *
     * @param CDbConnection $conn database connection
     * @param integer $pk node object to delete
     * @param array &$deletedNodes ids of deleted nodes
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function deleteByPk($conn, $pk, &$deletedNodes)
    {
        try {
            \Yii::trace('Delete node by pk with : ' . $pk, 'sweelix.yii1.ext.db.dao');
            $sql = 'CALL spNodeDelete(:nodeId, :returnList)';
            $cmd = $conn->createCommand($sql);
            $cmd->bindValue(':nodeId', $pk, \PDO::PARAM_INT);
            $returnList = 1;
            $cmd->bindValue(':returnList', $returnList, \PDO::PARAM_INT);
            $reader = $cmd->query();
            $deleteNodes = array();
            foreach ($reader as $row) {
                $deletedNodes[] = $row['nodeId'];
            }
            $result = true;
        } catch (\Exception $e) {
            \Yii::log('Error in ' . __METHOD__ . '():' . $e->getMessage(), \CLogger::LEVEL_ERROR,
                'sweelix.yii1.ext.db.dao');
            $result = false;
        }
        return $result;
    }

    /**
     * Insert extends method
     *
     * Insert node and into node
     *
     * @param EntityNode &$node node to insert
     * @param integer $targetId id of target node
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function insert(EntityNode &$node, $targetId)
    {
        try {
            \Yii::trace('Insert node into nodeId : ' . $targetId, 'sweelix.yii1.ext.db.dao');
            // List of attributes to send with the proc call
            $attributeNames = array(
                'nodeTitle',
                'nodeUrl',
                'nodeData',
                'nodeCreateDate',
                'nodeDisplayMode',
                'nodeRedirection',
                'nodeStatus',
                'nodeViewed',
                'authorId',
                'templateId',
                'languageId'
            );

            // Used to prefix a placeholder
            $paramPrefix = ':';
            // Used as parameters of procedure call
            $placeholders = array($paramPrefix . 'targetNodeId');
            // Used for value binding
            $values = array($paramPrefix . 'targetNodeId' => $targetId);

            // Build placeholders and values
            foreach ($attributeNames as $attributeName) {
                $value = $node->$attributeName;

                if ($value instanceof \CDbExpression) {
                    //If it's a CDbExpression, placeholder is the expressions and values are eventual expression parameters
                    $placeholders[] = $value->expression;
                    foreach ($value->params as $k => $v) {
                        $values[$k] = $v;
                    }
                } else {
                    //If it isn't a CDExpression placeholder is the name of the attribute, value is the value of the attribute
                    $placeholders[] = $paramPrefix . $attributeName;
                    $values[$paramPrefix . $attributeName] = $value;
                }
            }

            // Build call command with placeholders as proc parameters
            $sql = 'CALL spNodeInsert(' . implode(', ', $placeholders) . ')';
            $cmd = $node->dbConnection->createCommand($sql);

            // Bind values
            foreach ($values as $name => $value) {
                $cmd->bindValue($name, $value);
            }

            $data = $cmd->queryRow();
            $node->nodeId = $data['nodeId'];
            $node->nodeLeftId = $data['nodeLeftId'];
            $node->nodeRightId = $data['nodeRightId'];
            $node->nodeLevel = $data['nodeLevel'];
            $result = true;

        } catch (\Exception $e) {
            \Yii::log('Error in ' . __METHOD__ . '():' . $e->getMessage(), \CLogger::LEVEL_ERROR,
                'sweelix.yii1.ext.db.dao');
            $result = false;
        }
        return $result;
    }

    /**
     * Move node
     *
     * Move node from one place to another with all its
     * children
     *
     * @param EntityNode &$node node object to move
     * @param integer $targetNodeId id of target node
     * @param enum $where moving mode can be first, last, before, after, in
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function move(EntityNode &$node, $targetNodeId, $where = 'in')
    {
        try {
            \Yii::trace('Move node ' . $node->nodeId . ' ' . $where . ' nodeId : ' . $targetNodeId,
                'sweelix.yii1.ext.db.dao');
            $sql = 'CALL spNodeMove(:nodeId, :targetNodeId, :where)';
            $where = strtolower($where);
            switch ($where) {
                case 'first':
                case 'last':
                case 'before':
                case 'after':
                case 'in':
                    // do nothing we are ok
                    break;
                default :
                    $where = 'in';
                    break;
            }
            $cmd = $node->dbConnection->createCommand($sql);
            $cmd->bindValue(':nodeId', $node->nodeId, \PDO::PARAM_INT);
            $cmd->bindValue(':targetNodeId', $targetNodeId, \PDO::PARAM_INT);
            $cmd->bindValue(':where', $where, \PDO::PARAM_STR);
            $data = $cmd->queryRow();
            $node->nodeLeftId = $data['nodeLeftId'];
            $node->nodeRightId = $data['nodeRightId'];
            $node->nodeLevel = $data['nodeLevel'];
            $result = true;
        } catch (\Exception $e) {
            \Yii::log('Error in ' . __METHOD__ . '():' . $e->getMessage(), \CLogger::LEVEL_ERROR,
                'sweelix.yii1.ext.db.dao');
            $result = false;
        }
        return $result;
    }

    /**
     * Reorder Node contents
     *
     * Reset order values for all contents in current node. Usefull when a content has been
     * deleted to moved from one node to another
     *
     * @param EntityNode &$node node object
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function reOrder(EntityNode &$node)
    {
        try {
            \Yii::trace('Reorder contents of node ' . $node->nodeId, 'sweelix.yii1.ext.db.dao');
            $sql = 'CALL spContentReorder(:nodeId)';
            $cmd = $node->dbConnection->createCommand($sql);
            $cmd->bindValue(':nodeId', $node->nodeId, \PDO::PARAM_INT);
            $cmd->execute();
            $result = true;
        } catch (\Exception $e) {
            \Yii::log('Error in ' . __METHOD__ . '():' . $e->getMessage(), \CLogger::LEVEL_ERROR,
                'sweelix.yii1.ext.db.dao');
            $result = false;
        }
        return $result;
    }
}
