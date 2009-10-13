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
 * auto-models.class.php, 16 mars 2009
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
 * Automobile Models class for galette Auto plugin
 *
 * @name AutoModels
 * @package Galette
 *
 */

require_once('auto-brands.class.php');

class AutoModels {
	const TABLE = 'models';
	const PK = 'id_model';
	const FIELD = 'model';

	protected $id;
	protected $model;
	protected $brand;

	/**
	* Default constructor
	* @param integer id model's id to load. Defaults to null
	*/
	public function __construct($id = null){
		$this->brand = new AutoBrands();
		if( is_int($id) ) $this->load($id);
	}

	/**
	* Get models list
	* @param brandId optionnal brand we want models for
	*/
	public function getList($brandId=null){
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' INNER JOIN ' . PREFIX_DB . AUTO_PREFIX . Autobrands::TABLE . ' ON (' . PREFIX_DB . AUTO_PREFIX . self::TABLE . '.' . AutoBrands::PK . '=' . PREFIX_DB . AUTO_PREFIX . AutoBrands::TABLE . '.' . AutoBrands::PK . ')';

		//if required, the where clause
		if( isset($brandId) && is_int($brandId) ){
			$query .= ' WHERE ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . '.' . AutoBrands::PK . '=' . $brandId;
		}

		// the order clause
		$query .= ' ORDER BY ' . self::FIELD . ' ASC';

		$result = $mdb->query( $query );

		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot load models list | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		return $result->fetchAll();
	}

	/**
	* Get models list for specified brand
	* @param brandId brand we want models for
	*/
	public function getListByBrand($brandId){
		if( isset($brandId) && is_int($brandId) )
			return $this->getList($brandId);
		else
			return -1;
	}

	public function load($id){
		global $mdb, $log;

		$query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' WHERE ' . self::PK . '=' . $id;

		$result = $mdb->query( $query );

		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot load model from id `' . $id . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}

		$r = $result->fetchRow();
		$this->id = $r->id_model;
		$this->model = $r->model;
		$id_brand = AutoBrands::PK;
		$this->brand->load( (int)$r->$id_brand );

		$result->free();

		return true;
	}

	public function store($new = false){
		global $mdb, $log;

		if( $new ) {
			$query = 'INSERT INTO ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' (model, ' . AutoBrands::PK . ')';
			$query .= ' VALUES (\'' . $this->model . '\', ' . $this->brand->id_brand . ')';
		} else {
			$query = 'UPDATE ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' SET model=\'' . $this->model . '\', ' . AutoModels::PK . '=' . $this->brand->id_brand . ' WHERE ' . self::PK . '=' . $this->id;
		}

		$result = $mdb->execute( $query );

		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot store model values `' . $this->id . '`, `' . $this->value . '`, `' . $this->brand->id_brand .  '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}
		return true;
	}

	public function delete($ids){
		global $mdb, $log;

		$query = 'DELETE FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' WHERE ' . self::PK . '=';
		$query .= implode(' OR ' . self::PK . '=', $ids);

		$result = $mdb->execute( $query );

		if (MDB2::isError($result)) {
			$log->log('[' . get_class($this) . '] Cannot delete model from ids `' . implode(' - ', $ids) . '` | ' . $result->getMessage() . '(' . $result->getDebugInfo() . ')', PEAR_LOG_WARNING);
			return false;
		}
		return true;
	}

	/* GETTERS */
	public function __get($name){
		global $log;
		$forbidden = array();
		if( !in_array($name, $forbidden) ) {
			$log->log('[' . get_class($this) . '] Trying to get `' . $name . '`', PEAR_LOG_DEBUG);
			switch( $name ){
				case 'brand':
					return $this->brand->id;
					break;
				case 'obrand':
					return $this->brand;
					break;
				default:
					return $this->$name;
					break;
			}
		} else {
			$log->log('[' . get_class($this) . '] Unable to retrieve `' . $name . '`', PEAR_LOG_INFO);
			return false;
		}
	}

	/* SETTERS */
	public function __set($name, $value){
		switch( $name ) {
			case 'model':
			case self::PK:
				$this->$name = $value;
				break;
			case 'brand':
				$this->brand = new AutoBrands( (int)$value );
				break;
		}
	}
}
?>