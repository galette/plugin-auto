{extends file="page.tpl"}

{block name="content"}
    {assign var='name' value=$obj->name}
    {assign var='pk' value=$obj->pk}
    {assign var='field' value=$obj->field}
    {assign var='list' value=$obj->getList()}
        <form action="" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{$field_name}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tbody>
    {foreach from=$list item=o name=obj_list}
                <tr class="{if $smarty.foreach.obj_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="_sel[]" value="{$o->$pk}"/>
                    </td>
                    <td><a href="object.php?set={$name}&#038;{if isset($show) and $show eq true}show{else}{$pk}{/if}={$o->$pk}">{$o->$field}</a></td>
                    <td class="center nowrap">
                        <a href="object.php?set={$set}&#038;{$pk}={$o->$pk}"><img src="{base_url}/{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a onclick="return confirm('{$delete_text}'.replace('%s', '{$o->$field}'))" href="object.php?set={$set}&#038;sup={$o->$pk}"><img src="{base_url}/{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
                    </td>
                </tr>
    {foreachelse}
                <tr><td colspan="3" class="emptylist">{_T string="no record found" domain="auto"}</td></tr>
    {/foreach}
            </tbody>
        </table>
    {if $list|@count gt 0}
        <div class="center cright">
            {_T string="Pages:"}<br/>
            <ul class="pages">{$pagination}</ul>
        </div>
    {/if}
            <ul class="selection_menu">
    {if $list|@count gt 0}
                <li>{_T string="Selection:"}</li>
                <li><input type="submit" id="delete" onclick="return confirm('{$deletes_text}');" name="delete" value="{_T string="Delete"}"/></li>
    {/if}
                <li>{_T string="Other:" domain="auto"}</li>
                <li><input type="submit" id="btnadd" name="donew" value="{$add_text}"/></li>
            </ul>
        </form>
{/block}

{block name="javascripts"}
    {if $list|@count gt 0}
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
