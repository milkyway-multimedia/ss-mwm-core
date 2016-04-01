<?php namespace Milkyway\SS\Core\Extensions;

/**
 * Milkyway Multimedia
 * DBField.php
 *
 * @package milkyway-multimedia/ss-mwm-core
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Extension;
use Object;
use Convert;

class DBField extends Extension {

    /**
     * Add a default Nice method
     * @return mixed
     */
    public function Nice() {
        return $this->owner->forTemplate();
    }

    /**
     * Allow any db field to be parsed (specifically for templates)
     *
     * @param string $parser
     * @return mixed
     */
    public function Parse($parser = 'TextParser') {
        if($parser == 'TextParser' || is_subclass_of($parser, 'TextParser')) {
            $obj = Object::create($parser, $this->owner->value);
            return $obj->parse();
        } else {
            return $this->owner->Nice();
        }
    }

    /**
     * Convert value to a full country name if found
     * @return false|string
     */
    public function FullCountry() {
        return singleton('mwm')->get_full_country_name($this->owner->value);
    }

    /**
     * Convert value to an appropriate HTML ID
     * @param bool $lowercase
     * @return string
     */
    public function HTMLID($lowercase = false) {
        $str = trim(str_replace(' ', '-', ucwords(str_replace(['_', '-', '/'], ' ', $this->owner->value))), '-');
        return $lowercase ? strtolower($str) : $str;
    }

    /**
     * Convert value to an appropriate class name
     * @return string
     */
    public function CLASSNAME() {
        return Convert::raw2htmlname(singleton('s')->create($this->owner->value)->camelize());
    }

    /**
     * Convert new lines to brs on output
     * @return string
     */
    public function nl2br() {
        return nl2br($this->owner->XML());
    }

    /**
     * Convert new lines to a list
     *
     * @param string $class
     * @param string $liClass
     * @param string $tag
     *
     * @return string
     */
    public function nl2list($class = '', $liClass = '', $tag = 'ul') {
        $val = trim($this->owner->XML(), "\n");

        $val = str_replace("\n", sprintf('</li><li%s>', $liClass ? 'class="' . $liClass . '"' : ''), $val);

        $val = $liClass ? '<li class="' . $liClass . '">' . $val . '</li>' : '<li>' . $val . '</li>';

        return $class ? '<' . $tag . ' class="' . $class . '">' . $val . '</' . $tag . '>' : '<' . $tag . '>' . $val . '</' . $tag . '>';
    }

    /**
     * Convert new lines to an ordered list
     * @param string $class
     * @param string $liClass
     *
     * @return mixed
     */
    public function nl2numbered($class = '', $liClass = '') {
        return $this->owner->nl2list($class, $liClass, 'ol');
    }

    /**
     * Convert value to a boolean suitable within the CMS
     * @return string
     */
    public function CMSBoolean() {
        return $this->owner->value ? '<span class="ui-button-icon-primary ui-icon btn-icon-accept"></span>' : '<span class="ui-button-icon-primary ui-icon btn-icon-decline"></span>';
    }

    /**
     * A simple debug view for the value of this field
     * @return string
     */
    public function debugView() {
        return '<pre>' . var_export($this->owner->value) . '</pre>';
    }

    /**
     * Format a field or return as unknown
     *
     * @param string $format
     * @return string
     */
    public function FormatOrUnknown($format = 'Nice') {
        return $this->owner->value && $this->owner->value != '0000-00-00 00:00:00' ? $this->owner->$format() : _t('_UNKNOWN_', '(unknown)');
    }

    /**
     * Format a field or return as cms false
     *
     * @param string $format
     * @return string
     */
    public function FormatOrNot($format = 'Nice') {
        return $this->owner->value && $this->owner->value != '0000-00-00 00:00:00' ? $this->owner->$format() : '<span class="ui-button-icon-primary ui-icon btn-icon-decline"></span>';
    }

    /**
     * Format a clean decimal (no additional zeros)
     * @return string
     */
    public function CleanDecimal() {
        return (float)$this->owner->value;
    }

    public function Trim($trim = null, $direction = '') {
        if($direction == 'r')
            return rtrim($this->owner->value, $trim);
        elseif($direction == 'l')
            return ltrim($this->owner->value, $trim);
        else
            return trim($this->owner->value, $trim);
    }

    public function Contains($contains) {
        return strpos($this->owner->value, $contains) !== false;
    }

    public function Replace($textToReplace, $replaceWith) {
        return str_replace($textToReplace, $replaceWith, $this->owner->value);
    }

    public function In() {
        return in_array($this->owner->value, (array)func_get_args());
    }
} 