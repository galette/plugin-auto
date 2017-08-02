<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette Auto plugin controller for properties (brands, models, colors, ...)
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
 * @since     Available since 2017-07-21
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

/**
 * Galette Auto plugin controller for properties (brands, models, colors, ...)
 *
 * @category  Plugins
 * @name      Autos
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2017-07-21
 */
class PropertiesController extends Controller
{
    /**
     * List models
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function modelsList(Request $request, Response $response, $args = [])
    {
        $numrows = $this->container->preferences->pref_numrows;
        if (isset($_GET['nbshow'])) {
            if (is_numeric($_GET['nbshow'])) {
                $numrows = $_GET['nbshow'];
            }
        }

        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }

        if (isset($this->container->session->filter_automodels)) {
            $mfilters = $this->container->session->filter_automodels;
        } else {
            $mfilters = new ModelsList();
        }

        if ($option !== null) {
            switch ($option) {
                case __('page', 'routes'):
                    $mfilters->current_page = (int)$value;
                    break;
                case __('order', 'routes'):
                    $mfilters->orderby = $value;
                    break;
            }
        }

        $models = new Models(
            $this->container->zdb,
            $this->container->preferences,
            $this->container->login,
            $mfilters
        );

        $module = $this->getModule();
        $smarty = $this->container->view->getSmarty();
        $smarty->addTemplateDir(
            $module['root'] . '/templates/' . $this->container->preferences->pref_theme,
            $module['route']
        );
        $smarty->compile_id = AUTO_SMARTY_PREFIX;

        //assign pagination variables to the template and add pagination links
        $mfilters->setSmartyPagination($this->container->router, $smarty, false);
        $this->container->session->filter_automodels = $mfilters;

        $params = [
            'page_title'    => _T("Models list", "auto"),
            'models'        => $models->getList(),
            'require_dialog'=> true
        ];

        // display page
        $this->container->view->render(
            $response,
            'file:[' . $module['route'] . ']models_list.tpl',
            $params
        );
        return $response;
    }

    /**
     * Add/edit model
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function modelEdit(Request $request, Response $response, $args = [])
    {
        $action = $args['action'];
        $id = null;
        $is_new = $args['action'] === __('add', 'routes');
        if (isset($args['id'])) {
            $id = (int)$args['id'];
        }

        if (!$is_new && $id === null) {
            throw new \RuntimeException(
                _T("Model ID cannot ben null calling edit route!", "auto")
            );
        } elseif ($is_new && $id !== null) {
             return $response
                ->withStatus(301)
                ->withHeader('Location', $this->router->pathFor('modelEdit', ['action' => __('add', 'routes')]));
        }

        $model = new Model($this->container->zdb);
        if ($is_new) {
            $title = _T("New model", "auto");
        } else {
            $model->load($id);
            $title = str_replace(
                '%s',
                $model->model,
                _T("Change model '%s'", "auto")
            );
        }

        if ($this->container->session->model !== null) {
            $model->check($this->container->session->model);
            $this->container->session->model = null;
        }

        $brand = new Brand();

        $params = [
            'page_title'        => $title,
            'mode'              => ($is_new ? 'new' : 'modif'),
            'model'             => $model,
            'brands'            => $brand->getList(),
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
            'file:[' . $module['route'] . ']model.tpl',
            $params
        );
        return $response;
    }

    /**
     * Do add/edit model
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function doModelEdit(Request $request, Response $response, $args = [])
    {
        $post = $request->getParsedBody();
        $is_new = $args['action'] === __('add', 'routes');

        $model = new Model($this->container->zdb);
        $error_detected = [];

        if (!$is_new) {
            if (isset($post[Model::PK])) {
                $model->load($post[Model::PK]);
            } else {
                $error_detected[]
                    = _T("- No id provided for modifying this record! (internal)", "auto");
            }
        }

        if (!$model->check($post)) {
            $error_detected = $model->getErrors();
        }

        if (count($error_detected) == 0) {
            $res = $model->store($is_new);
            if (!$res) {
                $error_detected[]
                    = _T("- An error occured while saving record. Please try again.", "auto");
            } else {
                $msg = $is_new ? _T("New model has been added!", "auto") :
                    _T("Model has been saved!", "auto");
                $this->container->flash->addMessage(
                    'success_detected',
                    $msg
                );
            }
        }

        $route = $this->container->router->pathFor('modelsList');
        if (count($error_detected) > 0) {
            //store entity in session
            $this->container->session->model = $post;
            if (!$is_new && !isset($args[Model::PK])) {
                $args['id'] = $post[Model::PK];
            }
            $route = $this->container->router->pathFor('modelEdit', $args);

            foreach ($error_detected as $error) {
                $this->container->flash->addMessage(
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
     * Remove model confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function removeModel(Request $request, Response $response, $args = [])
    {
        $model = new Model($this->container->zdb);
        $model->load((int)$args['id']);
        $route = $this->container->router->pathFor('modelsList');

        $data = [
            'id'            => $args['id'],
            'redirect_uri'  => $route
        ];

        // display page
        $this->container->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Model", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove model %1$s', 'auto'),
                    $model->model
                ),
                'form_url'      => $this->container->router->pathFor('doRemoveModel', ['id' => $model->id]),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Remove models confirmation page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function removeModels(Request $request, Response $response, $args = [])
    {
        $route = $this->container->router->pathFor('modelsList');
        $ids = $this->container->session->filter_automodels_sel;

        $data = [
            'id'            => $ids,
            'redirect_uri'  => $route
        ];

        // display page
        $this->container->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T("Model", "auto"),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => _T('Remove models', 'auto'),
                'message'       => str_replace(
                    '%count',
                    count($data['id']),
                    _T('You are about to remove %count models.', 'auto')
                ),
                'form_url'      => $this->container->router->pathFor('doRemoveModel'),
                'cancel_uri'    => $route,
                'data'          => $data
            )
        );
        return $response;
    }

    /**
     * Do remove model
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $args     Optionnal args
     *
     * @return Response
     */
    public function doRemoveModel(Request $request, Response $response, $args = [])
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

            $model = new Model($this->container->zdb);
            $del = $model->delete($ids);

            if ($del !== true) {
                $error_detected = _T("An error occured trying to remove models :/", "auto");

                $this->container->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%count',
                    count($ids),
                    _T("%count models have been successfully deleted.", "auto")
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
