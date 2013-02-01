        <form action="" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{_T string="Model"}</th>
                    <th>{_T string="Brand"}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="4" class="right" id="table_footer">
{if $models|@count gt 0}
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
{foreach from=$models item=m name=models_list}
                <tr class="{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="_sel[]" value="{$m->id_model}"/>
                    </td>
                    <td><a href="models.php?id_model={$m->id_model}">{$m->model}</a></td>
                    <td><a href="models.php?id_model={$m->id_model}">{$m->brand}</a></td>
                    <td class="center nowrap">
                        <a href="models.php?id_model={$m->id_model}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a onclick="return confirm('{_T string="Do you really want to delete the model '%s'?"|escape:"javascript"}'.replace('%s', '{$m->model}'))" href="models.php?sup={$m->id_model}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
                    </td>
                </tr>
{foreachelse}
                <tr><td colspan="4" class="emptylist">{_T string="no record found"}</td></tr>
{/foreach}
            </tbody>
        </table>
            <ul class="selection_menu">
{if $models|@count gt 0}
                <li>{_T string="Selection:"}</li>
                <li><input type="submit" id="delete" onclick="return confirm('{_T string="Do you really want to delete selected models?"|escape:"javascript"}');" name="delete" value="{_T string="Delete"}"/></li>
{/if}
                <li>{_T string="Other:"}</li>
                <li><input type="submit" id="btnadd" name="donew" value="{_T string="Add new model"}"/></li>
            </ul>
        </form>
{if $models|@count gt 0}
        <script type="text/javascript">
        var _is_checked = true;
        var _bind_check = function(){ldelim}
            $('#checkall').click(function(){ldelim}
                $('table.listing :checkbox[name=member_sel[]]').each(function(){ldelim}
                    this.checked = _is_checked;
                {rdelim});
                _is_checked = !_is_checked;
                return false;
            {rdelim});
            $('#checkinvert').click(function(){ldelim}
                $('table.listing :checkbox[name=member_sel[]]').each(function(){ldelim}
                    this.checked = !$(this).is(':checked');
                {rdelim});
                return false;
            {rdelim});
        {rdelim}
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){ldelim}
            $('#table_footer').parent().before('<tr><td id="checkboxes" colspan="4"><span class="fleft"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></span></td></tr>');
            _bind_check();
        {rdelim});
        </script>
{/if}
