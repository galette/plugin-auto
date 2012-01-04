<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile autos class for galette Auto plugin
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
 * @since     Available since 0.7dev - 2009-09-26
 */

require_once 'auto.class.php';

/**
 * Automobile autos class for galette Auto plugin
 *
 * @category  Plugins
 * @name      Auto
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2012 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */
class Autos
{
    const TABLE = Auto::TABLE;
    const PK = Auto::PK;

    private $_filter = null;

    /**
    * Default constructor
    */
    public function __construct()
    {
    }

    /**
     * Remove specified vehicles
     *
     * @param interger|array $ids Vehicles identifiers to delete
     *
     * @return boolean
     */
    public function removeVehicles($ids)
    {
        global $zdb, $log, $hist;

        $list = array();
        if ( is_numeric($ids) ) {
            //we've got only one identifier
            $list[] = $ids;
        } else {
            $list = $ids;
        }

        if ( is_array($list) ) {
            try {
                $zdb->db->beginTransaction();

                //Retrieve some informations
                $select = new Zend_Db_Select($zdb->db);
                $select->from(
                    array('a' => PREFIX_DB . AUTO_PREFIX . self::TABLE),
                    array(self::PK, 'a.car_name', 'c.brand', 'b.model')
                )->join(
                    array('b' => PREFIX_DB . AUTO_PREFIX . AutoModels::TABLE),
                    'a.' . AutoModels::PK . ' = b.' . AutoModels::PK
                )->join(
                    array('c' => PREFIX_DB . AUTO_PREFIX . AutoBrands::TABLE),
                    'b.' . AutoBrands::PK . ' = c.' . AutoBrands::PK
                )->where(self::PK . ' IN (?)', $ids);

                $vehicles = $select->query()->fetchAll();

                $infos = null;
                foreach ($vehicles as $vehicle ) {
                    $str_v = $vehicle->id_car . ' - ' . $vehicle->car_name .
                        ' (' . $vehicle->brand . ' ' . $vehicle->model . ')';
                    $infos .=  $str_v . "\n";

                    $p = new AutoPicture($vehicle->id_car);
                    if ( $p->hasPicture() ) {
                        if ( !$p->delete() ) {
                            $log->log(
                                'Unable to delete picture for vehicle ' .
                                $str_v,
                                PEAR_LOG_ERR
                            );
                            throw new Exception(
                                'Unable to delete picture for vehicle ' .
                                $str_v
                            );
                        } else {
                            $hist->add(
                                "Vehicle Picture deleted",
                                $str_adh
                            );
                        }
                    }
                }

                //delete vehicles
                $del = $zdb->db->delete(
                    PREFIX_DB . AUTO_PREFIX . self::TABLE,
                    self::PK . ' IN (' . implode(',', $ids) . ')'
                );

                //add an history entry
                $hist->add(
                    "Delete vehicles cards",
                    $infos
                );

                //commit all changes
                $zdb->db->commit();
            } catch (Exception $e) {
                $zdb->db->rollBack();
                $log->log(
                    'Unable to delete selected vehicle(s) |' .
                    $e->getMessage(),
                    PEAR_LOG_ERR
                );
                return false;
            }
        } else {
            //not numeric and not an array: incorrect.
            $log->log(
                'Asking to remove vehicles, but without providing an array or a single numeric value.',
                PEAR_LOG_WARNING
            );
            return false;
        }
    }

    /**
    * Get the list of all vehicles
    *
    * @param boolean      $as_autos return the results as an array of Auto object.
    *                                   When true, fields are not relevant
    * @param boolean      $mine     show only current logged member cars
    * @param array|string $fields   field(s) name(s) to get. Should be a string
    *                               or an array. If null, all fields will be returned
    * @param string       $filter   should add filter... TODO
    *
    * @return array|Autos[]
    */
    public static function getList(
        $as_autos=false, $mine=false, $fields=null, $filter=null
    ) {
        global $zdb, $log, $login;

        /** TODO: Check if filter is valid ? */
        if ( $filter != null && trim($filter) != '' ) {
            $this->_filter = $filter;
        }

        $fieldsList = ( $fields != null && !$as_autos )
            ? (( !is_array($fields) || count($fields) < 1 )
                ? (array)'*'
                : implode(', ', $fields))
            : (array)'*';

        try {
            $select = new Zend_Db_Select($zdb->db);
            $select->from(
                PREFIX_DB . AUTO_PREFIX . self::TABLE,
                $fieldsList
            );

            //restict on user self vehicles when not admin, or if admin and
            //requested 'my vehicles'
            if ( $mine == true || !$login->isAdmin() ) {
                $select->where(Adherent::PK . ' = ?', $login->id);
            }

            $results = $select->query()->fetchAll();
            $autos = array();
            if ( $as_autos ) {
                foreach ( $results as $row ) {
                    $autos[] = new Auto($row);
                }
            } else {
                $autos = $results;
            }
            return $autos;
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot list Autos | ' .
                $e->getMessage(),
                PEAR_LOG_ERR
            );
            return false;
        }
    }
}
?>