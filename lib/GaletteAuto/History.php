<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile History class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2023 The Galette Team
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
 * @copyright 2009-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-10-02
 */

namespace GaletteAuto;

use Analog\Analog;
use ArrayObject;
use Galette\Core\Db;
use Galette\Entity\Adherent;

/**
 * Automobile History class for galette Auto plugin
 *
 * @category  Plugins
 * @name      History
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 *
 * @property integer $id_car
 * @property array $fields
 * @property array $entries
 */
class History
{
    private $zdb;
    public const TABLE = 'history';

    //fields list and type
    private $fields = array(
        Auto::PK            => 'integer',
        Adherent::PK        => 'integer',
        'history_date'      => 'datetime',
        'car_registration'  => 'text',
        Color::PK           => 'integer',
        State::PK           => 'integer'
    );

    //history entries
    private $entries;
    private $id_car;

    /**
     * Default constructor
     *
     * @param Db      $zdb Database instance
     * @param integer $id  history entry's id to load. Defaults to null
     */
    public function __construct(Db $zdb, $id = null)
    {
        $this->zdb = $zdb;
        if ($id != null && is_int($id)) {
            $this->load($id);
        }
    }

    /**
     * Loads history for specified car
     *
     * @param integer $id car's id we want history for
     *
     * @return void|false
     */
    public function load($id)
    {
        if ($id == null || !is_int($id)) {
            Analog::log(
                '[' . get_class($this) .
                '] Unable to load car\'s history : Invalid car id (id was: `' .
                $id . '`)',
                Analog::ERROR
            );
            return false;
        }

        $this->id_car = $id;

        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE);
            $select->where(
                array(
                    Auto::PK => $id
                )
            )->order('history_date ASC');

            $results = $this->zdb->execute($select);
            $this->entries = $results->toArray();
            $this->formatEntries();
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot get car\'s history (id was ' .
                $this->id_car . ') | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Get the most recent history entry
     *
     * @return ArrayObject|false row
     */
    public function getLatest()
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE);
            $select->where(
                array(
                    Auto::PK => $this->id_car
                )
            )->order('history_date DESC')->limit(1);

            $results = $this->zdb->execute($select);
            if ($results->count() > 0) {
                return $results->current();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) .
                '] Cannot get car\'s latest history entry | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Format entries dates, also loads Member
     *
     * @return void
     */
    private function formatEntries()
    {
        for ($i = 0; $i < count($this->entries); $i++) {
            //put a formatted date to show
            $date = new \DateTime($this->entries[$i]['history_date']);
            $this->entries[$i]['formatted_date'] = $date->format(__('Y-m-d'));
            //associate member to current history entry
            $this->entries[$i]['owner']
                = new Adherent($this->zdb, (int)$this->entries[$i]['id_adh']);
            //associate color
            $this->entries[$i]['color']
                = new Color($this->zdb, (int)$this->entries[$i]['id_color']);
            //associate state
            $this->entries[$i]['state']
                = new State($this->zdb, (int)$this->entries[$i]['id_state']);
        }
    }

    /**
     * Register a new history entry.
     *
     * @param array $props list of properties to update
     *
     * @return void
     */
    public function register($props)
    {
        Analog::log(
            '[' . get_class($this) . '] Trying to register a new history entry.',
            Analog::DEBUG
        );

        try {
            $fields = $this->fields;
            ksort($fields);
            ksort($props);

            $values = array();
            foreach ($props as $key => $prop) {
                $values[$key] = $prop;
            }

            $insert = $this->zdb->insert(AUTO_PREFIX . self::TABLE);
            $insert->values($values);
            $add = $this->zdb->execute($insert);

            if ($add->count() > 0) {
                Analog::log(
                    '[' . get_class($this) .
                    '] new AutoHistory entry set successfully.',
                    Analog::DEBUG
                );
            } else {
                throw new \Exception(
                    'An error occurred registering car new history entry :('
                );
            }
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot register new histroy entry | ' .
                $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get($name)
    {
        switch ($name) {
            case Auto::PK:
                $ka = Auto::PK;
                return $this->$ka;
            case 'fields':
                return array_keys($this->fields);
            case 'entries':
                return $this->entries;
            default:
                Analog::log(
                    '[' . get_class($this) . '] Trying to get an unknown property (' .
                    $name . ')',
                    Analog::INFO
                );
                break;
        }
    }
}
