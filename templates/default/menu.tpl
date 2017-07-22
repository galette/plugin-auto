{if $login->isLogged()}
        <h1 class="nojs">{_T string="Cars" domain="auto"}</h1>
        <ul>
    {if $login->isAdmin() || $login->isStaff()}
            <li{if $PAGENAME eq "object.php" and $set eq "colors"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=colors">{_T string="Colors list" domain="auto"}</a></li>
            <li{if $PAGENAME eq "object.php" and $set eq "states"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=states">{_T string="States list" domain="auto"}</a></li>
            <li{if $PAGENAME eq "object.php" and $set eq "finitions"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=finitions">{_T string="Finitions list" domain="auto"}</a></li>
            <li{if $PAGENAME eq "object.php" and $set eq "bodies"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=bodies">{_T string="Bodies list" domain="auto"}</a></li>
            <li{if $PAGENAME eq "object.php" and $set eq "transmissions"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=transmissions">{_T string="Transmissions list" domain="auto"}</a></li>
            <li{if $PAGENAME eq "object.php" and $set eq "brands"} class="selected"{/if}><a href="{$galette_base_path}{$galette_galette_auto_path}object.php?set=brands">{_T string="Brands list" domain="auto"}</a></li>
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
