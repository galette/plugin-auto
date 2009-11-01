		<h1 id="titre">{_T string="Cars list"}</h1>

		<form action="" method="post" id="listform">
		<table id="listing">
			<thead>
				<tr>
					<th class="listing actions_row"></th>
					<th class="listing">{_T string="Name"}</th>
					<th class="listing">{_T string="Brand"}</th>
					<th class="listing">{_T string="Model"}</th>
					<th class="listing actions_row">{_T string="Actions"}</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4" class="right" id="table_footer">
{if $autos|@count gt 0}
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
	{assign var='brand' value=$auto->model->obrand}
				<tr>
					<td class="tbl_line_{if $smarty.foreach.autos_list.iteration % 2 eq 0}even{else}odd{/if}">
						<input type="checkbox" name="_sel[]" value="{$auto->id}"/>
					</td>
					<td class="tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="vehicles_edit.php?id_car={$auto->id}">{$auto->name}</a></td>
					<td class="tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="vehicles_edit.php?id_car={$auto->id}">{$brand->value}</a></td>
					<td class="tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="vehicles_edit.php?id_car={$auto->id}">{$auto->model->model}</a></td>
					<td class="center nowrap tbl_line_{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
						<a href="vehicles_edit.php?id_car={$auto->id}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
						<a onclick="return confirm('{_T string="Do you really want to delete the car '%s'?"|escape:"javascript"}'.replace('%s', '{$auto->name}'))" href="vehicles_list.php?sup={$auto->id}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
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
{if $autos|@count gt 0}
		<script type="text/javascript">
		//<![CDATA[
		var _is_checked = true;
		var _bind_check = function(){ldelim}
			$('#checkall').click(function(){ldelim}
				$('#listing :checkbox[name=_sel[]]').each(function(){ldelim}
					this.checked = _is_checked; 
				{rdelim});
				_is_checked = !_is_checked;
			{rdelim});
			$('#checkinvert').click(function(){ldelim}
				$('#listing :checkbox[name=_sel[]]').each(function(){ldelim}
					this.checked = !$(this).is(':checked'); 
				{rdelim});
			{rdelim});
		{rdelim}
		{* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
		$(function(){ldelim}
			$('#table_footer').append('<span class="fleft"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></span>');
			_bind_check();
			{* $('#nbshow').change(function() {ldelim}
				this.form.submit();
			{rdelim});*}
		{rdelim});
		//]]>
		</script>
{/if}
