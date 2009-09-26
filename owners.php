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
 * Owners list
 * Make possible to change a car's owner
 *
 * This page should be loaded directly, or via ajax.
 * Via ajax, we do not have a full html page, but only 
 * that will be displayed using javascript on another page
 *
 * @package    Galette
 * @author     Johan Cwiklinski
 * @copyright  2009 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id: owners.php 556 2009-03-13 06:48:49Z trashy $
 * @since      Disponible depuis la Release 0.7alpha
 */

$base_path = '../../';
require_once($base_path . 'includes/galette.inc.php');
if ( !$login->isLogged() )
{
	header("location: index.php");
	die();
}

// check for ajax mode
$ajax = ( isset($_GET['ajax']) && $_GET['ajax'] == 'true' ) ? true : false;

require_once(WEB_ROOT . 'classes/members.class.php');

$owners = Members::getList(true);

//Set the path to the current plugin's templates, but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->assign('ajax', $ajax);
$tpl->assign('owners', $owners);

if( $ajax ){
	$tpl->assign('mode', 'ajax');
	$tpl->display("owners.tpl");
} else {
	$content = $tpl->fetch("owners.tpl");
	$tpl->assign("content",$content);
	//Set path to main Galette's template
	$tpl->template_dir = $orig_template_path;
	$tpl->display("page.tpl");
}
?>