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
 * Home page.
 *
 * @package   core
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load all libraries.
require_once(dirname(__FILE__).'/autoload.php');

// Start app.
try {
	front_controller::load();
} catch (ae_exception $e) {
	$e->print_exception();
} catch (Exception $e) {
	echo 'Exceptional and fatal error on start application: '.$e->getMessage();
}
