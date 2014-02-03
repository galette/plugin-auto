        <form action="" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{_T string="Name"}</th>
                    <th>{_T string="Brand"}</th>
                    <th>{_T string="Model"}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tfoot>
                <tr id="table_footer">
                    <td colspan="5" class="center">
{if $autos|@count gt 0}
                        {_T string="Pages:"}
                        <ul class="pages">{$pagination}</ul>
{/if}
                    </td>
                </tr>
            </tfoot>
            <tbody>
{foreach from=$autos item=auto name=autos_list}
    {assign var='brand' value=$auto->model->obrand}
                <tr class="{if $smarty.foreach.autos_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="vehicle_sel[]" value="{$auto->id}"/>
                    </td>
                    <td><a href="{if $show_mine eq 1}my_{/if}vehicles_edit.php?id_car={$auto->id}">{$auto->name}</a></td>
                    <td><a href="{if $show_mine eq 1}my_{/if}vehicles_edit.php?id_car={$auto->id}">{$brand->value}</a></td>
                    <td><a href="{if $show_mine eq 1}my_{/if}vehicles_edit.php?id_car={$auto->id}">{$auto->model->model}</a></td>
                    <td class="center nowrap">
                        <a href="{if $show_mine eq 1}my_{/if}vehicles_edit.php?id_car={$auto->id}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a onclick="return confirm('{_T string="Do you really want to delete the car '%s'?"|escape:"javascript"}'.replace('%s', '{$auto->name}'))" href="{if $show_mine eq 1}my_{/if}vehicles_list.php?sup={$auto->id}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
                    </td>
                </tr>
{foreachelse}
                <tr><td colspan="5" class="emptylist">{if $show_mine eq 1}{_T string="No car has been registered yet for your account."}{else}{_T string="No car in the database"}{/if}</td></tr>
{/foreach}
            </tbody>
        </table>
            <ul class="selection_menu">
{if $autos|@count gt 0}
                <li>{_T string="Selection:"}</li>
                <li><input type="submit" id="delete" onclick="return confirm('{_T string="Do you really want to delete selected vehicles?"|escape:"javascript"}');" name="delete" value="{_T string="Delete"}"/></li>
{/if}
                <li>{_T string="Other:"}</li>
                <li><input type="submit" id="btnadd" name="donew" value="{_T string="Add new vehicle"}"/></li>
            </ul>
{if isset($id_adh)}
            <input type="hidden" name="id_adh" value="{$id_adh}"/>
{/if}
        </form>
{if $autos|@count gt 0}
        <script type="text/javascript">
        //<![CDATA[
        var _is_checked = true;
        var _bind_check = function(){
            $('#checkall').click(function(){
                $('table.listing :checkbox[name=_sel[]]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
            });
            $('#checkinvert').click(function(){
                $('table.listing :checkbox[name=_sel[]]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){
            $('#table_footer').before('<tr><td class="left" colspan="5"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></td></tr>');
            _bind_check();
        });
        //]]>
        </script>
{/if}
