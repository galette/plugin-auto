<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Manage/list vehicles
 *
 * This page should be loaded directly, or via ajax.
 * Via ajax, we do not have a full html page, but only
 * that will be displayed using javascript on another page
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
 * @version   SVN: $Id: vehicles.php 556 2009-05-12 07:30:49Z trashy $
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */

use GaletteAuto\Autos;
use GaletteAuto\AutosList;

define('GALETTE_BASE_PATH', '../../');
if ( !isset($mine) ) {
    $mine = false;
}
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
if ( !$login->isLogged()
    || (!$mine && !$login->isAdmin()
    && !$login->isStaff())
) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}

$id_adh = null;
if ( isset($_GET['id_adh']) && ($login->isAdmin() || $login->isStaff()) ) {
    $id_adh = $_GET['id_adh'];
}

if ( isset($_POST['donew']) ) {
    if ( $mine ) {
        header('location: my_vehicles_edit.php');
    } else {
        $location = 'vehicles_edit.php';
        if ( isset($_POST['id_adh']) ) {
            $location .= '?id_adh=' . $_POST['id_adh'];
        }
        header('location: ' . $location);
    }
}

$numrows = $preferences->pref_numrows;
if ( isset($_GET["nbshow"]) ) {
    if ( is_numeric($_GET["nbshow"]) ) {
        $numrows = $_GET["nbshow"];
    }
}

//Constants from plugin
require_once '_config.inc.php';

$auto = new Autos();

if (isset($_GET['sup']) || isset($_POST['delete'])) {
    if ( isset($_GET['sup']) ) {
        $auto->removeVehicles($_GET['sup']);
    } else if ( isset($_POST['vehicle_sel']) ) {
        $auto->removeVehicles($_POST['vehicle_sel']);
    }
}

$title = _T("Cars list");
if ( $mine == 1 ) {
    $title = _T("My Cars");
}
if ( $id_adh !== null ) {
    $title = _T("Member's cars");
}
$tpl->assign('page_title', $title);
//Set the path to the current plugin's templates,
//but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->compile_id = AUTO_SMARTY_PREFIX;

$afilters = new AutosList();

// Simple filters
if (isset($_GET['page'])) {
    $afilters->current_page = (int)$_GET['page'];
}

$title = _T("Vehicles list");
$tpl->assign('title', $title);
if ( $id_adh === null ) {
    $tpl->assign('autos', $auto->getList(true, $mine, null, $afilters));
} else {
    $tpl->assign('id_adh', $id_adh);
    $tpl->assign('autos', $auto->getMemberList($id_adh, $afilters));
}
$tpl->assign('show_mine', $mine);

//assign pagination variables to the template and add pagination links
$afilters->setSmartyPagination($tpl);

$content = $tpl->fetch('vehicles_list.tpl', AUTO_SMARTY_PREFIX);
$tpl->assign('content', $content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display('page.tpl', AUTO_SMARTY_PREFIX);
