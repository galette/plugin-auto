<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette Auto plugin controller for properties (brands, models, colors, ...)
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
 * @since     Available since 2017-07-21
 */

namespace GaletteAuto\Controllers\Crud;

use Galette\Controllers\AbstractPluginController;
use GaletteAuto\AbstractObject;
use GaletteAuto\Body;
use GaletteAuto\Brand;
use GaletteAuto\Color;
use GaletteAuto\Finition;
use GaletteAuto\Model;
use GaletteAuto\State;
use GaletteAuto\Transmission;
use Slim\Http\Request;
use Slim\Http\Response;
use GaletteAuto\Filters\ModelsList;
use GaletteAuto\Filters\PropertiesList;
use GaletteAuto\Repository\Models;

/**
 * Galette Auto plugin controller for properties (brands, models, colors, ...)
 *
 * @category  Plugins
 * @name      Autos
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-21
 */
class PropertiesController extends AbstractPluginController
{
    /**
     * @Inject("Plugin Galette Auto")
     * @var integer
     */
    protected $module_info;

    /**
     * List brands
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function brandsList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'brands', $option, $value);
    }

    /**
     * List colors
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function colorsList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'colors', $option, $value);
    }

    /**
     * List states
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function statesList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'states', $option, $value);
    }

    /**
     * List finitions
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function finitionsList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'finitions', $option, $value);
    }

    /**
     * List bodies
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function bodiesList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'bodies', $option, $value);
    }

    /**
     * List transmissions
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function transmissionsList(
        Request $request,
        Response $response,
        string $option = null,
        $value = null
    ): Response {
        return $this->propertiesList($request, $response, 'transmissions', $option, $value);
    }

    /**
     * List properties
     *
     * @param Request        $request  Request
     * @param Response       $response Response
     * @param string         $property Property name
     * @param string|null    $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    protected function propertiesList(
        Request $request,
        Response $response,
        string $property,
        string $option = null,
        $value = null
    ): Response {
        $get = $request->getQueryParams();

        switch ($property) {
            case 'colors':
                $obj = new Color($this->zdb);
                $title = _T("Colors list", "auto");
                $add_text = _T("Add new color", "auto");
                break;
            case 'states':
                $obj = new State($this->zdb);
                $title = _T("States list", "auto");
                $add_text = _T("Add new state", "auto");
                break;
            case 'finitions':
                $obj = new Finition($this->zdb);
                $title = _T("Finitions list", "auto");
                $add_text = _T("Add new finition", "auto");
                break;
            case 'bodies':
                $obj = new Body($this->zdb);
                $title = _T("Bodies list", "auto");
                $add_text = _T("Add new body", "auto");
                break;
            case 'transmissions':
                $obj = new Transmission($this->zdb);
                $title = _T("Transmissions list", "auto");
                $add_text = _T("Add new transmission", "auto");
                break;
            case 'brands':
                $obj = new Brand($this->zdb);
                $title = _T("Brands list", "auto");
                $show_title = _T("Brand '%s'", "auto");
                $add_text = _T("Add new brand", "auto");
                $can_show = true;
                break;
            default:
                throw new \RuntimeException('Unknown property ' . $property);
                break;
        }

        $filters = $this->getFilters($obj);
        if (isset($get['nbshow']) && is_numeric($get['nbshow'])) {
            $filters->show = $get['nbshow'];
        }
        $obj->setFilters($filters);

        switch ($option) {
            case 'page':
                $filters->current_page = (int)$value;
                break;
            case 'order':
                $filters->orderby = $value;
                break;
        }

        $this->saveFilters($obj, $filters);

        //assign pagination variables to the template and add pagination links
        $filters->setViewPagination($this->router, $this->view, false);

        $params = [
            'page_title'    => $title,
            //'models'        => $models->getList(),
            'set'           => $property,
            'field_name'    => $obj->getFieldLabel(),
            'add_text'      => $add_text,
            'obj'           => $obj,
            'require_dialog' => true
        ];

        if (isset($can_show)) {
            $params['show'] = $can_show;
        }

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('object_list'),
            $params
        );
        return $response;
    }

    /**
     * Filtering
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param string   $property Property name
     *
     * @return Response
     */
    public function filter(Request $request, Response $response, string $property): Response
    {
        $post = $request->getParsedBody();
        $class = '\GaletteAuto\\' . ucwords($property);
        $filters = $this->getFilters($class);

        if (isset($post['clear_filter'])) {
            $filters->reinit();
        } else {
            if (isset($post['nbshow']) && is_numeric($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }

        $this->saveFilters($class, $filters);

        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $class::getListRoute($this->router, $property)
            );
    }

    /**
     * Add/edit property
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $action   'add' or 'edit'
     * @param string   $property Property name
     * @param integer  $id       Property ID, if any
     *
     *
     * @return Response
     */
    public function propertyEdit(Request $request, Response $response, string $action, string $property, int $id = null)
    {
        $is_new = ($action === 'add');

        if (!$is_new && $id === null) {
            throw new \RuntimeException(
                str_replace(
                    '%property',
                    $property,
                    _T("%property ID cannot ben null calling edit route!", "auto")
                )
            );
        } elseif ($is_new && $id !== null) {
             return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor(
                    'propertyEdit',
                    [
                        'property'  => $property,
                        'action'    => 'add'
                    ]
                ));
        }

        $classname = AbstractObject::getClassForPropName($property);
        $object = new $classname($this->zdb);
        if ($is_new) {
            $title = _T("New", "auto");
        } else {
            $object->load($id);
            $title = str_replace(
                '%s',
                $object->{$object::FIELD},
                _T("Change '%s'", "auto")
            );
        }

        $session_oname = 'auto_' . $property;
        if ($this->session->$session_oname !== null) {
            $object = $this->session->$session_oname;
            $this->session->$session_oname = null;
        }

        $params = [
            'page_title'    => $title,
            'mode'          => ($is_new ? 'new' : 'modif'),
            'obj'           => $object,
            'set'           => $property,
        ];

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('object'),
            $params
        );
        return $response;
    }

    /**
     * Do add/edit property
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $action   'add' or 'edit'
     * @param string   $property Property name
     * @param integer  $id       Property ID, if any
     *
     *
     * @return Response
     */
    public function doPropertyEdit(
        Request $request,
        Response $response,
        string $action,
        string $property,
        int $id = null
    ): Response {
        $classname = AbstractObject::getClassForPropName($property);
        $object = new $classname($this->zdb);

        $post = $request->getParsedBody();
        $is_new = ($action === 'add');

        $error_detected = [];

        if (!$is_new) {
            if (isset($post[$object->pk])) {
                $object->load($post[$object->pk]);
            } else {
                $error_detected[]
                    = _T("- No id provided for modifying this record! (internal)", "auto");
            }
        }

        $value = $request->getParsedBodyParam($object->field, $default = null);
        if ($value == null) {
            $error_detected[] = _T("- You must provide a value!", "auto");
        } else {
            $object->value = $value;
        }

        if (count($error_detected) == 0) {
            $res = $object->store($is_new);
            if (!$res) {
                $error_detected[]
                    = _T("- An error occured while saving record. Please try again.", "auto");
            } else {
                $msg = str_replace(
                    '%property',
                    $object->getFieldLabel(),
                    $is_new ? _T("New %property has been added!", "auto") :
                    _T("%property has been saved!", "auto")
                );
                $this->flash->addMessage(
                    'success_detected',
                    $msg
                );
            }
        }

        $route = AbstractObject::getListRoute($this->router, $property);

        if (count($error_detected) > 0) {
            //store entity in session
            $session_oname = 'auto_' . $property;
            $this->session->$session_oname = $object;
            if (!$is_new) {
                $id = $post[$object->pk];
            }
            $route = $this->router->pathFor(
                'propertyEdit',
                [
                    'action' => $action,
                    'property' => $property,
                    'id' => $id
                ]
            );

            foreach ($error_detected as $error) {
                $this->flash->addMessage(
                    'error_detected',
                    $error
                );
            }
        }

        return $response
            ->withStatus(301)
            ->withHeader('Location', $route);
    }

    /**
     * Show property
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $property Property name
     * @param integer  $id       Property ID, if any
     *
     *
     * @return Response
     */
    public function propertyShow(Request $request, Response $response, string $property, int $id)
    {
        $classname = AbstractObject::getClassForPropName($property);
        $object = new $classname($this->zdb);
        $object->load($id);
        $title = str_replace(
            '%s',
            $object->{$object::FIELD},
            _T("Show '%s' brand", "auto")
        );

        $params = [
            'page_title'    => $title,
            'obj'           => $object
        ];

        if ($object instanceof \GaletteAuto\Brand) {
            $models = new Models(
                $this->zdb,
                $this->preferences,
                $this->login,
                new ModelsList()
            );
            $params['models'] = $models->getList($object->id);
        }

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('object_show'),
            $params
        );
        return $response;
    }

    /**
     * Remove property confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $property Property name
     * @param integer  $id       Property id
     *
     * @return Response
     */
    public function removeProperty(Request $request, Response $response, string $property, int $id)
    {
        $classname = AbstractObject::getClassForPropName($property);
        $object = new $classname($this->zdb);
        $object->load($id);

        $route = AbstractObject::getListRoute($this->router, $property);

        $data = [
            'id'            => $id,
            'property'      => $property,
            'redirect_uri'  => $route
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => $object->getFieldLabel(),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove %1$s %2$s', 'auto'),
                    $object->getFieldLabel(),
                    $object->value
                ),
                'form_url'      => $this->router->pathFor(
                    'doRemoveProperty',
                    ['property' => $property, 'id' => $object->id]
                ),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Remove properties confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $property Property name
     *
     * @return Response
     */
    public function removeProperties(Request $request, Response $response, string $property)
    {
        $classname = AbstractObject::getClassForPropName($property);
        $object = new $classname($this->zdb);

        $route = AbstractObject::getListRoute($this->router, $property);
        $filter_name = 'filter_auto' . $property . '_sel';
        $ids = $this->session->$filter_name;

        $data = [
            'id'            => $ids,
            'redirect_uri'  => $route
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => $object->getFieldLabel(),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove %1$s %2$s', 'auto'),
                    $object->getFieldLabel(),
                    $object->value
                ),
                'message'       => str_replace(
                    ['%count', '%property'],
                    [count($data['id']), $object->getFieldLabel()],
                    _T('You are about to remove %count %property.', 'auto')
                ),
                'form_url'      => $this->router->pathFor('doRemoveProperty', ['property' => $property]),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Do remove property
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param string   $property Property name
     * @param integer  $id       Property id
     *
     * @return Response
     */
    public function doRemoveProperty(Request $request, Response $response, string $property, int $id = null)
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

            $model = new Model($this->zdb);
            $del = $model->delete($ids);

            $classname = AbstractObject::getClassForPropName($property);
            $object = new $classname($this->zdb);
            $del = $object->delete($ids);

            if ($del !== true) {
                $error_detected = str_replace(
                    '%property',
                    $object->getFieldLabel(),
                    _T('An error occured trying to remove %property :/', 'auto')
                );

                $this->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    ['%count', '%property'],
                    [count($ids), $object->getFieldLabel()],
                    _T("%count %property have been successfully deleted.", "auto")
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
     * Get filters
     *
     * @param AbstractObject|string $class Class name or instance
     *
     * @return PropertiesList
     */
    protected function getFilters($class): PropertiesList
    {
        $filter_name = 'filter_auto' . $class::FIELD;
        return $this->session->$filter_name ?? new PropertiesList($class::FIELD);
    }

    /**
     * Save filters
     *
     * @param AbstractObject|string $class   Class name or instance
     * @param PropertiesList        $filters Filters instance
     *
     * @return void
     */
    protected function saveFilters($class, PropertiesList $filters): void
    {
        $filter_name = 'filter_auto' . $class::FIELD;
        $this->session->$filter_name = $filters;
    }
}
