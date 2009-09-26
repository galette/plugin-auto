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
 * Add/Edit vehicles
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

$numrows = PREF_NUMROWS;
if (isset($_GET["nbshow"]))
	if (is_numeric($_GET["nbshow"]))
		$numrows = $_GET["nbshow"];

//Constants and classes from plugin
require_once('_config.inc.php');

require_once('classes/auto.class.php');

$is_new = ( isset($_GET[Auto::PK]) && is_int($_GET[Auto::PK]) ) ? false : true;
$set = get_form_value('set', null);

$auto = new Auto();
$title = ( $is_new ) ? _T("New vehicle") : _T("Change vehicle '%s'");

//We have a new or a modified object
if( get_numeric_form_value( 'modif', 0) == 1 || get_numeric_form_value('new', 0) == 1 && !isset($_POST['cancel']) ){
	// initialize warnings
	$error_detected = array();
	$warning_detected = array();
	$confirm_detected = array();

	/*if( !$is_new && get_numeric_form_value(Auto::PK, null) != null ) {
		$auto->load( get_numeric_form_value(Auto::PK, '') );
	} else if(!$is_new) {
		$error_detected[] = _T("- No id provided for modifying this record! (internal)");
	}

	$model->model = get_form_value('model', null);
	$brand_id = get_numeric_form_value('brand', null);

	if( $brand_id == -1 )
		$error_detected[] = _T("- You must select a brand!");
	else
		$model->brand = $brand_id;

	if( $model->model == '' )
		$error_detected[] = _T("- You must provide a value!");

	if( count($error_detected) == 0 ){
		$res = $model->store( $is_new );
		if( !$res )
			$error_detected[] = _T("- An error occured while saving record. Please try again.");
		else
			header('location:models.php');
	}*/
}else if( isset($_POST['cancel']) ){
	unset($_POST['new']);
	$is_new = false;
	unset($_POST['modif']);
}

if(isset($error_detected))
	$tpl->assign("error_detected",$error_detected);

//Set the path to the current plugin's templates, but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->assign('mode', (($is_new) ? 'new' : 'modif'));

if( !$is_new ){
	$auto->load( get_numeric_form_value(Auto::PK, '') );
	//$title = str_replace('%s', $auto->model, $title);
}
$tpl->assign('require_calendar', true);
$tpl->assign('require_dialog', true);
$tpl->assign('title', $title);
$tpl->assign('car', $auto);
$models = new AutoModels();
$tpl->assign('models', $models->getList());
$brands = new AutoBrands();
$tpl->assign('brands', $brands->getList());
$colors = new AutoColors();
$tpl->assign('colors', $colors->getList());
$bodies = new AutoBodies();
$tpl->assign('bodies', $bodies->getList());
$transmissions = new AutoTransmissions();
$tpl->assign('transmissions', $transmissions->getList());
$finitions = new AutoFinitions();
$tpl->assign('finitions', $finitions->getList());
$tpl->assign('fuels', $auto->listFuels());
$content = $tpl->fetch("vehicles.tpl");

$tpl->assign("content",$content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display("page.tpl");
?>