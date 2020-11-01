<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Models repository management
 *
 * PHP version 5
 *
 * Copyright © 2017 The Galette Team
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
 * @category  Repository
 * @package   GaletteAuto
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     2017-07-24
 */

namespace GaletteAuto\Repository;

use Galette\Core\Db;
use Galette\Core\Preferences;
use Galette\Core\Login;
use Galette\Repository\Repository;
use GaletteAuto\Model;
use GaletteAuto\Brand;
use GaletteAuto\Filters\ModelsList;
use Analog\Analog;
use Laminas\Db\Sql\Expression;

/**
 * Models repository management
 *
 * @category  Repository
 * @name      Models
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2017-07-24
 */

class Models extends Repository
{
    public const TABLE = Model::TABLE;
    public const PK = Model::PK;

    private $count;

    /**
     * Main constructor
     *
     * @param Db          $zdb         Database instance
     * @param Preferences $preferences Galette preferences
     * @param Login       $login       Logged in instance
     * @param ModelsList  $filters     Filters
     */
    public function __construct(Db $zdb, Preferences $preferences, Login $login, ModelsList $filters)
    {
        parent::__construct($zdb, $preferences, $login, null, 'GaletteAuto', AUTO_PREFIX);
        $this->setFilters($filters);
    }

    /**
     * Get the list of all models as an array
     *
     * @param Db $zdb Database instance
     *
     * @return array
     */
    /*public static function getArrayList($zdb)
    {
        $otitles = self::getList($zdb);
        $titles = array();
        foreach ($otitles as $t) {
            $titles[$t->id] = $t->short;
        }
        return $titles;
    }*/

    /**
     * Get the list of all models
     *
     * @param integer $brandId   Optionnal brand we want models for
     * @param boolean $as_object Whether to return an array f objects or a ResultSet
     *
     * @return Model[]
     */
    public function getList($brandId = null, $as_object = true)
    {
        $select = $this->buildSelect();

        if ($brandId !== null) {
            $select->where(
                array(
                    'm.' . Brand::PK => $brandId
                )
            );
        } else {
            $this->filters->setLimits($select);
        }
        $results = $this->zdb->execute($select);

        if ($as_object) {
            $models = array();
            foreach ($results as $r) {
                $pk = self::PK;
                $models[$r->$pk] = new Model($this->zdb, $r);
            }
            return $models;
        } else {
            return $results;
        }
    }

    /**
     * Builds the SELECT statement
     *
     * @return string SELECT statement
     */
    private function buildSelect()
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . self::TABLE, 'm');
            $select->join(
                array('b' => PREFIX_DB . AUTO_PREFIX . Brand::TABLE),
                'm.' . Brand::PK . '= b.' . Brand::PK
            );
            $select->order(self::buildOrderClause());
            $this->proceedCount($select);

            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for models | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Builds the order clause
     *
     * @return string SQL ORDER clause
     */
    private function buildOrderClause()
    {
        $order = array();

        switch ($this->filters->orderby) {
            case ModelsList::ORDERBY_BRAND:
                $order[] = 'b.brand ' . $this->filters->ordered;
                break;
            default:
            case ModelsList::ORDERBY_MODEL:
                $order[] = 'm.model ' . $this->filters->ordered;
                break;
        }

        return $order;
    }

    /**
     * Count contributions from the query
     *
     * @param Select $select Original select
     *
     * @return void
     */
    private function proceedCount($select)
    {
        try {
            $countSelect = clone $select;
            $countSelect->reset($countSelect::COLUMNS);
            $countSelect->reset($countSelect::JOINS);
            $countSelect->reset($countSelect::ORDER);
            $countSelect->columns(
                array(
                    self::PK => new Expression('COUNT(' . self::PK . ')')
                )
            );

            $results = $this->zdb->execute($countSelect);
            $result = $results->current();

            $k = self::PK;
            $this->count = $result->$k;

            if ($this->count > 0) {
                $this->filters->setCounter($this->count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count models | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Add default values in database
     *
     * @param boolean $check_first Check first if it seem initialized, defaults to true
     *
     * @return boolean
     */
    public function installInit($check_first = true)
    {
        return true;
    }

    /**
     * Get count for current query
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
