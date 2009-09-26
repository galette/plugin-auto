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
	const TABLE = 'auto';
	const PK = 'id_auto';

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
	//private $engine_size;			//cylindrée
	private $transmission;			//type de transmission
	private $body;				//carrosserie
	private $history;			//historique
	private $owner;				//propriétaire actuel

	const FUEL_PETROL = 1;
	const FUEL_DIESEL = 2;
	const FUEL_GAS = 3;
	const FUEL_ELECTRICITY = 4;
	const FUEL_BIO = 5;

	/**
	* Default constructor
	*/
	public function __construct(){
		// initialize linked objects properly
		$this->model = new AutoModels();
		$this->color = new AutoColors();
		$this->state = new AutoStates();
		$this->owner = new Adherent();
		$this->transmission = new AutoTransmissions();
		$this->finition = new AutoFinitions();
		$this->picture = new AutoPicture();
	}

	/**
	* Return the list of available fuels
	*/
	public function listFuels(){
		$f = array(
			1	=>	_T("Petrol"),
			2	=>	_T("Diesel"),
			3	=>	_T("Gas"),
			4	=>	_T("Electricity"),
			5	=>	_T("Bio")
		);
		return $f;
	}

	/**
	* Get the list of all vehicles
	*/
	public function getList(){
		return array();
	}

	/* GETTERS */
	public function hasPicture(){
		//not yet implemented
		return false;
	}
	public function __get($name){
		global $log;
		$forbidden = array();
		if( !in_array($name, $forbidden) )
			return $this->$name;
		else {
			$log->log('[Auto] Unable to retrieve `' . $name . '`', PEAR_LOG_INFO);
			return false;
		}
	}

	/* SETTERS */
	public function __set($name, $value){
		/** TODO: What to do ? :-) */
	}
}
?>