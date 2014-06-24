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
 * Controller base. Extends foreach new controller.
 *
 * @package   core
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class controller_base {

    /**
     * Controler model.
     *
     * @var model_base
     */
    protected $model;

    /**
     * Data returned for controller model.
     *
     * @var mixed
     */
    public $data;

    /**
     * Views.
     *
     * @var view
     */
    public $view;


    /**
     * Load model and views.
     */
    public function __construct() {

        global $MVC;

        $modelname = str_replace('_controller', '_model', $MVC->controller);
        $this->model = new $modelname;

        $this->view = new view();
    }


    /**
     * User can access to this action?
     *
     * @todo
     * @return boolean Can acces.
     */
    public function check_access() {

        global $MVC;

        return true;
    }


    /**
     * Default action.
     */
    public function default_flow() {

        global $MVC;

        if (!method_exists($this->model, $MVC->action)) {
            throw new app_exception('actionnotfound', 'core', $MVC->action);
        }

        $this->data = $this->model->{$MVC->action}();

        // Default structs.
        $struct['html'] = array('view' => $MVC->action, 'component' => $MVC->component);
        $struct['ajaxhtml'] = $struct['html'];
        $struct['pdf'] = $struct['html'];
        $struct['log'] = true;

        $this->view->output($this, $struct);
    }

}

?>