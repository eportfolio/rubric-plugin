{include file="header.tpl"}
<div align="right">
<a class="btn nojs-hidden-inline" href="{$WWWROOT}artefact/rubric/managetemplate.php">
<span class="btn-back">{str tag="back"}</span>
</a>
</div>
<br/>
<div class="tabswrap">
	<ul class="in-page-tabs">
		{foreach from=$years item=year key=key}
			<li><a href="{$WWWROOT}artefact/rubric/statistics.php?id={$rubric}&year={$key}">{$year}</a></li>
		{/foreach}
	</ul>
</div>
<div class="subpage">
{if $results}
<div id="rubricwrap" style="height:100%; width:100%; overflow-x:scroll;">
<table border="1" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td bgcolor="#F5F5F5"></td>
		<td bgcolor="#F5F5F5" align="center">{str tag='average' section='artefact.rubric' }</td>
		{foreach from=$usrs item=name key=usr}
			<td bgcolor="#F5F5F5" align="center"><a href="{$WWWROOT}artefact/rubric/edit/index.php?id={$rubric}&user={$usr}&show=1">{$name}</a></td>
		{/foreach}
	</tr>
{foreach from=$results item=skill key=key}
	<tr>
	<td width="200" bgcolor="#F5F5F5"><a href="#" onclick="onClickDisplay({$key})">{$skills[$key][0]}</a>
	<div id="skill_{$key}" style="display:none;">{$skills[$key][1]}</div></td>
	<td width="200" align="center">{$skillsaverage[$key]}</td>
	{foreach from=$skill item=usr}
		{if $usr['default_flg'] == 0}
			<td width="200" align="center">{$usr['label']}</td>
		{else}
			<td bgcolor="{$usr['bgcolor']}" width="200">{$usr['label']}</td>
		{/if}
	{/foreach}
	</tr>
{/foreach}
	<tr>
			<td bgcolor="#F5F5F5">{str tag='total' section='artefact.rubric' }</td><td align="center">{$totalaverage['totalaverage']}</td>
		{foreach from=$usrstotalaverage item=total}
			<td align="center">{$total['total']}</td>
		{/foreach}
	</tr>
	<tr>
		<td bgcolor="#F5F5F5">{str tag='average' section='artefact.rubric' }</td><td align="center">{$totalaverage['averageaverage']}</td>
		{foreach from=$usrstotalaverage item=average}
			<td align="center">{$average['average']}</td>
		{/foreach}
	</tr>
</table>
<br/>
</div>
{else}
<div class="message">
{str tag='noitem' section='artefact.rubric' }
</div>
{/if}
</div>
{include file="footer.tpl"}