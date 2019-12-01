<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

use Analog\Analog;
use GaletteAuto\Autos;
use GaletteAuto\AutosList;
use GaletteAuto\Auto;
use GaletteAuto\Controller;
use GaletteAuto\PropertiesController;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

$this->get(
    '/vehicle/photo[/{id:\d+}]',
    function ($request, $response, $args) {
        $id = isset($args['id']) ? $args['id'] : '';
        $picture = new GaletteAuto\Picture($this->plugins, $id);
        $picture->display();
    }
)->setName('vehiclePhoto');

$this->get(
    '/vehicles[/{option:page|order}/{value:\d+}]',
    Controller::class . ':vehiclesList'
)->setName('vehiclesList')->add($authenticate);

$this->get(
    '/member/{id:\d+}/vehicles[/{option:page|order}/{value:\d+}]',
    Controller::class . ':vehiclesList'
)->setName('memberVehiclesList')->add($authenticate);

$this->get(
    '/my-vehicles',
    Controller::class . ':myVehiclesList'
)->setName('myVehiclesList')->add($authenticate);

$this->get(
    '/vehicle/{action:add|edit}[/{id:\d+}]',
    Controller::class . ':showAddEditVehicle'
)->setName('vehicleEdit')->add($authenticate);

$this->post(
    '/vehicle/{action:add|edit}[/{id:\d+}]',
    Controller::class . ':doAddEditVehicle'
)->setName('doVehicleEdit')->add($authenticate);

$this->get(
    '/vehicle/history[/{id:\d+}]',
    Controller::class . ':vehicleHistory'
)->setName('vehicleHistory')->add($authenticate);

$this->post(
    '/ajax/models',
    Controller::class . ':ajaxModels'
)->setName('ajaxModels')->add($authenticate);

$this->get(
    '/vehicle/remove/{id:\d+}',
    Controller::class . ':removeVehicle'
)->setName('removeVehicle')->add($authenticate);

$this->get(
    '/vehicles/remove',
    Controller::class . ':removeVehicles'
)->setName('removeVehicles')->add($authenticate);

$this->post(
    '/vehicle/remove[/{id:\d+}]',
    Controller::class . ':doRemoveVehicle'
)->setName('doRemoveVehicle')->add($authenticate);

//Batch actions on vehicles list
$this->post(
    '/vehicles/batch',
    function ($request, $response) {
        $post = $request->getParsedBody();

        if (isset($post['vehicle_sel'])) {
            $this->session->filter_vehicles = $post['vehicle_sel'];

            if (isset($post['delete'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('removeVehicles'));
            }
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T("No vehicle was selected, please check at least one name.", "auto")
            );

            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('myVehiclesList'));
        }
    }
)->setName('batch-vehicleslist')->add($authenticate);

$this->get(
    '/models[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':modelsList'
)->setName('modelsList')->add($authenticate);

$this->get(
    '/models/{action:add|edit}[/{id:\d+}]',
    PropertiesController::class . ':modelEdit'
)->setName('modelEdit')->add($authenticate);

$this->post(
    '/models/{action:add|edit}[/{id:\d+}]',
    PropertiesController::class . ':doModelEdit'
)->setName('doModelEdit')->add($authenticate);

$this->get(
    '/model/remove/{id:\d+}',
    PropertiesController::class . ':removeModel'
)->setName('removeModel')->add($authenticate);

$this->get(
    '/models/remove',
    PropertiesController::class . ':removeModels'
)->setName('removeModels')->add($authenticate);

$this->post(
    '/model/remove[/{id:\d+}]',
    PropertiesController::class . ':doRemoveModel'
)->setName('doRemoveModel')->add($authenticate);

//Batch actions on models list
$this->post(
    '/models/batch',
    function ($request, $response) {
        $post = $request->getParsedBody();

        if (isset($post['_sel'])) {
            $this->session->filter_automodels_sel = $post['_sel'];

            if (isset($post['delete'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('removeModels'));
            }
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T("No model was selected, please check at least one name.", "auto")
            );

            return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('modelsList'));
        }
    }
)->setName('batch-modelslist')->add($authenticate);

$this->get(
    '/brands[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':brandsList'
)->setName('brandsList')->add($authenticate);

$this->get(
    '/colors[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':colorsList'
)->setName('colorsList')->add($authenticate);

$this->get(
    '/states[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':statesList'
)->setName('statesList')->add($authenticate);

$this->get(
    '/finitions[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':finitionsList'
)->setName('finitionsList')->add($authenticate);

$this->get(
    '/bodies[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':bodiesList'
)->setName('bodiesList')->add($authenticate);

$this->get(
    '/transmissions[/{option:page|order}/{value:\d+}]',
    PropertiesController::class . ':transmissionsList'
)->setName('transmissionsList')->add($authenticate);

$this->get(
    '/{property:brand|color|state|finition|body|transmission}/{action:add|edit}[/{id:\d+}]',
    PropertiesController::class . ':propertyEdit'
)->setName('propertyEdit')->add($authenticate);

$this->post(
    '/{property:brand|color|state|finition|body|transmission}/{action:add|edit}[/{id:\d+}]',
    PropertiesController::class . ':doPropertyEdit'
)->setName('doPropertyEdit')->add($authenticate);

$this->get(
    '/{property:brand}/show/{id:\d+}',
    PropertiesController::class . ':propertyShow'
)->setName('propertyShow')->add($authenticate);

//Batch actions on properties lists
$this->post(
    '/{property:brand|color|state|finition|body|transmission}/batch',
    function ($request, $response, $args) {
        $post = $request->getParsedBody();

        if (isset($post['_sel'])) {
            $filter_name = 'filter_auto' . $args['property'] . '_sel';
            $this->session->$filter_name = $post['_sel'];

            if (isset($post['delete'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor(
                        'removeProperties',
                        ['property' => $args['property']]
                    ));
            }
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T("No entry was selected, please check at least one.", "auto")
            );

            $route = null;
            switch ($property) {
                case 'color':
                    $route = $this->container->router->pathFor('colorsList');
                    break;
                case 'state':
                    $route = $this->container->router->pathFor('statesList');
                    break;
                case 'finition':
                    $route = $this->container->router->pathFor('finitionsList');
                    break;
                case 'body':
                    $route = $this->container->router->pathFor('bodiesList');
                    break;
                case 'transmission':
                    $route = $this->container->router->pathFor('transmissionsList');
                    break;
                case 'brand':
                    $route = $this->container->router->pathFor('brandsList');
                    break;
                default:
                    throw new \RuntimeException('Unknown property ' . $property);
                    break;
            }

            return $response
                ->withStatus(301)
                ->withHeader('Location', $route);
        }
    }
)->setName('batch-propertieslist')->add($authenticate);

$this->get(
    '/{property:brand|color|state|finition|body|transmission}/remove/{id:\d+}',
    PropertiesController::class . ':removeProperty'
)->setName('removeProperty')->add($authenticate);

$this->get(
    '/{property:brand|color|state|finition|body|transmission}'. '/remove',
    PropertiesController::class . ':removeProperties'
)->setName('removeProperties')->add($authenticate);

$this->post(
    '/{property:brand|color|state|finition|body|transmission}/remove[/{id:\d+}]',
    PropertiesController::class . ':doRemoveProperty'
)->setName('doRemoveProperty')->add($authenticate);
