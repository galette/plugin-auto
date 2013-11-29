<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile History class for galette Auto plugin
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
 * @since     Available since 0.7dev - 2009-10-02
 */

namespace GaletteAuto;

use Analog\Analog as Analog;
use Galette\Entity\Adherent;

/**
 * Automobile History class for galette Auto plugin
 *
 * @category  Plugins
 * @name      History
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2013 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class History
{
    const TABLE = 'history';

    //fields list and type
    private $_fields = array(
        Auto::PK            => 'integer',
        Adherent::PK        => 'integer',
        'history_date'      => 'datetime',
        'car_registration'  => 'text',
        Color::PK           => 'integer',
        State::PK           => 'integer'
    );

    //history entries
    private $_entries;
    private $_id_car;

    /**
    * Default constructor
    *
    * @param integer $id history entry's id to load. Defaults to null
    */
    public function __construct($id = null)
    {
        if ( $id != null && is_int($id) ) {
            $this->load($id);
        }
    }

    /**
    * Loads history for specified car
    *
    * @param integer $id car's id we want history for
    *
    * @return void
    */
    public function load($id)
    {
        global $zdb;

        if ( $id == null || !is_int($id) ) {
            Analog::log(
                '[' . get_class($this) .
                '] Unable to load car\'s history : Invalid car id (id was: `' .
                $id . '`)',
                Analog::ERROR
            );
            return false;
        }

        $this->_id_car = $id;

        try {
            $select = new \Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . AUTO_PREFIX . self::TABLE)
                ->where(Auto::PK . ' = ?', $id)
                ->order('history_date ASC');

            $this->_entries = $select->query()->fetchAll();
            $this->_formatEntries();
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot get car\'s history (id was ' .
                $this->_id_car . ') | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
    * Get the most recent history entry
    *
    * @return ResultSet row
    */
    public function getLatest()
    {
        global $zdb;

        try {
            $select = new \Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . AUTO_PREFIX . self::TABLE)
                ->where(Auto::PK . ' = ?', $this->_id_car)
                ->order('history_date DESC')
                ->limit(1);
            return $select->query()->fetch();
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
    private function _formatEntries()
    {
        for ( $i = 0 ; $i < count($this->_entries); $i++ ) {
            //put a formatted date to show
            //strftime output is ISO-8859-1...
            $this->_entries[$i]->formatted_date = utf8_encode(
                strftime(
                    '%d %B %Y',
                    strtotime($this->_entries[$i]->history_date)
                )
            );
            //associate member to current history entry
            $this->_entries[$i]->owner
                = new Adherent((int)$this->_entries[$i]->id_adh);
            //associate color
            $this->_entries[$i]->color
                = new Color((int)$this->_entries[$i]->id_color);
            //associate state
            $this->_entries[$i]->state
                = new State((int)$this->_entries[$i]->id_state);
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
        global $zdb;

        Analog::log(
            '[' . get_class($this) . '] Trying to register a new history entry.',
            Analog::DEBUG
        );

        try {
            $fields = $this->_fields;
            ksort($fields);
            ksort($props);

            $values = array();
            foreach ( $props as $key=>$prop ) {
                $values[$key] = $prop;
            }

            $add = $zdb->db->insert(
                PREFIX_DB . AUTO_PREFIX . self::TABLE,
                $values
            );

            if ( $add > 0 ) {
                Analog::log(
                    '[' . get_class($this) .
                    '] new AutoHistory entry set successfully.',
                    Analog::DEBUG
                );
            } else {
                throw new \Exception(
                    'An error occured registering car new history entry :('
                );
            }
        } catch (Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot register new histroy entry | ' .
                $e->getMessage(),
                Analog::ERROR
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
        switch($name){
        case Auto::PK:
            $ka = Auto::PK;
            $k = '_' . $ka;
            return $this->$k;
            break;
        case 'fields':
            return array_keys($this->_fields);
            break;
        case 'entries':
            return $this->_entries;
            break;
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
