<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile autos class for galette Auto plugin
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
 * @since     Available since 0.7dev - 2009-09-26
 */

namespace GaletteAuto;

use Analog\Analog;
use Galette\Entity\Adherent;
use Zend\Db\Sql\Expression;

/**
 * Automobile autos class for galette Auto plugin
 *
 * @category  Plugins
 * @name      Autos
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */
class Autos
{
    const TABLE = Auto::TABLE;
    const PK = Auto::PK;

    private $_filter = null;
    private $_count = null;

    /**
     * Remove specified vehicles
     *
     * @param interger|array $ids Vehicles identifiers to delete
     *
     * @return boolean
     */
    public function removeVehicles($ids)
    {
        global $zdb, $hist;

        $list = array();
        if ( is_numeric($ids) ) {
            //we've got only one identifier
            $list[] = $ids;
        } else {
            $list = $ids;
        }

        if ( is_array($list) ) {
            try {
                $zdb->connection->beginTransaction();

                //Retrieve some informations
                $select = $zdb->select(AUTO_PREFIX . self::TABLE, 'a');
                $select->columns(
                    array(
                        self::PK,
                        'car_name'
                    )
                )->join(
                    array('b' => PREFIX_DB . AUTO_PREFIX . Model::TABLE),
                    'a.' . Model::PK . ' = b.' . Model::PK,
                    array('model')
                )->join(
                    array('c' => PREFIX_DB . AUTO_PREFIX . Brand::TABLE),
                    'b.' . Brand::PK . ' = c.' . Brand::PK,
                    array('brand')
                )->where->in(self::PK, $ids);

                $vehicles = $zdb->execute($select);

                $infos = null;
                foreach ($vehicles as $vehicle ) {
                    $str_v = $vehicle->id_car . ' - ' . $vehicle->car_name .
                        ' (' . $vehicle->brand . ' ' . $vehicle->model . ')';
                    $infos .=  $str_v . "\n";

                    $p = new Picture($vehicle->id_car);
                    if ( $p->hasPicture() ) {
                        if ( !$p->delete() ) {
                            Analog::log(
                                'Unable to delete picture for vehicle ' .
                                $str_v,
                                Analog::ERROR
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
                $delete = $zdb->delete(AUTO_PREFIX . self::TABLE);
                $delete->where->in(self::PK, $ids);
                $zdb->execute($delete);

                //add an history entry
                $hist->add(
                    _T("Delete vehicles cards"),
                    $infos
                );

                //commit all changes
                $zdb->connection->commit();
            } catch (\Exception $e) {
                $zdb->connection->rollBack();
                Analog::log(
                    'Unable to delete selected vehicle(s) |' .
                    $e->getMessage(),
                    Analog::ERROR
                );
                return false;
            }
        } else {
            //not numeric and not an array: incorrect.
            Analog::log(
                'Asking to remove vehicles, but without providing an array or a single numeric value.',
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Get vehicles list for specified member
     *
     * @param int   $id_adh  Members id
     * @param array $filters Filters
     *
     * @return array
     */
    public function getMemberList($id_adh, $filters)
    {
        return $this->getList(true, false, null, $filters, $id_adh);
    }

    /**
     * Get the list of all vehicles
     *
     * @param boolean      $as_autos return the results as an array of Auto object.
     *                                   When true, fields are not relevant
     * @param boolean      $mine     show only current logged member cars
     * @param array|string $fields   field(s) name(s) to get. Should be a string
     *                               or an array. If null, all fields will be returned
     * @param AutosList    $filters  Filters
     * @param int          $id_adh   Member id
     *
     * @return array|Autos[]
     */
    public function getList(
        $as_autos=false, $mine=false, $fields=null, $filters=null, $id_adh = null
    ) {
        global $zdb, $login;

        $fieldsList = ( $fields != null && !$as_autos )
            ? (( !is_array($fields) || count($fields) < 1 )
                ? (array)'*'
                : implode(', ', $fields))
            : (array)'*';

        try {
            $select = $zdb->select(AUTO_PREFIX . self::TABLE, 'a');
            $select->columns($fieldsList);

            //restict on user self vehicles when not admin, or if admin and
            //requested 'my vehicles'
            if ( $mine == true || !$login->isAdmin() ) {
                $select->where(
                    array(
                        Adherent::PK => $login->id
                    )
                );
            }

            //restrict on specified user vehicles if an id has been provided
            if ( $id_adh !== null ) {
                $select->where(
                    array(
                        Adherent::PK => $id_adh
                    )
                );
            }

            $this->_proceedCount($select, $filters);

            if ( $filters !== null ) {
                $filters->setLimit($select);
            }

            $results = $zdb->execute($select);
            $autos = array();
            if ( $as_autos ) {
                foreach ( $results as $row ) {
                    $autos[] = new Auto($row);
                }
            } else {
                $autos = $results;
            }
            return $autos;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot list Autos | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Count vehicles from the query
     *
     * @param Zend_Db_Select $select  Original select
     * @param AutosList      $filters Filters
     *
     * @return void
     */
    private function _proceedCount($select, $filters)
    {
        global $zdb;

        try {
            $countSelect = clone $select;
            $countSelect->reset($countSelect::COLUMNS);
            $countSelect->reset($countSelect::ORDER);
            $countSelect->reset($countSelect::HAVING);
            $countSelect->columns(
                array(
                    'count' => new Expression('count(DISTINCT a.' . self::PK . ')')
                )
            );

            $have = $select->having;
            if ( $have->count() > 0 ) {
                foreach ( $have->getPredicates() as $h ) {
                    $countSelect->where($h);
                }
            }

            $results = $zdb->execute($countSelect);
            $this->_count = $results->current()->count;
            if ( isset($filters) && $this->_count > 0 ) {
                $filters->setCounter($this->_count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count vehicles | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }
}
