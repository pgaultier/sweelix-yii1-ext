<?php
/**
 * File Token.php
 *
 * PHP version 5.4+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 */

namespace sweelix\yii1\ext\behaviors;

use sweelix\yii1\ext\entities\Token as EntityToken;

/**
 * This class handle the indexer behavior
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     1.6.0
 */
class Token extends \CBehavior
{

    public $type;

    public static $defaultValues = array('weight' => 1, 'minLength' => 3, 'numerical' => false);

    /**
     *
     * Catching events with their assiocated functions
     *
     * @return array
     * @since  2.0.0
     */
    public function events()
    {
        return array(
            'onAfterSave' => 'createIndex',
        );
    }

    private $_id;
    private $_parsedTemplate;

    public $id;
    public $title;
    public $subtitle;

    /**
     * This function is used to get template information.
     *
     * @return array
     * @since  2.0.0
     */
    public function getParsedTemplate()
    {
        if ($this->_parsedTemplate === null) {
            $data = $this->getOwner()->getTemplateDefinition($this->getOwner()->templateId);
            $this->_parsedTemplate = array();
            foreach ($data as $key => $info) {
                if ($key !== 'separator') {
                    if (isset($info['index']) === false) {
                        $info['index'] = array();
                    }
                    if (($info['index'] !== false) && (is_array($info['index']) === false)) {
                        $info['index'] = array('weight' => $info['index']);
                    }
                    if (is_array($info['index']) === true) {
                        foreach (self::$defaultValues as $parameter => $parameterValue) {
                            $this->_parsedTemplate[$key][$parameter] = isset($info['index'][$parameter]) ?
                                $info['index'][$parameter] :
                                $parameterValue;
                        }
                    } else {
                        $this->_parsedTemplate[$key] = false;
                    }
                }
            }
        }
        return $this->_parsedTemplate;
    }

    /**
     *  This function handle the data
     *
     * @param  string $propertyData only the data string
     * @param  string $property the property
     * @param  array $info array of the template
     * @paran  array  $data  all the data
     *
     * @return void
     * @since  2.0.0
     */
    public function handleData($propertyData, $property, $info, $data)
    {

        $element = $this->type . "Id";
        EntityToken::model()->deleteAllByAttributes(array(
            'elementId' => $this->id,
            'elementProperty' => $property,
            'elementType' => $this->type,
        ));

        if ($data[$property] !== false) {
            if ($data[$property]['numerical'] === true) {
                $obj = new EntityToken();

                $obj->elementId = $this->id;
                $obj->elementType = $this->type;
                $obj->$element = $this->id;
                $obj->tokenKey = $propertyData;
                $obj->tokenNumeric = \CPropertyValue::ensureFloat($propertyData);
                $obj->elementTitle = $this->title;
                $obj->elementProperty = $property;
                $obj->tokenDateCreate = new \CDbExpression('now()');
                $obj->tokenWeight = $info['weight'];

                if ($obj->validate()) {
                    $obj->save();
                }


            } else {
                $propertyData = strip_tags($propertyData);
                $propertyData = strtr($propertyData, array(
                    'á' => 'a',
                    'à' => 'a',
                    'â' => 'a',
                    'ä' => 'a',
                    'é' => 'e',
                    'è' => 'e',
                    'ê' => 'e',
                    'ë' => 'e',
                    'í' => 'i',
                    'ì' => 'i',
                    'î' => 'i',
                    'ï' => 'i',
                    'ó' => 'o',
                    'ò' => 'o',
                    'ô' => 'o',
                    'ö' => 'o',
                    'ú' => 'u',
                    'ù' => 'u',
                    'û' => 'u',
                    'ü' => 'u',
                    'ç' => 'c',
                ));
                $propertyData = strtolower(preg_replace('/[^a-z0-9\-]+/i', ' ', $propertyData));
                // mysql handle transliteration directly
                $split = preg_split('/[\s]+/', $propertyData);
                $indexedWord = array_count_values($split);

                foreach ($indexedWord as $word => $value) {
                    if (strlen($word) >= $data[$property]['minLength']) {
                        $obj = new EntityToken();
                        $obj->elementId = $this->id;
                        $obj->elementType = $this->type;
                        $obj->$element = $this->id;
                        $obj->tokenKey = $word;
                        $obj->elementTitle = $this->title;
                        $obj->elementProperty = $property;
                        $obj->tokenDateCreate = new \CDbExpression('now()');
                        $obj->tokenWeight = $value * $info['weight'];
                        if ($obj->validate()) {
                            $obj->save();
                        }
                    }
                }
            }

        }
    }

    /**
     *
     * This function fill the database.
     *
     * @return void
     * @since  2.0.0
     */
    public function createIndex()
    {
        if ($this->type !== false) {
            $id = $this->type . 'Id';
            $title = $this->type . 'Title';

            $this->id = $this->getOwner()->$id;
            $this->title = $this->getOwner()->$title;
            if ($this->type == 'content') {
                $subtitle = $this->type . 'Subtitle';
                $this->subtitle = $this->getOwner()->$subtitle;
                $defaultValues = array(
                    'title' => self::$defaultValues,
                    'subtitle' => self::$defaultValues,
                );
            } else {
                $defaultValues = array('title' => self::$defaultValues);
            }

            $data = $this->getParsedTemplate();

            $data = array_merge($data, $defaultValues);
            foreach ($data as $prop => $info) {
                if ($prop !== 'title' && $prop !== 'subtitle') {
                    $propertyData = $this->getOwner()->prop($prop);
                } else {
                    if ($prop === 'title') {
                        $propertyData = $this->title;
                    } else {
                        $propertyData = $this->subtitle;
                    }
                }
                if (is_array($propertyData) === true) {
                    foreach ($propertyData as $propData) {
                        $this->handleData($propData, $prop, $info, $data);
                    }
                } else {
                    $this->handleData($propertyData, $prop, $info, $data);
                }
            }
        }
    }
}
