{assign var='name' value=$obj->name}
{assign var='pk' value=$obj->pk}
{assign var='field' value=$obj->field}
{assign var='list' value=$obj->getList()}
		<h1 id="titre">{$field_name}</h1>

		<form action="" method="post" id="listform">
		<table id="listing">
			<thead>
				<tr>
					<th class="listing actions_row"></th>
					<th class="listing">{$field_name}</th>
					<th class="listing actions_row">{_T string="Actions"}</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3" class="right">
						<input type="hidden" name="set" value="{$set}"/>
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
					</td>
				</tr>
			</tfoot>
			<tbody>
{foreach from=$list item=o name=obj_list}
				<tr>
					<td class="tbl_line_{if $smarty.foreach.obj_list.iteration % 2 eq 0}even{else}odd{/if}">
						<input type="checkbox" name="_sel[]" value="{$o->$pk}"/>
					</td>
					<td class="tbl_line_{if $smarty.foreach.obj_list.iteration % 2 eq 0}even{else}odd{/if}"><a href="object.php?set={$name}&#038;{if $show eq true}show{else}{$pk}{/if}={$o->$pk}">{$o->$field}</a></td>
					<td class="center nowrap tbl_line_{if $smarty.foreach.obj_list.iteration % 2 eq 0}even{else}odd{/if}">
						<a href="object.php?set={$set}&#038;{$pk}={$o->$pk}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
						<a onclick="return confirm('{$delete_text|escape:"javascript"}'.replace('%s', '{$o->$field}'))" href="object.php?set={$set}&#038;sup={$o->$pk}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
					</td>
				</tr>
{foreachelse}
				<tr><td colspan="3" class="emptylist">{_T string="no record found"}</td></tr>
{/foreach}
			</tbody>
		</table>
			<ul class="selection_menu">
{if $list|@count gt 0}
				<li>{_T string="Selection:"}</li>
				<li><input type="submit" id="delete" class="submit" onclick="return confirm('{$deletes_text|escape:"javascript"}');" name="delete" value="{_T string="Delete"}"/></li>
{/if}
				<li>{_T string="Other:"}</li>
				<li><input type="submit" id="donew" class="submit" name="donew" value="{$add_text}"/></li>
			</ul>
		</form>
