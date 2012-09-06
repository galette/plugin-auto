<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Logo handling
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

use Galette\Common\KLogger;
use Galette\Core\Picture;

require_once '_config.inc.php';
require_once 'auto.class.php';

/**
 * Logo handling
 *
 * @category  Plugins
 * @name      AutoPicture
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2012 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class AutoPicture extends Picture
{
    protected $tbl_prefix = AUTO_PREFIX;
    const PK = Auto::PK;

    /**
    * Default constructor.
    *
    * @param int $id_adh the id of the member
    */
    public function __construct( $id_adh='' )
    {
        $this->store_path = GALETTE_PHOTOS_PATH . '/auto_photos/';
        if ( !file_exists($this->store_path) ) {
            if ( !mkdir($this->store_path) ) {
                KLogger::log(
                    'Unable to create photo dir `' . $this->store_path . '`.',
                    KLogger::ERR
                );
            } else {
                KLogger::log(
                    'New directory `' . $this->store_path . '` has been created',
                    KLogger::INFO
                );
            }
        } else if ( !is_dir($this->store_path) ) {
            KLogger::log(
                'Unable to store plugin images, since `' . $this->store_path .
                '` is not a directory.',
                KLogger::WARN
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
        global $plugins;
        $this->file_path = $plugins->getTemplatesPathFromName('Galette Auto') . '/images/car.png';
        $this->format = 'png';
        $this->mime = 'image/png';
        $this->has_picture = false;
    }
}
?>
