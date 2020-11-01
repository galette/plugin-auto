{extends file="page.tpl"}

{block name="content"}
{assign var='name' value=$obj->name}
{assign var='pk' value=$obj->pk}
{assign var='field' value=$obj->field}
        <div class="bigtable">
{if $name eq 'brands'}
    {if $models|@count gt 0}
            <p>{_T string="Registered models for the brand '%s':" pattern="/%s/" replace=$obj->value domain="auto"}</p>
            <ul>
        {foreach item=model from=$models}
                <li><a href="{path_for name="modelEdit" data=["action" => "edit", "id" => $model->id]}">{$model->model}</a></li>
        {/foreach}
            </ul>
    {else}
            <p>{_T string="The brand '%s' does not have any registered model at this time." pattern="/%s/" replace=$obj->value domain="auto"}</p>
    {/if}
            <p><a href="{path_for name="modelAdd"}?brand={$obj->id}">{_T string="Create a new model for brand '%s'" pattern="/%s/" replace=$obj->value domain="auto"}</a></p>
{/if}
        </div>
{/block}
