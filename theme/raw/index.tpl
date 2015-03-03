{include file="header.tpl"}
<div id="rubricwrap">
    <div class="rbuttons">
    {if $isAdmimOrStuff }
        <a class="btn" href="{$WWWROOT}artefact/rubric/managetemplate.php">{str section="artefact.rubric" tag="managetemplate"}</a>
    {/if}
        <a class="btn" href="{$WWWROOT}artefact/rubric/new.php">{str section="artefact.rubric" tag="newrubric"}</a>
    </div>
{if !$rubric.data}
    <div class="message">{$strnorubricaddone|safe}</div>
{else}
<table id="rubriclist" class="fullwidth listing">
    <tbody>
        {$rubric.tablerows|safe}
    </tbody>
</table>
   {$rubric.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
