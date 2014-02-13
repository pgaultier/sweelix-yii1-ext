<?php
/**
 * File m130322_171244_initToken.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  migrations
 * @package   sweelix.yii1.ext.migrations
 */

namespace sweelix\yii1\ext\migrations;

/**
 * Class m130322_171244_initToken
 *
 * Initialize the database token tables
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  migrations
 * @package   sweelix.yii1.ext.migrations
 * @since     2.0.0
 */
class m130322_171244_initToken extends \CDbMigration {

	public function safeUp() {
		$this->createTable('{{tokens}}', array(
				'elementProperty'=>'string NOT NULL',
				'elementId' => 'bigint',
				'elementType' => 'string DEFAULT NULL',
				'elementTitle' => 'string DEFAULT NULL',
				'tokenKey'=>'string NOT NULL',
				'tokenNumeric'=>'float',
				'contentId'=>'bigint DEFAULT NULL',
				'nodeId'=>'bigint DEFAULT NULL',
				'tagId'=>'bigint DEFAULT NULL',
				'groupId'=>'bigint DEFAULT NULL',
				'tokenWeight'=>'integer DEFAULT 0',
				'tokenDateCreate'=>'datetime NOT NULL',
				'primary key(elementProperty, elementId, elementType, tokenKey)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->addForeignKey('contentIdFk', '{{tokens}}', 'contentId', '{{contents}}', 'contentId', 'CASCADE', 'CASCADE');
		$this->addForeignKey('nodeIdFk', '{{tokens}}', 'nodeId', '{{nodes}}', 'nodeId', 'CASCADE', 'CASCADE');
		$this->addForeignKey('groupIdFk', '{{tokens}}', 'groupId', '{{groups}}', 'groupId', 'CASCADE', 'CASCADE');
		$this->addForeignKey('tagIdFk', '{{tokens}}', 'tagId', '{{tags}}', 'tagId', 'CASCADE', 'CASCADE');
		$this->createIndex('elementPropertyIdx', '{{tokens}}', 'elementProperty');
		$this->createIndex('elementIdIdx', '{{tokens}}', 'elementId');
		$this->createIndex('elementTypeIdx', '{{tokens}}', 'elementType');
		$this->createIndex('elementTitleIdx', '{{tokens}}', 'elementTitle');
		$this->createIndex('tokenKeyIdx', '{{tokens}}', 'tokenKey');
		$this->createIndex('tokenNumericIdx', '{{tokens}}', 'tokenNumeric');
		$this->createIndex('tokenDateCreateIdx', '{{tokens}}', 'tokenDateCreate');
		$this->insert('{{swauthItem}}', array(
				'name' => 'search',
				'type' => \CAuthItem::TYPE_ROLE,
				'description' => '',
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthAssignment}}', array(
				'itemname' => 'search',
				'userid' => 1,
				'bizrule' => null,
				'data' => 'N;',
		));
	}

	public function safeDown() {
		$this->delete('{{swauthAssignment}}', 'itemname = :itemname', array(':itemname' => 'search'));
		$this->delete('{{swauthItem}}', 'name = :name', array(':name' => 'search'));
		$this->dropIndex('elementPropertyIdx', '{{tokens}}');
		$this->dropIndex('elementTypeIdx', '{{tokens}}');
		$this->dropIndex('elementTitleIdx', '{{tokens}}');
		$this->dropIndex('tokenKeyIdx', '{{tokens}}');
		$this->dropIndex('tokenNumericIdx', '{{tokens}}');
		$this->dropIndex('tokenDateCreateIdx', '{{tokens}}');
		$this->dropForeignKey('groupIdFk', '{{tokens}}');
		$this->dropForeignKey('tagIdFk', '{{tokens}}');
		$this->dropForeignKey('nodeIdFk', '{{tokens}}');
		$this->dropForeignKey('contentIdFk', '{{tokens}}');
		$this->dropTable('{{tokens}}');
	}
}