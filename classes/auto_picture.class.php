<?php

// Copyright © 2009 Johan Cwiklinski
//
// This file is part of Galette (http://galette.tuxfamily.org).
//
// Galette is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Galette is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Galette. If not, see <http://www.gnu.org/licenses/>.

/**
 * Logo handling
 *
 * @package Galette
 * 
 * @author     Johan Cwiklinski
 * @copyright  2009 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id: logo.class.php 546 2009-03-05 06:09:00Z trashy $
 */

require_once(WEB_ROOT . 'classes/picture.class.php');
require_once('_config.inc.php');
require_once('auto.class.php');

class AutoPicture extends Picture{
	protected $tbl_prefix = AUTO_PREFIX;
	const PK = Auto::PK;

	/**
	* @override
	*/
	protected function getDefaultPicture(){
		global $plugins;
		$this->file_path = $plugins->getTemplatesPath('Auto') . '/images/car.png';
		$this->format = 'png';
		$this->mime = 'image/png';
		$this->has_picture = false;
	}
}
?>
