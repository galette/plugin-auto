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
 * members.class.php, 28 février 2009
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
 * Members class for galette
 *
 * @name Members
 * @package Galette
 *
 */

require_once('auto.class.php');

class Autos {
	const TABLE = Auto::TABLE;
	const PK = Auto::PK;

	private $filter = null;

	/**
	* Default constructor
	*/
	public function __construct(){}

	/**
	* Get the list of all vehicles
	* @param as_autos return the results as an array of Auto object. When true, fields are not relevant
	* @param fields field(s) name(s) to get. Should be a string or an array. If null, all fields will be returned
	* @param filter should add filter... TODO
	*/
	public static function getList($as_autos=false, $fields=null, $filter=null){
		global $mdb, $log;

		/** TODO: Check if filter is valid ? */
		if( $filter != null && trim($filter) != '' ) $this->filter = $filter;

		$fieldsList = ( $fields != null && !$as_autos ) ? (( !is_array($fields) || count($fields) < 1 ) ? '*' : implode(', ', $fields)) : '*';

		$requete = 'SELECT ' . $fieldsList . ' FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE;

		$result = $mdb->query( $requete );
		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot list Autos | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		$autos = array();
		if( $as_autos ) {
			foreach( $result->fetchAll() as $row ){
				$autos[] = new Auto($row);
			}
		} else {
			$autos = $result->fetchAll();
		}
		return $autos;
	}
}
?>