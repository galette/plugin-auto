<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Models list for specific brand, ajax powered
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
 * @version   SVN: $Id: models-ajax.php 556 2009-03-13 06:48:49Z trashy $
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */

define('GALETTE_BASE_PATH', '../../');
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
if ( !$login->isLogged() ) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}

//Constants and classes from plugin
require_once '_config.inc.php';
require_once 'classes/auto-models.class.php';

header('Content-Type: application/json;charset=utf-8');
$list = array();

$m = new AutoModels();

if ( isset($_GET['brand']) && $_GET['brand'] != '' ) {
    $list = $m->getListByBrand((int)$_GET['brand']);
} else {
    $list = $m->getList();
}

echo json_encode($list);
?>
