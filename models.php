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
 * Gestion des Modèles
 *
 * @package    Galette
 * @author     Johan Cwiklinski
 * @copyright  2009 Johan Cwiklinski
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version    $Id: modeles.php 556 2009-03-13 06:48:49Z trashy $
 * @since      Disponible depuis la Release 0.7alpha
 */

$base_path = '../../';
require_once( $base_path . 'includes/galette.inc.php');
if( !$login->isLogged() || !$login->isAdmin() ) {
	header('location: ' . $base_path . 'index.php');
	die();
}

$numrows = PREF_NUMROWS;
if (isset($_GET["nbshow"]))
	if (is_numeric($_GET["nbshow"]))
		$numrows = $_GET["nbshow"];

$is_new = ( get_numeric_form_value('new', '') == 1  || isset($_POST['donew']) || isset($_GET['donew']) ) ? true : false;
$set = get_form_value('set', null);

//Constants and classes from plugin
require_once('_config.inc.php');
require_once('classes/auto-models.class.php');

$model = new AutoModels();
$title = ( $is_new ) ? _T("New model") : _T("Change model '%s'");

//We have a new or a modified object
if( get_numeric_form_value( 'modif', 0) == 1 || get_numeric_form_value('new', 0) == 1 && !isset($_POST['cancel']) ){
	// initialize warnings
	$error_detected = array();
	$warning_detected = array();
	$confirm_detected = array();

	if( !$is_new && get_numeric_form_value(AutoModels::PK, null) != null ) {
		$model->load( get_numeric_form_value(AutoModels::PK, '') );
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
	}
}else if( isset($_POST['cancel']) ){
	unset($_POST['new']);
	$is_new = false;
	unset($_POST['modif']);
}

//Delete cases. $_GET['sup']} to delete from a single line ; $_POST["delete"] to delete from checkboxes
if (isset($_GET["sup"]) || isset($_POST["delete"])) {
	$del = array();
	if( isset($_GET["sup"]) )
		$del[] = $_GET["sup"];
	else
		foreach ($_POST["_sel"] as $supval)
			if (is_numeric($supval))
				$del[] = $supval;

	$model->delete($del);
}

if(isset($error_detected))
	$tpl->assign("error_detected",$error_detected);

//Set the path to the current plugin's templates, but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->assign('mode', (($is_new) ? 'new' : 'modif'));
$tpl->compile_id = SMARTY_PREFIX;

if( isset( $_GET[AutoModels::PK] ) || $is_new ) {
	if( !$is_new ){
		$model->load( get_numeric_form_value(AutoModels::PK, '') );
		$title = str_replace('%s', $model->model, $title);
	}
	if( isset($_GET['brand']) ) $model->brand = $_GET['brand'];
	$tpl->assign('title', $title);
	$tpl->assign('model', $model);
	require_once('classes/auto-brands.class.php');
	$b = new AutoBrands();
	$tpl->assign('brands', $b->getList());
	$content = $tpl->fetch('model.tpl', AUTO_SMARTY_PREFIX);
} else {
	$title = _T("Models list");
	$tpl->assign('title', $title);
	$tpl->assign('models', $model->getList());
	$content = $tpl->fetch('models_list.tpl', AUTO_SMARTY_PREFIX);
}
$tpl->assign('content', $content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display('page.tpl', AUTO_SMARTY_PREFIX);
?>