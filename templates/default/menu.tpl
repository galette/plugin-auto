		<div class="nav1">
			<h1>{_T string="Cars"}</h1>
			<ul>
{if $login->isAdmin()}
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=colors">{_T string="Colors list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=states">{_T string="States list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=finitions">{_T string="Finitions list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=bodies">{_T string="Bodies list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=transmissions">{_T string="Transmissions list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}object.php?set=brands">{_T string="Brands list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}models.php">{_T string="Models list"}</a></li>
				<li><a href="{$galette_base_path}{$galette_auto_path}vehicles_list.php">{_T string="Cars list"}</a></li>
{/if}
{* Super Admin is not a regular user *}
{if !$login->isSuperAdmin()}
				<li><a href="{$galette_base_path}{$galette_auto_path}.php">{_T string="My Cars"}</a></li>
{/if}
			</ul>
		</div>