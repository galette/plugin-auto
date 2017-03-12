        <table id="listing">
            <thead>
                <tr> 
                    {*<th class="listing" id="id_row">#</th>*}
                    <th class="listing left">
                        {_T string="Date"}
                    </th>
                    <th class="listing left">
                        {_T string="Owner" domain="auto"}
                    </th>
                    <th class="listing left">
                        {_T string="Registration" domain="auto"}
                    </th>
                    <th class="listing left">
                        {_T string="Color" domain="auto"}
                    </th>
                    <th class="listing left">
                        {_T string="State" domain="auto"}
                    </th>
                </tr>
            </thead>
            <tbody>
{foreach from=$entries item=entry}
    {assign var='owner' value=$entry.owner}
                <tr>
                    <td>{$entry.formatted_date}</td>
                    <td>
                        {if $owner->isMan()}
                            <img src="{$template_subdir}images/icon-male.png" alt="{_T string="[M]" domain="auto"}" width="16" height="16"/>
                        {elseif $owner->isWoman()}
                            <img src="{$template_subdir}images/icon-female.png" alt="{_T string="[W]" domain="auto"}" width="16" height="16"/>
                        {elseif $owner->isCompany()}
                            <img src="{$template_subdir}images/icon-company.png" alt="{_T string="[W]" domain="auto"}" width="16" height="16"/>
                        {else}
                            <img src="{$template_subdir}images/icon-empty.png" alt="" width="10" height="12"/>
                        {/if}
                        {if $owner->isAdmin()}
                            <img src="{$template_subdir}images/icon-star.png" alt="{_T string="[admin]" domain="auto"}" width="16" height="16"/>
                        {else}
                            <img src="{$template_subdir}images/icon-empty.png" alt="" width="12" height="13"/>
                        {/if}
                        {$owner->sfullname}
                    </td>
                    <td>{$entry.car_registration}</td>
                    <td>{$entry.color->value}</td>
                    <td>{$entry.state->value}</td>
                </tr>
{foreachelse}
                <tr><td colspan="5" class="emptylist">{_T string="no history entries" domain="auto"}</td></tr>
{/foreach}
            </tbody>
        </table>
