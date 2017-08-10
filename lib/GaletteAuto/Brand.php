<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Automobile Brands class for galette Auto plugin
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

/**
 * Automobile Brands class for galette Auto plugin
 *
 * @category  Plugins
 * @name      AutoBrands
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2014 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class Brand extends AbstractObject
{
    const TABLE = 'brands';
    const PK = 'id_brand';
    const FIELD = 'brand';
    const NAME = 'brands';

    /**
    * Default constructor
    *
    * @param Db      $zdb Database instance
    * @param integer $id  brand's id to load. Defaults to null
    */
    public function __construct(Db $zdb, $id = null)
    {
        parent::__construct(
            $zdb,
            self::TABLE,
            self::PK,
            self::FIELD,
            self::NAME,
            $id
        );
    }

    /**
    * List of models for a specific brand
    *
    * @param integer $brand Brand identifier
    *
    * @return ResultSet
    */
    public function getModels($brand)
    {
        try {
            $select = $this->zdb->select(AUTO_PREFIX . Model::TABLE);
            $select->where(
                array(
                    self::PK => $brand
                )
            )->order(Model::FIELD . ' ASC');

            $results = $this->zdb->execute($select);
            return $results;
        } catch (\Exception $e) {
            Analog::log(
                '[' . get_class($this) . '] Cannot load models list | ' .
                $e->getMessage(),
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
        if ($name == self::FIELD) {
            return parent::__get('field');
        }
        if ($name == self::PK) {
            return parent::__get('id');
        } else {
            return parent::__get($name);
        }
    }
}
