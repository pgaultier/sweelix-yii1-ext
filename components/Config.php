<?php
/**
 * File Config.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweelix.yii1.ext.components
 */

namespace sweelix\yii1\ext\components;

/**
 * Class Config allow basic configuration of the cms extension
 *
 * id of the module should be set to "sweelix". If not, we will attempt to find
 * correct module.
 *
 * <code>
 * 	'components' => array(
 * 		...
 * 		'sweelix' => array(
 * 			'class'=>'sweelix\yii1\ext\components\Config',
 * 		),
 * 		...
 * </code>
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweelix.yii1.ext.components
 * @since     1.2.0
 *
 * @property string urlPattern
 */
class Config extends \CApplicationComponent {
	/**
	 * Return current version
	 *
	 * @return string
	 * @since  1.2.0
	 */
	public static function getVersion() {
		return '2.2.0';
	}
	/**
	 * Return product info
	 *
	 * @return string
	 * @since  1.2.0
	 */
	public static function getLink($htmlOptions=array()) {
		if(isset($htmlOptions['title']) === false) {
			$htmlOptions['title'] = 'Sweelix';
		}
		return \CHtml::link('Sweelix', 'http://www.sweelix.net', $htmlOptions);
	}
	private $_urlPattern;
	/**
	 * Define filtering pattern
	 *
	 * @param string $pattern regular expression with / (ex: /[^\.a-z0-9_-]+/i)
	 *
	 * @return void
	 * @since  1.8.0
	 */
	public function setUrlPattern($pattern) {
		$this->_urlPattern = $pattern;
	}

	/**
	 * Get defined filtering pattern for cms url
	 *
	 * @return string
	 * @since  1.8.0
	 */
	public function getUrlPattern() {
		if($this->_urlPattern === null) {
			$this->_urlPattern = '/[^\.a-z0-9_-]+/i';
		}
		return $this->_urlPattern;
	}
}
