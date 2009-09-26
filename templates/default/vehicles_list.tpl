		<h1 id="titre">{_T string="Vehicles list"}</h1>

		<form action="" method="post" id="listform">
		<table id="listing">
			<thead>
				<tr>
					<th class="listing actions_row"></th>
					<th class="listing">{_T string="Model"}</th>
					<th class="listing">{_T string="Brand"}</th>
					<th class="listing actions_row">{_T string="Actions"}</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4" class="right">
{if $autos|@count gt 0}
						<a href="#" onclick="check();" class="fleft">{_T string="(Un)Check all"}</a>
						{_T string="Pages:"}
						<span class="pagelink">
						{* {section name="pageLoop" start=1 loop=$nb_pages+1}
							{if $smarty.section.pageLoop.index eq $page}
								{$smarty.section.pageLoop.index}
							{else}
								<a href="colors.php?nbshow={$smarty.get.nbshow}&amp;page={$smarty.section.pageLoop.index}">{$smarty.section.pageLoop.index}</a>
							{/if}
						{/section} *}
						</span>
{/if}
					</td>
				</tr>
			</tfoot>
			<tbody>
{foreach from=$autos item=auto name=autos_list}
				<tr>
					<td class="tbl_line_{if $smarty.foreach.autos_list.iteration % 2 eq 0}even{else}odd{/if}">
						<input type="checkbox" name="_sel[]" value="{*{$auto->id_model}*}"/>
					</td>
					<td class="tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="vehicles.php?id={*{$auto->id_model}*}">{*{$m->model}*}</a></td>
					<td class="tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="models.php?id_model={*{$m->id_model}*}">{*{$m->brand}*}</a></td>
					<td class="center nowrap tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
						<a href="models.php?id_model={*{$m->id_model}*}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
						<a onclick="return confirm('{_T string="Do you really want to delete the model '%s'?"|escape:"javascript"}'.replace('%s', '{*{$m->model}*}'))" href="vehicles.php?sup={*{$m->id_model}*}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
					</td>
				</tr>
{foreachelse}
				<tr><td colspan="4" class="emptylist">{_T string="no record found"}</td></tr>
{/foreach}
			</tbody>
		</table>
			<ul class="selection_menu">
{if $autos|@count gt 0}
				<li>{_T string="Selection:"}</li>
				<li><input type="submit" id="delete" class="submit" onclick="return confirm('{_T string="Do you really want to delete selected vehicles?"|escape:"javascript"}');" name="delete" value="{_T string="Delete"}"/></li>
{/if}
				<li>{_T string="Other:"}</li>
				<li><input type="submit" id="donew" class="submit" name="donew" value="{_T string="Add new vehicle"}"/></li>
			</ul>
		</form>
