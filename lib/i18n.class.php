<?php

// This file is part of TmaS
//
// TmaS is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TmaS is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TmaS. If not, see <http://www.gnu.org/licenses/>.

/**
 * String traslation class.
 *
 * @package   lang
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class i18n {

    /** 
     * @var string Identificador del literal 
     */
    protected $identifier;
    /** @var string Argumentos pasados por parámetro para completar el string */
    /** @var array|stdClass Any arguments required for the string. Default null */
    protected $arg = null;
    /** @var string Lengua. */
    protected $lang = null;

    /** 
     * @var array The strings.
     *            Example: $strings['core']['key']
     */
    private static $strings = array('core');

    /**
     * Print string.
     *
     * @param string $key String key.
     * @param string $component Component lang.
     * @param string $param Additional information.
     */
    public static function put($key, $component = 'core', $param = '') {
        echo i18n::get($key, $component, $param);
    }

    /**
     * Obtiene una cadena según el idioma del usuario
     * 
     * @param string $key Identificador de la cadena
     * @param string $component Component lang.
     * @param string $param Additional information.
     * @return string
     */
    public static function get($key, $component = 'core', $param = '') {
        
        if (empty(i18n::$strings[$component][$key])) {
            i18n::init_i18n($component);
        }
        
        if (empty(i18n::$strings[$component][$key])) {
            throw new app_exception('langkeynotfound');
        }

        $string = i18n::$strings[$component][$key];

        if (is_string($param)) {
          $string = str_replace('{$ads}', (string)$param, $string);
        }
        
        return $string;
    }
    
    
    /**
     * Load all strings.
     * 
     * @param $string Component.
     * @throws app_exception
     */
    private static function init_i18n($component = 'core') {

        global $CFG;

        // Load default language libraries.
        $filepath = $CFG->homedir.'/lang/'.$CFG->defaultlang.'/'.$component.'.php';
        if (!file_exists($filepath)) {
            throw new app_exception(i18n::get('langfilenotexist', 'exception'));
        }
        
      include($filepath);
      i18n::$strings[$component] = $string;

       // If user can define these language, load it.
      if (!empty($_SESSION['lang']) && $_SESSION['lang'] != $CFG->defaultlang) {
            
        $userlangfilepath = $CFG->homedir.'/lang/'.$_SESSION['lang'].'/'.$component.'.php';
        if (!file_exists($userlangfilepath)) {
          throw new app_exception(i18n::get('langfilenotexist', 'exception'));
        }
            
        include($userlangfilepath);
        i18n::$strings[$component] = $string;
      }
    }
}