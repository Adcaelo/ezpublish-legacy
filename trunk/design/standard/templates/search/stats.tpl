<div class="maincontentheader">
<h1>{"Search statistics"|i18n("design/standard/search")}</h1>
</div>

<h2>{"Most frequent search phrases"|i18n("design/standard/search")}</h2>

<table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
	<th>
	{"Phrase"|i18n("design/standard/search")}
	</th>
	<th>
	{"Number of phrases"|i18n("design/standard/search")}
	</th>
	<th>
	{"Average result returned"|i18n("design/standard/search")}
	</th>
</tr>
{section name=Phrase loop=$most_frequent_phrase_array sequence=array(bglight,bgdark)}
<tr>
	<td class="{$Phrase:sequence}">
	{$Phrase:item.phrase|wash}
	</td>
	<td class="{$Phrase:sequence}">
	{$Phrase:item.phrase_count}
	</td>
	<td class="{$Phrase:sequence}">
	{$Phrase:item.result_count|l10n(number)}
	</td>
</tr>
{/section}
</table>
