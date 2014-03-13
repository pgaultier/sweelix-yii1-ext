<?php
/**
 * Template.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 */

namespace sweelix\yii1\ext\behaviors;

/**
 * This class handle template related information
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     1.6.0
 */
class Template extends \CBehavior {

	const CACHE_KEY_TEMPLATE = 'sweelix.yii1.ext.behaviors.templates' ;
	const CACHE_KEY_SUB_PROPERTIES = 'sweelix.yii1.ext.behaviors.templates.subproperties';

	public $templatesDefinitionsAlias = 'application.templates';

	private static $_expire=60;
	/**
	 * Get cache duration
	 *
	 * @return integer
	 * @since  1.6.0
	 */
	public function getExpire() {
		return self::$_expire;
	}
	/**
	 * Define cache duration
	 *
	 * @param integer $duration number of seconds to cache the value
	 *
	 * @return void
	 * @since  1.6.0
	 */
	public function setExpire($duration) {
		self::$_expire = \CPropertyValue::ensureInteger($duration);
	}
	private static $_templateDefinitions;
	private static $_subProperties;

	/**
	 * Retrieve template information in database
	 *
	 * @param integer $templateId templateId to fetch
	 * @param string  $mode       information to retrieve : definition = template definition, composite = template used for composition
	 *
	 * @return string
	 * @since  1.6.0
	 */
	protected function getTemplateData($templateId, $mode='definition') {
		if( (self::$_templateDefinitions === null) ) {
			if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get(self::CACHE_KEY_TEMPLATE)) !== false)) {
				self::$_templateDefinitions = $cachedData;
			} else {
				$templates = \Yii::app()->getDb()->createCommand()->select('templateId, templateDefinition, templateComposite')
					->from('templates')
					->queryAll(false);
				foreach($templates as $template) {
					self::$_templateDefinitions[$template[0]] = array(
						'definition' => $template[1],
						'composite' => empty($template[2])?false:$template[2]
					);
				}
				if((\Yii::app()->cache !== null)) {
					\Yii::app()->cache->set(self::CACHE_KEY_TEMPLATE, self::$_templateDefinitions, self::$_expire );
				}
			}
		}
		return ($templateId === null)?null:self::$_templateDefinitions[$templateId][$mode];
	}

	/**
	 * Reset known template information
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function resetTemplateData() {
		self::$_templateDefinitions = null;
		if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get(self::CACHE_KEY_TEMPLATE)) !== false)) {
			\Yii::app()->cache->delete(self::CACHE_KEY_TEMPLATE);
		}
	}

	/**
	 * Retrieve template view name from database for composite
	 *
	 * @param integer $templateId templateId to fetch
	 *
	 * @return string
	 * @since  1.6.0
	 */
	public function getCompositeTemplate($templateId) {
		return $this->getTemplateData($templateId, 'composite');
	}

	/**
	 * Retrieve subproperties
	 *
	 * @param integer $templateId templateId to fetch
	 *
	 * @return string
	 * @since  1.6.0
	 */
	public function getSubProperties($templateId) {
		$cacheKey = self::CACHE_KEY_SUB_PROPERTIES.':'.$templateId;
		if((self::$_subProperties === null) || (key_exists($templateId, self::$_subProperties) === false)) {
			if((\Yii::app()->cache !== null) && (($cachedData = \Yii::app()->cache->get($cacheKey)) !== false)) {
				self::$_subProperties[$templateId] = $cachedData;
			} else {
				$path = \Yii::getPathOfAlias($this->templatesDefinitionsAlias).DIRECTORY_SEPARATOR.$this->getTemplateData($templateId, 'definition').'.php';
				if(file_exists($path) === true) {
					$tplData = require($path);
					self::$_subProperties[$templateId] = array_keys($tplData);
				} else {
					self::$_subProperties[$templateId] = array();
				}
				if((\Yii::app()->cache !== null)) {
					\Yii::app()->cache->set($cacheKey, self::$_subProperties[$templateId], self::$_expire );
				}
			}
		}
		return self::$_subProperties[$templateId];
	}
	/**
	 * Get template definition, usefull when we need
	 * information from the model
	 *
	 * @param integer $templateId templateId
	 *
	 * @return array
	 * @since  1.6.0
	 */
	public function getTemplateDefinition($templateId) {
		$tplData = array();
		$path = \Yii::getPathOfAlias($this->templatesDefinitionsAlias).DIRECTORY_SEPARATOR.$this->getTemplateData($templateId, 'definition').'.php';
		if(file_exists($path) === true) {
			$tplData = require($path);
		}
		return $tplData;
	}

	/**
	 * Get template definition, usefull when we need
	 * information from the model
	 *
	 * @param integer $templateId templateId
	 *
	 * @return array
	 * @since  1.6.0
	 * @todo   implement correct rendering template system
	 */
	public function getRenderingTemplate($templateId) {
		$tplData = null;
		$path = \Yii::getPathOfAlias($this->templatesDefinitionsAlias).DIRECTORY_SEPARATOR.$this->getTemplateData($templateId, 'definition').'.tpl.php';
		if(file_exists($path) === true) {
			$tplData = require($path);
		}
		return $tplData;
	}

	/**
	 * check if subproperty exists
	 *
	 * @param integer $templateId templateId to fetch
	 * @param string  $name       property name
	 *
	 * @return boolean
	 * @since  1.6.0
	 */
	public function getIsSubProperty($name) {
		$properties = $this->getSubProperties($this->getOwner()->templateId);
		if(is_array($properties) && in_array($name, $properties)) {
			return true;
		} else {
			return false;
		}
	}
}