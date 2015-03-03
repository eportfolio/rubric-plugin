{include file="header.tpl"}
{if $show}
<div align="right">
<a class="btn nojs-hidden-inline" href="javascript:history.back()">
<span class="btn-back">{str tag="back"}</span>
</a>
</div>
<br/>
{/if}
<div id="rubricwrap1" style="height:100%; width:100%; overflow-y:scroll; overflow-x:scroll;">
    {$radarchart|safe}
</div>

{if $isyeardisplay}
	<div id="rubricwrap2" style="height:100%; width:100%; overflow-y:scroll; overflow-x:scroll;">
	    {$linechart|safe}
	</div>
{/if}

<div id="rubricwrap" style="height:100%; width:100%; overflow-y:scroll; overflow-x:scroll;">
<table style="height:100%; width:1000px;">

{*
<div id="rubricwrap" style="height:100%; width:100%; overflow-y:scroll; overflow-x:scroll;">
<table style="width:{$width}px;">
*}
	<tr>
		<td>
		<table border="1" cellspacing="0" cellpadding="0" >
		    {* ヘッダタイトル *}
		    {* 2013/07/29 SCSK MOD
		    <tr><th style="background-color: #F5F5F5; width:140px;"></th>
		    *}
		    <tr><th style="background-color: #F5F5F5; width:120px;"></th>

		    {foreach from=$years item=year}
		        {* 2013/07/29 SCSK MOD
		        <th style="text-align: center;background-color: #F5F5F5;width: 170px;">
		        *}
		        <th style="text-align: center;background-color: #F5F5F5;width:120px;">

		        {$year->title}
		        {if $year->title != ""}
		        <br>
		        {/if}
		        {str section="artefact.rubric" tag="attainment"}
		        </th>
		    {/foreach}
		    {* 2013/07/29 SCSK MOD
		    <th style="text-align: center;width: 75px;background-color: #F5F5F5;">{str section="artefact.rubric" tag="nextstep"}</th>
		    *}
		    <th style="text-align: center;background-color: #F5F5F5;">{str section="artefact.rubric" tag="nextstep"}</th>

		    </tr>
		    {* ヘッダタイトル *}
		    {* 中身 *}
		    {$r = 0}
		    {foreach from=$viewlist key=rk item=row}
		    <tr >
		        <th style="text-align: center;vertical-align:middle;background-color: #F5F5F5;width:120px;"><div title="{$skills[$r]->description}">{$skills[$r]->title}</div></th>
		        {foreach from=$row key=ck item=col}
		            <td bgcolor="{$col->bgcolor}" style="width:120px;">
		            <a href="{$WWWROOT}artefact/rubric/edit/attainment.php?id={$col->id}&rubric={$rubric}" title="{str tag="edit"}" >
		            {if $col->default_flg}
		            	{$col->label}
		            {else}
		            	{str section="artefact.rubric" tag="defaultskilltitle"}
		            {/if}
		            </a><br>
		            {foreach from=$imglist[$rk][$ck] item=val}
		            {if $col->title != null}
		            	<a href="{$WWWROOT}artefact/file/download.php?file={$val->fileno}" target="_blank" title="{$val->title}をダウンロードする">{$val->title}</a><br>
		            {/if}
		            {/foreach}
		            {nl2br($col->comment)}<br>
		            </td>
		        {/foreach}
		        <td style="background-color: #FFFFFF; width: 80px;">{$col->nextlabel}<br></td>
		    </tr>
		    {$r += 1}
		    {/foreach}
		     {* 中身 *}
		</table>
		</td>
		<td style="text-align: center;">
		<table>
			<tr><td>凡例</td></tr>
			{foreach from=$colors item=val}
				<tr>
					<td bgcolor="{$val->bgcolor}">{$val->title}</td>
				</tr>
			{/foreach}
		</table>
		</td>
	</tr>
</table>
</div>


{include file="footer.tpl"}
