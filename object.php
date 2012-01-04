<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * "Objects" management (colors, transmissions, finitions, bodies, states, brands)
 *
 * This page should be loaded directly, or via ajax.
 * Via ajax, we do not have a full html page, but only
 * that will be displayed using javascript on another page
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
 * @version   SVN: $Id: object.php 556 2009-03-13 06:48:49Z trashy $
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */

$base_path = '../../';
require_once $base_path . 'includes/galette.inc.php';
if ( !$login->isLogged() || !$login->isAdmin() ) {
    header('location: ' . $base_path . 'index.php');
    die();
}

$numrows = $preferences->pref_numrows;
if ( isset($_GET['nbshow']) ) {
    if ( is_numeric($_GET['nbshow']) ) {
        $numrows = $_GET['nbshow'];
    }
}

$is_new = ( get_numeric_form_value('new', '') == 1
    || isset($_POST['donew']) || isset($_GET['addnew']) )
    ? true
    : false;
$set = get_form_value('set', null);

//Constants and classes from plugin
require_once '_config.inc.php';

switch ( $set ) {
case 'colors':
    include_once 'classes/auto-colors.class.php';
    $obj = new AutoColors();
    $title = ( $is_new )
        ? _T("New color")
        : ( isset($_GET[AutoColors::PK])
            ? _T("Change color '%s'")
            : _T("Colors list") );
    $field_name = _T("Color");
    $add_text = _T("Add new color");
    $deletes_text = _T("Do you really want to delete selected colors?");
    $delete_text = _T("Do you really want to delete the color '%s'?");
    break;
case 'states':
    include_once 'classes/auto-states.class.php';
    $obj = new AutoStates();
    $title = ( $is_new )
        ? _T("New state")
        : ( isset($_GET[AutoStates::PK])
            ? _T("Change state '%s'")
            : _T("States list") );
    $field_name = _T("State");
    $add_text = _T("Add new state");
    $deletes_text = _T("Do you really want to delete selected states?");
    $delete_text = _T("Do you really want to delete the state '%s'?");
    break;
case 'finitions':
    include_once 'classes/auto-finitions.class.php';
    $obj = new AutoFinitions();
    $title = ( $is_new )
        ? _T("New finition")
        : ( isset($_GET[AutoFinitions::PK])
            ? _T("Change finition '%s'")
            : _T("Finitions list") );
    $field_name = _T("Finition");
    $add_text = _T("Add new finition");
    $deletes_text = _T("Do you really want to delete selected finitions?");
    $delete_text = _T("Do you really want to delete the finition '%s'?");
    break;
case 'bodies':
    include_once 'classes/auto-bodies.class.php';
    $obj = new AutoBodies();
    $title = ( $is_new )
        ? _T("New body")
        : ( isset($_GET[AutoBodies::PK])
            ? _T("Change body '%s'")
            : _T("Bodies list") );
    $field_name = _T("Body");
    $add_text = _T("Add new body");
    $deletes_text = _T("Do you really want to delete selected bodies?");
    $delete_text = _T("Do you really want to delete the body '%s'?");
    break;
case 'transmissions':
    include_once 'classes/auto-transmissions.class.php';
    $obj = new AutoTransmissions();
    $title = ( $is_new )
        ? _T("New transmission")
        : ( isset($_GET[AutoTransmissions::PK])
            ? _T("Change transmission '%s'")
            : _T("Transmissions list") );
    $field_name = _T("Transmission");
    $add_text = _T("Add new transmission");
    $deletes_text = _T("Do you really want to delete selected transmissions?");
    $delete_text = _T("Do you really want to delete the transmission '%s'?");
    break;
case 'brands':
    include_once 'classes/auto-brands.class.php';
    $obj = new AutoBrands();
    $title = ( $is_new )
        ? _T("New brand")
        : ( isset($_GET[AutoBrands::PK])
            ? _T("Change brand '%s'")
            : _T("Brands list"));
    $show_title = _T("Brand '%s'");
    $field_name = _T("Brand");
    $add_text = _T("Add new brand");
    $deletes_text = _T("Do you really want to delete selected brands?");
    $delete_text = _T("Do you really want to delete the brand '%s'?");
    $can_show = true;
    /** TODO: get models list */
    //$models = AutoModels::getBrandList();
    break;
default: //by default, we redirecto to index page
    $log->log(
        '[Auto Plugin] No "set" defined for ; could not load object.',
        PEAR_LOG_WARNING
    );
    header('location: ' . $base_path . 'index.php');
    die();
    break;
}

//We have a new or a modified object
if ( get_numeric_form_value('modif', 0) == 1
    || get_numeric_form_value('new', 0) == 1
) {
    // initialize warnings
    $error_detected = array();
    $warning_detected = array();
    $confirm_detected = array();

    if ( !$is_new && get_numeric_form_value($obj->pk, null) != null ) {
        $obj->load(get_numeric_form_value($obj->pk, ''));
    } else if ( !$is_new ) {
        $error_detected[]
            = _T("- No id provided for modifying this record! (internal)");
    }

    $obj->value = get_form_value($obj->field, null);

    if ( $obj->value == '' ) {
        $error_detected[] = _T("- You must provide a value!");
    }

    if ( count($error_detected) == 0 ) {
        $res = $obj->store($is_new);
        if ( !$res ) {
            $error_detected[]
                = _T("- An error occured while saving record. Please try again.");
        } else {
            header('location:object.php?set=' . $set);
        }
    }
}

//Delete cases. $_GET['sup']} to delete from a single line ;
//$_POST["delete"] to delete from checkboxes
if ( isset($_GET['sup']) || isset($_POST['delete']) ) {
    $del = array();
    if ( isset($_GET['sup']) ) {
        $del[] = $_GET['sup'];
    } else {
        foreach ( $_POST['_sel'] as $supval ) {
            if ( is_numeric($supval) ) {
                $del[] = $supval;
            }
        }
    }

    $obj->delete($del);
}

if ( isset($error_detected) ) {
    $tpl->assign('error_detected', $error_detected);
}

//Set the path to the current plugin's templates,
//but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;
$tpl->assign('set', $set);
$tpl->assign('mode', (($is_new) ? 'new' : 'modif'));
if ( isset($can_show) ) {
    $tpl->assign('show', $can_show);
}
$tpl->assign('field_name', $field_name);
$tpl->compile_id = AUTO_SMARTY_PREFIX;

if ( isset($can_show)
    && $can_show == true
    && get_numeric_form_value('show', null) != null
) {
    $obj->load(get_numeric_form_value('show', ''));
    if ( $obj->name == 'brands' ) {
        $tpl->assign('models', $obj->getModels(get_numeric_form_value('show', '')));
    }
    $title = str_replace('%s', $obj->value, $show_title);
    $tpl->assign('page_title', $title);
    $tpl->assign('obj', $obj);
    $content = $tpl->fetch('object_show.tpl', AUTO_SMARTY_PREFIX);
} else if ( isset( $_GET[$obj->pk] ) || $is_new ) {
    if ( !$is_new ) {
        $obj->load(get_numeric_form_value($obj->pk, ''));
        $title = str_replace('%s', $obj->value, $title);
    }
    $tpl->assign('page_title', $title);
    $tpl->assign('obj', $obj);
    $content = $tpl->fetch('object.tpl', AUTO_SMARTY_PREFIX);
} else {
    $tpl->assign('add_text', $add_text);
    $tpl->assign('deletes_text', $deletes_text);
    //$delete_text = str_replace('%s', $obj->value, $delete_text);
    $tpl->assign('page_title', $title);
    $tpl->assign('delete_text', $delete_text);
    $tpl->assign('obj', $obj);
    $content = $tpl->fetch('object_list.tpl', AUTO_SMARTY_PREFIX);
}

$tpl->assign('content', $content);
//Set path to main Galette's template
$tpl->template_dir = $orig_template_path;
$tpl->display('page.tpl', AUTO_SMARTY_PREFIX);
?>