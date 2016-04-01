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
    '/',
    function () {
        echo 'Coucou de Auto !';
    }
)->setName('auto');

$this->get(
    '/vehicle/photo[/{id:\d+}]',
    function ($request, $response, $args) {
        $id = isset($args['id']) ? $args['id'] : null;
        $picture = new GaletteAuto\Picture($this->plugins, $id);
        $picture->display();
    }
)->setName('vehiclePhoto');

$this->get(
    '/vehicles-list[/member/{id:\d+}]',
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

        $auto = new Autos();

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

        $title = _T("Cars list");
        if ($id_adh !== null) {
            $title = _T("Member's cars");
        }

        $params = [
            'page_title'    => $title,
            'title'         => _T("Vehicles list"),
            'show_mine'     => false
        ];

        /*$title = _T("Vehicles list");
        $tpl->assign('title', $title);*/
        if ($id_adh === null) {
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
    '/vehicle/{action:add|edit}[/{id:\d+}]',
    function ($request, $response, $args) use ($module) {
        $action = $args['action'];
        $is_new = $action === 'add';
        $set = get_form_value('set', null);

        if ($action === 'edit' && !isset($args['id'])) {
            throw new \RuntimeException(
                _T("Car ID cannot be null calling edit route!")
            );
        } elseif ($action === 'add' && isset($args['id'])) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('vehicleEdit', ['action' => 'add']));
        }

        $auto = new Auto($this->plugins);
        if (!$is_new) {
            $auto->load((int)$args['id']);
        } else {
            if ($mine) {
                $auto->appropriateCar($this->login);
            } else {
                if (isset($_GET['id_adh']) && ($login->isAdmin() || $login->isStaff())) {
                    $auto->owner = $_GET['id_adh'];
                }
            }
        }

        $title = ($is_new)
            ? _T("New vehicle")
            : str_replace('%s', $auto->name, _T("Change vehicle '%s'"));

        $params = [
            'page_title'        => $title,
            'mode'              => (($is_new) ? 'new' : 'modif'),
            'require_calendar'  => true,
            'require_dialog'    => true,
            'show_mine'         => $mine,
            'car'               => $auto,
            'models'            => $auto->model->getList((int)$auto->model->brand),
            'js_init_models'    => (($auto->model->brand != '') ? false : true),
            'brands'            => $auto->model->obrand->getList(),
            'colors'            => $auto->color->getList(),
            'bodies'            => $auto->body->getList(),
            'transmissions'     => $auto->transmission->getList(),
            'finitions'         => $auto->finition->getList(),
            'states'            => $auto->state->getList(),
            'fuels'             => $auto->listFuels(),
            'time'              => time()
        ];

        //We have a new or a modified object
        /*if (get_numeric_form_value('modif', 0) == 1
            || get_numeric_form_value('new', 0) == 1
            && !isset($_POST['cancel'])
        ) {
            // initialize warnings
            $error_detected = array();
            $warning_detected = array();
            $confirm_detected = array();

            if (!$is_new && get_numeric_form_value(Auto::PK, null) != null) {
                $auto->load(get_numeric_form_value(Auto::PK, null));
            } else if (!$is_new) {
                $error_detected[]
                    = _T("- No id provided for modifying this record! (internal)");
            }

            ** TODO: make required fields dynamic, as in main Galette *
            $required = array(
                'name'                      => 1,
                'model'                     => 1,
                'first_registration_date'   => 1,
                'first_circulation_date'    => 1,
                'color'                     => 1,
                'state'                     => 1,
                'registration'              => 1,
                'body'                      => 1,
                'transmission'              => 1,
                'finition'                  => 1,
                'fuel'                      => 1
            );

            //check for required fields, and correct values
            foreach ( $auto->getProperties() as $prop ) {
                $value = get_form_value($prop, null);
                switch ( $prop ) {
                //string values, no check
                case 'name':
                case 'comment':
                    $value = get_form_value($prop, null);
                    if ( $value == '' && in_array($prop, array_keys($required)) ) {
                        $error_detected[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' .$auto->getPropName($prop) . '</a>',
                            _T("- Mandatory field %s empty.")
                        );
                    } else {
                        $auto->$prop = $value;
                    }
                    break;
                //string values with special check
                case 'chassis_number':
                case 'registration':
                    ** TODO: how are built chassis number and registration? *
                    if ( $value == '' && in_array($prop, array_keys($required)) ) {
                        $error_detected[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' .$auto->getPropName($prop) . '</a>',
                            _T("- Mandatory field %s empty.")
                        );
                    } else {
                        $auto->$prop = $value;
                    }
                    break;
                //dates
                case 'first_registration_date':
                case 'first_circulation_date':
                    if ( $value == '' && in_array($prop, array_keys($required)) ) {
                        $error_detected[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' .$auto->getPropName($prop) . '</a>',
                            _T("- Mandatory field %s empty.")
                        );
                    } elseif ( preg_match("@^([0-9]{2})/([0-9]{2})/([0-9]{4})$@", $value, $array_jours) ) {
                        if ( checkdate($array_jours[2], $array_jours[1], $array_jours[3]) ) {
                            $value = $array_jours[3].'-'.$array_jours[2].'-'.$array_jours[1];
                            $auto->$prop = $value;
                        } else {
                            $error_detected[] = str_replace(
                                '%s',
                                $auto->getPropName($prop),
                                _T("- Non valid date for %s!")
                            );
                        }
                    } else {
                        $error_detected[] = str_replace(
                            '%s',
                            $auto->getPropName($prop),
                            _T("- Wrong date format for %s (dd/mm/yyyy)!")
                        );
                    }
                    break;
                //numeric values
                case 'mileage':
                case 'seats':
                case 'horsepower':
                case 'engine_size':
                    if ( $value == '' && in_array($prop, array_keys($required)) ) {
                        $error_detected[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' .$auto->getPropName($prop) . '</a>',
                            _T("- Mandatory field %s empty.")
                        );
                    } else {
                        if ( is_int((int)$value) ) {
                            $auto->$prop = $value;
                        } else if ( $value != '' ) {
                            $error_detected[] = str_replace(
                                '%s',
                                '<a href="#' . $prop . '">' .$auto->getPropName($prop) . '</a>',
                                _T("- You must enter a positive integer for %s")
                            );
                        }
                    }
                    break;
                //constants
                case 'fuel':
                    if ( in_array($value, array_keys($auto->listFuels())) ) {
                        $auto->fuel = $value;
                    } else {
                        $error_detected[] = _T("- You must choose a fuel in the list");
                    }
                    break;
                //external objects
                case 'finition':
                case 'color':
                case 'model':
                case 'transmission':
                case 'body':
                case 'state':
                case 'model':
                    if ( $value > 0 ) {
                        $auto->$prop = $value;
                    } else {
                        $name = '';
                        switch ( $prop ) {
                        case 'finition':
                            $name = Finition::FIELD;
                            break;
                        case 'color':
                            $name = Color::FIELD;
                            break;
                        case 'model':
                            $name = Model::FIELD;
                            break;
                        case 'transmission':
                            $name = Transmission::FIELD;
                            break;
                        case 'body':
                            $name = Body::FIELD;
                            break;
                        case 'state':
                            $name = State::FIELD;
                            break;
                        default:
                            Analog::log(
                                'Unable to retrieve the textual value for prop `' .
                                $prop . '`',
                                Analog::INFO
                            );
                            $name = '(unknow)';
                        }
                        $error_detected[] = str_replace(
                            '%s',
                            '<a href="#' . $prop . '">' . $auto->getPropName($name) . '</a>',
                            _T("- You must choose a %s in the list")
                        );
                    }
                    break;
                case 'owner':
                    $value = get_numeric_form_value($prop, 0);
                    if ( $value > 0 ) {
                        $auto->$prop = $value;
                    } else {
                        $error_detected[] = _T("- you must attach an owner to this car");
                    }
                    break;
                default:
                    ** TODO: what's the default? *
                    Analog::log(
                        'Trying to edit an Auto property that is not catched in the source code! (prop is: ' . $prop . ')',
                        Analog::ERROR
                    );
                    break;
                }//switch
            }//foreach

            // picture upload
            if ( isset($_FILES['photo']) ) {
                if ( $_FILES['photo']['tmp_name'] !='' ) {
                    if ( is_uploaded_file($_FILES['photo']['tmp_name']) ) {
                        $res = $auto->picture->store($_FILES['photo']);
                        if ( $res < 0) {
                            switch ( $res ) {
                            case Picture::INVALID_FILE:
                                $patterns = array('|%s|', '|%t|');
                                $replacements = array(
                                    $auto->picture->getAllowedExts(),
                                    htmlentities($auto->picture->getBadChars())
                                );
                                $error_detected[] = preg_replace(
                                    $patterns,
                                    $replacements,
                                    _T("- Filename or extension is incorrect. Only %s files are allowed. File name should not contains any of: %t")
                                );
                                break;
                            case Picture::FILE_TOO_BIG:
                                $error_detected[] = preg_replace(
                                    '|%d|',
                                    Picture::MAX_FILE_SIZE,
                                    _T("File is too big. Maximum allowed size is %d")
                                );
                                break;
                            case Picture::MIME_NOT_ALLOWED:
                                ** FIXME: should be more descriptive *
                                $error_detected[] = _T("Mime-Type not allowed");
                                break;
                            case Picture::SQL_ERROR:
                            case Picture::SQL_BLOB_ERROR:
                                $error_detected[] = _T("An SQL error has occured.");
                                break;
                            }
                        }
                    }
                }
            }

            //delete photo
            if ( isset($_POST['del_photo']) ) {
                if ( !$auto->picture->delete() ) {
                    $error_detected[]
                        = _T("An error occured while trying to delete car's photo");
                }
            }

            //if no errors were thrown, we can store the car
            if ( count($error_detected) == 0 ) {
                if ( !$auto->store($is_new) ) {
                    $error_detected[]
                        = _T("- An error has occured while saving car in the database.");
                } else {
                    if ( $mine ) {
                        header('location: my_vehicles.php');
                    } else {
                        header('location: vehicles_list.php');
                    }
                }
            }
        } else if ( isset($_POST['cancel']) ) {
            unset($_POST['new']);
            $is_new = false;
            unset($_POST['modif']);
        }*/

        $smarty = $this->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = AUTO_SMARTY_PREFIX;

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']vehicles.tpl',
            $params
        );
        return $response;

    }
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
