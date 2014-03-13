<?php
/**
 * Composite.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 */

namespace sweelix\yii1\ext\behaviors;
use sweelix\yii1\ext\db\CriteriaBuilder;

/**
 * This class handle composed information
 * It is used to merge contents in a single element,
 * to display it as a composed metacontent
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.0.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     1.6.0
 */
class Composite extends Template {
	/* @var string default subviews directory */
	public $templateDirectory='/composite/';

	/**
	 * Try to autodetect current composite type and render it
	 *
	 * @param boolean $return        render in place or return it
	 * @param boolean $processOutput should we process output
	 *
	 * @return mixed
	 * @since  2.0.0
	 */
	public function renderComposite($return=false, $processOutput=false) {
		$output = '';
		if($this->getOwner()->getNodeId() !== null) {
			$output = $this->renderCompositeNode(true, $processOutput);
		} elseif($this->getOwner()->getTagId() !== null) {
			$output = $this->renderCompositeTag(true, $processOutput);
		} elseif($this->getOwner()->getGroupId() !== null) {
			$output = $this->renderCompositeGroup(true, $processOutput);
		}
		if($return)
			return $output;
		else
			echo $output;
	}
	/**
	 * Render composite node. Fetch all publishable
	 * articles from current node and render it
	 *
	 * @param boolean $return        render in place or return it
	 * @param boolean $processOutput should we process output
	 *
	 * @return mixed
	 * @since  1.6.0
	 */
	public function renderCompositeNode($return=false, $processOutput=false) {
		$criteriaBuilder = new CriteriaBuilder('content');
		$criteriaBuilder->filterBy('nodeId', $this->getOwner()->getNodeId());
		$criteriaBuilder->published();
		$criteriaBuilder->orderBy('contentOrder');
		$composedArticles = $criteriaBuilder->findAll();

		$output = $this->renderRawComposite($composedArticles);
		if($processOutput)
			$output=$this->getOwner()->processOutput($output);
		if($return)
			return $output;
		else
			echo $output;
	}

	/**
	 * Render composite tag. Fetch all publishable
	 * articles from current tag and render it
	 *
	 * @param boolean $return        render in place or return it
	 * @param boolean $processOutput should we process output
	 *
	 * @return mixed
	 * @since  2.0.0
	 */
	public function renderCompositeTag($return=false, $processOutput=false) {
		$criteriaBuilder = new CriteriaBuilder('content');
		$criteriaBuilder->filterBy('tagId', $this->getOwner()->getTagId());
		$criteriaBuilder->published();
		$criteriaBuilder->orderBy('contentStartDate', 'desc');
		$criteriaBuilder->orderBy('contentOrder');
		$composedArticles = $criteriaBuilder->findAll();

		$output = $this->renderRawComposite($composedArticles);
		if($processOutput)
			$output=$this->getOwner()->processOutput($output);
		if($return)
			return $output;
		else
			echo $output;
	}

	/**
	 * Render composite group. Fetch all publishable
	 * articles from current group and render it
	 *
	 * @param boolean $return        render in place or return it
	 * @param boolean $processOutput should we process output
	 *
	 * @return mixed
	 * @since  2.0.0
	 */
	public function renderCompositeGroup($return=false, $processOutput=false) {
		$criteriaBuilder = new CriteriaBuilder('content');
		$criteriaBuilder->filterBy('groupId', $this->getOwner()->grtGroupId());
		$criteriaBuilder->published();
		$criteriaBuilder->orderBy('contentStartDate', 'desc');
		$criteriaBuilder->orderBy('contentOrder');
		$composedArticles = $criteriaBuilder->findAll();

		$output = $this->renderRawComposite($composedArticles);
		if($processOutput)
			$output=$this->getOwner()->processOutput($output);
		if($return)
			return $output;
		else
			echo $output;
	}


	/**
	 * Render composited element
	 *
	 * @param array $composedArticles article to composite
	 *
	 * @return string
	 * @since  2.0.0
	 */
	protected function renderRawComposite($composedArticles) {
		$output = '';
		foreach($composedArticles as $art) {
			$composite = $this->getCompositeTemplate($art->templateId);
			if($composite !== false) {
				$view = $this->templateDirectory.$composite;
				$controller = \Yii::app()->getController();
				if($controller->getViewFile($view) !== false) {
					$output .= $controller->renderPartial($view, array('content' => $art), true);
				}
			}
		}
		return $output;
	}
}
