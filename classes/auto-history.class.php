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
 * auto-history.class.php, 02 octobre 2009
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
 * Automobile History class for galette Auto plugin
 *
 * @name AutoHistory
 * @package Galette
 *
 */

require_once(WEB_ROOT . 'classes/adherent.class.php');
require_once('auto.class.php');
require_once('auto-colors.class.php');
require_once('auto-states.class.php');

class AutoHistory{
	const TABLE = 'history';

	//fields list and type
	private $fields = array(
		Auto::PK		=>	'integer',	//pk
		Adherent::PK		=>	'integer',	//pk
		'history_date'		=>	'datetime',	//pk
		'car_registration'	=>	'text',
		AutoColors::PK		=>	'integer',
		AutoStates::PK		=>	'integer'
	);
	//history entries
	private $entries;
	private $id_car;

	/**
	* Default constructor
	*/
	public function __construct($id = null){
		global $log;
		if( $id != null && is_int($id) ){
			$this->load($id);
		}
	}

	/**
	* Loads history for specified car
	* @param integer car's id we want history for
	*/
	public function load($id){
		global $mdb, $log;

		if( $id == null || !is_int($id) ){
			$log->log('[AutoHistory] Unable to load car\'s history : Invalid car id (id was: `' . $id . '`)', PEAR_LOG_ERR);
			return false;
		}

		$this->id_car = $id;
		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' WHERE ' . Auto::PK . '=' . $id . ' ORDER BY history_date DESC';

		$result = $mdb->query($query);
		if (MDB2::isError($result)) {
			$log->log('[AutoHistory] Cannot get car\'s history (id was ' . $this->id_car . ') | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_ERR);
			return false;
		}
		$this->entries = $result->fetchAll();
		$this->formatEntries();
	}

	/**
	* Returns the most recent history entry
	*/
	public function getLatest(){
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' WHERE ' . Auto::PK . '=' . $this->id_car . ' ORDER BY history_date DESC LIMIT 1';

		$result = $mdb->query($query);
		if( MDB2::isError($result) ){
			$log->log('[AutoHistory] Cannot get car\'s latest history entry | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_ERR);
			return false;
		}

		return $result->fetchRow();
	}

	/**
	* Format entries dates, also loads Member
	*/
	private function formatEntries(){
		for($i = 0 ; $i < count($this->entries) ; $i++ ){
			//put a fromatted date to show
			$this->entries[$i]->formatted_date = strftime('%d %B %Y', strtotime($this->entries[$i]->history_date));
			//associate member to current history entry
			$this->entries[$i]->owner = new Adherent( (int)$this->entries[$i]->id_adh );
			//associate color
			$this->entries[$i]->color = new AutoColors( (int)$this->entries[$i]->id_color );
			//associate state
			$this->entries[$i]->state = new AutoStates( (int)$this->entries[$i]->id_state );
		}
	}

	/**
	* Register a new history entry.
	* @param array props list of properties to update
	*/
	public function register($props){
		global $mdb, $log;
		$log->log('[AutoHistory] Trying to register a new history entry.', PEAR_LOG_DEBUG);

		$fields = $this->fields;
		ksort($fields);
		ksort($props);
		$query = 'INSERT INTO ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' (' . implode(', ', array_keys($fields)) . ') VALUES (';
		foreach($props as $key=>$prop){
			$query .= ($this->fields[$key] == 'integer') ? $prop : $mdb->quote($prop);
			if( end(array_keys($fields)) != $key ) $query .= ', ';
		}
		$query .= ')';

		$result = $mdb->query($query);
		if( MDB2::isError($result) ){
			$log->log('[AutoHistory] Cannot register new histroy entry | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_ERR);
			return false;
		} else {
			$log->log('[AutoHistory] new AutoHistory entry set successfully.', PEAR_LOG_DEBUG);
		}
	}

	/* GETTERS */
	public function __get($name){
		global $log;
		switch($name){
			case Auto::PK:
				$k = Auto::PK;
				return $this->$k;
				break;
			case 'fields':
				return array_keys($this->fields);
				break;
			case 'entries':
				return $this->entries;
				break;
			default:
				$log->log('[AutoHistory] Trying to get an unknown property (' . $name . ')', PEAR_LOG_INFO);
				break;
		}
	}
}
?>