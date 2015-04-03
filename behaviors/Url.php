<?php
/**
 * Url.php
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

use sweelix\yii1\ext\entities\Node;
use sweelix\yii1\ext\entities\Content;
use sweelix\yii1\ext\entities\Group;
use sweelix\yii1\ext\entities\Tag;
use sweelix\yii1\ext\entities\Url as EntityUrl;

/**
 * This class handle prop and extended properties
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2014 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   3.1.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweelix.yii1.ext.behaviors
 * @since     2.0.0
 */
class Url extends \CActiveRecordBehavior
{

    /**
     * Attach events to model
     * @see CActiveRecordBehavior::events()
     */
    public function events()
    {
        return array(
            'onBeforeValidate' => 'transliterateUrl',
            'onAfterDelete' => 'deleteUrl',
        );
    }

    /**
     * Delete urls when element is removed
     *
     * @return void
     * @since  3.0.0
     */
    public function deleteUrl($event)
    {
        $urlElementType = null;
        $urlElementId = null;
        if ($this->getOwner() instanceof Content) {
            $urlElementType = 'content';
            $urlElementId = $this->getOwner()->contentId;
        } elseif ($this->getOwner() instanceof Node) {
            $urlElementType = 'node';
            $urlElementId = $this->getOwner()->nodeId;
        } elseif ($this->getOwner() instanceof Tag) {
            $urlElementType = 'tag';
            $urlElementId = $this->getOwner()->tagId;
        } elseif ($this->getOwner() instanceof Group) {
            $urlElementType = 'group';
            $urlElementId = $this->getOwner()->groupId;
        } else {
            throw new \CException('Owner is invalid');
        }

        EntityUrl::model()->deleteAllByAttributes(array(
            'urlElementType' => $urlElementType,
            'urlElementId' => $urlElementId
        ));
    }

    /**
     * Clean up and transliterate url to be sure they are valid
     *
     * @return void
     * @since  2.0.0
     */
    public function transliterateUrl($event)
    {
        $titleProperty = null;
        $urlProperty = null;
        if ($this->getOwner() instanceof Content) {
            $titleProperty = 'contentTitle';
            $urlProperty = 'contentUrl';
        } elseif ($this->getOwner() instanceof Node) {
            $titleProperty = 'nodeTitle';
            $urlProperty = 'nodeUrl';
        } elseif ($this->getOwner() instanceof Tag) {
            $titleProperty = 'tagTitle';
            $urlProperty = 'tagUrl';
        } elseif ($this->getOwner() instanceof Group) {
            $titleProperty = 'groupTitle';
            $urlProperty = 'groupUrl';
        } else {
            throw new \CException('Owner is invalid');
        }

        if (empty($this->getOwner()->{$urlProperty}) === true) {
            $this->getOwner()->{$urlProperty} = strtr($this->getOwner()->{$titleProperty}, self::$_transliteration);
        }
        $searchRegex = array(\Yii::app()->getComponent('sweelix')->getUrlPattern(), '/[-]{2,}/', '/[-][\.]/');
        $replaceRegex = array('-', '-', '.');
        $this->getOwner()->{$urlProperty} = trim(preg_replace($searchRegex, $replaceRegex,
            strtolower($this->getOwner()->{$urlProperty})), ' -');
        $dirname = pathinfo($this->getOwner()->{$urlProperty}, PATHINFO_DIRNAME);
        $this->getOwner()->{$urlProperty} = (($dirname == '.') ? '' : $dirname . '/') . pathinfo($this->getOwner()->{$urlProperty},
                PATHINFO_FILENAME);
        $this->getOwner()->{$urlProperty} = trim($this->getOwner()->{$urlProperty}, '/');
    }

    /**
     * @var array transliteration strings
     */
    private static $_transliteration = array(
        "À" => "A",
        "Á" => "A",
        "Â" => "A",
        "Ã" => "A",
        "Ä" => "Ae",
        "Å" => "A",
        "Æ" => "A",
        "Ā" => "A",
        "Ą" => "A",
        "Ă" => "A",
        "Ç" => "C",
        "Ć" => "C",
        "Č" => "C",
        "Ĉ" => "C",
        "Ċ" => "C",
        "Ď" => "D",
        "Đ" => "D",
        "È" => "E",
        "É" => "E",
        "Ê" => "E",
        "Ë" => "E",
        "Ē" => "E",
        "Ę" => "E",
        "Ě" => "E",
        "Ĕ" => "E",
        "Ė" => "E",
        "Ĝ" => "G",
        "Ğ" => "G",
        "Ġ" => "G",
        "Ģ" => "G",
        "Ĥ" => "H",
        "Ħ" => "H",
        "Ì" => "I",
        "Í" => "I",
        "Î" => "I",
        "Ï" => "I",
        "Ī" => "I",
        "Ĩ" => "I",
        "Ĭ" => "I",
        "Į" => "I",
        "İ" => "I",
        "Ĳ" => "IJ",
        "Ĵ" => "J",
        "Ķ" => "K",
        "Ľ" => "K",
        "Ĺ" => "K",
        "Ļ" => "K",
        "Ŀ" => "K",
        "Ł" => "L",
        "Ñ" => "N",
        "Ń" => "N",
        "Ň" => "N",
        "Ņ" => "N",
        "Ŋ" => "N",
        "Ò" => "O",
        "Ó" => "O",
        "Ô" => "O",
        "Õ" => "O",
        "Ö" => "Oe",
        "Ø" => "O",
        "Ō" => "O",
        "Ő" => "O",
        "Ŏ" => "O",
        "Œ" => "OE",
        "Ŕ" => "R",
        "Ř" => "R",
        "Ŗ" => "R",
        "Ś" => "S",
        "Ş" => "S",
        "Ŝ" => "S",
        "Ș" => "S",
        "Š" => "S",
        "Ť" => "T",
        "Ţ" => "T",
        "Ŧ" => "T",
        "Ț" => "T",
        "Ù" => "U",
        "Ú" => "U",
        "Û" => "U",
        "Ü" => "Ue",
        "Ū" => "U",
        "Ů" => "U",
        "Ű" => "U",
        "Ŭ" => "U",
        "Ũ" => "U",
        "Ų" => "U",
        "Ŵ" => "W",
        "Ŷ" => "Y",
        "Ÿ" => "Y",
        "Ý" => "Y",
        "Ź" => "Z",
        "Ż" => "Z",
        "Ž" => "Z",
        "à" => "a",
        "á" => "a",
        "â" => "a",
        "ã" => "a",
        "ä" => "ae",
        "ā" => "a",
        "ą" => "a",
        "ă" => "a",
        "å" => "a",
        "æ" => "ae",
        "ç" => "c",
        "ć" => "c",
        "č" => "c",
        "ĉ" => "c",
        "ċ" => "c",
        "ď" => "d",
        "đ" => "d",
        "è" => "e",
        "é" => "e",
        "ê" => "e",
        "ë" => "e",
        "ē" => "e",
        "ę" => "e",
        "ě" => "e",
        "ĕ" => "e",
        "ė" => "e",
        "ƒ" => "f",
        "ĝ" => "g",
        "ğ" => "g",
        "ġ" => "g",
        "ģ" => "g",
        "ĥ" => "h",
        "ħ" => "h",
        "ì" => "i",
        "í" => "i",
        "î" => "i",
        "ï" => "i",
        "ī" => "i",
        "ĩ" => "i",
        "ĭ" => "i",
        "į" => "i",
        "ı" => "i",
        "ĳ" => "ij",
        "ĵ" => "j",
        "ķ" => "k",
        "ĸ" => "k",
        "ł" => "l",
        "ľ" => "l",
        "ĺ" => "l",
        "ļ" => "l",
        "ŀ" => "l",
        "ñ" => "n",
        "ń" => "n",
        "ň" => "n",
        "ņ" => "n",
        "ŉ" => "n",
        "ŋ" => "n",
        "ò" => "o",
        "ó" => "o",
        "ô" => "o",
        "õ" => "o",
        "ö" => "oe",
        "ø" => "o",
        "ō" => "o",
        "ő" => "o",
        "ŏ" => "o",
        "œ" => "oe",
        "ŕ" => "r",
        "ř" => "r",
        "ŗ" => "r",
        "ś" => "s",
        "š" => "s",
        "ş" => "s",
        "ť" => "t",
        "ţ" => "t",
        "ù" => "u",
        "ú" => "u",
        "û" => "u",
        "ü" => "ue",
        "ū" => "u",
        "ů" => "u",
        "ű" => "u",
        "ŭ" => "u",
        "ũ" => "u",
        "ų" => "u",
        "ŵ" => "w",
        "ÿ" => "y",
        "ý" => "y",
        "ŷ" => "y",
        "ż" => "z",
        "ź" => "z",
        "ž" => "z",
        "ß" => "ss",
        "ſ" => "ss",
        "Α" => "A",
        "Ά" => "A",
        "Ἀ" => "A",
        "Ἁ" => "A",
        "Ἂ" => "A",
        "Ἃ" => "A",
        "Ἄ" => "A",
        "Ἅ" => "A",
        "Ἆ" => "A",
        "Ἇ" => "A",
        "ᾈ" => "A",
        "ᾉ" => "A",
        "ᾊ" => "A",
        "ᾋ" => "A",
        "ᾌ" => "A",
        "ᾍ" => "A",
        "ᾎ" => "A",
        "ᾏ" => "A",
        "Ᾰ" => "A",
        "Ᾱ" => "A",
        "Ὰ" => "A",
        "Ά" => "A",
        "ᾼ" => "A",
        "Β" => "B",
        "Γ" => "G",
        "Δ" => "D",
        "Ε" => "E",
        "Έ" => "E",
        "Ἐ" => "E",
        "Ἑ" => "E",
        "Ἒ" => "E",
        "Ἓ" => "E",
        "Ἔ" => "E",
        "Ἕ" => "E",
        "Έ" => "E",
        "Ὲ" => "E",
        "Ζ" => "Z",
        "Η" => "I",
        "Ή" => "I",
        "Ἠ" => "I",
        "Ἡ" => "I",
        "Ἢ" => "I",
        "Ἣ" => "I",
        "Ἤ" => "I",
        "Ἥ" => "I",
        "Ἦ" => "I",
        "Ἧ" => "I",
        "ᾘ" => "I",
        "ᾙ" => "I",
        "ᾚ" => "I",
        "ᾛ" => "I",
        "ᾜ" => "I",
        "ᾝ" => "I",
        "ᾞ" => "I",
        "ᾟ" => "I",
        "Ὴ" => "I",
        "Ή" => "I",
        "ῌ" => "I",
        "Θ" => "TH",
        "Ι" => "I",
        "Ί" => "I",
        "Ϊ" => "I",
        "Ἰ" => "I",
        "Ἱ" => "I",
        "Ἲ" => "I",
        "Ἳ" => "I",
        "Ἴ" => "I",
        "Ἵ" => "I",
        "Ἶ" => "I",
        "Ἷ" => "I",
        "Ῐ" => "I",
        "Ῑ" => "I",
        "Ὶ" => "I",
        "Ί" => "I",
        "Κ" => "K",
        "Λ" => "L",
        "Μ" => "M",
        "Ν" => "N",
        "Ξ" => "KS",
        "Ο" => "O",
        "Ό" => "O",
        "Ὀ" => "O",
        "Ὁ" => "O",
        "Ὂ" => "O",
        "Ὃ" => "O",
        "Ὄ" => "O",
        "Ὅ" => "O",
        "Ὸ" => "O",
        "Ό" => "O",
        "Π" => "P",
        "Ρ" => "R",
        "Ῥ" => "R",
        "Σ" => "S",
        "Τ" => "T",
        "Υ" => "Y",
        "Ύ" => "Y",
        "Ϋ" => "Y",
        "Ὑ" => "Y",
        "Ὓ" => "Y",
        "Ὕ" => "Y",
        "Ὗ" => "Y",
        "Ῠ" => "Y",
        "Ῡ" => "Y",
        "Ὺ" => "Y",
        "Ύ" => "Y",
        "Φ" => "F",
        "Χ" => "X",
        "Ψ" => "PS",
        "Ω" => "O",
        "Ώ" => "O",
        "Ὠ" => "O",
        "Ὡ" => "O",
        "Ὢ" => "O",
        "Ὣ" => "O",
        "Ὤ" => "O",
        "Ὥ" => "O",
        "Ὦ" => "O",
        "Ὧ" => "O",
        "ᾨ" => "O",
        "ᾩ" => "O",
        "ᾪ" => "O",
        "ᾫ" => "O",
        "ᾬ" => "O",
        "ᾭ" => "O",
        "ᾮ" => "O",
        "ᾯ" => "O",
        "Ὼ" => "O",
        "Ώ" => "O",
        "ῼ" => "O",
        "α" => "a",
        "ά" => "a",
        "ἀ" => "a",
        "ἁ" => "a",
        "ἂ" => "a",
        "ἃ" => "a",
        "ἄ" => "a",
        "ἅ" => "a",
        "ἆ" => "a",
        "ἇ" => "a",
        "ᾀ" => "a",
        "ᾁ" => "a",
        "ᾂ" => "a",
        "ᾃ" => "a",
        "ᾄ" => "a",
        "ᾅ" => "a",
        "ᾆ" => "a",
        "ᾇ" => "a",
        "ὰ" => "a",
        "ά" => "a",
        "ᾰ" => "a",
        "ᾱ" => "a",
        "ᾲ" => "a",
        "ᾳ" => "a",
        "ᾴ" => "a",
        "ᾶ" => "a",
        "ᾷ" => "a",
        "β" => "b",
        "γ" => "g",
        "δ" => "d",
        "ε" => "e",
        "έ" => "e",
        "ἐ" => "e",
        "ἑ" => "e",
        "ἒ" => "e",
        "ἓ" => "e",
        "ἔ" => "e",
        "ἕ" => "e",
        "ὲ" => "e",
        "έ" => "e",
        "ζ" => "z",
        "η" => "i",
        "ή" => "i",
        "ἠ" => "i",
        "ἡ" => "i",
        "ἢ" => "i",
        "ἣ" => "i",
        "ἤ" => "i",
        "ἥ" => "i",
        "ἦ" => "i",
        "ἧ" => "i",
        "ᾐ" => "i",
        "ᾑ" => "i",
        "ᾒ" => "i",
        "ᾓ" => "i",
        "ᾔ" => "i",
        "ᾕ" => "i",
        "ᾖ" => "i",
        "ᾗ" => "i",
        "ὴ" => "i",
        "ή" => "i",
        "ῂ" => "i",
        "ῃ" => "i",
        "ῄ" => "i",
        "ῆ" => "i",
        "ῇ" => "i",
        "θ" => "th",
        "ι" => "i",
        "ί" => "i",
        "ϊ" => "i",
        "ΐ" => "i",
        "ἰ" => "i",
        "ἱ" => "i",
        "ἲ" => "i",
        "ἳ" => "i",
        "ἴ" => "i",
        "ἵ" => "i",
        "ἶ" => "i",
        "ἷ" => "i",
        "ὶ" => "i",
        "ί" => "i",
        "ῐ" => "i",
        "ῑ" => "i",
        "ῒ" => "i",
        "ΐ" => "i",
        "ῖ" => "i",
        "ῗ" => "i",
        "κ" => "k",
        "λ" => "l",
        "μ" => "m",
        "ν" => "n",
        "ξ" => "ks",
        "ο" => "o",
        "ό" => "o",
        "ὀ" => "o",
        "ὁ" => "o",
        "ὂ" => "o",
        "ὃ" => "o",
        "ὄ" => "o",
        "ὅ" => "o",
        "ὸ" => "o",
        "ό" => "o",
        "π" => "p",
        "ρ" => "r",
        "ῤ" => "r",
        "ῥ" => "r",
        "σ" => "s",
        "ς" => "s",
        "τ" => "t",
        "υ" => "y",
        "ύ" => "y",
        "ϋ" => "y",
        "ΰ" => "y",
        "ὐ" => "y",
        "ὑ" => "y",
        "ὒ" => "y",
        "ὓ" => "y",
        "ὔ" => "y",
        "ὕ" => "y",
        "ὖ" => "y",
        "ὗ" => "y",
        "ὺ" => "y",
        "ύ" => "y",
        "ῠ" => "y",
        "ῡ" => "y",
        "ῢ" => "y",
        "ΰ" => "y",
        "ῦ" => "y",
        "ῧ" => "y",
        "φ" => "f",
        "χ" => "x",
        "ψ" => "ps",
        "ω" => "o",
        "ώ" => "o",
        "ὠ" => "o",
        "ὡ" => "o",
        "ὢ" => "o",
        "ὣ" => "o",
        "ὤ" => "o",
        "ὥ" => "o",
        "ὦ" => "o",
        "ὧ" => "o",
        "ᾠ" => "o",
        "ᾡ" => "o",
        "ᾢ" => "o",
        "ᾣ" => "o",
        "ᾤ" => "o",
        "ᾥ" => "o",
        "ᾦ" => "o",
        "ᾧ" => "o",
        "ὼ" => "o",
        "ώ" => "o",
        "ῲ" => "o",
        "ῳ" => "o",
        "ῴ" => "o",
        "ῶ" => "o",
        "ῷ" => "o",
        "¨" => "",
        "΅" => "",
        "᾿" => "",
        "῾" => "",
        "῍" => "",
        "῝" => "",
        "῎" => "",
        "῞" => "",
        "῏" => "",
        "῟" => "",
        "῀" => "",
        "῁" => "",
        "΄" => "",
        "΅" => "",
        "`" => "",
        "῭" => "",
        "ͺ" => "",
        "᾽" => "",
        "А" => "A",
        "Б" => "B",
        "В" => "V",
        "Г" => "G",
        "Д" => "D",
        "Е" => "E",
        "Ё" => "E",
        "Ж" => "ZH",
        "З" => "Z",
        "И" => "I",
        "Й" => "I",
        "К" => "K",
        "Л" => "L",
        "М" => "M",
        "Н" => "N",
        "О" => "O",
        "П" => "P",
        "Р" => "R",
        "С" => "S",
        "Т" => "T",
        "У" => "U",
        "Ф" => "F",
        "Х" => "KH",
        "Ц" => "TS",
        "Ч" => "CH",
        "Ш" => "SH",
        "Щ" => "SHCH",
        "Ы" => "Y",
        "Э" => "E",
        "Ю" => "YU",
        "Я" => "YA",
        "а" => "A",
        "б" => "B",
        "в" => "V",
        "г" => "G",
        "д" => "D",
        "е" => "E",
        "ё" => "E",
        "ж" => "ZH",
        "з" => "Z",
        "и" => "I",
        "й" => "I",
        "к" => "K",
        "л" => "L",
        "м" => "M",
        "н" => "N",
        "о" => "O",
        "п" => "P",
        "р" => "R",
        "с" => "S",
        "т" => "T",
        "у" => "U",
        "ф" => "F",
        "х" => "KH",
        "ц" => "TS",
        "ч" => "CH",
        "ш" => "SH",
        "щ" => "SHCH",
        "ы" => "Y",
        "э" => "E",
        "ю" => "YU",
        "я" => "YA",
        "Ъ" => "",
        "ъ" => "",
        "Ь" => "",
        "ь" => "",
        "ð" => "d",
        "Ð" => "D",
        "þ" => "th",
        "Þ" => "TH"
    );

}