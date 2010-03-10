		<h1 class="nojs">{_T string="Cars"}</h1>
		<ul>
{if $login->isAdmin()}
			<li{if $PAGENAME eq "object.php" and $set eq "colors"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=colors">{_T string="Colors list"}</a></li>
			<li{if $PAGENAME eq "object.php" and $set eq "states"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=states">{_T string="States list"}</a></li>
			<li{if $PAGENAME eq "object.php" and $set eq "finitions"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=finitions">{_T string="Finitions list"}</a></li>
			<li{if $PAGENAME eq "object.php" and $set eq "bodies"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=bodies">{_T string="Bodies list"}</a></li>
			<li{if $PAGENAME eq "object.php" and $set eq "transmissions"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=transmissions">{_T string="Transmissions list"}</a></li>
			<li{if $PAGENAME eq "object.php" and $set eq "brands"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}object.php?set=brands">{_T string="Brands list"}</a></li>
			<li{if $PAGENAME eq "model.php"} class="selected"{/if}><a href="{$galette_base_path}{$galette_auto_path}models.php">{_T string="Models list"}</a></li>
			<li class="mnu_last{if $PAGENAME eq "vehicles_list.php" or $PAGENAME eq "vehicles_edit.php"} selected{/if}"><a href="{$galette_base_path}{$galette_auto_path}vehicles_list.php">{_T string="Cars list"}</a></li>
{/if}
{* Super Admin is not a regular user *}
{if !$login->isSuperAdmin()}
			<li class="mnu_last{if $PAGENAME eq "my_vehicles.php" or $PAGENAME eq "my_vehicles_edit.php"} selected{/if}"><a href="{$galette_base_path}{$galette_auto_path}my_vehicles.php">{_T string="My Cars"}</a></li>
{/if}
		</ul>