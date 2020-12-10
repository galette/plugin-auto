{extends file="page.tpl"}

{block name="content"}
        <form action="{path_for name="batch-vehicleslist"}" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{_T string="Name" domain="auto"}</th>
                    <th>{_T string="Brand" domain="auto"}</th>
                    <th>{_T string="Model" domain="auto"}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tbody>
{foreach from=$autos item=auto name=autos_list}
    {assign var='brand' value=$auto->model->obrand}
    {assign var='edit_link' value={path_for name="vehicleEdit" data=["action" => "edit", "id" => $auto->id]}}
                <tr class="{if $smarty.foreach.autos_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="vehicle_sel[]" value="{$auto->id}"/>
                    </td>
                    <td><a href="{$edit_link}">{$auto->name}</a></td>
                    <td><a href="{$edit_link}">{$brand->value}</a></td>
                    <td><a href="{$edit_link}">{$auto->model->model}</a></td>
                    <td class="center nowrap">
                        <a href="{$edit_link}" class="tooltip action">
                            <i class="fas fa-edit"></i>
                            <span class="sr-only">{_T string="Edit %vehicle" domain="auto" pattern="/%vehicle/" replace=$auto->name}</span>
                        </a>
                        <a
                            class="delete tooltip"
                            href="{path_for name="removeVehicle" data=["id" => $auto->id]}"
                        >
                            <i class="fas fa-trash"></i>
                            <span class="sr-only">{_T string="%vehiclename: remove from database" pattern="/%vehiclename/" replace=$auto->name domain="auto"}</span>
                        </a>
                    </td>
                </tr>
{foreachelse}
                <tr><td colspan="5" class="emptylist">{if $show_mine eq 1}{_T string="No car has been registered yet for your account." domain="auto"}{else}{_T string="No car in the database" domain="auto"}{/if}</td></tr>
{/foreach}
            </tbody>
        </table>

{if $autos|@count gt 0}
        <div class="center cright">
            {_T string="Pages:"}<br/>
            <ul class="pages">{$pagination}</ul>
        </div>
{/if}
            <ul class="selection_menu">
{if $autos|@count gt 0}
                <li>{_T string="For the selection:"}</li>
                <li>
                    <button type="submit" id="delete" name="delete" class="delete">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                        {_T string="Delete"}
                    </button>
                </li>
{/if}
                <li>{_T string="Other:" domain="auto"}</li>
                <li>
                    <a
                        class="button"
                        href="{path_for name="vehicleEdit" data=["action" => "add"]}"
                    >
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                        {_T string="Add new vehicle" domain="auto"}
                    </a>
                </li>
            </ul>
{if isset($id_adh)}
            <input type="hidden" name="id_adh" value="{$id_adh}"/>
{/if}
        </form>
{/block}

{block name="javascripts"}
    {if $autos|@count gt 0}
        <script type="text/javascript">
        {include file="js_removal.tpl"}
        //<![CDATA[
        var _is_checked = true;
        var _bind_check = function(){
            $('#checkall').click(function(){
                $('table.listing :checkbox[name="vehicle_sel[]"]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
                return false;
            });
            $('#checkinvert').click(function(){
                $('table.listing :checkbox[name="vehicle_sel[]"]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
                return false;
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant if JS is inactive *}
        $(function(){
            var _checklinks = '<div class="checkboxes"><a href="#" class="checkall tooltip"><i class="fas fa-check-square"></i> {_T string="(Un)Check all" escape="js"}</a> | <a href="#" class="checkinvert tooltip"><i class="fas fa-exchange-alt"></i> {_T string="Invert selection" escape="js"}</a></div>';
            $('.listing').before(_checklinks);
            $('.listing').after(_checklinks);
            _bind_check();
        });
        //]]>
        </script>
    {/if}
{/block}
