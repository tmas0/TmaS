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
 * Exception class.
 *
 * @package   core
 * @copyright 2014 onwards Toni Mas <antoni.mas@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class app_exception extends Exception {

	/**
	 * Unkown exception code.
	 */
	const UNKOWN = '0';

	/**
	 * Database exception code.
	 */
	const DB = '1';

	/**
	 * Middleware exception code.
	 */
	const HTML = '2';

	/**
	 * Constructor.
	 *
	 * @param string $message The exception message.
	 * @param int $component Component affected by the exception.
	 * @param int $code The exception code.
	 */
	public function __construct($message, $component=app_exception::UNKOWN, $code='00') {
		if (!is_int($code) and strlen($code) <> 2) {
			throw new Exception(i18n::get('invalid', 'exception', $code));
		}

		switch ($component) {
		 	case app_exception::UNKOWN:
		 		$message = i18n::get('unkown', 'exception').': '.$message;
		 		break;
		 	
		 	case app_exception::DB:
		 		$message = i18n::get('database', 'exception').': '.$message;
		 		break;

		 	case app_exception::HTML:
		 		$message = i18n::get('html', 'exception').': '.$message;
		 		break;

		 	default:
		 		// Unkown.
		 		$message = i18n::get('unkown', 'exception').': '.$message;
		 		break;
		 }

		 // Call parent class.
		 parent::__construct($message, $code);
	}

	/**
	 * Print exception.
	 */
	public function print_exception() {
		global $CFG, $OUTPUT;
		
		if (! headers_sent() ) {
			$OUTPUT->display_head();
	        echo $OUTPUT->get_header();
		}
		
		echo $this->get_exception_info();
		
		$OUTPUT->display_footer();
		
		
	}

	/**
	 * Get exception info.
	 *
	 * @param object $e Exception.
	 * @param bool $debug Display debug info.
	 * @return string HTML output.
	 */
	protected function get_exception_info($debug = false) {
		$output = '<div class="error">';
	    $output .= $this->getMessage();

	    if ($debug) {
	        $output .= '<br/>'.$e->debuginfo;
	    }

	    $output .= '</div>';

	    return $output;
	}
}