<?php
/**
 * File m131121_080000_upgradeDatabaseTo21.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  migrations
 * @package   sweelix.yii1.ext.migrations
 */

namespace sweelix\yii1\ext\migrations;

/**
 * Class m131121_080000_upgradeDatabaseTo21
 *
 * Upgrade 2.0 database structure to 2.1
 * enhanced stored procedures and xxxDate
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  migrations
 * @package   sweelix.yii1.ext.migrations
 * @since     2.1.0
 */
class m131121_080000_upgradeDatabaseTo21 extends \CDbMigration {

	/**
	 * Initialize database
	 *
	 * @return void
	 * @since  2.1.0
	 */
	public function up() {
		$table = $this->dbConnection->getSchema()->getTable('{{contents}}');
		if(isset($table->columns['contentCreateDate']) === false) {
			$this->addColumn('{{contents}}', 'contentCreateDate', 'datetime AFTER contentViewed');
			$this->addColumn('{{contents}}', 'contentUpdateDate', 'datetime AFTER contentCreateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{nodes}}');
		if(isset($table->columns['nodeCreateDate']) === false) {
			$this->addColumn('{{nodes}}', 'nodeCreateDate', 'datetime AFTER nodeViewed');
			$this->addColumn('{{nodes}}', 'nodeUpdateDate', 'datetime AFTER nodeCreateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{groups}}');
		if(isset($table->columns['groupCreateDate']) === false) {
			$this->addColumn('{{groups}}', 'groupCreateDate', 'datetime AFTER groupData');
			$this->addColumn('{{groups}}', 'groupUpdateDate', 'datetime AFTER groupCreateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{tags}}');
		if(isset($table->columns['tagCreateDate']) === false) {
			$this->addColumn('{{tags}}', 'tagCreateDate', 'datetime AFTER tagData');
			$this->addColumn('{{tags}}', 'tagUpdateDate', 'datetime AFTER tagCreateDate');
		}
		$this->alterColumn('{{contents}}', 'contentStartDate', 'datetime');
		$this->alterColumn('{{contents}}', 'contentEndDate', 'datetime');

		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spContentMove')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spContentReorder')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeDelete')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeInsert')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeMove')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spFlushUrl')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spContentMove(
				IN inSourceContentId BIGINT(20),
				IN inWhere ENUM(\'top\', \'bottom\', \'up\', \'down\', \'before\', \'after\'),
				IN inTargetContentId BIGINT(20)
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE sourceNodeId BIGINT(20);
				DECLARE sourceNbContents BIGINT(20);
				DECLARE sourceOrder BIGINT(20);
				DECLARE sourceTargetOrder BIGINT(20);

				SELECT nodeId INTO sourceNodeId FROM contents WHERE contentId = inSourceContentId;
				CALL spContentReorder(sourceNodeId);
				SELECT contentOrder INTO sourceOrder FROM contents WHERE contentId = inSourceContentId;

				CASE inWhere
					WHEN \'before\' THEN
							BEGIN
							SELECT contentOrder INTO sourceTargetOrder FROM contents WHERE contentId = inTargetContentId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId AND contentOrder >= sourceTargetOrder;
							UPDATE contents SET contentOrder = sourceTargetOrder WHERE contentId = inSourceContentId;
						END;
					WHEN \'after\' THEN
						BEGIN
							SELECT contentOrder INTO sourceTargetOrder FROM contents WHERE contentId = inTargetContentId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId AND contentOrder > sourceTargetOrder;
							UPDATE contents SET contentOrder = sourceTargetOrder + 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'top\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'bottom\' THEN
						BEGIN
							SELECT count(contentId) INTO sourceNbContents FROM contents WHERE nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = sourceNbContents + 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'up\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE contentOrder = (sourceOrder-1) AND nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = contentOrder - 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'down\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder - 1 WHERE contentOrder = (sourceOrder+1) AND nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE contentId = inSourceContentId;
						END;
				END CASE;
				CALL spContentReorder(sourceNodeId);
				SELECT * FROM contents WHERE contentId = inSourceContentId;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spContentReorder(
			IN inSourceNodeId BIGINT(20)
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE currentContentId BIGINT(20) DEFAULT 0;
				DECLARE done INT DEFAULT 0;
				DECLARE counter BIGINT(20) DEFAULT 0;
				DECLARE contentList CURSOR FOR
					SELECT contentId
					FROM contents
					WHERE nodeId = inSourceNodeId
					ORDER BY contentOrder ASC, contentStartDate ASC, contentId ASC;
				DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

				OPEN contentList;
				SET counter = 1;
				REPEAT
					FETCH contentList INTO currentContentId;
					IF NOT done THEN
						UPDATE contents set contentOrder = counter WHERE contentId = currentContentId;
						SET counter = counter + 1;
					END IF;
				UNTIL done END REPEAT;
				CLOSE contentList;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeDelete(
				IN inTargetNodeId bigint(20),
				IN inReturnList INT
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE currentLeftBorder	bigint(20);
				DECLARE currentRightBorder bigint(20);
				DECLARE currentWidth			 bigint(20);

				SELECT nodeLeftId, nodeRightId, (nodeRightId - nodeLeftId + 1)
					INTO @currentLeftBorder, @currentRightBorder, @currentWidth
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				CREATE TEMPORARY TABLE IF NOT EXISTS tmpDeletedNodes(
					nodeId bigint(20)
				);

				INSERT INTO tmpDeletedNodes
					SELECT nodeId
					FROM nodes
					WHERE nodeLeftId >= @currentLeftBorder AND nodeRightId <= @currentRightBorder;
				DELETE FROM nodes
				WHERE nodeId IN (
					SELECT nodeId
					FROM tmpDeletedNodes
				);

				UPDATE nodes
				SET nodeRightId = nodeRightId - @currentWidth
					WHERE nodeRightId > @currentRightBorder;

				UPDATE nodes
				SET nodeLeftId = nodeLeftId - @currentWidth
				WHERE nodeLeftId > @currentRightBorder;

				IF (inReturnList = 1) THEN
					SELECT nodeId
					FROM tmpDeletedNodes;
				END IF;
				DROP TABLE tmpDeletedNodes;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeInsert(
				IN inTargetNodeId bigint(20),
				IN inNodeTitle text,
				IN inNodeUrl varchar(255),
				IN inNodeData longblob,
				IN inNodeCreateDate datetime,
				IN inNodeDisplayMode enum(\'first\', \'list\', \'redirect\'),
				IN inNodeRedirection bigint(20),
				IN inNodeStatus enum(\'draft\', \'online\', \'offline\'),
				IN inNodeViewed bigint(20),
				IN inAuthorId bigint(20),
				IN inTemplateId bigint(20),
				IN inLanguageId varchar(8)
			)
					MODIFIES SQL DATA
			BEGIN
				DECLARE currentRightBorder bigint(20);
				DECLARE currentLevel bigint(20);

				SELECT nodeRightId, nodeLevel
				INTO @currentRightBorder, @currentLevel
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				UPDATE nodes
				SET nodeRightId = nodeRightId + 2
				WHERE nodeRightId >= @currentRightBorder;

				UPDATE nodes
				SET nodeLeftId = nodeLeftId + 2
				WHERE nodeLeftId >= @currentRightBorder;

				INSERT INTO nodes
					(
						nodeTitle,
						nodeUrl,
						nodeData,
						nodeCreateDate,
						nodeDisplayMode,
						nodeRedirection,
						nodeLeftId,
						nodeRightId,
						nodeLevel,
						nodeStatus,
						nodeViewed,
						authorId,
						templateId,
						languageId
					)
				VALUES
					(
						inNodeTitle,
						inNodeUrl,
						inNodeData,
						inNodeCreateDate,
						inNodeDisplayMode,
						inNodeRedirection,
						@currentRightBorder,
						@currentRightBorder + 1,
						@currentLevel + 1,
						inNodeStatus,
						inNodeViewed,
						inAuthorId,
						inTemplateId,
						inLanguageId
					);
					/* TODO: refresh url table */
					/* CALL sp_build_url(LAST_INSERT_ID(), inNodeTitle, \'NODE\', 0); */
				SELECT * FROM nodes where nodeId = LAST_INSERT_ID();
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeMove(
				IN inSourceNodeId BIGINT(20),
				IN inTargetNodeId BIGINT(20),
				IN inWhere ENUM (\'in\', \'before\', \'after\')
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE sourceLeftId	BIGINT(20);
				DECLARE sourceRightId BIGINT(20);
				DECLARE sourceLevel	 BIGINT(20);
				DECLARE sourceWidth	 BIGINT(20);
				DECLARE targetLeftId	BIGINT(20);
				DECLARE targetRightId BIGINT(20);
				DECLARE targetLevel	 BIGINT(20);
				DECLARE targetWidth	 BIGINT(20);
				SET FOREIGN_KEY_CHECKS=0;
				DROP TABLE IF EXISTS tmpContentsWhileMoveNodes;
				CREATE TEMPORARY TABLE tmpContentsWhileMoveNodes (
					contentId BIGINT (20) NOT NULL,
					nodeId BIGINT (20) NOT NULL,
					PRIMARY KEY (contentId, nodeId)
				);
				DROP TABLE IF EXISTS tmpMoveNodes;
				CREATE TEMPORARY TABLE tmpMoveNodes (
					nodeId BIGINT (20) NOT NULL,
					nodeTitle TEXT NOT NULL,
					nodeUrl VARCHAR(255) DEFAULT NULL,
					nodeData LONGBLOB DEFAULT NULL,
					nodeDisplayMode ENUM(\'first\', \'list\', \'redirect\') NOT NULL DEFAULT \'first\',
					nodeRedirection BIGINT(20) DEFAULT NULL,
					nodeLeftId BIGINT(20) NOT NULL,
					nodeRightId BIGINT(20) NOT NULL,
					nodeLevel BIGINT(20) NOT NULL,
					nodeStatus ENUM(\'draft\', \'online\', \'offline\') NOT NULL DEFAULT \'draft\',
					nodeViewed BIGINT(20) NOT NULL DEFAULT 0,
					nodeCreateDate DATETIME DEFAULT NULL,
					nodeUpdateDate DATETIME DEFAULT NULL,
					authorId BIGINT(20) DEFAULT NULL,
					templateId BIGINT(20) DEFAULT NULL,
					languageId VARCHAR(8) DEFAULT \'na-na\',
					PRIMARY KEY (nodeId),
					KEY nodeRedirection (nodeRedirection)
				);

				/* get source node info */
				SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
				INTO sourceLeftId, sourceRightId, sourceLevel, sourceWidth
				FROM nodes
				WHERE nodeId = inSourceNodeId;

				/* get original target node info */
				SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
				INTO targetLeftId, targetRightId, targetLevel, targetWidth
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				IF NOT ((sourceLeftId < targetLeftId) && (sourceRightId > targetRightId)) THEN

					/* 1.1 copy node to move */
					INSERT INTO tmpMoveNodes
						SELECT
							nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, nodeCreateDate, nodeUpdateDate, authorId, templateId, languageId
						FROM nodes
						WHERE nodeLeftId >= sourceLeftId AND nodeRightId <= sourceRightId;
					/* 1.2 reset the level */
					UPDATE tmpMoveNodes
					SET
						nodeLeftId = (nodeLeftId - sourceLeftId + 1),
						nodeRightId = (nodeRightId - sourceLeftId + 1),
						nodeLevel = (nodeLevel - sourceLevel + 1);

					/* 2 delete node */
					CALL spNodeDelete(inSourceNodeId, 0);

					/* 3 reinsert node */

					/* 3.1 get target node info */
					SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
					INTO targetLeftId, targetRightId, targetLevel, targetWidth
					FROM nodes
					WHERE nodeId = inTargetNodeId;

					CASE inWhere
					WHEN \'before\' THEN
						BEGIN
							/* 3.2.1 insert node before target node */
							/* 3.2.1.1 open space to insert nodes */
							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId >= targetLeftId;

							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId >= targetLeftId;
									/* 3.2.1.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetLeftId - 1,
								nodeRightId = nodeRightId + targetLeftId - 1,
								nodeLevel = nodeLevel + targetLevel - 1;
							/* 3.2.1.3 reimport nodes */
							INSERT
								INTO nodes
							SELECT
								nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, nodeCreateDate, nodeUpdateDate, authorId, templateId, languageId
							FROM tmpMoveNodes;
						END;
					WHEN \'after\' THEN
						BEGIN
							/* 3.2.2 insert node after target node */
							/* 3.2.2.1 open space to insert nodes */
							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId > targetRightId;

							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId > targetRightId;
							/* 3.2.1.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetRightId ,
								nodeRightId = nodeRightId + targetRightId ,
								nodeLevel = nodeLevel + targetLevel - 1;
							/* 3.2.1.3 reimport nodes */
							INSERT
							INTO nodes
							SELECT
								nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, nodeCreateDate, nodeUpdateDate, authorId, templateId, languageId
							FROM tmpMoveNodes;
						END;
					ELSE
						BEGIN
							/* 3.2.3 insert node into target node (last position) */
							/* 3.2.3.1 open space to insert nodes */
							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId > targetRightId;

							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId >= targetRightId;
							/* 3.2.3.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetRightId - 1,
								nodeRightId = nodeRightId + targetRightId - 1,
								nodeLevel = nodeLevel + targetLevel;
							/* 3.2.3.3 reimport nodes */
							INSERT
							INTO nodes
								SELECT
									nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, nodeCreateDate, nodeUpdateDate, authorId, templateId, languageId
								FROM
									tmpMoveNodes;
						END;
					END CASE;

				END IF;
				SET FOREIGN_KEY_CHECKS=1;
				SELECT *
				FROM nodes
				WHERE nodeId = inSourceNodeId;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spFlushUrl ()
				MODIFIES SQL DATA
			BEGIN
				DECLARE elementId BIGINT(20) DEFAULT 0;
				DECLARE elementType VARCHAR(32);
				DECLARE elementUrl VARCHAR(255);
				DECLARE done INT DEFAULT 0;
				DECLARE dupkey INT DEFAULT 0;
				DECLARE duped INT DEFAULT 0;
				DECLARE contentList CURSOR FOR
					SELECT contentId, contentUrl , \'content\'
					FROM contents
					ORDER BY contentId ASC;
				DECLARE nodeList CURSOR FOR
					SELECT nodeId, nodeUrl , \'node\'
					FROM nodes
					ORDER BY nodeId ASC;
				DECLARE tagList CURSOR FOR
					SELECT tagId, tagUrl , \'tag\'
					FROM tags
					ORDER BY tagId ASC;
				DECLARE groupList CURSOR FOR
					SELECT groupId, groupUrl , \'group\'
					FROM groups
					ORDER BY groupId ASC;

				DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
				DECLARE CONTINUE HANDLER FOR SQLSTATE \'23000\' SET dupkey = 1;

				TRUNCATE TABLE urls;
				OPEN contentList;
				REPEAT
					FETCH contentList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'c\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'c\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE contents set contentUrl = elementUrl where contentId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE contentList;
				SET done = 0;
				OPEN nodeList;
				REPEAT
					FETCH nodeList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
							SET elementUrl = CONCAT(\'n\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'n\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE nodes set nodeUrl = elementUrl where nodeId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE nodeList;
				SET done = 0;
				OPEN tagList;
				REPEAT
					FETCH tagList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'t\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'t\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE tags set tagUrl = elementUrl where tagId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE tagList;
				SET done = 0;
				OPEN groupList;
				REPEAT
					FETCH groupList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'g\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
								SET dupkey = 0;
								SET elementUrl = CONCAT(\'g\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE groups set groupUrl = elementUrl where groupId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE groupList;
			END')->execute();
	}

	/**
	 * Downgrade database.
	 *
	 * @return void
	 * @since  2.1.0
	 */
	public function down() {
		$table = $this->dbConnection->getSchema()->getTable('{{contents}}');
		if(isset($table->columns['contentCreateDate']) === true) {
			$this->dropColumn('{{contents}}', 'contentCreateDate');
			$this->dropColumn('{{contents}}', 'contentUpdateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{nodes}}');
		if(isset($table->columns['nodeCreateDate']) === true) {
			$this->dropColumn('{{nodes}}', 'nodeCreateDate');
			$this->dropColumn('{{nodes}}', 'nodeUpdateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{groups}}');
		if(isset($table->columns['groupCreateDate']) === true) {
			$this->dropColumn('{{groups}}', 'groupCreateDate');
			$this->dropColumn('{{groups}}', 'groupUpdateDate');
		}
		$table = $this->dbConnection->getSchema()->getTable('{{tags}}');
		if(isset($table->columns['tagCreateDate']) === true) {
			$this->dropColumn('{{tags}}', 'tagCreateDate');
			$this->dropColumn('{{tags}}', 'tagUpdateDate');
		}
		$this->alterColumn('{{contents}}', 'contentStartDate', 'date');
		$this->alterColumn('{{contents}}', 'contentEndDate', 'date');

		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spContentMove')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spContentReorder')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeDelete')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeInsert')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spNodeMove')->execute();
		$this->getDbConnection()->createCommand('DROP PROCEDURE IF EXISTS spFlushUrl')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spContentMove(
				IN inSourceContentId BIGINT(20),
				IN inWhere ENUM(\'top\', \'bottom\', \'up\', \'down\', \'before\', \'after\'),
				IN inTargetContentId BIGINT(20)
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE sourceNodeId BIGINT(20);
				DECLARE sourceNbContents BIGINT(20);
				DECLARE sourceOrder BIGINT(20);
				DECLARE sourceTargetOrder BIGINT(20);

				SELECT nodeId INTO sourceNodeId FROM contents WHERE contentId = inSourceContentId;
				CALL spContentReorder(sourceNodeId);
				SELECT contentOrder INTO sourceOrder FROM contents WHERE contentId = inSourceContentId;

				CASE inWhere
					WHEN \'before\' THEN
							BEGIN
							SELECT contentOrder INTO sourceTargetOrder FROM contents WHERE contentId = inTargetContentId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId AND contentOrder >= sourceTargetOrder;
							UPDATE contents SET contentOrder = sourceTargetOrder WHERE contentId = inSourceContentId;
						END;
					WHEN \'after\' THEN
						BEGIN
							SELECT contentOrder INTO sourceTargetOrder FROM contents WHERE contentId = inTargetContentId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId AND contentOrder > sourceTargetOrder;
							UPDATE contents SET contentOrder = sourceTargetOrder + 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'top\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'bottom\' THEN
						BEGIN
							SELECT count(contentId) INTO sourceNbContents FROM contents WHERE nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = sourceNbContents + 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'up\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE contentOrder = (sourceOrder-1) AND nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = contentOrder - 1 WHERE contentId = inSourceContentId;
						END;
					WHEN \'down\' THEN
						BEGIN
							UPDATE contents SET contentOrder = contentOrder - 1 WHERE contentOrder = (sourceOrder+1) AND nodeId = sourceNodeId;
							UPDATE contents SET contentOrder = contentOrder + 1 WHERE contentId = inSourceContentId;
						END;
				END CASE;
				CALL spContentReorder(sourceNodeId);
				SELECT * FROM contents WHERE contentId = inSourceContentId;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spContentReorder(
			IN inSourceNodeId BIGINT(20)
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE currentContentId BIGINT(20) DEFAULT 0;
				DECLARE done INT DEFAULT 0;
				DECLARE counter BIGINT(20) DEFAULT 0;
				DECLARE contentList CURSOR FOR
					SELECT contentId
					FROM contents
					WHERE nodeId = inSourceNodeId
					ORDER BY contentOrder ASC, contentStartDate ASC, contentId ASC;
				DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

				OPEN contentList;
				SET counter = 1;
				REPEAT
					FETCH contentList INTO currentContentId;
					IF NOT done THEN
						UPDATE contents set contentOrder = counter WHERE contentId = currentContentId;
						SET counter = counter + 1;
					END IF;
				UNTIL done END REPEAT;
				CLOSE contentList;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeDelete(
				IN inTargetNodeId bigint(20),
				IN inReturnList INT
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE currentLeftBorder	bigint(20);
				DECLARE currentRightBorder bigint(20);
				DECLARE currentWidth			 bigint(20);

				SELECT nodeLeftId, nodeRightId, (nodeRightId - nodeLeftId + 1)
					INTO @currentLeftBorder, @currentRightBorder, @currentWidth
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				CREATE TEMPORARY TABLE IF NOT EXISTS tmpDeletedNodes(
					nodeId bigint(20)
				);

				INSERT INTO tmpDeletedNodes
					SELECT nodeId
					FROM nodes
					WHERE nodeLeftId >= @currentLeftBorder AND nodeRightId <= @currentRightBorder;
				DELETE FROM nodes
				WHERE nodeId IN (
					SELECT nodeId
					FROM tmpDeletedNodes
				);

				UPDATE nodes
				SET nodeRightId = nodeRightId - @currentWidth
					WHERE nodeRightId > @currentRightBorder;

				UPDATE nodes
				SET nodeLeftId = nodeLeftId - @currentWidth
				WHERE nodeLeftId > @currentRightBorder;

				IF (inReturnList = 1) THEN
					SELECT nodeId
					FROM tmpDeletedNodes;
				END IF;
				DROP TABLE tmpDeletedNodes;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeInsert(
				IN inTargetNodeId bigint(20),
				IN inNodeTitle text,
				IN inNodeUrl varchar(255),
				IN inNodeData longblob,
				IN inNodeDisplayMode enum(\'first\', \'list\', \'redirect\'),
				IN inNodeRedirection bigint(20),
				IN inNodeStatus enum(\'draft\', \'online\', \'offline\'),
				IN inNodeViewed bigint(20),
				IN inAuthorId bigint(20),
				IN inTemplateId bigint(20),
				IN inLanguageId varchar(8)
			)
					MODIFIES SQL DATA
			BEGIN
				DECLARE currentRightBorder bigint(20);
				DECLARE currentLevel bigint(20);

				SELECT nodeRightId, nodeLevel
				INTO @currentRightBorder, @currentLevel
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				UPDATE nodes
				SET nodeRightId = nodeRightId + 2
				WHERE nodeRightId >= @currentRightBorder;

				UPDATE nodes
				SET nodeLeftId = nodeLeftId + 2
				WHERE nodeLeftId >= @currentRightBorder;

				INSERT INTO nodes
					(
						nodeTitle,
						nodeUrl,
						nodeData,
						nodeDisplayMode,
						nodeRedirection,
						nodeLeftId,
						nodeRightId,
						nodeLevel,
						nodeStatus,
						nodeViewed,
						authorId,
						templateId,
						languageId
					)
				VALUES
					(
						inNodeTitle,
						inNodeUrl,
						inNodeData,
						inNodeDisplayMode,
						inNodeRedirection,
						@currentRightBorder,
						@currentRightBorder + 1,
						@currentLevel + 1,
						inNodeStatus,
						inNodeViewed,
						inAuthorId,
						inTemplateId,
						inLanguageId
					);
					/* TODO: refresh url table */
					/* CALL sp_build_url(LAST_INSERT_ID(), inNodeTitle, \'NODE\', 0); */
				SELECT * FROM nodes where nodeId = LAST_INSERT_ID();
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spNodeMove(
				IN inSourceNodeId BIGINT(20),
				IN inTargetNodeId BIGINT(20),
				IN inWhere ENUM (\'in\', \'before\', \'after\')
			)
				MODIFIES SQL DATA
			BEGIN
				DECLARE sourceLeftId	BIGINT(20);
				DECLARE sourceRightId BIGINT(20);
				DECLARE sourceLevel	 BIGINT(20);
				DECLARE sourceWidth	 BIGINT(20);
				DECLARE targetLeftId	BIGINT(20);
				DECLARE targetRightId BIGINT(20);
				DECLARE targetLevel	 BIGINT(20);
				DECLARE targetWidth	 BIGINT(20);
				SET FOREIGN_KEY_CHECKS=0;
				DROP TABLE IF EXISTS tmpContentsWhileMoveNodes;
				CREATE TEMPORARY TABLE tmpContentsWhileMoveNodes (
					contentId BIGINT (20) NOT NULL,
					nodeId BIGINT (20) NOT NULL,
					PRIMARY KEY (contentId, nodeId)
				);
				DROP TABLE IF EXISTS tmpMoveNodes;
				CREATE TEMPORARY TABLE tmpMoveNodes (
					nodeId BIGINT (20) NOT NULL,
					nodeTitle TEXT NOT NULL,
					nodeUrl VARCHAR(255) DEFAULT NULL,
					nodeData LONGBLOB DEFAULT NULL,
					nodeDisplayMode ENUM(\'first\', \'list\', \'redirect\') NOT NULL DEFAULT \'first\',
					nodeRedirection BIGINT(20) DEFAULT NULL,
					nodeLeftId BIGINT(20) NOT NULL,
					nodeRightId BIGINT(20) NOT NULL,
					nodeLevel BIGINT(20) NOT NULL,
					nodeStatus ENUM(\'draft\', \'online\', \'offline\') NOT NULL DEFAULT \'draft\',
					nodeViewed BIGINT(20) NOT NULL DEFAULT 0,
					authorId BIGINT(20) DEFAULT NULL,
					templateId BIGINT(20) DEFAULT NULL,
					languageId VARCHAR(8) DEFAULT \'na-na\',
					PRIMARY KEY (nodeId),
					KEY nodeRedirection (nodeRedirection)
				);

				/* get source node info */
				SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
				INTO sourceLeftId, sourceRightId, sourceLevel, sourceWidth
				FROM nodes
				WHERE nodeId = inSourceNodeId;

				/* get original target node info */
				SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
				INTO targetLeftId, targetRightId, targetLevel, targetWidth
				FROM nodes
				WHERE nodeId = inTargetNodeId;

				IF NOT ((sourceLeftId < targetLeftId) && (sourceRightId > targetRightId)) THEN

					/* 1.1 copy node to move */
					INSERT INTO tmpMoveNodes
						SELECT
							nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, authorId, templateId, languageId
						FROM nodes
						WHERE nodeLeftId >= sourceLeftId AND nodeRightId <= sourceRightId;
					/* 1.2 reset the level */
					UPDATE tmpMoveNodes
					SET
						nodeLeftId = (nodeLeftId - sourceLeftId + 1),
						nodeRightId = (nodeRightId - sourceLeftId + 1),
						nodeLevel = (nodeLevel - sourceLevel + 1);

					/* 2 delete node */
					CALL spNodeDelete(inSourceNodeId, 0);

					/* 3 reinsert node */

					/* 3.1 get target node info */
					SELECT nodeLeftId, nodeRightId, nodeLevel, (nodeRightId - nodeLeftId + 1)
					INTO targetLeftId, targetRightId, targetLevel, targetWidth
					FROM nodes
					WHERE nodeId = inTargetNodeId;

					CASE inWhere
					WHEN \'before\' THEN
						BEGIN
							/* 3.2.1 insert node before target node */
							/* 3.2.1.1 open space to insert nodes */
							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId >= targetLeftId;

							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId >= targetLeftId;
									/* 3.2.1.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetLeftId - 1,
								nodeRightId = nodeRightId + targetLeftId - 1,
								nodeLevel = nodeLevel + targetLevel - 1;
							/* 3.2.1.3 reimport nodes */
							INSERT
								INTO nodes
							SELECT
								nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, authorId, templateId, languageId
							FROM tmpMoveNodes;
						END;
					WHEN \'after\' THEN
						BEGIN
							/* 3.2.2 insert node after target node */
							/* 3.2.2.1 open space to insert nodes */
							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId > targetRightId;

							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId > targetRightId;
							/* 3.2.1.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetRightId ,
								nodeRightId = nodeRightId + targetRightId ,
								nodeLevel = nodeLevel + targetLevel - 1;
							/* 3.2.1.3 reimport nodes */
							INSERT
							INTO nodes
							SELECT
								nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, authorId, templateId, languageId
							FROM tmpMoveNodes;
						END;
					ELSE
						BEGIN
							/* 3.2.3 insert node into target node (last position) */
							/* 3.2.3.1 open space to insert nodes */
							UPDATE nodes
							SET nodeLeftId = nodeLeftId + sourceWidth
							WHERE nodeLeftId > targetRightId;

							UPDATE nodes
							SET nodeRightId = nodeRightId + sourceWidth
							WHERE nodeRightId >= targetRightId;
							/* 3.2.3.2 prepare moved nodes */
							UPDATE tmpMoveNodes
							SET
								nodeLeftId = nodeLeftId + targetRightId - 1,
								nodeRightId = nodeRightId + targetRightId - 1,
								nodeLevel = nodeLevel + targetLevel;
							/* 3.2.3.3 reimport nodes */
							INSERT
							INTO nodes
								SELECT
									nodeId, nodeTitle, nodeUrl, nodeData, nodeDisplayMode, nodeRedirection, nodeLeftId, nodeRightId, nodeLevel, nodeStatus, nodeViewed, authorId, templateId, languageId
								FROM
									tmpMoveNodes;
						END;
					END CASE;

				END IF;
				SET FOREIGN_KEY_CHECKS=1;
				SELECT *
				FROM nodes
				WHERE nodeId = inSourceNodeId;
			END')->execute();
		$this->getDbConnection()->createCommand('CREATE PROCEDURE spFlushUrl ()
				MODIFIES SQL DATA
			BEGIN
				DECLARE elementId BIGINT(20) DEFAULT 0;
				DECLARE elementType VARCHAR(32);
				DECLARE elementUrl VARCHAR(255);
				DECLARE done INT DEFAULT 0;
				DECLARE dupkey INT DEFAULT 0;
				DECLARE duped INT DEFAULT 0;
				DECLARE contentList CURSOR FOR
					SELECT contentId, contentUrl , \'content\'
					FROM contents
					ORDER BY contentId ASC;
				DECLARE nodeList CURSOR FOR
					SELECT nodeId, nodeUrl , \'node\'
					FROM nodes
					ORDER BY nodeId ASC;
				DECLARE tagList CURSOR FOR
					SELECT tagId, tagUrl , \'tag\'
					FROM tags
					ORDER BY tagId ASC;
				DECLARE groupList CURSOR FOR
					SELECT groupId, groupUrl , \'group\'
					FROM groups
					ORDER BY groupId ASC;

				DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
				DECLARE CONTINUE HANDLER FOR SQLSTATE \'23000\' SET dupkey = 1;

				TRUNCATE TABLE urls;
				OPEN contentList;
				REPEAT
					FETCH contentList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'c\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'c\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE contents set contentUrl = elementUrl where contentId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE contentList;
				SET done = 0;
				OPEN nodeList;
				REPEAT
					FETCH nodeList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
							SET elementUrl = CONCAT(\'n\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'n\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE nodes set nodeUrl = elementUrl where nodeId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE nodeList;
				SET done = 0;
				OPEN tagList;
				REPEAT
					FETCH tagList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'t\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
							SET dupkey = 0;
								SET elementUrl = CONCAT(\'t\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE tags set tagUrl = elementUrl where tagId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE tagList;
				SET done = 0;
				OPEN groupList;
				REPEAT
					FETCH groupList INTO elementId, elementUrl, elementType ;
					IF NOT done THEN
						REPEAT
							SET dupkey = 0;
							IF(ISNULL(elementUrl) || (CHAR_LENGTH(elementUrl) = 0)) THEN
								SET elementUrl = CONCAT(\'g\', elementId);
							END IF;
							INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
							IF dupkey THEN
								SET dupkey = 0;
								SET elementUrl = CONCAT(\'g\', elementId, \'-\', elementUrl);
								INSERT INTO urls (urlValue, urlElementType, urlElementId) VALUES (elementUrl, elementType, elementId );
								IF NOT(dupkey) THEN
									UPDATE groups set groupUrl = elementUrl where groupId = elementId;
								END IF;
							END IF;
						UNTIL NOT(dupkey) END REPEAT;
					END IF;
				UNTIL done END REPEAT;
				CLOSE groupList;
			END')->execute();

	}
}


