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
     * Get current module informations
     *
     * @return array
     */
    private function getModule()
    {
        $modules = $this->container->plugins->getModules();
        $module = $modules[$this->container->get('Plugin Galette Auto')];
        return $module;
    }

    /**
     * Check ACLs for specific member
     *
     * @param integer $id_adh   Members id to check right for
     * @param string  $redirect Path to redirect to (myVehiclesList per default)
     *
     * @return boolean
     */
    private function checkAclsFor($id_adh, $redirect = null)
    {
        //maybe should this be a middleware... but I do not know how to pass redirect :/
        if ($this->container->login->id != $id_adh
            && !$this->container->login->isAdmin()
            && !$this->container->login->isStaff()
        ) {
            $deps = array(
                'picture'   => false,
                'dues'      => false
            );
            $member = new Adherent($this->container->zdb, $id_adh, $deps);
            if (!$this->container->login->isGroupManager($member->groups)) {
                //no right to see requested member.
                if ($redirect === false) {
                    return false;
                }

                $this->flash->addMessage(
                    'error_detected',
                    _T("You do not have enought privileges.", "auto")
                );

                if ($redirect === null) {
                    $redirect = $this->container->router->pathFor('myVehiclesList');
                }
                return $response
                    ->withStatus(403)
                    ->withHeader('Location', $redirect);
            }
        }
        return true;
    }

    /**
     * List my vehicles
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function myVehiclesList(Request $request, Response $response, $args = [])
    {
        $args['id_adh'] = $this->container->login->id;
        $args['mine']   = true;
        return $this->vehiclesList($request, $response, $args);
    }

    /**
     * List vehicles
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function vehiclesList(Request $request, Response $response, $args = [])
    {
        $id_adh = null;
        if (isset($args['id'])) {
            $id_adh = (int)$args['id'];
            $this->checkAclsFor($args['id']);
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

        $numrows = $this->container->preferences->pref_numrows;
        if (isset($_GET["nbshow"])) {
            if (is_numeric($_GET["nbshow"])) {
                $numrows = $_GET["nbshow"];
            }
        }

        $auto = new Autos($this->container->plugins, $this->container->zdb);

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

        $module = $this->getModule();
        $smarty = $this->container->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->container->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = AUTO_SMARTY_PREFIX;

        $afilters = new AutosList();

        // Simple filters
        /*if (isset($_GET['page'])) {
            $afilters->current_page = (int)$_GET['page'];
        }*/

        $title = _T("Cars list", "auto");
        if (isset($args['mine'])) {
            $title = _T("My cars", "auto");
        } elseif ($id_adh !== null) {
            $title = _T("Member's cars", "auto");
        }

        $params = [
            'page_title'    => $title,
            'title'         => _T("Vehicles list", "auto"),
            'show_mine'     => isset($args['mine'])
        ];

        if ($id_adh === null) {
            $params['autos'] = $auto->getList(true, isset($args['mine']), null, $afilters);
        } else {
            $params['id_adh'] = $id_adh;
            $params['autos'] = $auto->getMemberList($id_adh, $afilters);
        }

        //assign pagination variables to the template and add pagination links
        $afilters->setSmartyPagination($this->container->router, $this->container->view->getSmarty());

        // display page
        $this->container->view->render(
            $response,
            'file:[' . $module['route'] . ']vehicles_list.tpl',
            $params
        );
        return $response;
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
            $this->checkAclsFor((int)$args['id_adh']);
            $auto->load((int)$args['id']);
        } else {
            if (isset($args['id_adh'])
                && ($this->container->login->isAdmin() || $this->container->login->isStaff())
            ) {
                $auto->owner = $args['id_adh'];
            } else {
                $auto->appropriateCar($this->container->login);
            }
        }

        if ($this->container->session->auto !== null) {
            $auto->check($this->container->session->auto);
            $this->container->session->auto = null;
        }

        $title = ($is_new)
            ? _T("New vehicle", "auto")
            : str_replace('%s', $auto->name, _T("Change vehicle '%s'", "auto"));

        $params = [
            'page_title'        => $title,
            'mode'              => (($is_new) ? 'new' : 'modif'),
            'require_calendar'  => true,
            'require_dialog'    => true,
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

        $module = $this->getModule();
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

    /**
     * Do add/edit route
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function doAddEditVehicle(Request $request, Response $response, $args = [])
    {
        $post = $request->getParsedBody();

        $action = $args['action'];
        $is_new = $action === __('add', 'routes');

        // initialize warnings
        $error_detected = array();
        $warning_detected = array();
        $success_detected = array();

        $this->checkAclsFor((int)$post['id_adh']);

        $auto = new Auto($this->container->plugins, $this->container->zdb);
        if (!$is_new) {
            if (isset($post[Auto::PK])) {
                $auto->load($post[Auto::PK]);
            } else {
                $error_detected[]
                    = _T("- No id provided for modifying this record! (internal)");
            }
        }

        if (!count($error_detected)) {
            $res = $auto->check($post);
            if ($res !== true) {
                $error_detected = $auto->getErrors();
            }
        }

        $route = $this->container->router->pathFor('vehiclesList');
        //if no errors were thrown, we can store the car
        if (count($error_detected) == 0) {
            if (!$auto->store($is_new)) {
                $error_detected[] = _T("- An error has occured while saving car in the database.");
            } else {
                $success_detected[] = _T("Vehicle has been saved!", "auto");
                if (!$this->checkAclsFor($args['id_adh'], false) || $this->container->login->id == $args['id_adh']) {
                    $route = $this->container->router->pathFor('myVehiclesList');
                }
            }
        }

        if (count($error_detected) > 0) {
            //store entity in session
            $this->container->session->auto = $post;
            if (!$is_new && !isset($args[Auto::PK])) {
                $args['id'] = $auto->id;
            }
            $route = $this->container->router->pathFor('vehicleEdit', $args);

            foreach ($error_detected as $error) {
                $this->container->flash->addMessage(
                    'error_detected',
                    $error
                );
            }
        }

        if (count($warning_detected) > 0) {
            foreach ($warning_detected as $warning) {
                $this->container->flash->addMessage(
                    'warning_detected',
                    $warning
                );
            }
        }
        if (count($success_detected) > 0) {
            foreach ($success_detected as $success) {
                $this->container->flash->addMessage(
                    'success_detected',
                    $success
                );
            }
        }

        return $response
            ->withStatus(301)
            ->withHeader('Location', $route);
    }

    /**
     * List models from ajax call
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function ajaxModels(Request $request, Response $response, $args = [])
    {
        $post = $request->getParsedBody();
        $list = array();
        $m = new Model();

        if (isset($post['brand']) && $post['brand'] != '') {
            $list = $m->getListByBrand((int)$post['brand']);
        } else {
            $list = $m->getList();
        }

        return $response->withJson($list);
    }
}
