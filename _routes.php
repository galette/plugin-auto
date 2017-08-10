<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use Analog\Analog;
use GaletteAuto\Autos;
use GaletteAuto\AutosList;
use GaletteAuto\Auto;
use GaletteAuto\Controller;
use GaletteAuto\PropertiesController;

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
    __('/vehicles', 'auto_routes') . '[' . __('/member', 'routes') . '/{id:\d+}]',
    Controller::class . ':vehiclesList'
)->setName('vehiclesList')->add($authenticate);

$this->get(
    __('/my-vehicles', 'auto_routes') . '[' . __('/member', 'routes') . '/{id:\d+}]',
    Controller::class . ':myVehiclesList'
)->setName('myVehiclesList')->add($authenticate);

$this->get(
    __('/vehicle', 'auto_routes') . '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    Controller::class . ':showAddEditVehicle'
)->setName('vehicleEdit')->add($authenticate);

$this->post(
    __('/vehicle', 'auto_routes') . '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    Controller::class . ':doAddEditVehicle'
)->setName('doVehicleEdit')->add($authenticate);

$this->post(
    __('/ajax', 'routes') . __('/models', 'auto_routes'),
    Controller::class . ':ajaxModels'
)->setName('ajaxModels')->add($authenticate);

$this->get(
    __('/vehicle', 'auto_routes') . __('/remove', 'routes') . '/{id:\d+}',
    Controller::class . ':removeVehicle'
)->setName('removeVehicle')->add($authenticate);

$this->get(
    __('/vehicles', 'auto_routes') . __('/remove', 'routes'),
    Controller::class . ':removeVehicles'
)->setName('removeVehicles')->add($authenticate);

$this->post(
    __('/vehicle', 'auto_routes') . __('/remove', 'routes') . '[/{id:\d+}]',
    Controller::class . ':doRemoveVehicle'
)->setName('doRemoveVehicle')->add($authenticate);

//Batch actions on vehicles list
$this->post(
    __('/vehicles', 'auto_routes') . __('/batch', 'routes'),
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
    __('/models', 'auto_routes') . '[/{option:' . __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':modelsList'
)->setName('modelsList')->add($authenticate);

$this->get(
    __('/models', 'auto_routes'). '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    PropertiesController::class . ':modelEdit'
)->setName('modelEdit')->add($authenticate);

$this->post(
    __('/models', 'auto_routes'). '/{action:' . __('add', 'routes') . '|' . __('edit', 'routes') .  '}[/{id:\d+}]',
    PropertiesController::class . ':doModelEdit'
)->setName('doModelEdit')->add($authenticate);

$this->get(
    __('/model', 'auto_routes') . __('/remove', 'routes') . '/{id:\d+}',
    PropertiesController::class . ':removeModel'
)->setName('removeModel')->add($authenticate);

$this->get(
    __('/models', 'auto_routes') . __('/remove', 'routes'),
    PropertiesController::class . ':removeModels'
)->setName('removeModels')->add($authenticate);

$this->post(
    __('/model', 'auto_routes') . __('/remove', 'routes') . '[/{id:\d+}]',
    PropertiesController::class . ':doRemoveModel'
)->setName('doRemoveModel')->add($authenticate);

//Batch actions on models list
$this->post(
    __('/models', 'auto_routes') . __('/batch', 'routes'),
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
    __('/brands', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':brandsList'
)->setName('brandsList')->add($authenticate);

$this->get(
    __('/colors', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':colorsList'
)->setName('colorsList')->add($authenticate);

$this->get(
    __('/states', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':statesList'
)->setName('statesList')->add($authenticate);

$this->get(
    __('/finitions', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':finitionsList'
)->setName('finitionsList')->add($authenticate);

$this->get(
    __('/bodies', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':bodiesList'
)->setName('bodiesList')->add($authenticate);

$this->get(
    __('/transmissions', 'auto_routes') . '[/{option:' .
    __('page', 'routes') . '|' . __('order', 'routes') . '}/{value:\d+}]',
    PropertiesController::class . ':transmissionsList'
)->setName('transmissionsList')->add($authenticate);
