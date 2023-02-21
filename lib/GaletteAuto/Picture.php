<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Logo handling
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
 * @since     Available since 0.7dev - 2009-09-26
 */

namespace GaletteAuto;

use Analog\Analog;
use Galette\Core\Picture as GalettePicture;
use Galette\Core\Plugins;

/**
 * Logo handling
 *
 * @category  Plugins
 * @name      AutoPicture
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class Picture extends GalettePicture
{
    private $plugins;

    protected $tbl_prefix = AUTO_PREFIX;
    public const PK = Auto::PK;

    /**
    * Default constructor.
    *
    * @param Plugins    $plugins Plugins
    * @param mixed|null $id_adh  ID of the member
    */
    public function __construct(Plugins $plugins, $id_adh = null)
    {
        $this->plugins = $plugins;
        $this->store_path = GALETTE_PHOTOS_PATH . '/auto_photos/';
        if (!file_exists($this->store_path)) {
            if (!mkdir($this->store_path)) {
                Analog::log(
                    'Unable to create photo dir `' . $this->store_path . '`.',
                    Analog::ERROR
                );
            } else {
                Analog::log(
                    'New directory `' . $this->store_path . '` has been created',
                    Analog::INFO
                );
            }
        } elseif (!is_dir($this->store_path)) {
            Analog::log(
                'Unable to store plugin images, since `' . $this->store_path .
                '` is not a directory.',
                Analog::WARNING
            );
        }
        parent::__construct($id_adh);
    }

    /**
    * Gets the default picture to show, anyways
    *
    * @see Logo::getDefaultPicture()
    *
    * @return void
    */
    protected function getDefaultPicture()
    {
        $this->file_path = realpath(
            $this->plugins->getTemplatesPathFromName('Galette Auto') .
            '/../../webroot/images/car.png'
        );
        $this->format = 'png';
        $this->mime = 'image/png';
        $this->has_picture = false;
    }
}
