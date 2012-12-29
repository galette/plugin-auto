        <table id="listing">
            <thead>
                <tr> 
                    {*<th class="listing" id="id_row">#</th>*}
                    <th class="listing left"> 
                        {_T string="Date"}
                    </th>
                    <th class="listing left">
                        {_T string="Owner"}
                    </th>
                    <th class="listing left">
                        {_T string="Registration"}
                    </th>
                    <th class="listing left">
                        {_T string="Color"}
                    </th>
                    <th class="listing left">
                        {_T string="State"}
                    </th>
                </tr>
            </thead>
            {*<tfoot>
                <tr>
                    <td colspan="3" class="right">
                        {_T string="Pages:"}
                        <span class="pagelink">
                        {section name="pageLoop" start=1 loop=$nb_pages+1}
                            {if $smarty.section.pageLoop.index eq $page}
                                {$smarty.section.pageLoop.index}
                            {else}
                                <a href="owners.php?nbshow={$smarty.get.nbshow}&amp;page={$smarty.section.pageLoop.index}">{$smarty.section.pageLoop.index}</a>
                            {/if}
                        {/section}
                        </span>
                    </td>
                </tr>
            </tfoot>*}
            <tbody>
{foreach from=$entries item=entry}
    {assign var='owner' value=$entry->owner}
                <tr>
                    <td>{$entry->formatted_date}</td>
                    <td>
                        {if $owner->politeness == constant('Galette\Entity\Politeness::MR')}
                            <img src="{$template_subdir}images/icon-male.png" alt="{_T string="[M]"}" width="16" height="16"/>
                        {elseif $owner->politeness == constant('Galette\Entity\Politeness::MRS') || $owner->politeness == constant('Galette\Entity\Politeness::MISS')}
                            <img src="{$template_subdir}images/icon-female.png" alt="{_T string="[W]"}" width="16" height="16"/>
                        {elseif $owner->politeness == constant('Galette\Entity\Politeness::COMPANY')}
                            <img src="{$template_subdir}images/icon-company.png" alt="{_T string="[W]"}" width="16" height="16"/>
                        {else}
                            <img src="{$template_subdir}images/icon-empty.png" alt="" width="10" height="12"/>
                        {/if}
                        {if $owner->isAdmin()}
                            <img src="{$template_subdir}images/icon-star.png" alt="{_T string="[admin]"}" width="16" height="16"/>
                        {else}
                            <img src="{$template_subdir}images/icon-empty.png" alt="" width="12" height="13"/>
                        {/if}
                        {$owner->sfullname}
                    </td>
                    <td>{$entry->car_registration}</td>
                    <td>{$entry->color->value}</td>
                    <td>{$entry->state->value}</td>
                </tr>
{foreachelse}
                <tr><td colspan="5" class="emptylist">{_T string="no history entries"}</td></tr>
{/foreach}
            </tbody>
        </table>
