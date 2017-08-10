{if $login->isLogged()}
        <h1 class="nojs">{_T string="Cars" domain="auto"}</h1>
        <ul>
    {if $login->isAdmin() || $login->isStaff()}
            <li{if $cur_route eq "colorsList"} class="selected"{/if}><a href="{path_for name="colorsList"}">{_T string="Colors list" domain="auto"}</a></li>
            <li{if $cur_route eq "statesList"} class="selected"{/if}><a href="{path_for name="statesList"}">{_T string="States list" domain="auto"}</a></li>
            <li{if $cur_route eq "finitionsList"} class="selected"{/if}><a href="{path_for name="finitionsList"}">{_T string="Finitions list" domain="auto"}</a></li>
            <li{if $cur_route eq "bodiesList"} class="selected"{/if}><a href="{path_for name="bodiesList"}">{_T string="Bodies list" domain="auto"}</a></li>
            <li{if $cur_route eq "transmissionsList"} class="selected"{/if}><a href="{path_for name="transmissionsList"}">{_T string="Transmissions list" domain="auto"}</a></li>
            <li{if $cur_route eq "brandsList"} class="selected"{/if}><a href="{path_for name="brandsList"}">{_T string="Brands list" domain="auto"}</a></li>
            <li{if $cur_route eq "modelsList"} class="selected"{/if}><a href="{path_for name="modelsList"}">{_T string="Models list" domain="auto"}</a></li>
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
