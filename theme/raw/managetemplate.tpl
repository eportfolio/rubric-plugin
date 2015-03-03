{include file="header.tpl"}
    {$form|safe}
    <br/>
{if !$rubric.data}
    <div class="message">{str section="artefact.rubric" tag="notemplate"}</div>
{else}
<table id="templatelist" class="fullwidth listing">
    <tbody>
        {$rubric.tablerows|safe}
    </tbody>
</table>
   {$rubric.pagination|safe}
{/if}
{include file="footer.tpl"}
