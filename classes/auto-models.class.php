<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Models class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2012 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Plugins
 * @package   GaletteAuto
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2012 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */

use Galette\Common\KLogger as KLogger;

require_once 'auto-brands.class.php';

/**
 * Automobile Models class for galette Auto plugin
 *
 * @category  Plugins
 * @name      AutoModels
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2012 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class AutoModels
{
    const TABLE = 'models';
    const PK = 'id_model';
    const FIELD = 'model';

    protected $id;
    protected $model;
    protected $brand;

    /**
    * Default constructor
    *
    * @param integer $id model's id to load. Defaults to null
    */
    public function __construct($id = null)
    {
        $this->brand = new AutoBrands();
        if ( is_int($id) ) {
            $this->load($id);
        }
    }

    /**
    * Get models list
    *
    * @param integer $brandId optionnal brand we want models for
    *
    * @return ResultSet
    */
    public function getList($brandId = null)
    {
        global $zdb, $log;

        try {
            $select = new Zend_Db_Select($zdb->db);

            $select->from(
                array('a' => PREFIX_DB . AUTO_PREFIX . self::TABLE)
            )->join(
                array('b' => PREFIX_DB . AUTO_PREFIX . Autobrands::TABLE),
                'a.' . AutoBrands::PK . '=b.' . AutoBrands::PK
            );

            //if required, the where clause
            if ( isset($brandId) && is_int($brandId) ) {
                $select->where('a.' . AutoBrands::PK . '= ?', $brandId);
            }

            // the order clause
            $select->order(self::FIELD . ' ASC');

            return $select->query()->fetchAll();
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot load models list | ' .
                $e->getMessage(),
                KLogger::ERR
            );
            return false;
        }
    }

    /**
    * Get models list for specified brand
    *
    * @param integer $brandId brand we want models for
    *
    * @return models list
    */
    public function getListByBrand($brandId)
    {
        if ( isset($brandId) && is_int($brandId) ) {
            return $this->getList($brandId);
        } else {
            return -1;
        }
    }

    /**
    * Load a model
    *
    * @param integer $id Id for the model we want
    *
    * @return boolean
    */
    public function load($id)
    {
        global $zdb, $log;

        try {
            $select = new Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . AUTO_PREFIX . self::TABLE)
                ->where(self::PK . ' = ?', $id);

            $r = $select->query()->fetch();
            $this->id = $r->id_model;
            $this->model = $r->model;
            $id_brand = AutoBrands::PK;
            $this->brand->load((int)$r->$id_brand);
            return true;
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot load model from id `' . $id .
                '` | ' . $e->getMessage(),
                KLogger::ERR
            );
            return false;
        }
    }

    /**
    * Store current model
    *
    * @param boolean $new New record or existing one
    *
    * @return boolean
    */
    public function store($new = false)
    {
        global $zdb, $log;

        try {
            $values = array(
                'model'         => $this->model,
                AutoBrands::PK  => $this->brand->id_brand
            );
            if ( $new ) {
                $zdb->db->insert(
                    PREFIX_DB . AUTO_PREFIX . self::TABLE,
                    $values
                );
            } else {
                $zdb->db->update(
                    PREFIX_DB . AUTO_PREFIX . self::TABLE,
                    $values,
                    self::PK . ' = ' . $this->id
                );
            }
            return true;
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot store model' .
                ' values `' . $this->id . '`, `' . $this->value . '` | ' .
                $e->getMessage(),
                KLogger::WARN
            );
            return false;
        }
    }

    /**
    * Delete some models
    *
    * @param array $ids Array of models id to delete
    *
    * @return boolean
    */
    public function delete($ids)
    {
        global $zdb, $log;

        try {
            $zdb->db->delete(
                PREFIX_DB . AUTO_PREFIX . self::TABLE,
                self::PK . ' IN (' . implode(',', $ids) . ')'
            );
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot delete models from ids `' .
                implode(' - ', $ids) . '` | ' . $e->getMessage(),
                KLogger::WARN
            );
            return false;
        }
    }

    /**
    * Global getter method
    *
    * @param string $name name of the property we want to retrive
    *
    * @return false|object the called property
    */
    public function __get($name)
    {
        global $log;
        $forbidden = array();
        if ( !in_array($name, $forbidden) ) {
            $log->log(
                '[' . get_class($this) . '] Trying to get `' . $name . '`',
                KLogger::DEBUG
            );
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
            $log->log(
                '[' . get_class($this) . '] Unable to retrieve `' . $name . '`',
                KLogger::INFO
            );
            return false;
        }
    }

    /**
    * Global setter method
    *
    * @param string $name  name of the property we want to assign a value to
    * @param object $value a relevant value for the property
    *
    * @return void
    */
    public function __set($name, $value)
    {
        switch ( $name ) {
        case 'model':
        case self::PK:
            $this->$name = $value;
            break;
        case 'brand':
            $this->brand = new AutoBrands((int)$value);
            break;
        }
    }
}
?>
