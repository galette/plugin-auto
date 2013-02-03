        <form action="" method="post" id="modifform" enctype="multipart/form-data">
        <div class="bigtable">
            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Car's base informations"}</legend>
                <div>
                <p>
                    <label for="name" class="bline">{_T string="Name:"}</label>
                    <input type="text" name="name" id="name" value="{$car->name}" maxlength="20" required/>
                </p>
                <p>
                    <span class="bline">
                        <label for="brand">{_T string="Brand"}</label>/<label for="model">{_T string="Model:"}</label>
                    </span>
                    <select name="brand" id="brand" required>
                        <option value="-1">{_T string="Choose a brand"}</option>
    {foreach from=$brands item=brand}
                        <option value="{$brand->id_brand}"{if $brand->id_brand eq $car->model->brand} selected="selected"{/if}>{$brand->brand}</option>
    {/foreach}
                    </select>
                    <select name="model" id="model" required>
                        <option value="-1">{_T string="Choose a model"}</option>
    {foreach from=$models item=model}
                        <option value="{$model->id_model}"{if $model->id_model eq $car->model->id} selected="selected"{/if}>{$model->model}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="first_registration_date" class="bline">{_T string="First registration date:"}</label>
                    <input type="text" name="first_registration_date" id="first_registration_date" value="{$car->first_registration_date}" maxlength="20" required/>
                </p>
                <p>
                    <label for="first_circulation_date" class="bline">{_T string="First circulation date:"}</label>
                    <input type="text" name="first_circulation_date" id="first_circulation_date" value="{$car->first_circulation_date}" maxlength="20" required/>
                </p>
                <p>
                    <label for="mileage" class="bline">{_T string="Mileage:"}</label>
                    <input type="text" name="mileage" id="mileage" value="{$car->mileage}" maxlength="20"/>
                </p>
                <p>
                    <label for="seats" class="bline">{_T string="Seats:"}</label>
                    <input type="text" name="seats" id="seats" value="{$car->seats}"/>
                </p>
                </div>
            </fieldset>

            <fieldset>
                <legend class="ui-state-active ui-corner-top">{_T string="Car's photo"}</legend>
                <p>
                    <span class="bline">{_T string="Picture:"}</span>
                    <img src="picture.php?id_adh={$car->id}&amp;rand={$time}" class="picture" width="{$car->picture->getOptimalWidth()}" height="{$car->picture->getOptimalHeight()}" alt="{_T string="Car's photo"}"/><br/>
{if $car->hasPicture() }
                    <span class="labelalign"><label for="del_photo">{_T string="Delete image"}</label></span><input type="checkbox" name="del_photo" id="del_photo" value="1"/><br/>
{/if}
                    <input class="labelalign" type="file" name="photo"/>
                </p>

            </fieldset>

            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Current car's state informations"}</legend>
                <div>
                <input type="hidden" name="owner" id="owner" value="{$car->owner->id}"/>
    {if $login->isAdmin()}
                <p class="notform">
                    {* Does car's history should be visible by the actual owner? *}
                    <a href="history.php?id_car={$car->id}" title="{_T string="Show full car state history"}" id="state_history">{_T string="Car state history"}</a>
                    <strong class="fright"><a href="#" id="change_owner" title="{_T string="Change car's owner"}">{_T string="Change"}</a> {_T string="Current owner:"} <span id="current_owner_name">{$car->owner->sfullname}</span></strong>
                </p>
    {/if}
                <p>
                    <label for="color" class="bline">{_T string="Color:"}</label>
                    <select name="color" id="color" required>
                        <option value="-1">{_T string="Choose a color"}</option>
    {foreach from=$colors item=color}
                        <option value="{$color->id_color}"{if $color->id_color eq $car->color->id} selected="selected"{/if}>{$color->color}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="state" class="bline">{_T string="State:"}</label>
                    <select name="state" id="state" required>
                        <option value="-1">{_T string="Choose a state"}</option>
    {foreach from=$states item=state}
                        <option value="{$state->id_state}"{if $state->id_state eq $car->state->id} selected="selected"{/if}>{$state->state}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="registration" class="bline">{_T string="Registration:"}</label>
                    <input type="text" name="registration" id="registration" value="{$car->registration}" required/>
                </p>
                </div>
            </fieldset>
            <fieldset class="cssform">
                <legend class="ui-state-active ui-corner-top">{_T string="Car's technical informations"}</legend>
                <div>
                <p>
                    <label class="bline" for="body">{_T string="Body:"}</label>
                    <select name="body" id="body" required>
                        <option value="-1">{_T string="Choose a body"}</option>
    {foreach from=$bodies item=body}
                        <option value="{$body->id_body}"{if $body->id_body eq $car->body->id} selected="selected"{/if}>{$body->body}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label class="bline" for="transmission">{_T string="Transmission:"}</label>
                    <select name="transmission" id="transmission" required>
                        <option value="-1">{_T string="Choose a transmission"}</option>
    {foreach from=$transmissions item=transmission}
                        <option value="{$transmission->id_transmission}"{if $transmission->id_transmission eq $car->transmission->id} selected="selected"{/if}>{$transmission->transmission}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label class="bline" for="finition">{_T string="Finition:"}</label>
                    <select name="finition" id="finition" required>
                        <option value="-1">{_T string="Choose a finition"}</option>
    {foreach from=$finitions item=finition}
                        <option value="{$finition->id_finition}"{if $finition->id_finition eq $car->finition->id} selected="selected"{/if}>{$finition->finition}</option>
    {/foreach}
                    </select>
                </p>
                <p>
                    <label for="chassis_number" class="bline">{_T string="Chassis number:"}</label>
                    <input type="text" name="chassis_number" id="chassis_number" value="{$car->chassis_number}"/>
                </p>
                <p>
                    <label for="horsepower" class="bline">{_T string="Horsepower:"}</label>
                    <input type="text" name="horsepower" id="horsepower" value="{$car->horsepower}"/>
                </p>
                <p>
                    <label for="engine_size" class="bline">{_T string="Engine size:"}</label>
                    <input type="text" name="engine_size" id="engine_size" value="{$car->engine_size}"/>
                </p>
                <p>
                    <label for="fuel" class="bline">{_T string="Fuel:"}</label>
                    <select name="fuel" id="fuel" required>
                        <option value="-1">{_T string="Choose a fuel"}</option>
    {foreach from=$fuels key=k item=fuel}
                        <option value="{$k}"{if $k eq $car->fuel} selected="selected"{/if}>{$fuel}</option>
    {/foreach}
                    </select>
                </p>
                </div>
            </fieldset>
            <fieldset>
                <legend class="ui-state-active ui-corner-top">{_T string="Comment"}</legend>
                <div>
                <p>
                    <label for="comment" class="bline">{_T string="Comment:"}</label>
                    <textarea name="comment" id="comment" cols="80" rows="3">{$car->comment}</textarea>
                </p>
                </div>
            </fieldset>
        </div>
        <div class="button-container">
            <input type="submit" id="btnsave" name="valid" value="{_T string="Save"}"/>
            <input type="hidden" name="{$mode}" value="1"/>
            <input type="hidden" name="id_car" value="{$car->id}"/>
        </div>
        </form>
        <script type="text/javascript">
            $(function() {
                _collapsibleFieldsets();

                $('#first_circulation_date').datepicker({
                    changeMonth: true,
                    changeYear: true,
                    showOn: 'button',
                    buttonImage: '{$template_subdir}images/calendar.png',
                    buttonImageOnly: true,
                    maxDate: '-0d',
                    yearRange: 'c-100'
                });
                $('#first_registration_date').datepicker({
                    changeMonth: true,
                    changeYear: true,
                    showOn: 'button',
                    buttonImage: '{$template_subdir}images/calendar.png',
                    buttonImageOnly: true,
                    maxDate: '-0d',
                    yearRange: 'c-100'
                });
                var _models = $('#model');
                var _modelChoose = $('#model :first');
    {if $js_init_models}
                {* If javascript is active, we do not want complete models list when page loads *}
                {* Empty model list *}
                _models.empty();
                {* Set the first option *}
                _modelChoose.appendTo(_models);
    {/if}
                {* Refresh models list when brand is changed *}
                $('#brand').change(function(){
                    var id_brand = $('#brand option:selected').attr('value');
                    {* Empty model list *}
                    _models.empty();
                    {* Set the first option *}
                    _modelChoose.appendTo(_models);
                    {* Get the new list for selected brand, and appent it to models on the page *}
                    $.get(
                        'models-ajax.php',
                        { brand: id_brand },
                        function(data){
                            $(data).each(function(i){
                                var _data = data[i];
                                $('<option value="' + _data.id_model + '">' + _data.model + '</option>').appendTo(_models);
                            });
                        },
                        'json'
                    );
                });
    {if $login->isAdmin()}
                {* Popup for owner change *}
                $('#change_owner').click(function(){

                    $.ajax({
                        url: '{$galette_base_path}ajax_members.php',
                        type: "POST",
                        data: {
                            ajax: true,
                            multiple: false,
                            from: 'single',
                            id: '{$car->owner->id}'
                        },
                        {include file="{php}echo GALETTE_ROOT . GALETTE_TPL_SUBDIR;{/php}js_loader.tpl"},
                        success: function(res){
                            _owners_dialog(res);
                        },
                        error: function() {
                            alert("{_T string="An error occured displaying members interface :(" escape="js"}");
                        }
                    });
                    return false;
                });

                var _owners_dialog = function(res){
                    var _el = $('<div id="owners_list" title="{_T string="Owners"}"> </div>');
                    _el.appendTo('#modifform').dialog({
                        modal: true,
                        hide: 'fold',
                        width: '60%',
                        height: 400,
                        close: function(event, ui){
                            _el.remove();
                        }
                    });
                    _owners_ajax_mapper(res);
                }

                var _owners_ajax_mapper = function(res){
                    $('#owners_list').append( res );
                    $('#owners_list tbody').find('a').each(function(){
                        $(this).click(function(){
                            var _id = this.href.substring(this.href.indexOf('id_adh=') + 7, this.href.length);
                            $('#owner').attr('value', _id);
                            $('#current_owner_name').html($(this).html());
                            $('#owners_list').dialog('close');
                            return false;
                        }).attr('title', '{_T string="Click to choose this owner for current car"}');
                    });

                    //Remap links
                    $('#owners_list .pages a').click(function(){
                        var _page = this.href.substring(this.href.indexOf('?')+6);

                        $.ajax({
                            url: '{$galette_base_path}ajax_members.php',
                            type: "POST",
                            data: {
                                ajax: true,
                                page: _page,
                                multiple: false
                            },
                            {include file="{php}echo GALETTE_ROOT . GALETTE_TPL_SUBDIR;{/php}js_loader.tpl"},
                            success: function(res){
                                $('#owners_list').empty();
                                _owners_ajax_mapper(res);
                            },
                            error: function() {
                                alert("{_T string="An error occured displaying members interface :(" escape="js"}");
                            }
                        });
                        return false;
                    });
                }

                $('#state_history').click(function(){
                    $.ajax({
                        url: this.href + '&amp;ajax=true',
                        data: { ajax: true },
                        success: function(res){
                            _history_dialog(res);
                        }
                    });
                    return false;
                });

                var _history_dialog = function(res){
                    var _el = $('<div id="history_list" title="{_T string="Car\\'s history"}"> </div>');
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
            });
        </script>
