{assign var='name' value=$obj->name}
{assign var='pk' value=$obj->pk}
{assign var='field' value=$obj->field}
        <div class="bigtable">
{if $name eq 'brands'}
    {if $models|@count gt 0}
            <p>{_T string="Registered models for the brand '%s':" pattern="/%s/" replace=$obj->value}</p>
            <ul>
        {foreach item=model from=$models}
                <li><a href="models.php?id_model={$model->id_model}">{$model->model}</a></li>
        {/foreach}
            </ul>
            <p><a href="models.php?donew=1&amp;brand={$obj->id}">{_T string="Create a new model for brand '%s'" pattern="/%s/" replace=$obj->value}</a></p>
    {else}
            <p>{_T string="The brand '%s' does not have any registered model at this time." pattern="/%s/" replace=$obj->value}<br/><a href="models.php?donew=1&amp;brand={$obj->id}">{_T string="Do you want to create a new one?"}</a></p>
    {/if}
{/if}
        </div>
