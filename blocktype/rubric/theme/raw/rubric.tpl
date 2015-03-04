
<div id="rubricwrap" style="height:100%;overflow-y:scroll; overflow-x:scroll;">
{if $display_type != 2}
    {if $chart}
        {$chart|safe}
    {else}
        <P id="rubric_rabel" style="font-weight : bold;color : red;">{$message}</P>
    {/if}
{else}

	{if $viewlist}
	<table>
		<tr>
			<td>
				<table border="1" cellspacing="0" cellpadding="0" style="height:100%; padding: 5px 10px;border: 1px solid #CCCCCC;">
				{*
				<table border="1" cellspacing="0" cellpadding="0" style="width:{$width}px;padding: 5px 10px;border: 1px solid #CCCCCC;table-layout: fixed;">
				*}
				    {* ヘッダタイトル *}
				    {* 2013/07/29 SCSK MOD
				    <tr><td style="background-color: #F5F5F5; width:80px;"></td>
				    *}
				    <tr><td style="width:130px; background-color: #F5F5F5;"></td>
				    {foreach from=$years item=year}
				    {* 2013/07/29 SCSK MOD
				        <th style="text-align: center;background-color: #F5F5F5; width:140px;" nowrap>{$year->title}<br>{str section="artefact.rubric" tag="achievement"}</th>
				    *}
				    	<th style="text-align: center;background-color: #F5F5F5; width:130px;" nowrap>{$year->title}<br>{str section="artefact.rubric" tag="achievement"}</th>
				    {/foreach}
				    {* 2013/07/29 SCSK MOD
				    <th style="width:75px;background-color: #F5F5F5;">{str section="artefact.rubric" tag="nextstep"}</th>
				    *}
				    <th style="width:130px; background-color: #F5F5F5;">{str section="artefact.rubric" tag="nextstep"}</th>
				    </tr>
				    {* ヘッダタイトル *}

				    {* 中身 *}
				    {$r = 0}
				    {foreach from=$viewlist key=rk item=row}
				    <tr>
				    	{*2013/07/29 SCSK MOD
				        <th style="text-align: center;vertical-align:middle;background-color: #F5F5F5;"><div title="{$skills[$r]->description}">{$skills[$r]->title}</div></th>
				        *}
				        <th style="text-align: center;vertical-align:middle;background-color: #F5F5F5;"><div title="{$skills[$r]->description}">{$skills[$r]->title}</div></th>
				        {foreach from=$row key=ck item=col}
				            <td bgcolor="{$col->bgcolor}">
				            {if $col->default_flg}
				            	{$col->label}
				            {else}
				            {str section="blocktype.rubric/rubric" tag="defaultskilltitle"}
				            {/if}<br>
				            {foreach from=$imglist[$rk][$ck] item=val}
				            	{if $col->title != null}
				            		{$val->title}<br>
				            	{/if}
				            {/foreach}
				            {nl2br($col->comment)}<br>
				            </td>
				        {/foreach}
				        <td style="background-color: #FFF;">{$col->nextlabel}<br></td>
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
    {else}
    <P id="rubric_rabel" style="font-weight : bold;color : red;">{str section="blocktype.rubric/rubric" tag="norubric"}</P>
	{/if}
{/if}
</div>
