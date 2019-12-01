{extends file="page.tpl"}

{block name="content"}
    {if $mode eq "new"}
        {assign var="action" value="add"}
    {else}
        {assign var="action" value="edit"}
    {/if}
    <form action="{path_for name="doModelEdit" data=["action" => $action]}" method="post" id="modifform">
        <div class="bigtable">
            <fieldset class="cssform">
                <p>
                    <label for="model" class="bline">{_T string="Model" domain="auto"}</label>
                    <input type="text" name="model" id="model" value="{$model->model}" maxlength="20" required/>
                </p>
                <p>
    {if $brands|@count gt 0}
                    <label for="brand" class="bline">{_T string="Brand" domain="auto"}</label>
                    <select name="brand" id="brand" required>
                        <option value="-1">{_T string="Select one brand" domain="auto"}</option>
        {foreach from=$brands item=brand}
                        <option value="{$brand->id_brand}"{if $brand->id_brand eq $model->brand} selected="selected"{/if}>{$brand->brand}</option>
        {/foreach}
                    </select>
    {else}
                    {_T string="No brand is registered yet. You have to create at least one brand to register models." domain="auto"}.<br/>
                    <a href="{path_for name="propertyEdit" data=["property" => "brand", "action" => "add"]}">{_T string="Add a brand" domain="auto"}<a>
    {/if}
                </p>
            </fieldset>
        </div>
        <div class="button-container">
            <input type="submit" id="btnsave" name="valid" value="{_T string="Save"}"/>
            <input type="reset" id="btncancel" name="cancel" value="{_T string="Cancel"}"/>
            <input type="hidden" name="{$mode}" value="1"/>
    {if $mode neq "new"}
            <input type="hidden" name="{constant('GaletteAuto\Model::PK')}" value="{$model->id}"/>
    {/if}
        </div>
        <p>{_T string="NB : The mandatory fields are in"} <span class="required">{_T string="red"}</span></p>
    </form>
{/block}
