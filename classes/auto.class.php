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
 * auto.class.php, 16 mars 2009
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
 * Automobile class for galette Auto plugin
 *
 * @name Auto
 * @package Galette
 *
 */

//main Galette classes required
require_once(WEB_ROOT . 'classes/adherent.class.php');
//current plugin classes required
require_once('auto_picture.class.php');
require_once('auto-models.class.php');
require_once('auto-bodies.class.php');
require_once('auto-colors.class.php');
require_once('auto-finitions.class.php');
require_once('auto-states.class.php');
require_once('auto-transmissions.class.php');

class Auto {
	const TABLE = 'cars';
	const PK = 'id_car';

	private $fields = array(
		'id_car'			=>	'integer',
		'car_name'			=>	'string',
		'car_registration'		=>	'string',
		'car_first_registration_date'	=>	'date',
		'car_first_circulation_date'	=>	'date',
		'car_mileage'			=>	'integer',
		'car_comment'			=>	'string',
		'car_creation_date'		=>	'date',
		'car_chassis_number'		=>	'string',
		'car_seats'			=>	'integer',
		'car_horsepower'		=>	'integer',
		'car_engine_size'		=>	'integer',
		'car_fuel'			=>	'integer',
		AutoColors::PK			=>	'integer',
		AutoBodies::PK			=>	'integer',
		AutoStates::PK			=>	'integer',
		AutoTransmissions::PK		=>	'integer',
		AutoFinitions::PK		=>	'integer',
		AutoModels::PK			=>	'integer'
	);

	private $id;				//identifiant
	private $registration;			//immatriculation
	private $name;				//petit nom
	private $first_registration_date;	//date de première immatriculation
	private $first_circulation_date;	//date de prmière mise en service
	private $mileage;			//kilométrage
	private $comment;			//commentaire
	private $chassis_number;		//numéro de chassis
	private $seats;				//nombre de places
	private $horsepower;			//puissance fiscale
	private $engine_size;			//cylindrée
	private $creation_date;			//date de création
	private $update_date;			//usefull ? better to get it from history //date de mise à jour de la fiche
	private $fuel;				//carburant

	//External objects
	private $picture;			//photo de la voiture
	private $finition;			//niveau de finition
	private $color;				//couleur
	private $model;				//modèle
	private $transmission;			//type de transmission
	private $body;				//carrosserie
	private $history;			//historique
	private $owner;				//propriétaire actuel
	private $state;				//état actuel

	const FUEL_PETROL = 1;
	const FUEL_DIESEL = 2;
	const FUEL_GAS = 3;
	const FUEL_ELECTRICITY = 4;
	const FUEL_BIO = 5;

	private $propnames;			//textual properties names

	//internal properties (not updatable outside the object)
	private $internals = array (
		'id', 
		'creation_date',
		'update_date',
		'history',
		'picture',
		'propnames',
		'internals',
		'fields'
	);
	/**
	* Default constructor
	*/
	public function __construct($args = null){
		$this->propnames = array(
			'name'				=>	_T("name"),
			'model'				=>	_T("model"),
			'registration'			=> 	_T("registration"),
			'first_registration_date'	=>	_T("first registration date"),
			'first_circulation_date'	=>	_T("first circulation date"),
			'mileage'			=>	_T("mileage"),
			'seats'				=>	_T("seats"),
			'horsepower'			=>	_T("horsepower"),
			'engine_size'			=>	_T("engine size"),
			'color'				=>	_T("color"),
			'state'				=>	_T("state")
		);

		$this->model = new AutoModels();
		$this->color = new AutoColors();
		$this->state = new AutoStates();
		$this->owner = new Adherent();
		$this->transmission = new AutoTransmissions();
		$this->finition = new AutoFinitions();
		$this->picture = new AutoPicture();
		$this->body = new AutoBodies();
		if ( is_object($args) ){
			$this->loadFromRS($args);
		}
	}

	/**
	* Loads a car from its id
	* @param id the identifiant for the car to load
	*/
	public function load($id){
		global $mdb, $log;

		$requete = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' WHERE ' . self::PK . '=' . $id;

		$result = $mdb->query( $requete );

		if (MDB2::isError($result)) {
			$log->log('Cannot load car form id `' . $id . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		$this->loadFromRS($result->fetchRow());
		$result->free();

		return true;
	}

	/**
	* Populate object from a resultset row
	*/
	private function loadFromRS($r){
		$pk = self::PK;
		$this->id = $r->$pk;
		$this->registration = $r->car_registration;
		$this->name = $r->car_name;
		$this->first_registration_date = $r->car_first_registration_date;
		$this->first_circulation_date = $r->car_first_circulation_date;
		$this->mileage = $r->car_mileage;
		$this->comment = $r->car_comment;
		$this->chassis_number = $r->car_chassis_number;
		$this->seats = $r->car_seats;
		$this->horsepower = $r->car_horsepower;
		$this->engine_size = $r->car_engine_size;
		$this->creation_date = $r->car_creation_date;
		//$this->update_date = $r->car_update_date;
		$this->fuel = $r->car_fuel;
		//External objects
		//$this->picture = new AutoPicture( (int)$this->id );
		$fpk = AutoFinitions::PK;
		$this->finition->load( (int)$r->$fpk );
		$cpk = AutoColors::PK;
		$this->color->load( (int)$r->$cpk );
		$mpk = AutoModels::PK;
		$this->model->load( (int)$r->$mpk );
		$tpk = AutoTransmissions::PK;
		$this->transmission->load( (int)$r->$tpk );
		$bpk = AutoBodies::PK;
		$this->body->load( (int)$r->$bpk );
		/** TODO: car's history */
		//$this->history->load( $this->id );
		/** FIXME: owner is not saved in database ! */
		//$this->owner = new Adherent( $r->owner );
		$spk = AutoStates::PK;
		$this->state->load( (int)$r->$spk );
	}

	/**
	* Return the list of available fuels
	*/
	public function listFuels(){
		$f = array(
			self::FUEL_PETROL	=>	_T("Petrol"),
			self::FUEL_DIESEL	=>	_T("Diesel"),
			self::FUEL_GAS		=>	_T("Gas"),
			self::FUEL_ELECTRICITY	=>	_T("Electricity"),
			self::FUEL_BIO		=>	_T("Bio")
		);
		return $f;
	}

	/**
	* Get the list of all vehicles
	*/
	/*public function getList(){
		global $mdb, $log;
		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE;

		$result = $mdb->query( $query );
		if( MDB2::isError($result) ){
			$log->log('An error has occured listing cars | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_ERR);
			return false;
		} else {
			return $result->fetchAll();
		}
	}*/

	/**
	* Stores the vehicle in the database
	* @param boolean new true if it's a new record, false to update on that already exists. Defaults to false
	*/
	public function store($new = false){
		global $mdb, $log;

		if( $new ) $this->creation_date = date('Y-m-d');
		$this->update_date = date('Y-m-d');

		$query = '';

		if( $new ){
			$query = 'INSERT INTO ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' (' . implode(', ', array_keys($this->fields) ) . ') VALUES (';
			foreach($this->fields as $k=>$v){
				switch($k){
					case self::PK:
						$query .= 'null, ';
						break;
					case AutoColors::PK:
						$query .= $this->color->id . ', ';
						break;
					case AutoBodies::PK:
						$query .= $this->body->id . ', ';
						break;
					case AutoStates::PK:
						$query .= $this->state->id . ', ';
						break;
					case AutoTransmissions::PK:
						$query .= $this->transmission->id . ', ';
						break;
					case AutoFinitions::PK:
						$query .= $this->finition->id . ', ';
						break;
					case AutoModels::PK:
						$query .= $this->model->id . ', ';
						break;
					default:
						$propName = substr($k, 4, strlen($k));
						switch($v){
							case 'string':
							case 'date':
								$query .= '\'' . $this->$propName . '\', ';
								break;
							case 'integer':
								$query .= (($this->$propName != '') ? $this->$propName : 0) . ', ';
								break;
							default:
								$query .= '\'' . $this->$propName . '\', ';
								break;
						}
						break;
				}
			}
			//remove last ', ', add final ')'
			$query = substr($query, 0, strlen($query)-2) . ')';
		} else {
			$query = 'UPDATE ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' SET ';
			foreach($this->fields as $k=>$v){
				switch($k){
					case self::PK:
						break;
					case AutoColors::PK:
						$query .= AutoColors::PK . '=' . $this->color->id . ', ';
						break;
					case AutoBodies::PK:
						$query .= AutoBodies::PK . '=' . $this->body->id . ', ';
						break;
					case AutoStates::PK:
						$query .= AutoStates::PK . '=' . $this->state->id . ', ';
						break;
					case AutoTransmissions::PK:
						$query .= AutoTransmissions::PK . '=' . $this->transmission->id . ', ';
						break;
					case AutoFinitions::PK:
						$query .= AutoFinitions::PK . '=' . $this->finition->id . ', ';
						break;
					case AutoModels::PK:
						$query .= AutoModels::PK . '=' . $this->model->id . ', ';
						break;
					default:
						$propName = substr($k, 4, strlen($k));
						switch($v){
							case 'string':
							case 'date':
								$query .= $k . '=\'' . $this->$propName . '\', ';
								break;
							case 'integer':
								$query .= $k . '=' . (($this->$propName != '') ? $this->$propName : null) . ', ';
								break;
							default:
								$query .= $k . '=\'' . $this->$propName . '\', ';
								break;
						}
						
						break;
				}
			}
			//remove last ', ', add where clause
			$query = substr($query, 0, strlen($query)-2) . ' WHERE ' . self::PK . '=' . $this->id;
		}

		$result = $mdb->query( $query );
		if( MDB2::isError($result) ){
			$log->log('An error has occured ' . (($new)?'inserting':'updating') . ' car | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_ERR);
			return false;
		}
		return true;
	}

	/**
	* List object's properties
	* @param boolean restrict : true to exclude $this->internals from returned result, false otherwise. Default to false
	*/
	private function getAllProperties($restrict = false){
		$result = array();
		foreach($this as $key => $value) {
			if(!$restrict || ($restrict && !in_array($key, $this->internals)))
				$result[] = $key;
		}
		return $result;
	}

	/**
	* Get object's properties. List only properties that can be modified externally (ie. not in $this->internals)
	*/
	public function getProperties(){
		return $this->getAllProperties(true);
	}

	/**
	* Does the current car has a picture?
	*/
	public function hasPicture(){
		return $this->picture->hasPicture();
	}

	/**
	* Returns plain text property name, generally used for translations
	*/
	public function getPropName($name){
		return $this->propnames[$name];
	}

	/* GETTERS */
	public function __get($name){
		global $log;
		$forbidden = array();
		if( !in_array($name, $forbidden) ) {
			switch($name){
				case 'first_registration_date':
				case 'first_circulation_date':
				case 'creation_date':
				case 'update_date':
					/** FIXME: date function from functions.inc.php does use adodb */
					return date_db2text($this->$name);
					break;
				default:
					return $this->$name;
					break;
			}
		} else {
			$log->log('[Auto] Unable to retrieve `' . $name . '`', PEAR_LOG_INFO);
			return false;
		}
	}

	/* SETTERS */
	public function __set($name, $value){
		/** TODO: What to do ? :-) */
		if( !in_array($name, $this->internals) ){
			switch($name){
				case 'finition':
					$this->finition->load( (int)$value );
					break;
				case 'color':
					$this->color->load( (int)$value );
					break;
				case 'model':
					$this->model->load( (int)$value );
					break;
				case 'transmission':
					$this->transmission->load( (int)$value );
					break;
				case 'body':
					$this->body->load( (int)$value );
					break;
				case 'owner':
					$this->owner->load( (int)$value );
					break;
				case 'state':
					$this->state->load( (int)$value );
					break;
				default:
					$this->$name = $value;
					break;
			}
		} else {
			$log->log('Trying to set an internal property (`' . $name . '`)', PEAR_LOG_INFO);
			return false;
		}
	}
}
?>