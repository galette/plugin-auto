<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Logo handling
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
 * @since     Available since 0.7dev - 2009-09-26
 */

require_once WEB_ROOT . 'classes/picture.class.php';
require_once '_config.inc.php';
require_once 'auto.class.php';

/**
 * Logo handling
 *
 * @category  Plugins
 * @name      AutoPicture
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2009-2011 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-03-16
 */
class AutoPicture extends Picture
{
    protected $tbl_prefix = AUTO_PREFIX;
    const PK = Auto::PK;
    //path is relative to Picture class, not to AutoPicture
    protected $store_path = '../plugins/Auto/auto_photos/';

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
        $this->file_path = $plugins->getTemplatesPath('Auto') . '/images/car.png';
        $this->format = 'png';
        $this->mime = 'image/png';
        $this->has_picture = false;
    }
}
?>
