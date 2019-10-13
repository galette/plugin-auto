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
use GaletteAuto\Filters\ModelsList;
use GaletteAuto\Repository\Models;
use GaletteAuto\History;

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
    protected function getModule()
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
    protected function checkAclsFor(Response $response, $id_adh, $redirect = null)
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

                $this->container->flash->addMessage(
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
            $this->checkAclsFor($response, $args['id']);
        }

        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }

        $numrows = $this->container->preferences->pref_numrows;
        if (isset($_GET["nbshow"])) {
            if (is_numeric($_GET["nbshow"])) {
                $numrows = $_GET["nbshow"];
            }
        }

        $auto = new Autos($this->container->plugins, $this->container->zdb);
        $afilters = new AutosList();

        // Simple filters
        if ($option !== null) {
            switch ($option) {
                case 'page':
                    $afilters->current_page = (int)$value;
                    break;
                case 'order':
                    $afilters->orderby = $value;
                    break;
            }
        }

        $title = _T("Cars list", "auto");
        if (isset($args['mine'])) {
            $title = _T("My cars", "auto");
        } elseif ($id_adh !== null) {
            $title = _T("Member's cars", "auto");
        }

        $params = [
            'page_title'    => $title,
            'title'         => _T("Vehicles list", "auto"),
            'show_mine'     => isset($args['mine']),
            'require_dialog'=> true
        ];

        if ($id_adh === null) {
            $params['autos'] = $auto->getList(true, isset($args['mine']), null, $afilters);
        } else {
            $params['id_adh'] = $id_adh;
            $params['autos'] = $auto->getMemberList($id_adh, $afilters);
        }

        //assign pagination variables to the template and add pagination links
        $afilters->setSmartyPagination($this->container->router, $this->container->view->getSmarty());
        $module = $this->getModule();

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
        $is_new = $action === 'add';

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
            $this->checkAclsFor($response, $auto->owner->id);
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

        $mfilters = new ModelsList();
        $models = new Models(
            $this->container->zdb,
            $this->container->preferences,
            $this->container->login,
            $mfilters
        );

        $params = [
            'page_title'        => $title,
            'mode'              => (($is_new) ? 'new' : 'modif'),
            'require_calendar'  => true,
            'require_dialog'    => true,
            'car'               => $auto,
            'models'            => $models->getList((int)$auto->model->brand),
            'js_init_models'    => (($auto->model->brand != '') ? false : true),
            'brands'            => $auto->model->obrand->getList(),
            'colors'            => $auto->color->getList(),
            'bodies'            => $auto->body->getList(),
            'transmissions'     => $auto->transmission->getList(),
            'finitions'         => $auto->finition->getList(),
            'states'            => $auto->state->getList(),
            'fuels'             => $auto->listFuels(),
            'time'              => time(),
            'required'          => $auto->getRequired()
        ];

        // members
        $members = [];
        $m = new \Galette\Repository\Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh'
        );
        $list_members = $m->getList(false, $required_fields, true);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname = mb_strtoupper($member->nom_adh, 'UTF-8') .
                    ' ' . ucwords(mb_strtolower($member->prenom_adh, 'UTF-8')) .
                    ' (' . $member->id_adh . ')';
                $members[$member->$pk] = $sname;
            }
        }

        $params['members'] = [
            'filters'   => $m->getFilters(),
            'count'     => $m->getCount()
        ];

        //check if current attached member is part of the list
        if ($auto->owner->id > 0
            && !isset($members[$auto->owner->id])
        ) {
            $members[$auto->owner->id] = Adherent::getSName($this->container->zdb, $auto->owner->id, true);
        }

        if (count($members)) {
            $params['members']['list'] = $members;
        }

        $module = $this->getModule();

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
        $is_new = $action === 'add';

        // initialize warnings
        $error_detected = array();
        $warning_detected = array();
        $success_detected = array();

        $this->checkAclsFor($response, (int)$post['id_adh']);

        $auto = new Auto($this->container->plugins, $this->container->zdb);
        if (!$is_new) {
            if (isset($post[Auto::PK])) {
                $auto->load($post[Auto::PK]);
            } else {
                $error_detected[]
                    = _T("- No id provided for modifying this record! (internal)", "auto");
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
                $error_detected[] = _T("- An error has occured while saving vehicle in the database.", "auto");
            } else {
                $success_detected[] = _T("Vehicle has been saved!", "auto");
                $id_adh = $auto->owner->id;
                if (!$this->checkAclsFor($response, $id_adh, false) || $this->container->login->id == $id_adh) {
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
     * Show vehicle history
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function vehicleHistory(Request $request, Response $response, $args = [])
    {
        $history = new History($this->container->zdb, (int)$args['id']);
        $auto = new Auto($this->container->plugins, $this->container->zdb, $history->{Auto::PK});
        $this->checkAclsFor($response, $auto->owner->id);

        $apk = Auto::PK;
        $params = [
            'entries'       => $history->entries,
            'page_title'    => str_replace('%d', $history->$apk, _T("History of car #%d", "auto")),
            'mode'          => $request->isXhr() ? 'ajax' : ''
        ];

        $module = $this->getModule();

        // display page
        $this->container->view->render(
            $response,
            'file:[' . $module['route'] . ']history.tpl',
            $params
        );
        return $response;
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
        $models = new Models(
            $this->container->zdb,
            $this->container->preferences,
            $this->container->login,
            new ModelsList()
        );

        $id_brand = null;
        if (isset($post['brand']) && $post['brand'] != '') {
            $id_brand = (int)$post['brand'];
        }
        $list = $models->getList($id_brand, false);

        return $response->withJson($list->toArray());
    }

    /**
     * Remove vehicle confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function removeVehicle(Request $request, Response $response, $args = [])
    {
        $auto = new Auto($this->container->plugins, $this->container->zdb);
        $auto->load((int)$args['id']);
        $id_adh = $auto->owner->id;
        $this->checkAclsFor($response, $id_adh);

        $route = $this->container->router->pathFor('vehiclesList');
        if (!$this->checkAclsFor($response, $id_adh, false) || $this->container->login->id == $id_adh) {
            $route = $this->container->router->pathFor('myVehiclesList');
        }

        $data = [
            'id'            => $args['id'],
            'redirect_uri'  => $route
        ];

        // display page
        $this->container->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Vehicle", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove vehicle %1$s', 'auto'),
                    $auto->name
                ),
                'form_url'      => $this->container->router->pathFor('doRemoveVehicle', ['id' => $auto->id]),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Remove vehicles confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function removeVehicles(Request $request, Response $response, $args = [])
    {
        $route = $this->container->router->pathFor('vehiclesList');
        $ids = $this->container->session->filter_vehicles;

        $auto = new Auto($this->container->plugins, $this->container->zdb);
        $auto->load((int)$ids[0]);
        $id_adh = $auto->owner->id;
        $this->checkAclsFor($response, $id_adh);

        $id_adh = $auto->owner->id;

        if (!$this->checkAclsFor($response, $id_adh, false) || $this->container->login->id == $id_adh) {
            $route = $this->container->router->pathFor('myVehiclesList');
        }

        $data = [
            'id'            => $ids,
            'redirect_uri'  => $route
        ];

        // display page
        $this->container->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Vehicle", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => _T('Remove vehicles', 'auto'),
                'message'       => str_replace(
                    '%count',
                    count($data['id']),
                    _T('You are about to remove %count vehicles.', 'auto')
                ),
                'form_url'      => $this->container->router->pathFor('doRemoveVehicle'),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Do remove vehicles
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function doRemoveVehicle(Request $request, Response $response, $args = [])
    {
        $post = $request->getParsedBody();
        $ajax = isset($post['ajax']) && $post['ajax'] === 'true';
        $success = false;

        $uri = isset($post['redirect_uri']) ?
            $post['redirect_uri'] :
            $this->router->pathFor('slash');

        if (!isset($post['confirm'])) {
            $this->container->flash->addMessage(
                'error_detected',
                _T("Removal has not been confirmed!")
            );
        } else {
            if (!is_array($post['id'])) {
                $ids = (array)$post['id'];
            } else {
                $ids = $post['id'];
            }

            $autos = new Autos($this->container->plugins, $this->container->zdb);
            $del = $autos->removeVehicles($ids);

            if ($del !== true) {
                $error_detected = _T("An error occured trying to remove vehicles :/", "auto");

                $this->container->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%count',
                    count($ids),
                    _T("%count vehicles have been successfully deleted.", "auto")
                );

                $this->container->flash->addMessage(
                    'success_detected',
                    $success_detected
                );

                $success = true;
            }
        }

        if (!$ajax) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $uri);
        } else {
            return $response->withJson(
                [
                    'success'   => $success
                ]
            );
        }
    }
}
