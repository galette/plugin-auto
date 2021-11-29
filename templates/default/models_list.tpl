{extends file="page.tpl"}

{block name="content"}
        <form action="{path_for name="modelsFilter"}" method="post" id="filtre">
        <div class="infoline">
            {_T string="%count model" plural="%count models" count=$count_models pattern="/%count/" replace=$count_models}
            <div class="fright">
                <label for="nbshow">{_T string="Records per page:"}</label>
                <select name="nbshow" id="nbshow">
                    {html_options options=$nbshow_options selected=$numrows}
                </select>
                <noscript> <span><input type="submit" value="{_T string="Change"}" /></span></noscript>
                {include file="forms_types/csrf.tpl"}
            </div>
        </div>
        </form>
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
            <tbody>
    {foreach from=$models item=m name=models_list}
        {assign var='edit_link' value={path_for name="modelEdit" data=["id" => $m->id]}}
                <tr class="{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="models_sel[]" value="{$m->id}"/>
                    </td>
                    <td><a href="{$edit_link}">{$m->model}</a></td>
                    <td><a href="{$edit_link}">{$m->obrand->value}</a></td>
                    <td class="center nowrap">
                        <a href="{$edit_link}" class="tooltip action">
                            <i class="fas fa-edit"></i>
                            <span class="sr-only">{_T string="Edit %property" domain="auto" pattern="/%property/" replace={$m->obrand->value|cat:' '|cat:$m->model}}</span>
                        </a>
                        <a
                            class="delete tooltip"
                            href="{path_for name="removeModel" data=["id" => $m->id]}"
                        >
                            <i class="fas fa-trash"></i>
                            <span class="sr-only">{_T string="%property: remove from database" pattern="/%property/" replace={$m->obrand->value|cat:' '|cat:$m->model} domain="auto"}</span>
                        </a>
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
            <li>{_T string="For the selection:"}</li>
            <button type="submit" id="delete" name="delete" class="delete">
                <i class="fas fa-trash" aria-hidden="true"></i>
                {_T string="Delete"}
            </button>
    {/if}
            <li>{_T string="Other:" domain="auto"}</li>
            <a
                class="button"
                href="{path_for name="modelAdd"}"
            >
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                {_T string="Add new model" domain="auto"}
            </a>
        </ul>
        {include file="forms_types/csrf.tpl"}
    </form>
{/block}

{block name="javascripts"}
    {if $models|@count gt 0}
        <script type="text/javascript">
        var _checkselection = function() {
            var _checkeds = $('table.listing').find('input[type=checkbox]:checked').length;
            if ( _checkeds == 0 ) {
                var _el = $('<div id="pleaseselect" title="{_T string="No model selected" escape="js" domain="auto"}">{_T string="Please make sure to select at least one model from the list to perform this action." escape="js" domain="auto"}</div>');
                _el.appendTo('body').dialog({
                    modal: true,
                    buttons: {
                        Ok: function() {
                            $(this).dialog( "close" );
                        }
                    },
                    close: function(event, ui){
                        _el.remove();
                    }
                });
                return false;
            }
            return true;
        }

        {include file="js_removal.tpl"}
        {include file="js_removal.tpl" selector="#delete" deleteurl="'{path_for name="removeModels"}'" extra_check="if (!_checkselection()) {ldelim}return false;{rdelim}" extra_data="delete: true, models_sel: $('#listform input[type=\"checkbox\"]:checked').map(function(){ return $(this).val(); }).get()" method="POST"}
        var _is_checked = true;
        var _bind_check = function(){
            $('#checkall').click(function(){
                $('table.listing :checkbox[name="models_sel[]"]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
                return false;
            });
            $('#checkinvert').click(function(){
                $('table.listing :checkbox[name="models_sel[]"]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
                return false;
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){
            var _checklinks = '<div class="checkboxes"><a href="#" class="checkall tooltip"><i class="fas fa-check-square"></i> {_T string="(Un)Check all" escape="js"}</a> | <a href="#" class="checkinvert tooltip"><i class="fas fa-exchange-alt"></i> {_T string="Invert selection" escape="js"}</a></div>';
            $('.listing').before(_checklinks);
            $('.listing').after(_checklinks);
            _bind_check();
        });
        </script>
    {/if}
{/block}
