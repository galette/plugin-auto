<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette Auto plugin controller
 *
 * PHP version 5
 *
 * Copyright © 2017 The Galette Team
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
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-18
 */

namespace GaletteAuto;

use Analog\Analog;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Galette\Core\Db;
use Galette\Core\Plugins;
use Galette\Entity\Adherent;
use Zend\Db\Sql\Expression;

/**
 * Galette Auto plugin controller
 *
 * @category  Plugins
 * @name      Autos
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-18
 */
class Controller
{
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container Dependencies container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Show add/edit route for member's own vehicles
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function showAddEditMyVehicle(Request $request, Response $response, $args = [])
    {
        $args['mine'] = true;
        return $this->showAddEditVehicle($request, $response, $args);
    }

    /**
     * Show add/edit route
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function showAddEditVehicle(Request $request, Response $response, $args = [])
    {
        $action = $args['action'];
        $is_new = $action === __('add', 'routes');

        if ($action === 'edit' && !isset($args['id'])) {
            throw new \RuntimeException(
                _T("Car ID cannot be null calling edit route!", "auto")
            );
        } elseif ($action === 'add' && isset($args['id'])) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->container->router->pathFor('vehicleEdit', ['action' => 'add']));
        }

        $auto = new Auto($this->container->plugins, $this->container->zdb);
        if (!$is_new) {
            $auto->load((int)$args['id']);
        } else {
            if (isset($args['mine'])) {
                $auto->appropriateCar($this->container->login);
            } else {
                if (isset($_GET['id_adh'])
                    && ($this->container->login->isAdmin() || $this->container->login->isStaff())
                ) {
                    $auto->owner = $_GET['id_adh'];
                }
            }
        }

        $title = ($is_new)
            ? _T("New vehicle", "auto")
            : str_replace('%s', $auto->name, _T("Change vehicle '%s'", "auto"));

        $params = [
            'page_title'        => $title,
            'mode'              => (($is_new) ? 'new' : 'modif'),
            'require_calendar'  => true,
            'require_dialog'    => true,
            'show_mine'         => isset($args['mine']),
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

        $modules = $this->container->plugins->getModules();
        $module = $modules[$this->container->get('Plugin Galette Auto')];
        $smarty = $this->container->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->container->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = AUTO_SMARTY_PREFIX;

        // display page
        $this->container->view->render(
            $response,
            'file:[' . $module['route'] . ']vehicles.tpl',
            $params
        );
        return $response;
    }
}
