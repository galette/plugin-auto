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
 * auto-objects.class.php, 16 mars 2009
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
 * Automobile Object abstract class for galette Auto plugin
 *
 * @name AutoObject
 * @package Galette
 *
 */


abstract class AutoObject {
	private $table;
	private $pk;
	private $field;
	private $name;

	protected $id;
	protected $value;

	/**
	* Default constructor
	*/
	public function __construct($table, $pk, $field, $name){
		$this->table = AUTO_PREFIX . $table;
		$this->pk = $pk;
		$this->field = $field;
		$this->name = $name;
	}

	public function getList(){
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . $this->table . ' ORDER BY ' . $this->field . ' ASC';

		$result = $mdb->query( $query );

		if (MDB2::isError($result)) {
			$log->log('[AutoObject] Cannot load ' . $this->name . ' list | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		return $result->fetchAll();
	}

	public function load($id){
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . $this->table . ' WHERE ' . $this->pk . '=' . $id;

		$result = $mdb->query( $query );

		if (MDB2::isError($result)) {
			$log->log('[AutoObject] Cannot load ' . $this->name . ' from id `' . $id . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		$r = $result->fetchRow();
		$pk = $this->pk;
		$this->id = $r->$pk;
		$field = $this->field;
		$this->value = $r->$field;

		$result->free();

		return true;
	}

	public function store($new = false){
		global $mdb, $log;

		if( $new ) {
			$query = 'INSERT INTO ' . PREFIX_DB . $this->table . ' (' . $this->field . ')';
			$query .= ' VALUES (\'' . $this->value . '\')';
		} else {
			$query = 'UPDATE ' . PREFIX_DB . $this->table . ' SET ' . $this->field . '=\'' . $this->value . '\' WHERE ' . $this->pk . '=' . $this->id;
		}

		//echo $query;

		$result = $mdb->execute( $query );

		if (MDB2::isError($result)) {
			$log->log('[AutoObject] Cannot store ' . $this->name . ' values `' . $this->id . '`, `' . $this->value . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}
		return true;
	}

	public function delete($ids){
		global $mdb, $log;

		$query = 'DELETE FROM ' . PREFIX_DB . $this->table . ' WHERE ' . $this->pk . '=';
		$query .= implode(' OR ' . $this->pk . '=', $ids);

		$result = $mdb->execute( $query );

		if (MDB2::isError($result)) {
			$log->log('[AutoObject] Cannot delete ' . $this->name . ' from id `' . $this->id . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}
		return true;
	}

	/* GETTERS */
	public function __get($name){
		global $log;
		$forbidden = array();
		if( !in_array($name, $forbidden) )
			return $this->$name;
		else {
			$log->log('[AutoObject] Unable to retrieve `' . $name . '`', PEAR_LOG_INFO);
			return false;
		}
	}

	/* SETTERS */
	public function __set($name, $value){
		switch( $name ) {
			case 'value':
				$this->value = $value;
				break;
		}
		/** TODO: What to do ? :-) */
	}
}
?>