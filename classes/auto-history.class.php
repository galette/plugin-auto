<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile History class for galette Auto plugin
 *
 * PHP version 5
 *
 * Copyright © 2009-2011 The Galette Team
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
 * @copyright 2009-2011 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-10-02
 */

require_once WEB_ROOT . 'classes/adherent.class.php';
require_once 'auto.class.php';
require_once 'auto-colors.class.php';
require_once 'auto-states.class.php';

/**
 * Automobile History class for galette Auto plugin
 *
 * @category  Plugins
 * @name      AutoHistory
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2011 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class AutoHistory
{
    const TABLE = 'history';

    //fields list and type
    private $_fields = array(
        Auto::PK            => 'integer',
        Adherent::PK        => 'integer',
        'history_date'      => 'datetime',
        'car_registration'  => 'text',
        AutoColors::PK      => 'integer',
        AutoStates::PK      => 'integer'
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
        global $log;
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
        global $zdb, $log;

        if ( $id == null || !is_int($id) ) {
            $log->log(
                '[' . get_class($this) .
                '] Unable to load car\'s history : Invalid car id (id was: `' .
                $id . '`)',
                PEAR_LOG_ERR
            );
            return false;
        }

        $this->_id_car = $id;

        try {
            $select = new Zend_Db_Select($zdb->db);
            $select->from(PREFIX_DB . AUTO_PREFIX . self::TABLE)
                ->where(Auto::PK . ' = ?', $id)
                ->order('history_date ASC');

            $this->_entries = $select->query()->fetchAll();
            $this->_formatEntries();
        } catch (Exception $e) {
            $log->log(
                '[' . get_class($this) . '] Cannot get car\'s history (id was ' .
                $this->_id_car . ') | ' . $e->getMessage(),
                PEAR_LOG_ERR
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
        global $mdb, $log;

        $query = 'SELECT * FROM ' . PREFIX_DB . AUTO_PREFIX . self::TABLE .
            ' WHERE ' . Auto::PK . '=' . $this->_id_car .
            ' ORDER BY history_date DESC LIMIT 1';

        $result = $mdb->query($query);
        if ( MDB2::isError($result) ) {
            $log->log(
                '[' . get_class($this) .
                '] Cannot get car\'s latest history entry | ' .
                $result->getMessage() . '(' . $result->getDebugInfo() . ')',
                PEAR_LOG_ERR
            );
            return false;
        }

        return $result->fetchRow();
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
            $this->_entries[$i]->formatted_date = strftime(
                '%d %B %Y',
                strtotime($this->_entries[$i]->history_date)
            );
            //associate member to current history entry
            $this->_entries[$i]->owner
                = new Adherent((int)$this->_entries[$i]->id_adh);
            //associate color
            $this->_entries[$i]->color
                = new AutoColors((int)$this->_entries[$i]->id_color);
            //associate state
            $this->_entries[$i]->state
                = new AutoStates((int)$this->_entries[$i]->id_state);
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
        global $mdb, $log;
        $log->log(
            '[' . get_class($this) . '] Trying to register a new history entry.',
            PEAR_LOG_DEBUG
        );

        $fields = $this->_fields;
        ksort($fields);
        ksort($props);
        $query = 'INSERT INTO ' . PREFIX_DB . AUTO_PREFIX . self::TABLE . ' (' .
            implode(', ', array_keys($fields)) . ') VALUES (';
        foreach ( $props as $key=>$prop ) {
            $query .= ($this->_fields[$key] == 'integer')
                ? $prop
                : $mdb->quote($prop);
            if ( end(array_keys($fields)) != $key ) {
                $query .= ', ';
            }
        }
        $query .= ')';

        $result = $mdb->query($query);
        if ( MDB2::isError($result) ) {
            $log->log(
                '[' . get_class($this) . '] Cannot register new histroy entry | ' .
                $result->getMessage() . '(' . $result->getDebugInfo() . ')',
                PEAR_LOG_ERR
            );
            return false;
        } else {
            $log->log(
                '[' . get_class($this) . '] new AutoHistory entry set successfully.',
                PEAR_LOG_DEBUG
            );
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
        switch($name){
        case Auto::PK:
            $k = Auto::PK;
            return $this->$k;
            break;
        case 'fields':
            return array_keys($this->_fields);
            break;
        case 'entries':
            return $this->_entries;
            break;
        default:
            $log->log(
                '[' . get_class($this) . '] Trying to get an unknown property (' .
                $name . ')',
                PEAR_LOG_INFO
            );
            break;
        }
    }
}
?>