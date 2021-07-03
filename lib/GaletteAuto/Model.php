<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Models class for galette Auto plugin
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
use Galette\Core\Db;
use GaletteAuto\Filters\ModelsList;

/**
 * Automobile Models class for galette Auto plugin
 *
 * @category  Plugins
 * @name      Model
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class Model
{
    public const TABLE = 'models';
    public const PK = 'id_model';
    public const FIELD = 'model';

    protected $id;
    protected $model;
    protected $brand;

    private $errors;
    private $zdb;

    /**
    * Default constructor
    *
    * @param Db    $zdb  Database instance
    * @param mixed $args model's id to load or ResultSet. Defaults to null
    */
    public function __construct(Db $zdb, $args = null)
    {
        $this->zdb = $zdb;
        $this->brand = new Brand($zdb);

        if ($args == null || is_int($args)) {
            if (is_int($args) && $args > 0) {
                $this->load($args);
            }
        } elseif (is_object($args)) {
            $this->loadFromRS($args);
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
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE);
            $select->where(
                array(
                    self::PK => $id
                )
            );

            $results = $this->zdb->execute($select);
            $result = $results->current();
            $this->loadFromRS($result);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load model from id `' . $id .
                '` | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Populate object from a resultset row
     *
     * @param ResultSet $r the resultset row
     *
     * @return void
     */
    private function loadFromRS($r)
    {
        $this->id = $r->id_model;
        $this->model = $r->model;
        $id_brand = Brand::PK;
        $this->brand->load((int)$r->$id_brand);
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
        try {
            $values = array(
                'model'     => $this->model,
                Brand::PK   => $this->brand->id_brand
            );
            if ($new) {
                $insert = $this->zdb->insert(AUTO_PREFIX . self::TABLE);
                $insert->values($values);
                $this->zdb->execute($insert);
            } else {
                $update = $this->zdb->update(AUTO_PREFIX . self::TABLE);
                $update->set($values)->where(
                    array(
                        self::PK => $this->id
                    )
                );
                $this->zdb->execute($update);
            }
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot store model' .
                ' values `' . $this->id . '`, `' . implode('`, `', $values) . '` | ' .
                $e->getMessage(),
                Analog::WARNING
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
        try {
            $delete = $this->zdb->delete(AUTO_PREFIX . self::TABLE);
            $delete->where->in(self::PK, $ids);
            $this->zdb->execute($delete);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot delete models from ids `' .
                implode(' - ', $ids) . '` | ' . $e->getMessage(),
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
        if (!in_array($name, $forbidden)) {
            switch ($name) {
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
            Analog::log(
                '[' . get_class($this) . '] Unable to retrieve `' . $name . '`',
                Analog::INFO
            );
            return false;
        }
    }

    /**
     * Check posted values validity
     *
     * @param array $post All values to check, basically the $_POST array
     *                    after sending the form
     *
     * @return boolean
     */
    public function check($post)
    {
        $this->errors = [];
        if (!isset($post['brand']) || $post['brand'] == -1) {
            $this->errors[] = _T("- You must select a brand!", "auto");
        } else {
            $this->brand = new Brand($this->zdb, (int)$post['brand']);
        }

        if (!isset($post['model']) || $post['model'] == '') {
            $this->errors[] = _T("- You must provide a value!", "auto");
        } else {
            $this->model = $post['model'];
        }
        return count($this->errors) === 0;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set brand from ID
     *
     * @param integer $id Brand ID
     *
     * @return Model
     */
    public function setBrand($id)
    {
        $this->brand = new Brand($this->zdb, $id);
    }
}
