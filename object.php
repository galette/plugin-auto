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
 * @version   SVN: $Id: object.php 556 2009-03-13 06:48:49Z trashy $
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7dev - 2009-09-26
 */

use GaletteAuto\Color;
use GaletteAuto\State;
use GaletteAuto\Finition;
use GaletteAuto\Body;
use GaletteAuto\Transmission;
use GaletteAuto\Brand;
use GaletteAuto\Model;

use Analog\Analog as Analog;

define('GALETTE_BASE_PATH', '../../');
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
if ( !$login->isLogged() || (!$login->isAdmin() && !$login->isStaff()) ) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
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

//Constants from plugin
require_once '_config.inc.php';

switch ( $set ) {
case 'colors':
    $obj = new Color();
    $title = ( $is_new )
        ? _T("New color", "auto")
        : ( isset($_GET[Color::PK])
            ? _T("Change color '%s'", "auto")
            : _T("Colors list", "auto") );
    $field_name = _T("Color", "auto");
    $add_text = _T("Add new color", "auto");
    $deletes_text = _T("Do you really want to delete selected colors?", "auto");
    $delete_text = _T("Do you really want to delete the color '%s'?", "auto");
    break;
case 'states':
    $obj = new State();
    $title = ( $is_new )
        ? _T("New state", "auto")
        : ( isset($_GET[State::PK])
            ? _T("Change state '%s'", "auto")
            : _T("States list", "auto") );
    $field_name = _T("State", "auto");
    $add_text = _T("Add new state", "auto");
    $deletes_text = _T("Do you really want to delete selected states?", "auto");
    $delete_text = _T("Do you really want to delete the state '%s'?", "auto");
    break;
case 'finitions':
    $obj = new Finition();
    $title = ( $is_new )
        ? _T("New finition", "auto")
        : ( isset($_GET[Finition::PK])
            ? _T("Change finition '%s'", "auto")
            : _T("Finitions list", "auto") );
    $field_name = _T("Finition", "auto");
    $add_text = _T("Add new finition", "auto");
    $deletes_text = _T("Do you really want to delete selected finitions?", "auto");
    $delete_text = _T("Do you really want to delete the finition '%s'?", "auto");
    break;
case 'bodies':
    $obj = new Body();
    $title = ( $is_new )
        ? _T("New body", "auto")
        : ( isset($_GET[Body::PK])
            ? _T("Change body '%s'", "auto")
            : _T("Bodies list", "auto") );
    $field_name = _T("Body", "auto");
    $add_text = _T("Add new body", "auto");
    $deletes_text = _T("Do you really want to delete selected bodies?", "auto");
    $delete_text = _T("Do you really want to delete the body '%s'?", "auto");
    break;
case 'transmissions':
    $obj = new Transmission();
    $title = ( $is_new )
        ? _T("New transmission", "auto")
        : ( isset($_GET[Transmission::PK])
            ? _T("Change transmission '%s'", "auto")
            : _T("Transmissions list", "auto") );
    $field_name = _T("Transmission", "auto");
    $add_text = _T("Add new transmission", "auto");
    $deletes_text = _T("Do you really want to delete selected transmissions?", "auto");
    $delete_text = _T("Do you really want to delete the transmission '%s'?", "auto");
    break;
case 'brands':
    $obj = new Brand();
    $title = ( $is_new )
        ? _T("New brand", "auto")
        : ( isset($_GET[Brand::PK])
            ? _T("Change brand '%s'", "auto")
            : _T("Brands list", "auto"));
    $show_title = _T("Brand '%s'", "auto");
    $field_name = _T("Brand", "auto");
    $add_text = _T("Add new brand", "auto");
    $deletes_text = _T("Do you really want to delete selected brands?", "auto");
    $delete_text = _T("Do you really want to delete the brand '%s'?", "auto");
    $can_show = true;
    /** TODO: get models list */
    //$models = Model::getBrandList();
    break;
default: //by default, we redirecto to index page
    Analog::log(
        '[Auto Plugin] No "set" defined for ; could not load object.',
        Analog::WARNING
    );
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
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
            = _T("- No id provided for modifying this record! (internal)", "auto");
    }

    $obj->value = get_form_value($obj->field, null);

    if ( $obj->value == '' ) {
        $error_detected[] = _T("- You must provide a value!", "auto");
    }

    if ( count($error_detected) == 0 ) {
        $res = $obj->store($is_new);
        if ( !$res ) {
            $error_detected[]
                = _T("- An error occured while saving record. Please try again.", "auto");
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
