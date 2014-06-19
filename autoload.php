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
 * Autoloader.
 *
 * @package   core
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load config file.
require_once(dirname(__FILE__).'/config.php');

// Hack, preload exception class.
require_once(dirname(__FILE__).'/lib/exception.class.php');

/**
 * Automatic classes loader.
 *
 * @package   core
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function __autoload($class) {
	global $CFG, $MVC;

    // List of directories who contains the classes.
    $paths = array('lib', 'html');

    if (!empty($MVC->component)) {
        // Paths of standard classes for each component.
        $componentpaths = array('lib', 'forms');

        foreach ($componentpaths as $key => $path) {
            $componentpaths[$key] = 'components/'.$MVC->component.'/'.$path;
        }
        $componentpaths[] = 'components/'.$MVC->component;

        $paths = array_merge($paths, $componentpaths);
    }

	// Load all system classes.
    foreach ($paths as $path) {
        if (file_exists($CFG->homedir.'/'.$path.'/'.$class.'.class.php')) {
            require_once($CFG->homedir.'/'.$path.'/'.$class.'.class.php');
            return true;
        }
    }

    throw new app_exception('Class '.$class.' not found', app_exception::UNKOWN, '00');
}