<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Object abstract class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2013 The Galette Team
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
 * @copyright 2009-2013 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */

use Analog\Analog as Analog;

/**
 * Automobile Object abstract class for galette Auto plugin
 *
 * @category  Plugins
 * @name      AutoObject
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2013 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
abstract class AutoObject
{
    private $_table;
    private $_pk;
    private $_field;
    private $_name;

    protected $id;
    protected $value;

    /**
    * Default constructor
    *
    * @param string  $table table name
    * @param string  $pk    primary key field
    * @param string  $field main field name
    * @param string  $name  name
    * @param integer $id    id to load. Defaults to null
    */
    public function __construct($table, $pk, $field, $name, $id = null)
    {
        $this->_table = AUTO_PREFIX . $table;
        $this->_pk = $pk;
        $this->_field = $field;
        $this->_name = $name;
        if ( is_int($id) ) {
            $this->load($id);
        }
    }

    /**
    * Get the list
    *
    * @return ResultSet
    */
    public function getList()
    {
        global $zdb;

        try {
            $select = new Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . $this->_table)
                ->order($this->_field . ' ASC');

            return $select->query()->fetchAll();
        } catch (Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load ' . $this->_name .
                ' list | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
    * Loads a record
    *
    * @param integer $id id of the record
    *
    * @return boolean
    */
    public function load($id)
    {
        global $zdb;

        try {
            $select = new Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . $this->_table)
                ->where($this->_pk . ' = ?', $id);

            $r = $select->query()->fetch();
            $pk = $this->_pk;
            $this->id = $r->$pk;
            $field = $this->_field;
            $this->value = $r->$field;

            return true;
        } catch (Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load ' . $this->_name .
                ' from id `' . $id . '` | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
    * Store current record
    *
    * @param boolean $new New record or existing one
    *
    * @return boolean
    */
    public function store($new = false)
    {
        global $zdb;

        try {
            $values = array(
                $this->_field => $this->value
            );
            if ( $new ) {
                $zdb->db->insert(
                    PREFIX_DB . $this->_table,
                    $values
                );
            } else {
                $zdb->db->update(
                    PREFIX_DB . $this->_table,
                    $values,
                    $this->_pk . ' = ' . $this->id
                );
            }
            return true;
        } catch (Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot store ' . $this->_name .
                ' values `' . $this->id . '`, `' . $this->value . '` | ' .
                $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
    * Delete some records
    *
    * @param array $ids Array of records id to delete
    *
    * @return boolean
    */
    public function delete($ids)
    {
        global $zdb;

        try {
            $zdb->db->delete(
                PREFIX_DB . $this->_table,
                $this->_pk . ' IN (' . implode(',', $ids) . ')'
            );
        } catch (Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot delete ' . $this->_name .
                ' from ids `' . implode(' - ', $ids) . '` | ' . $e->getMessage(),
                Analog::WARNING
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
        $forbidden = array();
        if ( !in_array($name, $forbidden) ) {
            if ( $name =='id' || $name == 'value' ) {
                return $this->$name;
            } else {
                $rname = '_' . $name;
                if ( isset($this->$rname) ) {
                    return $this->$rname;
                }
            }
        } else {
            Analog::log(
                '[' . get_class($this) . '] Unable to retrieve `' . $name . '`',
                Analog::INFO
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
        switch( $name ) {
        case 'value':
            $this->value = $value;
            break;
        }
    }
}
