<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette Auto plugin main class
 *
 * PHP version 5
 *
 * Copyright © 2022 The Galette Team
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
 * @copyright 2022 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

namespace GaletteAuto;

use Galette\Entity\Adherent;
use Galette\Core\GalettePlugin;

/**
 * Galette Auto plugin main class
 *
 * @category  Plugins
 * @name      PluginGaletteAuto
 * @package   GaletteAuto
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2022 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class PluginGaletteAuto extends GalettePlugin
{
    /**
     * Extra menus entries
     *
     * @return array|array[]
     */
    public static function getMenusContents(): array
    {
        /** @var Login $login */
        global $login;
        $menus = [];

        if ($login->isLogged()) {
            if ($login->isAdmin() || $login->isStaff() || $login->isGroupManager()) {
                $menus['plugin_auto'] = [
                    'title' => _T("Cars", "auto"),
                    'icon' => 'car',
                    'items' => []
                ];
            }

            if ($login->isAdmin() || $login->isStaff()) {
                $menus['plugin_auto']['items'] = array_merge(
                    $menus['plugin_auto']['items'],
                    [
                        [
                            'label' => _T("Colors list", "auto"),
                            'title' => _T("Colors list", "auto"),
                            'route' => ['name' => 'colorsList']
                        ],
                        [
                            'label' => _T("States list", "auto"),
                            'title' => _T("States list", "auto"),
                            'route' => ['name' => 'statesList']
                        ],
                        [
                            'label' => _T("Finitions list", "auto"),
                            'title' => _T("Finitions list", "auto"),
                            'route' => ['name' => 'finitionsList']
                        ],
                        [
                            'label' => _T("Bodies list", "auto"),
                            'title' => _T("Bodies list", "auto"),
                            'route' => ['name' => 'bodiesList']
                        ],
                        [
                            'label' => _T("Transmissions list", "auto"),
                            'title' => _T("Transmissions list", "auto"),
                            'route' => ['name' => 'transmissionsList']
                        ],
                        [
                            'label' => _T("Brands list", "auto"),
                            'title' => _T("Brands list", "auto"),
                            'route' => ['name' => 'brandsList']
                        ],
                        [
                            'label' => _T("Models list", "auto"),
                            'title' => _T("Models list", "auto"),
                            'route' => ['name' => 'modelsList']
                        ],

                    ]
                );
            }

            if ($login->isAdmin() || $login->isStaff() || $login->isGroupManager()) {
                $menus['plugin_auto']['items'][] = [
                    'label' => _T("Cars list", "auto"),
                    'title' => _T("Cars list", "auto"),
                    'route' => ['name' => 'vehiclesList']
                ];
            }

            // Super Admin is not a regular user
            if (!$login->isSuperAdmin()) {
                $menus['myaccount'] = [
                    'items' => [
                        [
                            'label' => _T("My cars", "auto"),
                            'title' => _T("My cars", "auto"),
                            'route' => ['name' => 'myVehiclesList']
                        ]
                    ]
                ];
            }
        }

        return $menus;
    }

    /**
     * Extra public menus entries
     *
     * @return array|array[]
     */
    public static function getPublicMenusItemsList(): array
    {
        return [];
    }

    /**
     * Get dashboards contents
     *
     * @return array|array[]
     */
    public static function getDashboardsContents(): array
    {
        /** @var Login $login */
        global $login;

        if ($login->isSuperAdmin()) {
            return [];
        }

        return [
            [
                'label' => _T("My cars", "auto"),
                'title' => _T("My cars", "auto"),
                'route' => [
                    'name' => 'myVehiclesList'
                ],
                'icon' => 'truck pickup'
            ]
        ];
    }

    /**
     * Get actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array|array[]
     */
    public static function getListActionsContents(Adherent $member): array
    {
        return [
            [
                'label' => _T("Member cars", "auto"),
                'title' => _T("Member cars", "auto"),
                'route' => [
                    'name' => 'memberVehiclesList',
                    'args' => ['id' => $member->id]
                ],
                'icon' => 'truck pickup grey'
            ],
        ];
    }

    /**
     * Get detailed actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array|array[]
     */
    public static function getDetailedActionsContents(Adherent $member): array
    {
        return static::getListActionsContents($member);
    }

    /**
     * Get batch actions contents
     *
     * @return array|array[]
     */
    public static function getBatchActionsContents(): array
    {
        return [];
    }
}
