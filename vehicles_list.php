<?php

// Copyright © 2009 Johan Cwiklinski
//
// This file is part of Galette (http://galette.tuxfamily.org).
//
// Galette is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Galette is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Galette. If not, see <http://www.gnu.org/licenses/>.

/** 
 * Manage/list vehicles
 *
 * @package    Galette
 * @author     Johan Cwiklinski
 * @copyright  2009 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id: vehicles.php 556 2009-05-12 07:30:49Z trashy $
 * @since      Disponible depuis la Release 0.7alpha
 */

$base_path = '../../';
require_once( $base_path . 'includes/galette.inc.php');
if( !$login->isLogged() || !$login->isAdmin() ) {
	header("location: index.php");
	die();
}

if( isset($_POST['donew']) ){
	header('location: vehicles_edit.php');
}


$numrows = PREF_NUMROWS;
if (isset($_GET["nbshow"]))
	if (is_numeric($_GET["nbshow"]))
		$numrows = $_GET["nbshow"];

$is_new = ( get_numeric_form_value('new', '') == 1  || isset($_POST['donew']) || isset($_GET['donew']) ) ? true : false;
$set = get_form_value('set', null);

//Constants and classes from plugin
require_once('_config.inc.php');

require_once('classes/autos.class.php');
$auto = new Autos();

//Set the path to the current plugin's templates, but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;

$title = _T("Vehicles list");
$tpl->assign('title', $title);
$tpl->assign('autos', $auto->getList(true));
$content = $tpl->fetch("vehicles_list.tpl");

$tpl->assign("content",$content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display("page.tpl");
?>