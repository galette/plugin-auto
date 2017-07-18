<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use Analog\Analog;
use GaletteAuto\Autos;
use GaletteAuto\AutosList;
use GaletteAuto\Color;
use GaletteAuto\State;
use GaletteAuto\Finition;
use GaletteAuto\Body;
use GaletteAuto\Transmission;
use GaletteAuto\Auto;
use GaletteAuto\Controller;

/**
 * Auto routes
 *
 * PHP version 5
 *
 * Copyright © 2016 The Galette Team
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
 * @copyright 2016 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     0.9dev 2016-03-02
 */

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

$this->get(
    __('/vehicle', 'auto_routes') . __('/photo', 'auto_routes') . '[/{id:\d+}]',
    function ($request, $response, $args) {
        $id = isset($args['id']) ? $args['id'] : '';
        $picture = new GaletteAuto\Picture($this->plugins, $id);
        $picture->display();
    }
)->setName('vehiclePhoto');

$this->get(
    __('/vehicles', 'auto_routes') . __('/list', 'routes') . '[' . __('/member', 'routes') . '/{id:\d+}]',
    function ($request, $response, $args) use ($module) {
        $id_adh = null;
        if (isset($args['id']) && ($this->login->isAdmin() || $this->login->isStaff())) {
            $id_adh = $args['id'];
        }

        /*if (isset($_POST['donew'])) {
            if ($mine) {
                header('location: my_vehicles_edit.php');
            } else {
                $location = 'vehicles_edit.php';
                if (isset($_POST['id_adh'])) {
                    $location .= '?id_adh=' . $_POST['id_adh'];
                }
                header('location: ' . $location);
            }
        }*/

        $numrows = $this->preferences->pref_numrows;
        if (isset($_GET["nbshow"])) {
            if (is_numeric($_GET["nbshow"])) {
                $numrows = $_GET["nbshow"];
            }
        }

        $auto = new Autos($this->plugins, $this->zdb);

        /*if (isset($_GET['sup']) || isset($_POST['delete'])) {
            if (isset($_GET['sup'])) {
                $auto->removeVehicles($_GET['sup']);
            } elseif (isset($_POST['vehicle_sel'])) {
                $auto->removeVehicles($_POST['vehicle_sel']);
            }
        }*/

        /*$title = _T("Cars list");
        if ($mine == 1) {
            $title = _T("My Cars");
        }
        $tpl->assign('page_title', $title);*/

        $smarty = $this->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = AUTO_SMARTY_PREFIX;


        $afilters = new AutosList();

        // Simple filters
        /*if (isset($_GET['page'])) {
            $afilters->current_page = (int)$_GET['page'];
        }*/

        $title = _T("Cars list", "auto");
        if ($id_adh !== null) {
            $title = _T("Member's cars", "auto");
        }

        $params = [
            'page_title'    => $title,
            'title'         => _T("Vehicles list", "auto"),
            'show_mine'     => false
        ];

        /*$title = _T("Vehicles list");
        $tpl->assign('title', $title);*/
        if ($id_adh === null) {
            //FIXME: do not hardcode!
            $mine = false;
            $params['autos'] = $auto->getList(true, $mine, null, $afilters);
        } else {
            $params['id_adh'] = $id_adh;
            $params['autos'] = $auto->getMemberList($id_adh, $afilters);
        }
        /*$tpl->assign('show_mine', $mine);*/

        //assign pagination variables to the template and add pagination links
        $afilters->setSmartyPagination($this->router, $this->view->getSmarty());

        /*$content = $tpl->fetch('vehicles_list.tpl', AUTO_SMARTY_PREFIX);
        $tpl->assign('content', $content);
        //Set path to main Galette's template
        $tpl->template_dir = $orig_template_path;
        $tpl->display('page.tpl', AUTO_SMARTY_PREFIX);*/

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']vehicles_list.tpl',
            $params
        );
        return $response;
    }
)->setName('vehiclesList')->add($authenticate);

$this->get(
    __('/vehicle', 'auto_routes') . '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    Controller::class . ':showAddEditVehicle'
)->setName('vehicleEdit')->add($authenticate);

$this->get(
    __('/myvehicle', 'auto_routes') . '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    Controller::class . ':showAddEditMyVehicle'
)->setName('vehicleEdit')->add($authenticate);


/*$this->get(
    '/localize-member/{id:\d+}',
    function ($request, $response, $args) use ($module, $module_id) {
        $id = $args['id'];
        $member = new Adherent((int)$id);

        if ($this->login->id != $id
            && !$this->login->isAdmin()
            && !$this->login->isStaff()
            && $this->login->isGroupManager()
        ) {
            //check if requested member is part of managed groups
            $groups = $member->groups;
            $is_managed = false;
            foreach ($groups as $g) {
                if ($this->login->isGroupManager($g->getId())) {
                    $is_managed = true;
                    break;
                }
            }
            if ($is_managed !== true) {
                //requested member is not part of managed groups, fall back to logged
                //in member
                $member->load($this->login->id);
                $id = $this->login->id;
            }
        }

        $coords = new Coordinates();
        $mcoords = $coords->getCoords($member->id);

        $towns = false;
        if (count($mcoords) === 0) {
            if ($member->town != '') {
                $t = new NominatimTowns();
                $towns = $t->search(
                    $member->town,
                    $member->country
                );
            }
        }

        $smarty = $this->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = MAPS_SMARTY_PREFIX;
        //set util paths
        $plugin_dir = basename(dirname($_SERVER['SCRIPT_NAME']));
        $smarty->assign(
            'galette_url',
            'http://' . $_SERVER['HTTP_HOST'] .
            preg_replace(
                "/\/plugins\/" . $plugin_dir . '/',
                "/",
                dirname($_SERVER['SCRIPT_NAME'])
            )
        );
        $smarty->assign(
            'plugin_url',
            'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/'
        );

        $params = [
            'page_title'        => _T("Maps") . ' - ' . str_replace(
                '%member',
                $member->sfullname,
                _T("%member geographic position")
            ),
            'member'            => $member,
            'require_dialog'    => true,
            'adh_map'           => true,
            'module_id'         => $module_id
        ];

        if ($towns !== false) {
            $params['towns'] = $towns;
        }

        if ($mcoords === false) {
            $this->flash->addMessage(
                'error_detected',
                _T("Coordinates has not been loaded. Maybe plugin tables does not exists in the datatabase?")
            );
        } elseif (count($mcoords) > 0) {
            $params['town'] = $mcoords;
        }

        if ($member->login == $this->login->login) {
            $params['mymap'] = true;
        }

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']mymap.tpl',
            $params
        );
        return $response;
    }
)->setName('maps_localize_member')->add($authenticate);

//member self localization
$this->get(
    '/mymap',
    function ($request, $response) {
        $deps = array(
            'picture'   => false,
            'groups'    => false,
            'dues'      => false
        );
        $member = new Adherent($this->login->login, $deps);
        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('maps_localize_member', ['id' => $member->id]));
    }
)->setName('maps_mymap')->add($authenticate);

//global map page
$this->get(
    '/map',
    function ($request, $response) use ($module, $module_id) {
        $login = $this->login;
        if (!$this->preferences->showPublicPages($login)) {
            //public pages are not actives
            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('slash'));
        }

        $coords = new Coordinates();
        $list = $coords->listCoords();

        $smarty = $this->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = MAPS_SMARTY_PREFIX;

        $params = [
            'require_dialog'    => true,
            'page_title'        => _T("Maps"),
            'module_id'         => $module_id
        ];

        if (!$login->isLogged()) {
            $params['is_public'] = true;
        }

        if ($list !== false) {
            $params['list'] = $list;
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T("Coordinates has not been loaded. Maybe plugin tables does not exists in the datatabase?")
            );
        }

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']maps.tpl',
            $params
        );
        return $response;
    }
)->setName('maps_map');

$this->post(
    '/-i-live-here[/{id:\d+}]',
    function ($request, $response, $args) {
        $id = null;
        if (isset($args['id'])) {
            $id = $args['id'];
        }
        $login = $this->login;
        $error = null;
        $message = null;

        if ($id === null && $login->isSuperAdmin()) {
            Analog::log(
                'SuperAdmin does note live anywhere!',
                Analog::INFO
            );
            $error = _T("Superadmin cannot be localized.");
        } elseif ($id === null) {
            $member = new Adherent($login->login);
            $id = $member->id;
        } elseif (!$login->isSuperAdmin()
            && !$login->isAdmin()
            && !$login->isStaff()
            && $login->isGroupManager()
        ) {
            $member = new Adherent((int)$id);
            //check if current logged in user can manage loaded member
            $groups = $member->groups;
            $can_manage = false;
            foreach ($groups as $group) {
                if ($login->isGroupManager($group->getId())) {
                    $can_manage = true;
                    break;
                }
            }
            if ($can_manage !== true) {
                Analog::log(
                    'Logged in member ' . $login->login .
                    ' has tried to load member #' . $id .
                    ' but do not manage any groups he belongs to.',
                    Analog::WARNING
                );
                $error = _T("Coordinates has not been removed :(");
            }
        }

        if ($error === null) {
            $post = $request->getParsedBody();
            $coords = new Coordinates();
            if (isset($post['remove'])) {
                $res = $coords->removeCoords($id);
                if ($res > 0) {
                    $message = _T("Coordinates has been removed!");
                } else {
                    $error = _T("Coordinates has not been removed :(");
                }
            } elseif (isset($post['latitude'])
                && isset($post['longitude'])
            ) {
                $res = $coords->setCoords(
                    $id,
                    $post['latitude'],
                    $post['longitude']
                );

                if ($res === true) {
                    $message = _T("New coordinates has been stored!");
                } else {
                    $error = _T("Coordinates has not been stored :(");
                }
            } else {
                $error = _T("Something went wrong :(");
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');

        $res = [
            'res'       => $error === null,
            'message'   => ($error === null ? $message : $error)
        ];

        $body = $response->getBody();
        $body->write(json_encode($res));

        return $response;
    }
)->setName('maps_ilivehere')->add($authenticate);*/
