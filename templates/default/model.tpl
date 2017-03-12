        <form action="" method="post" id="modifform">
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
                    <p>{php}echo preg_replace('/%(.*)%/', '<a href="object.php?set=brands&#038;addnew=1">\\1</a>', _T("No brand is registered yet. You have to %create at least one brand% to register models." domain="auto"));{/php}</p>
{/if}
                </p>
            </fieldset>
        </div>
        <div class="button-container">
            <input type="submit" id="btnsave" name="valid" value="{_T string="Save"}"/>
            <input type="reset" id="btncancel" name="cancel" value="{_T string="Cancel"}"/>
            <input type="hidden" name="{$mode}" value="1"/>
            <input type="hidden" name="model_id" value="{$model->id}"/>
        </div>
        <p>{_T string="NB : The mandatory fields are in"} <span class="required">{_T string="red"}</span></p>
        </form>
