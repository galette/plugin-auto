<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Auto models controller
 *
 * PHP version 5
 *
 * Copyright © 2020 The Galette Team
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
 * @category  Controllers
 * @package   GaletteAuto
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2020-12-09
 */

namespace GaletteAuto\Controllers\Crud;

use Galette\Controllers\Crud\AbstractPluginController;
use GaletteAuto\Brand;
use GaletteAuto\Filters\ModelsList;
use GaletteAuto\Model;
use GaletteAuto\Repository\Models;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Galette auto models controller
 *
 * @category  Controllers
 * @name      ModelsController
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2020-12-09
 */

class ModelsController extends AbstractPluginController
{
    /**
     * @Inject("Plugin Galette Auto")
     * @var integer
     */
    protected $module_info;

    // CRUD - Create

    /**
     * Add page
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function add(Request $request, Response $response): Response
    {
        return $this->edit($request, $response, null, 'add');
    }

    /**
     * Add action
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function doAdd(Request $request, Response $response): Response
    {
        return $this->doEdit($request, $response, null, 'add');
    }

    // /CRUD - Create
    // CRUD - Read

    /**
     * List page
     *
     * @param Request        $request  PSR Request
     * @param Response       $response PSR Response
     * @param string         $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function list(Request $request, Response $response, $option = null, $value = null): Response
    {
        if (isset($this->session->filter_automodels)) {
            $mfilters = $this->session->filter_automodels;
        } else {
            $mfilters = new ModelsList();
        }

        $get = $request->getQueryParams();
        if (isset($get['nbshow']) && is_numeric($get['nbshow'])) {
            $mfilters->show = $get['nbshow'];
        }

        if ($option !== null) {
            switch ($option) {
                case 'page':
                    $mfilters->current_page = (int)$value;
                    break;
                case 'order':
                    $mfilters->orderby = $value;
                    break;
            }
        }

        $models = new Models(
            $this->zdb,
            $this->preferences,
            $this->login,
            $mfilters
        );
        $list = $models->getList();

        //call after getList
        $this->session->filter_automodels = $mfilters;

        //assign pagination variables to the template and add pagination links
        $mfilters->setViewPagination($this->router, $this->view);

        $params = [
            'page_title'     => _T("Models list", "auto"),
            'models'         => $list,
            'count_models'   => $models->getCount(),
            'require_dialog' => true,
            'filters'        => $mfilters,
        ];

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('models_list'),
            $params
        );
        return $response;
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
        $filters = $this->session->filter_automodels ?? new ModelsList();

        if (isset($post['clear_filter'])) {
            $filters->reinit();
        } else {
            if (isset($post['nbshow']) && is_numeric($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }

        $this->session->filter_automodels = $filters;

        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('modelsList'));
    }

    // /CRUD - Read
    // CRUD - Update

    /**
     * Edit page
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param int|null $id       Model id
     * @param string   $action   Action
     *
     * @return Response
     */
    public function edit(Request $request, Response $response, int $id = null, $action = 'edit'): Response
    {
        $model = new Model($this->zdb);

        if ($this->session->automodel !== null) {
            $model->check($this->session->auto_model);
            $this->session->automodel = null;
        }

        $model_id = null;
        if ($id !== null) {
            $model_id = $id;
        }

        if ($action === 'edit') {
            // initialize model structure with database values
            $model->load($model_id);
            if ($model->id == '') {
                //not possible to load, exit
                throw new \RuntimeException('Model does not exists!');
            }
        }

        // template variable declaration
        if ($action === 'edit') {
            $title = sprintf(
                _T("Change model '%s'", "auto"),
                $model->model
            );
        } else {
            $title = _T("New model", "auto");
            $get = $request->getQueryParams();
            if (isset($get['brand'])) {
                $model->setBrand((int)$get['brand']);
            }
        }

        $brand = new Brand($this->zdb);
        $params = [
            'page_title'        => $title,
            'mode'              => ($action === 'add' ? 'new' : 'modif'),
            'model'             => $model,
            'brands'            => $brand->getList(),
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']model.tpl',
            $params
        );
        return $response;
    }

    /**
     * Edit action
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param null|int $id       Model id for edit
     * @param string   $action   Either add or edit
     *
     * @return Response
     */
    public function doEdit(Request $request, Response $response, int $id = null, $action = 'edit'): Response
    {
        $post = $request->getParsedBody();
        $is_new = ($action === 'add');

        $model = new Model($this->zdb);
        $error_detected = [];

        if (!$is_new) {
            $model->load($post[Model::PK]);
        }

        if (!$model->check($post)) {
            $error_detected = $model->getErrors();
        }

        if (count($error_detected) === 0) {
            $res = $model->store($is_new);
            if (!$res) {
                $error_detected[]
                    = _T("- An error occurred while saving record. Please try again.", "auto");
            } else {
                $msg = $is_new ? _T("New model has been added!", "auto") :
                    _T("Model has been saved!", "auto");
                $this->flash->addMessage(
                    'success_detected',
                    $msg
                );
            }
        }

        $route = $this->router->pathFor('modelsList');
        if (count($error_detected) > 0) {
            //store entity in session
            $this->session->auto_model = $post;
            if (!$is_new) {
                $id = $post[Model::PK];
            }
            $route = $this->router->pathFor(
                'modelEdit',
                [
                    'action'    => $action,
                    'id'        => $id
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

    // /CRUD - Update
    // CRUD - Delete

    /**
     * Get redirection URI
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function redirectUri(array $args): string
    {
        return $this->router->pathFor('modelsList');
    }

    /**
     * Get form URI
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function formUri(array $args): string
    {
        return $this->router->pathFor(
            'doRemoveModel',
            $args
        );
    }

    /**
     * Get confirmation removal page title
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function confirmRemoveTitle(array $args): string
    {
        if (isset($args['ids'])) {
            $count = count($args['ids']);
            return sprintf(
                //TRANS: first parameter is the number of models
                _Tn('Remove %1$s model', 'Remove %1$s models', $count, 'auto'),
                $count,
            );
        } else {
            $model = new Model($this->zdb);
            $model->load((int)$args['id']);
            return sprintf(
                //TRANS: first parameter is the model name
                _T('Remove model "%1$s"', 'auto'),
                $model->model
            );
        }
    }

    /**
     * Remove object
     *
     * @param array $args Route arguments
     * @param array $post POST values
     *
     * @return boolean
     */
    protected function doDelete(array $args, array $post): bool
    {
        $model = new Model($this->zdb);

        if (!is_array($post['id'])) {
            $ids = (array)$post['id'];
        } else {
            $ids = $post['id'];
        }

        return  $model->delete($ids);
    }

    // /CRUD - Delete
    // /CRUD
}
