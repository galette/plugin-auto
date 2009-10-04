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
 * auto-brands.class.php, 16 mars 2009
 *
 * @package Galette
 * 
 * @author     Johan Cwiklinski <johan@x-tnd.be>
 * @copyright  2009 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id$
 * @since      Disponible depuis la Release 0.7alpha
 */

/**
 * Automobile Brands class for galette Auto plugin
 *
 * @name AutoBrands
 * @package Galette
 *
 */

require_once('auto-objects.class.php');
require_once('auto-models.class.php');

class AutoBrands extends AutoObject {
	const TABLE = 'brands';
	const PK = 'id_brand';
	const FIELD = 'brand';
	const NAME = 'brands';

	/**
	* Default constructor
	* @param integer id brand's id to load. Defaults to null
	*/
	public function __construct($id = null){
		parent::__construct(
			self::TABLE,
			self::PK,
			self::FIELD,
			self::NAME,
			$id
		);
	}

	public function getModels($brand) {
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . AutoModels::TABLE . ' WHERE ' . self::PK . '=' . $brand . ' ORDER BY ' . AutoModels::FIELD . ' ASC';

		$result = $mdb->query( $query );

		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot load models list | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		return $result->fetchAll();
	}


	/* GETTERS */
	public function __get($name){
		if( $name == self::FIELD )
			return parent::__get('field');
		if( $name == self::PK )
			return parent::__get('id');
		else
			return parent::__get($name);
	}
}
?>