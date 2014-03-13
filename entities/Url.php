<?php
/**
 * File Url.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 */

namespace sweelix\yii1\ext\entities;
use sweelix\yii1\ext\db\ar\Url as ActiveRecordUrl;

/**
 * Class Url
 *
 * This is the model class for table "urls".
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  entities
 * @package   sweelix.yii1.ext.entities
 * @since     1.0.0
 */
class Url extends ActiveRecordUrl {
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className entity classname automatically set
	 *
	 * @return Url the static model class
	 * @since  1.0.0
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * The followings are the available model relations:
	 *
	 * @return array relational rules.
	 * @since  1.0.0
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * Flush all urls and rewrite everything according to what's in
	 * database
	 *
	 * @return void
	 * @since  1.6.1
	 */
	public function flush() {
		$this->getDbConnection()->createCommand('CALL spFlushUrl()')->execute();
	}

	/**
	 * Create or update url for specific element
	 *
	 * @param string  $url  url to store
	 * @param string  $type type of element content|node|tag|group
	 * @param integer $id   id of the element
	 *
	 * @since  1.0.0
	 */
	public static function store($url, $type, $id) {
		$criteria = new \CDbCriteria();
		$criteria->condition = '(urlElementType <> :elementType or urlElementId <> :elementId) and urlValue = :urlValue';
		$criteria->params = array(
			':elementType' => $type,
			':elementId' => $id,
			':urlValue' => $url,
		);
		$testUrl = Url::model()->find($criteria);
		if($testUrl === null) {
			// ok we can do it
			$criteria = new \CDbCriteria();
			$criteria->condition = 'urlElementType = :elementType and urlElementId = :elementId';
			$criteria->params = array(
				':elementType' => $type,
				':elementId' => $id,
			);
			$originalUrl = Url::model()->find($criteria);
			if($originalUrl === null) {
				$originalUrl = new Url();
				$originalUrl->urlElementId = $id;
				$originalUrl->urlElementType = $type;
			}
			$originalUrl->urlValue = $url;
			$originalUrl->save();
		} else {
			throw new \CException(\Yii::t('sweelix', 'Uncaught url exception'));
		}
	}
}