{if $login->isLogged()}
        <h1 class="nojs">{_T string="Cars" domain="auto"}</h1>
        <ul>
    {if $login->isAdmin() || $login->isStaff()}
            <li{if $cur_route eq "colorsList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='color' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="colorsList"}">{_T string="Colors list" domain="auto"}</a></li>
            <li{if $cur_route eq "statesList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='state' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="statesList"}">{_T string="States list" domain="auto"}</a></li>
            <li{if $cur_route eq "finitionsList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='finition' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="finitionsList"}">{_T string="Finitions list" domain="auto"}</a></li>
            <li{if $cur_route eq "bodiesList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='body' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="bodiesList"}">{_T string="Bodies list" domain="auto"}</a></li>
            <li{if $cur_route eq "transmissionsList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='transmission' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="transmissionsList"}">{_T string="Transmissions list" domain="auto"}</a></li>
            <li{if $cur_route eq "brandsList" || ($cur_route eq 'propertyEdit' && $cur_subroute eq {_T string='brand' domain='auto_routes'}) || ($cur_route eq 'propertyShow' && $cur_subroute eq {_T string='brand' domain='auto_routes'})} class="selected"{/if}><a href="{path_for name="brandsList"}">{_T string="Brands list" domain="auto"}</a></li>
            <li{if $cur_route eq "modelsList" || $cur_route eq "modelEdit"} class="selected"{/if}><a href="{path_for name="modelsList"}">{_T string="Models list" domain="auto"}</a></li>
    {/if}
    {if $login->isAdmin() || $login->isStaff() || $login->isGroupManager()}
            <li{if $cur_route eq "vehiclesList"} class="selected"{/if}><a href="{path_for name="vehiclesList"}">{_T string="Cars list" domain="auto"}</a></li>
    {/if}
    {* Super Admin is not a regular user *}
    {if !$login->isSuperAdmin()}
            <li{if $cur_route eq "myVehiclesList"} class="selected"{/if}><a href="{path_for name="myVehiclesList"}">{_T string="My Cars" domain="auto"}</a></li>
    {/if}
        </ul>
{/if}
