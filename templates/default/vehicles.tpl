{extends file="page.tpl"}

{block name="content"}
        {if $mode eq 'new'}
            {assign var="action" value="add"}
        {else}
            {assign var="action" value="edit"}
        {/if}
        <form action="{path_for name="doVehicleEdit" data=["action" => $action]}" method="post" id="modifform" enctype="multipart/form-data">
        <div class="bigtable">
            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Car's base informations" domain="auto"}</legend>
                <div>
                <p>
                    <label for="name" class="bline">{_T string="Name:" domain="auto"}</label>
                    <input type="text" name="name" id="name" value="{$car->name}" maxlength="20"{if isset($required.name)} required="required"{/if}/>
                </p>
                <p>
                    <span class="bline">
                        <label for="brand">{_T string="Brand" domain="auto"}</label>/<label for="model">{_T string="Model:" domain="auto"}</label>
                    </span>
                    <select name="brand" id="brand"{if isset($required.brand)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a brand" domain="auto"}</option>
    {foreach from=$brands item=brand}
                        <option value="{$brand->id_brand}"{if $brand->id_brand eq $car->model->brand} selected="selected"{/if}>{$brand->brand}</option>
    {/foreach}
                    </select>
                    <select name="model" id="model" class="nochosen"{if isset($required.model)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a model" domain="auto"}</option>
    {foreach from=$models item=model}
                        <option value="{$model->id}"{if $model->id eq $car->model->id} selected="selected"{/if}>{$model->model}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="first_registration_date" class="bline">{_T string="First registration date:" domain="auto"}</label>
                    <input type="text" name="first_registration_date" id="first_registration_date" value="{$car->first_registration_date}" maxlength="20"{if isset($required.first_registration_date)} required="required"{/if}/>
                </p>
                <p>
                    <label for="first_circulation_date" class="bline">{_T string="First circulation date:" domain="auto"}</label>
                    <input type="text" name="first_circulation_date" id="first_circulation_date" value="{$car->first_circulation_date}" maxlength="20"{if isset($required.first_circulation_date)} required="required"{/if}/>
                </p>
                <p>
                    <label for="mileage" class="bline">{_T string="Mileage:" domain="auto"}</label>
                    <input type="number" name="mileage" id="mileage" value="{$car->mileage}" maxlength="20"{if isset($required.mileage)} required="required"{/if}/>
                </p>
                <p>
                    <label for="seats" class="bline">{_T string="Seats:" domain="auto"}</label>
                    <input type="text" name="seats" id="seats" value="{$car->seats}"{if isset($required.seats)} required="required"{/if}/>
                </p>
                </div>
            </fieldset>

            <fieldset class="galette_form">
                <legend class="ui-state-active ui-corner-top">{_T string="Car's photo" domain="auto"}</legend>
                <p>
                    <span class="bline vtop">{_T string="Picture:" domain="auto"}</span>
                    <img src="{if $car->id}{path_for name="vehiclePhoto" data=["id" => $car->id]}{else}{path_for name="vehiclePhoto"}{/if}" class="picture" width="{$car->picture->getOptimalWidth()}" height="{$car->picture->getOptimalHeight()}" alt="{_T string="Car's photo" domain="auto"}"/><br/>
{if $car->hasPicture() }
                    <span class="labelalign"><label for="del_photo">{_T string="Delete image" domain="auto"}</label></span><input type="checkbox" name="del_photo" id="del_photo" value="1"/><br/>
{/if}
                    <input class="labelalign" type="file" name="photo"/>
                </p>

            </fieldset>

            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Current car's state informations" domain="auto"}</legend>
                <div>
{if $car->id}
                    <p>
                        <span class="bline">{_T string="Current owner:" domain="auto"}</span>
                        <span>
                            {$car->owner->sfullname}
    {if $login->isAdmin() || $login->isStaff()}
                            {* Does car's history should be visible by the actual owner? *}
                            <a href="{path_for name="vehicleHistory" data=["id" => $car->id]}" title="{_T string="Show full car state history" domain="auto"}" id="state_history">
                                <i class="fa fa-history"></i>
                                <span class="sr-only">{_T string="Car state history" domain="auto"}</span>
                            </a>
                            <input type="checkbox" name="change_owner" id="change_owner" title="{_T string="Change car's owner" domain="auto"}" value="1"/>
                            <label for="change_owner" title="{_T string="Change car's owner" domain="auto"}">{_T string="Change owner" domain="auto"}</label>
    {/if}
                        </span>
                    </p>
{/if}
                    <p id="owners_list"{if $car->id} class="hidden"{/if}>
{if not $car->id}
                        <input type="hidden" name="change_owner" id="change_owner" value="1"/>
{/if}
                        <label class="bline" for="owner">{_T string="Owner:" domain="auto"}</label>
{if $login->isAdmin() || $login->isStaff()}
                        <select name="owner" id="owner" class="nochosen"{if not $car->id} required="required"{/if}>
                            <option value="">{_T string="Search for name or ID and pick member"}</option>
                            {foreach $members.list as $k=>$v}
                                <option value="{$k}"{if $car->owner->id == $k} selected="selected"{/if}>{$v}</option>
                            {/foreach}
                        </select>
{else}
                        <input type="hidden" name="owner" value="{$car->owner->id}"/>
                        {$members.list[$car->owner->id]}
{/if}
                    </p>
                <p>
                    <label for="color" class="bline">{_T string="Color:" domain="auto"}</label>
                    <select name="color" id="color"{if isset($required.color)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a color" domain="auto"}</option>
    {foreach from=$colors item=color}
                        <option value="{$color->id_color}"{if $color->id_color eq $car->color->id} selected="selected"{/if}>{$color->color}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="state" class="bline">{_T string="State:" domain="auto"}</label>
                    <select name="state" id="state"{if isset($required.state)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a state" domain="auto"}</option>
    {foreach from=$states item=state}
                        <option value="{$state->id_state}"{if $state->id_state eq $car->state->id} selected="selected"{/if}>{$state->state}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="registration" class="bline">{_T string="Registration:" domain="auto"}</label>
                    <input type="text" name="registration" id="registration" value="{$car->registration}"{if isset($required.registration)} required="required"{/if}/>
                </p>
                </div>
            </fieldset>
            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Car's technical informations" domain="auto"}</legend>
                <div>
                <p>
                    <label class="bline" for="body">{_T string="Body:" domain="auto"}</label>
                    <select name="body" id="body"{if isset($required.body)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a body" domain="auto"}</option>
    {foreach from=$bodies item=body}
                        <option value="{$body->id_body}"{if $body->id_body eq $car->body->id} selected="selected"{/if}>{$body->body}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label class="bline" for="transmission">{_T string="Transmission:" domain="auto"}</label>
                    <select name="transmission" id="transmission"{if isset($required.transmission)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a transmission" domain="auto"}</option>
    {foreach from=$transmissions item=transmission}
                        <option value="{$transmission->id_transmission}"{if $transmission->id_transmission eq $car->transmission->id} selected="selected"{/if}>{$transmission->transmission}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label class="bline" for="finition">{_T string="Finition:" domain="auto"}</label>
                    <select name="finition" id="finition"{if isset($required.finition)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a finition" domain="auto"}</option>
    {foreach from=$finitions item=finition}
                        <option value="{$finition->id_finition}"{if $finition->id_finition eq $car->finition->id} selected="selected"{/if}>{$finition->finition}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="chassis_number" class="bline">{_T string="Chassis number:" domain="auto"}</label>
                    <input type="text" name="chassis_number" id="chassis_number" value="{$car->chassis_number}"{if isset($required.chassis_number)} required="required"{/if}/>
                </p>
                <p>
                    <label for="horsepower" class="bline">{_T string="Horsepower:" domain="auto"}</label>
                    <input type="text" name="horsepower" id="horsepower" value="{$car->horsepower}"{if isset($required.horsepower)} required="required"{/if}/>
                </p>
                <p>
                    <label for="engine_size" class="bline">{_T string="Engine size:" domain="auto"}</label>
                    <input type="text" name="engine_size" id="engine_size" value="{$car->engine_size}"{if isset($required.engine_size)} required="required"{/if}/>
                </p>
                <p>
                    <label for="fuel" class="bline">{_T string="Fuel:" domain="auto"}</label>
                    <select name="fuel" id="fuel"{if isset($required.fuel)} required="required"{/if}>
                        <option value="-1">{_T string="Choose a fuel" domain="auto"}</option>
    {foreach from=$fuels key=k item=fuel}
                        <option value="{$k}"{if $k eq $car->fuel} selected="selected"{/if}>{$fuel}</option>
    {/foreach}
                    </select>
                </p>
                </div>
            </fieldset>
            <fieldset class="galette_form">
                <legend class="ui-state-active ui-corner-top">{_T string="Comment" domain="auto"}</legend>
                <div>
                <p>
                    <label for="comment" class="bline vtop">{_T string="Comment:" domain="auto"}</label>
                    <textarea name="comment" id="comment" cols="80" rows="3"{if isset($required.comment)} required="required"{/if}>{$car->comment}</textarea>
                </p>
                </div>
            </fieldset>
        </div>
        <div class="button-container">
            <button type="submit" id="btnsave" name="valid" class="action">
                <i class="fas fa-save fa-fw"></i> {_T string="Save"}
            </button>
            <input type="hidden" name="{$mode}" value="1"/>
            <input type="hidden" name="id_car" value="{$car->id}"/>
            {include file="forms_types/csrf.tpl"}
        </div>
        </form>
{/block}

{block name="javascripts"}
        <script type="text/javascript">
            var _models;
            {include file="js_chosen_adh.tpl" js_chosen_id="#owner"}
            $(function() {
                _collapsibleFieldsets();

                $('#first_circulation_date').datepicker({
                    changeMonth: true,
                    changeYear: true,
                    showOn: 'button',
                    buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>',
                    maxDate: '-0d',
                    yearRange: 'c-100:c+0'
                });
                $('#first_registration_date').datepicker({
                    changeMonth: true,
                    changeYear: true,
                    showOn: 'button',
                    buttonText: '<i class="far fa-calendar-alt"></i> <span class="sr-only">{_T string="Select a date" escape="js"}</span>',
                    maxDate: '-0d',
                    yearRange: 'c-100:c+0'
                });

                var _modelChoose = $('#model :first');
                _models = $('#model').selectize({
                    maxItems:       1,
                    render: {
                        option: function(item, escape) {
                            return '<div class="option">' + escape(item.text) + '</div>';
                        }
                    }
                });
    {if $js_init_models}
                {* If javascript is active, we do not want complete models list when page loads *}
                {* Empty model list *}
                _models[0].selectize.clear();
                _models[0].selectize.clearOptions();
                {* Set the first option *}
                _models[0].selectize.settings.placeholder = _modelChoose.text();
                _models[0].selectize.updatePlaceholder();
    {/if}
                {* Refresh models list when brand is changed *}
                $('#brand').on('change', function(){
                    var id_brand = $('#brand option:selected').attr('value');
                    {* Empty model list *}
                    _models[0].selectize.clear();
                    _models[0].selectize.clearOptions();
                    {* Set the first option *}
                    _models[0].selectize.settings.placeholder = _modelChoose.text();
                    _models[0].selectize.updatePlaceholder();
                    {* Get the new list for selected brand, and appent it to models on the page *}
                    $.post(
                        '{path_for name="ajaxModels"}',
                        { brand: id_brand },
                        function(data){
                            $(data).each(function(i){
                                var _data = data[i];
                                _models[0].selectize.addOption({
                                    value: _data.id_model,
                                    text: _data.model
                                });
                            });
                        },
                        'json'
                    );
                });
    {if $login->isAdmin() || $login->isStaff()}
                {* Popup for owner change *}
                $('#change_owner').change(function(e){
                    e.preventDefault();
                    $('#owners_list').toggleClass('hidden');
                    $('#owners_list').backgroundFade(
                        {
                            sColor:'#ffffff',
                            eColor:'#DDDDFF',
                            steps:10
                        },
                        function() {
                            $(this).backgroundFade(
                                {
                                    sColor:'#DDDDFF',
                                    eColor:'#ffffff'
                                }
                            );
                        });
                });

        {if $mode != 'new'}
                $('#state_history').click(function(){
                    $.ajax({
                        url: this.href,
                        success: function(res){
                            _history_dialog(res);
                        }
                    });
                    return false;
                });

                var _history_dialog = function(res){
                    var _el = $('<div id="history_list" title="{_T string="Car\\'s history" domain="auto"}"> </div>');
                    _el.appendTo('#modifform').dialog({
                        modal: true,
                        hide: 'fold',
                        width: '60%',
                        height: 400,
                        close: function(event, ui){
                            _el.remove();
                        }
                    });
                    $('#history_list').append( res );
                }
        {/if}
    {/if}
            });
        </script>
{/block}
