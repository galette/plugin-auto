<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Autos list paginator
 *
 * PHP version 5
 *
 * Copyright © 2012-2023 The Galette Team
 *
 * This file is part of Galette (https://galette.eu).
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
 * @category  Filters
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2012-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      https://galette.eu
 * @since     0.73dev 2012-10-16
 */

namespace GaletteAuto\Filters;

use Galette\Core\Pagination;
use Laminas\Db\Sql\Select;

/**
 * Autos list filters and paginator
 *
 * @name      AutosList
 * @category  Filters
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2012-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     0.7.3dev - 2012-12-29
 */

class AutosList extends Pagination
{
    /**
     * Returns the field we want to default set order to
     *
     * @return string field name
     */
    protected function getDefaultOrder(): string
    {
        return 'car_name';
    }

    /**
     * Add SQL limit
     *
     * @param Select $select Original select
     *
     * @return self
     */
    public function setLimit(Select $select): self
    {
        $this->setLimits($select);
        return $this;
    }


    /**
     * Build href
     *
     * @param int $page Page
     *
     * @return string
     */
    protected function getHref(int $page): string
    {
        $args = [
            'option'    => 'page',
            'value'     => $page
        ];

        if ($this->view->getEnvironment()->getGlobals()['cur_subroute']) {
            $args['type'] = $this->view->getEnvironment()->getGlobals()['cur_subroute'];
        }

        if ($this->view->getEnvironment()->getGlobals()['cur_route'] === 'memberVehiclesList') {
            $args['id'] = $this->view->getEnvironment()->getGlobals()['cur_subroute'];
        }

        $href = $this->routeparser->urlFor(
            $this->view->getEnvironment()->getGlobals()['cur_route'],
            $args
        );
        return $href;
    }
}
