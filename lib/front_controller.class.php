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
 * App controller.
 *
 * @author     Toni Mas
 * @package    core
 * @copyright  2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class front_controller {

	private static $defaultcomponent = 'index';
    private static $defaultcontroller = 'index';
    private static $defaultaction = 'login';

    /**
     * Load App.
     *
     * Startup the App.
     */
    public static function load() {
        global $CFG, $OUTPUT, $MVC;

        // Force login.
        if ( !auth::is_loggedin() ) {
        	// Login.
        	$MVC->component = front_controller::$defaultcomponent;
			$controller = 'index';
			$MVC->action = 'login';
        } else {
        	// Edit.
        	$controller = 'index';
			$MVC->action = 'edit';
        }

        // Set specific or default controller.
        if ($controller) {
            $MVC->controller = $controller.'_controller';
        } else {
            $MVC->controller = front_controller::$defaultcontroller.'_controller';
        }

        // Make a controller instance.
        try {
            $instance = new $MVC->controller();
        } catch (app_exception $e) {
            front_controller::exception($e);
        }

        // If set action, load it, else load defuault action.
        if (!method_exists($instance, $MVC->action)) {
            $action = 'default_flow';
        } else {
            $action = $MVC->action;
        }


        try {
        	// Execte the action.
            $instance->{$action}();
        } catch (app_exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * Show exceptions
     *
     * @param controller_base $instance
     * @param app_exception $e
     */
    private static function exception($e, $instance = false) {

        global $CFG, $MVC;

        // If the controller had loaded.
        if ($instance) {
            $instance->view->output_exception($instance, $e);
        // Else, print it.
        } else {
        	$e->print_exception();
            die();
        }
    }
}