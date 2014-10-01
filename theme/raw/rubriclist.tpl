{foreach from=$rubrics.data item=rubric}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <div class="fr rubricstatus">
                 <a href="{$WWWROOT}artefact/rubric/edit/index.php?id={$rubric->id}" title="{str tag="edit"}" ><img src="{theme_url filename='images/btn_edit.png'}" alt="{str tag=edit}"></a>
                 <a href="{$WWWROOT}artefact/rubric/delete/index.php?id={$rubric->id}" title="{str tag="delete"}"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
            </div>

            <h3><a href="{$WWWROOT}artefact/rubric/edit/index.php?id={$rubric->id}">{$rubric->title}</a></h3>

            <div class="codesc">{$rubric->description}</div>
        </td>
    </tr>
{/foreach}
