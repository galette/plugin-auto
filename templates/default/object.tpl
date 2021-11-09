{extends file="page.tpl"}

{block name="content"}
    {assign var='name' value=$obj->name}
    {assign var='pk' value=$obj->pk}
    {assign var='field' value=$obj->field}

        <form action="" method="post" id="modifform">
        <div class="bigtable">
            <fieldset class="cssform">
                <p>
                    <label for="{$field}" class="bline">{$obj->getFieldLabel()}</label>
                    <input type="text" name="{$field}" id="{$field}" value="{$obj->value}" maxlength="20" required autofocus/>
                </p>
            </fieldset>
        </div>
        <div class="button-container">
            <button type="submit" id="btnsave" name="valid" class="action">
                <i class="fas fa-save fa-fw"></i> {_T string="Save"}
            </button>
            <input type="hidden" name="set" value="{$set}"/>
            <input type="hidden" name="{$mode}" value="1"/>
            <input type="hidden" name="{$pk}" value="{$obj->id}"/>
            {include file="forms_types/csrf.tpl"}
        </div>
        <p>{_T string="NB : The mandatory fields are in"} <span class="required">{_T string="red"}</span></p>
        </form>
{/block}
