<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Object abstract class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2014 The Galette Team
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
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */

namespace GaletteAuto;

use Analog\Analog;
use Slim\Router;
use Galette\Core\Db;
use GaletteAuto\Filters\PropertiesList;

/**
 * Automobile Object abstract class for galette Auto plugin
 *
 * @category  Plugins
 * @name      AbstractObject
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
abstract class AbstractObject
{
    private $table;
    private $pk;
    private $field;
    private $name;

    protected $zdb;
    protected $id;
    protected $value;
    protected $filters;

    /**
     * Default constructor
     *
     * @param Db      $zdb   Database instance
     * @param string  $table table name
     * @param string  $pk    primary key field
     * @param string  $field main field name
     * @param string  $name  name
     * @param integer $id    id to load. Defaults to null
     */
    public function __construct(Db $zdb, $table, $pk, $field, $name, $id = null)
    {
        $this->zdb = $zdb;
        $this->table = AUTO_PREFIX . $table;
        $this->pk = $pk;
        $this->field = $field;
        $this->name = $name;
        if (is_int($id)) {
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
        try {
            $select = $this->zdb->select($this->table);
            $select->order($this->field . ' ASC');

            $results = $this->zdb->execute($select);
            $list = [];
            foreach ($results as $row) {
                $list[] = $row;
            }
            return $list;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load ' . $this->name .
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
        try {
            $select = $this->zdb->select($this->table);
            $select->where(
                array(
                    $this->pk => $id
                )
            );

            $results = $this->zdb->execute($select);
            $result = $results->current();
            $pk = $this->pk;
            $this->id = $result->$pk;
            $field = $this->field;
            $this->value = $result->$field;

            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load ' . $this->name .
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
        try {
            $values = array(
                $this->field => $this->value
            );
            if ($new) {
                $insert = $this->zdb->insert($this->table);
                $insert->values($values);
                $this->zdb->execute($insert);
            } else {
                $update = $this->zdb->update($this->table);
                $update->set($values)->where(
                    array(
                        $this->pk => $this->id
                    )
                );
                $this->zdb->execute($update);
            }
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot store ' . $this->name .
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
        try {
            $delete = $this->zdb->delete($this->table);
            $delete->where->in($this->pk, $ids);
            $this->zdb->execute($delete);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot delete ' . $this->name .
                ' from ids `' . implode(' - ', $ids) . '` | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Set filters
     *
     * @param PropertiesList $filters Filters
     *
     * @return void
     */
    public function setFilters(PropertiesList $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get field label
     *
     * @return string
     */
    abstract public function getFieldLabel();

    /**
     * Get property route name
     *
     * @return string
     */
    abstract public function getRouteName();

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
        if (!in_array($name, $forbidden)) {
            if ($name =='id' || $name == 'value') {
                return $this->$name;
            } else {
                if (isset($this->$name)) {
                    return $this->$name;
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
        switch ($name) {
            case 'value':
                $this->value = $value;
                break;
        }
    }

    /**
     * Get list route
     *
     * @param Router $router   Router instance
     * @param string $property Property name
     *
     * @return string
     */
    public static function getListRoute(Router $router, $property)
    {
        $route = null;
        switch ($property) {
            case 'color':
                $route = $router->pathFor('colorsList');
                break;
            case 'state':
                $route = $router->pathFor('statesList');
                break;
            case 'finition':
                $route = $router->pathFor('finitionsList');
                break;
            case 'body':
                $route = $router->pathFor('bodiesList');
                break;
            case 'transmission':
                $route = $router->pathFor('transmissionsList');
                break;
            case 'brand':
                $route = $router->pathFor('brandsList');
                break;
            default:
                throw new \RuntimeException('Unknown property ' . $property);
                break;
        }
        return $route;
    }

    /**
     * Get object name from route property
     *
     * @param string $property Route property
     *
     * @return string
     */
    public static function getClassForPropName($property)
    {
        $classname = '\GaletteAuto\\';
        switch ($property) {
            case 'brand':
                $classname .= 'Brand';
                break;
            case 'color':
                $classname .= 'Color';
                break;
            case 'state':
                $classname .= 'State';
                break;
            case 'finition':
                $classname .= 'Finition';
                break;
            case 'body':
                $classname .= 'Body';
                break;
            case 'transmission':
                $classname .= 'Transmission';
                break;
            default:
                throw new \RuntimeException('Unknown property ' . $property);
                break;
        }
        return $classname;
    }
}
