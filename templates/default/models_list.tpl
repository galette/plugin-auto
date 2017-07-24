{extends file="page.tpl"}

{block name="content"}
        <form action="" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{_T string="Model" domain="auto"}</th>
                    <th>{_T string="Brand" domain="auto"}</th>
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
        {assign var='edit_link' value={path_for name="modelEdit" data=["action" => {_T string="edit" domain="routes"}, "id" => $m->id]}}
                <tr class="{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="_sel[]" value="{$m->id}"/>
                    </td>
                    <td><a href="{$edit_link}">{$m->model}</a></td>
                    <td><a href="{$edit_link}">{$m->obrand->value}</a></td>
                    <td class="center nowrap">
                        <a href="{$edit_link}"><img src="{base_url}/{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a onclick="return confirm('{_T string="Do you really want to delete the model '%s'?" escape="js" domain="auto"}'.replace('%s', '{$m->model}'))" href="models.php?sup={$m->id}"><img src="{base_url}/{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
                    </td>
                </tr>
    {foreachelse}
                <tr><td colspan="4" class="emptylist">{_T string="no record found" domain="auto"}</td></tr>
    {/foreach}
            </tbody>
        </table>
    {if $models|@count gt 0}
        <div class="center cright">
            {_T string="Pages:"}<br/>
            <ul class="pages">{$pagination}</ul>
        </div>
    {/if}
        <ul class="selection_menu">
    {if $models|@count gt 0}
            <li>{_T string="Selection:"}</li>
            <li><input type="submit" id="delete" onclick="return confirm('{_T string="Do you really want to delete selected models?" escape="js" domain="auto"}');" name="delete" value="{_T string="Delete"}"/></li>
    {/if}
            <li>{_T string="Other:" domain="auto"}</li>
            <li><a class="button" href="{path_for name="modelEdit" data=["action" => {_T string="add" domain="routes"}]}" id="btnadd">{_T string="Add new model" domain="auto"}</a></li>
        </ul>
    </form>
{/block}

{block name="javascripts"}
    {if $models|@count gt 0}
        <script type="text/javascript">
        var _is_checked = true;
        var _bind_check = function(){
            $('#checkall').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
                return false;
            });
            $('#checkinvert').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
                return false;
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){
            $('#table_footer').parent().before('<tr><td id="checkboxes" colspan="4"><span class="fleft"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></span></td></tr>');
            _bind_check();
        });
        </script>
    {/if}
{/block}
