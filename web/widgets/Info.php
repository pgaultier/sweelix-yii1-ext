<?php
/**
 * File Info.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  widgets
 * @package   sweelix.yii1.ext.web.widgets
 */

namespace sweelix\yii1\ext\web\widgets;
use sweelix\yii1\ext\web\Controller;
use sweelix\yii1\ext\components\Config as Ext;

/**
 * Class Info display info needed by the developper
 * current controller, action, element, ...
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.1
 * @link      http://www.sweelix.net
 * @category  widgets
 * @package   sweelix.yii1.ext.web.widgets
 * @since     2.0.0
 */
class Info extends \CWidget {
	private $_cmsInfo;
	public $htmlOptions=array(
		'style' => 'z-index:10000;font-family:Arial,Helvetica,Verdana;font-size:11px;position:absolute;list-style-type:none;top:0;right:0;border:1px solid #999999;background-color: #F0F0F0;border-radius: 5px 5px 5px 5px;margin:2px;padding: 2px 10px;'
	);
	private $_subOptions=array();
	/**
	 * Override widget init
	 * @see CWidget::init()
	 *
	 * @return void
	 * @since  1.2.0
	 */
	public function init() {
		if(defined('YII_DEBUG') && (YII_DEBUG === true)) {
			\Yii::trace(__METHOD__.'()', 'sweelix.yii1.ext.web.widgets');
			parent::init();
			ob_start();
			if($this->getController() instanceof Controller) {
				$this->_cmsInfo = array(
					'sweelix' => Ext::getVersion(),
					'nodeId' => $this->getController()->nodeId,
					'contentId' => $this->getController()->contentId,
					'groupId' => $this->getController()->groupId,
					'tagId' => $this->getController()->tagId,
					'controllerAction' => $this->getController()->id.' / '.$this->getController()->getAction()->id,
				);
			} else {
				$this->_cmsInfo = array(
					'sweelix' => Ext::getVersion(),
					'nodeId' => null,
					'contentId' => null,
					'groupId' => null,
					'tagId' => null,
					'controllerAction' => $this->getController()->id.' / '.$this->getController()->getAction()->id,
				);
			}
			if(isset($this->htmlOptions['subOptions']) == true) {
				$this->_subOptions = $this->htmlOptions['subOptions'];
				unset($this->htmlOptions['subOptions']);
			}
		}
	}

	/**
	 * Override widget run
	 * @see CWidget::run()
	 *
	 * @return void
	 * @since  1.2.0
	 */
	public function run() {
		if(defined('YII_DEBUG') && (YII_DEBUG === true)) {
			\Yii::trace(__METHOD__.'()', 'sweelix.yii1.ext.web.widgets');
			parent::run();
			$content = ob_get_contents();
			ob_end_clean();
			$infoBlock = \CHtml::tag('ul', $this->htmlOptions, null, false)."\n";
			foreach($this->_cmsInfo as $info => $value) {
				if($value !== null) {
					switch($info) {
						case 'sweelix' :
							$infoBlock .= \CHtml::tag('li', $this->_subOptions,
									Sweext::getLink(array('style' => 'color:#333', 'target'=>'_blank')).' '.Sweext::getVersion()
								);
							break;
						case 'nodeId' :
								$infoBlock .= \CHtml::tag('li', $this->_subOptions,
									\CHtml::link(\Yii::t('sweelix', 'Node {id}', array('{id}' => $value)), array('sweeft/structure/node/detail', $info=>$value), array('title' => $value, 'style' => 'color:#333'))
								);
							break;
						case 'contentId' :
								$infoBlock .= \CHtml::tag('li', $this->_subOptions,
									\CHtml::link(\Yii::t('sweelix', 'Content {id}', array('{id}' => $value)), array('sweeft/structure/content/detail', $info=>$value), array('title' => $value, 'style' => 'color:#333'))
								);
							break;
						case 'groupId' :
								$infoBlock .= \CHtml::tag('li', $this->_subOptions,
									\CHtml::link(\Yii::t('sweelix', 'Group {id}', array('{id}' => $value)), array('sweeft/cloud/group/detail', $info=>$value), array('title' => $value, 'style' => 'color:#333'))
								);
							break;
						case 'tagId' :
								$infoBlock .= \CHtml::tag('li', $this->_subOptions,
									\CHtml::link(\Yii::t('sweelix', 'Tag {id}', array('{id}' => $value)), array('sweeft/cloud/tag/detail', $info=>$value), array('title' => $value, 'style' => 'color:#333'))
								);
							break;
						default:
								$infoBlock .= \CHtml::tag('li', $this->_subOptions, $value);
							break;
					}
				}
			}
			$infoBlock .= \CHtml::closeTag('ul')."\n";
			echo $infoBlock;
		}
	}
}
