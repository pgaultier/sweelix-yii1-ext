<?php
/**
 * File m121121_080000_initDatabase.php
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
 * Class m121121_080000_initDatabase
 *
 * Initialize the database structure and define minimal
 * data to bootstrap
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
class m121121_080000_initDatabase extends \CDbMigration {

	/**
	 * Initialize database
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function up() {
		$this->createTable('{{authors}}', array(
			'authorId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'authorEmail'=>'varchar(255) NOT NULL',
			'authorPassword'=>'varchar(64) NOT NULL',
			'authorFirstname'=>'varchar(45)',
			'authorLastname'=>'varchar(45)',
			'authorLastLogin'=>'datetime',
			'languageId'=>'varchar(8) DEFAULT \'na-na\'',
			'PRIMARY KEY (authorId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{contentMeta}}', array(
			'contentId'=>'bigint(20) NOT NULL',
			'metaId'=>'varchar(32) NOT NULL',
			'contentMetaValue'=>'text',
			'PRIMARY KEY (contentId, metaId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{contentTag}}', array(
			'contentId'=>'bigint(20) NOT NULL',
			'tagId'=>'bigint(20) NOT NULL',
			'PRIMARY KEY (contentId, tagId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{contents}}', array(
			'contentId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'contentTitle'=>'text NOT NULL',
			'contentSubtitle'=>'text',
			'contentUrl'=>'varchar(255)',
			'contentData'=>'longblob',
			'contentOrder'=>'int(11) NOT NULL DEFAULT \'0\'',
			'contentStartDate'=>'date',
			'contentEndDate'=>'date',
			'contentStatus'=>'enum(\'draft\',\'online\',\'offline\') NOT NULL DEFAULT \'draft\'',
			'contentViewed'=>'bigint(20) NOT NULL DEFAULT \'0\'',
			'nodeId'=>'bigint(20)',
			'authorId'=>'bigint(20)',
			'templateId'=>'bigint(20)',
			'languageId'=>'varchar(8) DEFAULT \'na-na\'',
			'PRIMARY KEY (contentId)'
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{groups}}', array(
			'groupId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'groupTitle'=>'text NOT NULL',
			'groupType'=>'enum(\'single\',\'multiple\') NOT NULL',
			'groupUrl'=>'varchar(255)',
			'groupData'=>'longblob',
			'templateId'=>'bigint(20)',
			'languageId'=>'varchar(8) DEFAULT \'na-na\'',
			'PRIMARY KEY (groupId)'
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{languages}}', array(
			'languageId'=>'varchar(8) NOT NULL DEFAULT\'\'',
			'languageTitle'=>'varchar(255) NOT NULL',
			'languageIsActive'=>'tinyint(1) DEFAULT \'0\'',
			'PRIMARY KEY (languageId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{metas}}', array(
			'metaId'=>'varchar(32) NOT NULL DEFAULT\'\'',
			'metaDefaultValue'=>'text',
			'PRIMARY KEY (metaId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{nodeMeta}}', array(
			'nodeId'=>'bigint(20) NOT NULL',
			'metaId'=>'varchar(32) NOT NULL DEFAULT\'\'',
			'nodeMetaValue'=>'text',
			'PRIMARY KEY (nodeId, metaId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{nodeTag}}', array(
			'nodeId'=>'bigint(20) NOT NULL',
			'tagId'=>'bigint(20) NOT NULL',
			'PRIMARY KEY (nodeId, tagId)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{nodes}}', array(
			'nodeId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'nodeTitle'=>'text NOT NULL',
			'nodeUrl'=>'varchar(255)',
			'nodeData'=>'longblob',
			'nodeDisplayMode'=>'enum(\'first\',\'list\',\'redirect\') NOT NULL DEFAULT \'first\'',
			'nodeRedirection'=>'bigint(20)',
			'nodeLeftId'=>'bigint(20) NOT NULL',
			'nodeRightId'=>'bigint(20) NOT NULL',
			'nodeLevel'=>'bigint(20) NOT NULL',
			'nodeStatus'=>'enum(\'draft\',\'online\',\'offline\') NOT NULL DEFAULT \'draft\'',
			'nodeViewed'=>'bigint(20) NOT NULL DEFAULT \'0\'',
			'authorId'=>'bigint(20)',
			'templateId'=>'bigint(20)',
			'languageId'=>'varchar(8) DEFAULT \'na-na\'',
			'PRIMARY KEY (nodeId)'
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{tags}}', array(
			'tagId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'tagTitle'=>'text NOT NULL',
			'tagUrl'=>'varchar(255)',
			'tagData'=>'longblob',
			'groupId'=>'bigint(20)',
			'templateId'=>'bigint(20)',
			'languageId'=>'varchar(8) DEFAULT \'na-na\'',
			'PRIMARY KEY (tagId)'
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{templates}}', array(
			'templateId'=>'bigint(20) NOT NULL AUTO_INCREMENT',
			'templateTitle'=>'text NOT NULL',
			'templateDefinition'=>'varchar(255) NOT NULL',
			'templateController'=>'varchar(255) NULL',
			'templateAction'=>'varchar(255) NULL',
			'templateComposite'=>'varchar(255) NULL',
			'templateType'=>'enum(\'single\',\'list\') NOT NULL',
			'PRIMARY KEY (templateId)'
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$this->createTable('{{urls}}', array(
			'urlValue'=>'varchar(255) NOT NULL',
			'urlElementType'=>'enum(\'node\',\'content\',\'tag\',\'group\') NOT NULL',
			'urlElementId'=>'bigint(20) NOT NULL',
			'PRIMARY KEY (urlValue)'
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{swauthItem}}', array(
				'name' => 'varchar(64) not null',
				'type' => 'integer NOT NULL',
				'description' => 'text',
				'bizrule' => 'text',
				'data' => 'text',
				'primary key (name)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{swauthItemChild}}',array(
				'parent' => 'varchar(64) not null',
				'child' => 'varchar(64) not null',
				'primary key (parent,child)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createTable('{{swauthAssignment}}', array(
				'itemname' => 'varchar(64) not null',
				'userid' => 'integer not null',
				'bizrule' => 'text',
				'data' => 'text',
				'primary key (itemname,userid)',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

		$this->createIndex('authorEmail', '{{authors}}', 'authorEmail', true);
		$this->createIndex('languageId', '{{authors}}', 'languageId', false);
		$this->addForeignKey('authors_fk_languageId',
			'{{authors}}',
			'languageId',
			'{{languages}}',
			'languageId'
		);

		$this->createIndex('contentId', '{{contentMeta}}', 'contentId', false);
		$this->addForeignKey('contentMeta_fk_contentId',
			'{{contentMeta}}',
			'contentId',
			'{{contents}}',
			'contentId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('metaId', '{{contentMeta}}', 'metaId', false);
		$this->addForeignKey('contentMeta_fk_metaId',
			'{{contentMeta}}',
			'metaId',
			'{{metas}}',
			'metaId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('authorId', '{{contents}}', 'authorId', false);
		$this->addForeignKey('contents_fk_authorId',
			'{{contents}}',
			'authorId',
			'{{authors}}',
			'authorId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('languageId', '{{contents}}', 'languageId', false);
		$this->addForeignKey('contents_fk_languageId',
			'{{contents}}',
			'languageId',
			'{{languages}}',
			'languageId'
		);

		$this->createIndex('nodeId', '{{contents}}', 'nodeId', false);
		$this->addForeignKey('contents_fk_nodeId',
			'{{contents}}',
			'nodeId',
			'{{nodes}}',
			'nodeId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('templateId', '{{contents}}', 'templateId', false);
		$this->addForeignKey('contents_fk_templateId',
			'{{contents}}',
			'templateId',
			'{{templates}}',
			'templateId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('contentId', '{{contentTag}}', 'contentId', false);
		$this->addForeignKey('contentTag_fk_contentId',
			'{{contentTag}}',
			'contentId',
			'{{contents}}',
			'contentId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('tagId', '{{contentTag}}', 'tagId', false);
		$this->addForeignKey('contentTag_fk_tagId',
			'{{contentTag}}',
			'tagId',
			'{{tags}}',
			'tagId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('languageId', '{{groups}}', 'languageId', false);
		$this->addForeignKey('groups_fk_languageId',
			'{{groups}}',
			'languageId',
			'{{languages}}',
			'languageId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('templateId', '{{groups}}', 'templateId', false);
		$this->addForeignKey('groups_fk_templateId',
			'{{groups}}',
			'templateId',
			'{{templates}}',
			'templateId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('metaId', '{{nodeMeta}}', 'metaId', false);
		$this->addForeignKey('nodeMeta_fk_metaId',
			'{{nodeMeta}}',
			'metaId',
			'{{metas}}',
			'metaId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('nodeId', '{{nodeMeta}}', 'nodeId', false);
		$this->addForeignKey('nodeMeta_fk_nodeId',
			'{{nodeMeta}}',
			'nodeId',
			'{{nodes}}',
			'nodeId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('languageId', '{{nodes}}', 'languageId', false);
		$this->createIndex('nodeLeftId', '{{nodes}}', 'nodeLeftId', false);
		$this->createIndex('nodeRightId', '{{nodes}}', 'nodeRightId', false);
		$this->createIndex('nodeLevel', '{{nodes}}', 'nodeLevel', false);
		$this->createIndex('authorId', '{{nodes}}', 'authorId', false);
		$this->addForeignKey('nodes_fk_authorId',
			'{{nodes}}',
			'authorId',
			'{{authors}}',
			'authorId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('nodeRedirection', '{{nodes}}', 'nodeRedirection', false);
		$this->addForeignKey('nodes_fk_nodeRedirection',
			'{{nodes}}',
			'nodeRedirection',
			'{{nodes}}',
			'nodeId',
			'SET NULL',
			'CASCADE'
		);
		$this->createIndex('templateId', '{{nodes}}', 'templateId', false);
		$this->addForeignKey('nodes_fk_templateId',
			'{{nodes}}',
			'templateId',
			'{{templates}}',
			'templateId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('nodeId', '{{nodeTag}}', 'nodeId', false);
		$this->addForeignKey('nodeTag_fk_nodeId',
			'{{nodeTag}}',
			'nodeId',
			'{{nodes}}',
			'nodeId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('tagId', '{{nodeTag}}', 'tagId', false);
		$this->addForeignKey('nodeTag_fk_tagId',
			'{{nodeTag}}',
			'tagId',
			'{{tags}}',
			'tagId',
			'CASCADE',
			'CASCADE'
		);

		$this->createIndex('groupId', '{{tags}}', 'groupId', false);
		$this->addForeignKey('tags_fk_groupId',
			'{{tags}}',
			'groupId',
			'{{groups}}',
			'groupId',
			'CASCADE',
			'CASCADE'
		);


		$this->createIndex('languageId', '{{tags}}', 'languageId', false);
		$this->addForeignKey('tags_fk_languageId',
			'{{tags}}',
			'languageId',
			'{{languages}}',
			'languageId',
			'SET NULL',
			'CASCADE'
		);

		$this->createIndex('templateId', '{{tags}}', 'templateId', false);
		$this->addForeignKey('tags_fk_templateId',
			'{{tags}}',
			'templateId',
			'{{templates}}',
			'templateId',
			'SET NULL',
			'CASCADE'
		);
		$this->createIndex('urlElementId', '{{urls}}', 'urlElementType, urlValue', true);
		$this->createIndex('urlElementType', '{{urls}}', 'urlElementType, urlElementId', true);

		$this->addForeignKey('swauthItemChild_ParentAuthItemFk', '{{swauthItemChild}}', 'parent', '{{swauthItem}}', 'name', 'CASCADE', 'CASCADE');
		$this->addForeignKey('swauthItemChild_ChildAuthItemFk', '{{swauthItemChild}}', 'child', '{{swauthItem}}', 'name', 'CASCADE', 'CASCADE');
		$this->addForeignKey('swauthAssignment_ItemAuthItemFk', '{{swauthAssignment}}', 'itemname', '{{swauthItem}}', 'name', 'CASCADE', 'CASCADE');


		$this->insert('{{languages}}', array(
			'languageId' => 'na-na',
			'languageTitle' => 'Undefined',
			'languageIsActive' => 1
		));
		$this->insert('{{authors}}', array(
			'authorEmail' => 'admin@sweelix.net',
			'authorPassword' => sha1('password'),
			'authorFirstname' => 'Super',
			'authorLastname' => 'Admin',
			'authorLastLogin' => null,
			'languageId' => 'na-na'
		));
		$this->insert('{{templates}}', array(
			'templateTitle' => 'List template',
			'templateDefinition'=>'listTemplate',
			'templateController' => 'ListTemplate',
			'templateAction' => null,
			'templateComposite' => null,
			'templateType' => 'list'
		));
		$this->insert('{{templates}}', array(
			'templateTitle' => 'Template for single element',
			'templateDefinition'=>'singleTemplate',
			'templateController' => 'SingleTemplate',
			'templateAction' => null,
			'templateComposite' => null,
			'templateType' => 'single'
		));
		$this->insert('{{nodes}}', array(
			'nodeTitle' => 'Root',
			'nodeUrl' => 'root',
			'nodeData' => null,
			'nodeDisplayMode' => 'list',
			'nodeLeftId' => 1,
			'nodeRightId' => 2,
			'nodeLevel' => 0,
			'nodeStatus' => 'offline',
			'nodeViewed' => 0,
			'authorId' => 1,
			'templateId' => 1,
			'languageId' => 'na-na'
		));
		$this->insert('{{groups}}', array(
			'groupTitle' => 'Group',
			'groupUrl' => 'group',
			'groupType' => 'single',
			'groupData' => null,
			'templateId' => 1,
			'languageId' => 'na-na',
		));
		$this->insert('{{metas}}', array(
			'metaId' => 'generator',
			'metaDefaultValue' => 'Sweelix'
		));
		$this->insert('{{swauthItem}}', array(
				'name' => 'dashboard',
				'type' => \CAuthItem::TYPE_ROLE,
				'description' => '',
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthItem}}', array(
				'name' => 'structure',
				'type' => \CAuthItem::TYPE_ROLE,
				'description' => '',
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthItem}}', array(
				'name' => 'cloud',
				'type' => \CAuthItem::TYPE_ROLE,
				'description' => '',
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthItem}}', array(
				'name' => 'users',
				'type' => \CAuthItem::TYPE_ROLE,
				'description' => '',
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthAssignment}}', array(
				'itemname' => 'dashboard',
				'userid' => 1,
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthAssignment}}', array(
				'itemname' => 'structure',
				'userid' => 1,
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthAssignment}}', array(
				'itemname' => 'cloud',
				'userid' => 1,
				'bizrule' => null,
				'data' => 'N;',
		));
		$this->insert('{{swauthAssignment}}', array(
				'itemname' => 'users',
				'userid' => 1,
				'bizrule' => null,
				'data' => 'N;',
		));
	}

	/**
	 * Un-initialize database. Drop everything to get an empty DB
	 *
	 * @return void
	 */
	public function down() {
    	$this->dropForeignKey('swauthItemChild_ParentAuthItemFk', '{{swauthItemChild}}');
    	$this->dropForeignKey('swauthItemChild_ChildAuthItemFk', '{{swauthItemChild}}');
    	$this->dropForeignKey('swauthAssignment_ItemAuthItemFk', '{{swauthAssignment}}');
		$this->dropForeignKey('authors_fk_languageId', '{{authors}}');
		$this->dropForeignKey('contentMeta_fk_contentId', '{{contentMeta}}');
		$this->dropForeignKey('contentMeta_fk_metaId', '{{contentMeta}}');
		$this->dropForeignKey('contents_fk_authorId', '{{contents}}');
		$this->dropForeignKey('contents_fk_languageId', '{{contents}}');
		$this->dropForeignKey('contents_fk_nodeId', '{{contents}}');
		$this->dropForeignKey('contents_fk_templateId', '{{contents}}');
		$this->dropForeignKey('contentTag_fk_contentId', '{{contentTag}}');
		$this->dropForeignKey('contentTag_fk_tagId', '{{contentTag}}');
		$this->dropForeignKey('groups_fk_languageId', '{{groups}}');
		$this->dropForeignKey('groups_fk_templateId', '{{groups}}');
		$this->dropForeignKey('nodeMeta_fk_metaId', '{{nodeMeta}}');
		$this->dropForeignKey('nodeMeta_fk_nodeId', '{{nodeMeta}}');
		$this->dropForeignKey('nodes_fk_authorId', '{{nodes}}');
		$this->dropForeignKey('nodes_fk_nodeRedirection', '{{nodes}}');
		$this->dropForeignKey('nodes_fk_templateId', '{{nodes}}');
		$this->dropForeignKey('nodeTag_fk_nodeId', '{{nodeTag}}');
		$this->dropForeignKey('nodeTag_fk_tagId', '{{nodeTag}}');
		$this->dropForeignKey('tags_fk_groupId', '{{tags}}');
		$this->dropForeignKey('tags_fk_languageId', '{{tags}}');
		$this->dropForeignKey('tags_fk_templateId', '{{tags}}');

    	$this->dropTable('{{swauthAssignment}}');
		$this->dropTable('{{swauthItemChild}}');
		$this->dropTable('{{swauthItem}}');

		$this->dropTable('{{urls}}');
		$this->dropTable('{{contentMeta}}');
		$this->dropTable('{{contentTag}}');
		$this->dropTable('{{nodeMeta}}');
		$this->dropTable('{{nodeTag}}');
		$this->dropTable('{{tags}}');
		$this->dropTable('{{groups}}');
		$this->dropTable('{{metas}}');
		$this->dropTable('{{contents}}');
		$this->dropTable('{{nodes}}');
		$this->dropTable('{{templates}}');
		$this->dropTable('{{authors}}');
		$this->dropTable('{{languages}}');
	}
}
