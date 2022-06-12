<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette Auto plugin controller
 *
 * PHP version 5
 *
 * Copyright © 2017-2021 The Galette Team
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
 * @copyright 2017-2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-18
 */

namespace GaletteAuto;

use Slim\Http\Request;
use Slim\Http\Response;
use Galette\Controllers\AbstractPluginController;
use Galette\Entity\Adherent;
use GaletteAuto\Filters\ModelsList;
use GaletteAuto\Filters\AutosList;
use GaletteAuto\Repository\Models;

/**
 * Galette Auto plugin controller
 *
 * @category  Plugins
 * @name      Autos
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-18
 */
class Controller extends AbstractPluginController
{
    /**
     * @Inject("Plugin Galette Auto")
     * @var integer
     */
    protected $module_info;

    /** @var boolean  */
    private $mine = false;

    /** @var integer */
    private $id_adh;

    /**
     * Check ACLs for specific member
     *
     * @param Response $response Response
     * @param integer  $id_adh   Members id to check right for
     * @param string   $redirect Path to redirect to (myVehiclesList per default)
     *
     * @return boolean
     */
    protected function checkAclsFor(Response $response, $id_adh, $redirect = null)
    {
        //maybe should this be a middleware... but I do not know how to pass redirect :/
        if (
            $this->login->id != $id_adh
            && !$this->login->isAdmin()
            && !$this->login->isStaff()
        ) {
            $deps = array(
                'picture'   => false,
                'dues'      => false
            );
            $member = new Adherent($this->zdb, $id_adh, $deps);
            if (!$this->login->isGroupManager($member->groups)) {
                //no right to see requested member.
                if ($redirect === false) {
                    return false;
                }

                $this->flash->addMessage(
                    'error_detected',
                    _T("You do not have enough privileges.", "auto")
                );

                if ($redirect === null) {
                    $redirect = $this->router->pathFor('myVehiclesList');
                }
                return $response
                    ->withStatus(403)
                    ->withHeader('Location', $redirect);
            }
        }
        return true;
    }

    /**
     * Vehicle photo
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param integer  $id       Vehicle id
     *
     * @return Response
     */
    public function vehiclePhoto(Request $request, Response $response, int $id = null): Response
    {
        $picture = new Picture($this->plugins, $id);

        $response = $response->withHeader('Content-Type', $picture->getMime())
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, file_get_contents($picture->getPath()));
        rewind($stream);

        return $response->withBody(new \Slim\Http\Stream($stream));
    }

    /**
     * List my vehicles
     *
     * @param Request     $request  Request
     * @param Response    $response Response
     * @param string|null $option   Either 'page' or 'order'
     * @param int|null    $value    Option value
     *
     * @return Response
     */
    public function myVehiclesList(Request $request, Response $response, string $option = null, int $value = null): Response
    {
        $this->id_adh = $this->login->id;
        $this->mine = true;
        return $this->vehiclesList($request, $response, $option, $value);
    }

    /**
     * List vehicles for a member
     *
     * @param Request     $request  Request
     * @param Response    $response Response
     * @param int         $id       Member ID
     * @param string|null $option   Either 'page' or 'order'
     * @param int|null    $value    Option value
     *
     * @return Response
     */
    public function memberVehiclesList(Request $request, Response $response, int $id, string $option = null, int $value = null): Response
    {
        $this->id_adh = $id;
        return $this->vehiclesList($request, $response, $option, $value);
    }
        /**
     * List vehicles
     *
     * @param Request     $request  Request
     * @param Response    $response Response
     * @param string|null $option   Either 'page' or 'order'
     * @param int|null    $value    Option value
     *
     * @return Response
     */
    public function vehiclesList(Request $request, Response $response, string $option = null, int $value = null): Response
    {
        $get = $request->getQueryParams();
        $id_adh = null;
        if (!empty($this->id_adh)) {
            $id_adh = (int)$this->id_adh;
            $this->checkAclsFor($response, $id_adh);
        }

        $auto = new Autos($this->plugins, $this->zdb);
        $afilters = $this->session->vehicles_filters ?? new AutosList();

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

        if (isset($get["nbshow"]) && is_numeric($get["nbshow"])) {
            $afilters->show = $get["nbshow"];
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
            'show_mine'     => $this->mine,
            'require_dialog' => true
        ];

        if ($id_adh === null) {
            $params['autos'] = $auto->getList(true, $this->mine, null, $afilters);
        } else {
            $params['id_adh'] = $id_adh;
            $params['autos'] = $auto->getMemberList($id_adh, $afilters);
        }
        $params['count_vehicles'] = $auto->getCount();

        $this->session->vehicles_filters = $afilters;

        //assign pagination variables to the template and add pagination links
        $afilters->setViewPagination($this->router, $this->view->getSmarty());

        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']vehicles_list.tpl',
            $params
        );
        return $response;
    }

    /**
     * Show add/edit route
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $action   Either 'add' or 'edit'
     * @param int|null $id       Vehicle id
     *
     * @return Response
     */
    public function showAddEditVehicle(Request $request, Response $response, string $action, int $id = null)
    {
        $is_new = ($action === 'add');

        if ($action === 'edit' && $id === null) {
            throw new \RuntimeException(
                _T("Car ID cannot be null calling edit route!", "auto")
            );
        } elseif ($action === 'add' && $id !== null) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('vehicleEdit', ['action' => 'add']));
        }

        $auto = new Auto($this->plugins, $this->zdb);
        if (!$is_new) {
            $auto->load($id);
            $this->checkAclsFor($response, $auto->owner->id);
        } else {
            $get = $request->getQueryParams();
            if (
                isset($get['id_adh'])
                && ($this->login->isAdmin() || $this->login->isStaff())
            ) {
                $auto->owner = (int)$get['id_adh'];
            } else {
                $auto->appropriateCar($this->login);
            }
        }

        if ($this->session->auto !== null) {
            $auto->check($this->session->auto);
            $this->session->auto = null;
        }

        $title = ($is_new)
            ? _T("New vehicle", "auto")
            : str_replace('%s', $auto->name, _T("Change vehicle '%s'", "auto"));

        $mfilters = new ModelsList();
        $models = new Models(
            $this->zdb,
            $this->preferences,
            $this->login,
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
        $members = $m->getSelectizedMembers($this->zdb, $this->login);

        $params['members'] = [
            'filters'   => $m->getFilters(),
            'count'     => $m->getCount()
        ];

        //check if current attached member is part of the list
        if (
            $auto->owner->id > 0
            && !isset($members[$auto->owner->id])
        ) {
            $members[$auto->owner->id] = Adherent::getSName($this->zdb, $auto->owner->id, true);
        }

        if (count($members)) {
            $params['members']['list'] = $members;
        }

        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']vehicles.tpl',
            $params
        );
        return $response;
    }

    /**
     * Do add/edit route
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $action   Either 'add' or 'edit'
     * @param int|null $id       Vehicle id
     *
     * @return Response
     */
    public function doAddEditVehicle(Request $request, Response $response, string $action = 'edit', int $id = null)
    {
        $post = $request->getParsedBody();

        $is_new = ($action === 'add');

        // initialize warnings
        $error_detected = array();
        $warning_detected = array();
        $success_detected = array();

        if (isset($post['id_adh'])) {
            $this->checkAclsFor($response, (int)$post['id_adh']);
        }

        $auto = new Auto($this->plugins, $this->zdb);
        if (!$is_new) {
            $auto->load($post[Auto::PK]);
        }

        if (!count($error_detected)) {
            $res = $auto->check($post);
            if ($res !== true) {
                $error_detected = $auto->getErrors();
            }
        }

        $route = $this->router->pathFor('vehiclesList');
        //if no errors were thrown, we can store the car
        if (count($error_detected) == 0) {
            if (!$auto->store($is_new)) {
                $error_detected[] = _T("- An error has occured while saving vehicle in the database.", "auto");
            } else {
                $success_detected[] = _T("Vehicle has been saved!", "auto");
                $id_adh = $auto->owner->id;
                if (!$this->checkAclsFor($response, $id_adh, false) || $this->login->id == $id_adh) {
                    $route = $this->router->pathFor('myVehiclesList');
                }
            }
        }

        if (count($error_detected) > 0) {
            //store entity in session
            $this->session->auto = $post;
            $args = ['action' => $action];
            $routename = 'vehicleEdit';
            $route = $this->router->pathFor($routename, $args);

            foreach ($error_detected as $error) {
                $this->flash->addMessage(
                    'error_detected',
                    $error
                );
            }
        }

        if (count($warning_detected) > 0) {
            foreach ($warning_detected as $warning) {
                $this->flash->addMessage(
                    'warning_detected',
                    $warning
                );
            }
        }
        if (count($success_detected) > 0) {
            foreach ($success_detected as $success) {
                $this->flash->addMessage(
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
     * @param integer  $id       Vehicle id
     *
     * @return Response
     */
    public function vehicleHistory(Request $request, Response $response, int $id)
    {
        $history = new History($this->zdb, $id);
        $auto = new Auto($this->plugins, $this->zdb, $history->{Auto::PK});
        $this->checkAclsFor($response, $auto->owner->id);

        $apk = Auto::PK;
        $params = [
            'entries'       => $history->entries,
            'page_title'    => str_replace('%d', $history->$apk, _T("History of car #%d", "auto")),
            'mode'          => $request->isXhr() ? 'ajax' : ''
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']history.tpl',
            $params
        );
        return $response;
    }

    /**
     * List models from ajax call
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function ajaxModels(Request $request, Response $response)
    {
        $post = $request->getParsedBody();
        $list = array();
        $models = new Models(
            $this->zdb,
            $this->preferences,
            $this->login,
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
        $auto = new Auto($this->plugins, $this->zdb);
        $auto->load((int)$args['id']);
        $id_adh = $auto->owner->id;
        $this->checkAclsFor($response, $id_adh);

        $route = $this->router->pathFor('vehiclesList');
        if (!$this->checkAclsFor($response, $id_adh, false) || $this->login->id == $id_adh) {
            $route = $this->router->pathFor('myVehiclesList');
        }

        $data = [
            'id'            => $args['id'],
            'redirect_uri'  => $route
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Vehicle", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove vehicle %1$s', 'auto'),
                    $auto->name
                ),
                'form_url'      => $this->router->pathFor('doRemoveVehicle', ['id' => $auto->id]),
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
        $post = $request->getParsedBody();
        $route = $this->router->pathFor('vehiclesList');
        $ids = $this->session->filter_vehicles ?? $post['vehicles_sel'];

        $auto = new Auto($this->plugins, $this->zdb);
        $auto->load((int)$ids[0]);
        $id_adh = $auto->owner->id;
        $this->checkAclsFor($response, $id_adh);

        $id_adh = $auto->owner->id;

        if (!$this->checkAclsFor($response, $id_adh, false) || $this->login->id == $id_adh) {
            $route = $this->router->pathFor('myVehiclesList');
        }

        $data = [
            'id'            => $ids,
            'redirect_uri'  => $route
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Vehicle", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => _T('Remove vehicles', 'auto'),
                'message'       => str_replace(
                    '%count',
                    count($data['id']),
                    _Tn('You are about to remove %count vehicle.', 'You are about to remove %count vehicles.', count($data['id']), 'auto')
                ),
                'form_url'      => $this->router->pathFor('doRemoveVehicle'),
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
            $this->flash->addMessage(
                'error_detected',
                _T("Removal has not been confirmed!")
            );
        } else {
            if (!is_array($post['id'])) {
                $ids = (array)$post['id'];
            } else {
                $ids = $post['id'];
            }

            $autos = new Autos($this->plugins, $this->zdb);
            $del = $autos->removeVehicles($ids);

            if ($del !== true) {
                $error_detected = _T("An error occured trying to remove vehicles :/", "auto");

                $this->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%count',
                    count($ids),
                    _T("%count vehicles have been successfully deleted.", "auto")
                );

                $this->flash->addMessage(
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

    /**
     * Filtering
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function filter(Request $request, Response $response): Response
    {
        $post = $request->getParsedBody();

        $filters = $this->session->vehicles_filters ?? new AutosList();

        if (isset($post['clear_filter'])) {
            $filters->reinit();
        } else {
            if (isset($post['nbshow']) && is_numeric($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }

        $this->session->vehicles_filter = $filters;

        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor('vehiclesList')
            );
    }
}
