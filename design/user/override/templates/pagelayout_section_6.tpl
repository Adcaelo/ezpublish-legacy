{*?template charset=utf8?*}
{let gallery_limit=8
     gallery_pre_items=2
     gallery_post_items=2}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">

<head>
    <title>{$site.title} - {section name=Path loop=$module_result.path}{$Path:item.text}{delimiter} / {/delimiter}{/section}</title>

    <link rel="stylesheet" type="text/css" href={"stylesheets/core.css"|ezdesign} />
    <link rel="stylesheet" type="text/css" href={"stylesheets/admin.css"|ezdesign} />
    <link rel="stylesheet" type="text/css" href={"stylesheets/mycompany.css"|ezdesign} />

<!-- Javascript START -->

<script language="JavaScript">
<!--
{literal}
function OpenWindow ( URL, WinName, Features ) {
	popup = window.open ( URL, WinName, Features );
	if ( popup.opener == null ) {
		remote.opener = window;
	}
	popup.focus();
}
{/literal}

// -->
</script>

<!-- Javascript END -->

{* check if we need a http-equiv refresh *}
{section show=$site.redirect}
<meta http-equiv="Refresh" content="{$site.redirect.timer}; URL={$site.redirect.location}" />
{/section}

<!-- Meta information START -->

{section name=meta loop=$site.meta}
<meta name="{$meta:key}" content="{$meta:item}" />
{/section}

<meta name="MSSmartTagsPreventParsing" content="TRUE" />

<meta name="generator" content="eZ publish" />

<!-- Meta information END -->

</head>

<body>

<!-- Top box START -->

<img src={"toppmeny.gif"|ezimage} alt="" border="0" usemap="#map" /><br /><br />

<MAP NAME="map">
<AREA SHAPE="RECT" COORDS="1,1,71,25" href={"content/view/full/26/"|ezurl}>
<AREA SHAPE="RECT" COORDS="73,0,156,24" href={"content/view/full/159/"|ezurl}>
<AREA SHAPE="RECT" COORDS="157,0,228,23" href={"content/view/full/62/"|ezurl}>
<AREA SHAPE="RECT" COORDS="229,0,299,24" href={"content/view/full/200/"|ezurl}>
<AREA SHAPE="RECT" COORDS="300,0,372,24" href={"content/view/full/32/"|ezurl}>
<AREA SHAPE="RECT" COORDS="374,1,448,24" href={"content/view/full/210/"|ezurl}>
<AREA SHAPE="RECT" COORDS="450,1,523,24" href={"content/view/full/82/"|ezurl}>
</MAP>

{let folder_list=fetch(content,list,hash(parent_node_id,158,sort_by,array(array(priority))))
     news_list=fetch(content,list,hash(parent_node_id,159,limit,5,sort_by,array(published,false()),class_filter_type,include,class_filter_array,array(2)))}

<table class="mainlayout" width="700" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td colspan="2">
    <a href={"content/view/full/159/"|ezurl}><img src={"mycompanylogo.jpg"|ezimage} width="700" height="68" alt="My company - business" border="0" /></a>
    </td>
</tr>
<tr>
    <td class="topmenuline" colspan="2">

    <form action={"/content/search/"|ezurl} method="get">

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        {section name=Folder loop=$folder_list}
        <td class="topmenu" width="98%">
        <a href={concat("/content/view/full/",$Folder:item.node_id,"/")|ezurl}>{$Folder:item.name}</a> 
        </td>
        {/section}
        <td align="right" width="1%">
        <input type="hidden" name="SectionID" value="6" />
        <input class="searchtext" type="text" size="10" name="SearchText" id="Search" value="" />&nbsp;
    	</td>
	    <td align="right" width="1%">
        <input class="searchbutton" name="SearchButton" type="image" src={"search.gif"|ezimage} value="{"Search"|i18n('pagelayout')}" />
        </td>
    </tr>
    </table>    

    </form>

    </td>
</tr>
<tr>
    <td valign="top">
    <p class="path">
    &gt;
     {section name=Path loop=$module_result.path offset=2 show=eq($DesignKeys:used.viewmode,'full')}
        {section show=$Path:item.url}
        <a href="{$Path:item.url}">{$Path:item.text}</a>
        {section-else}
	{$Path:item.text}
        {/section}

        {delimiter}
        /
        {/delimiter}
    {section-else}
     {section name=Path loop=$module_result.path}
        {section show=$Path:Path:item.url}
        <a href="{$Path:item.url}">{$Path:Path:item.text}</a>
        {section-else}
	    {$Path:Path:item.text}
        {/section}

        {delimiter}
        /
        {/delimiter}
     {/section}
    {/section}
    </p>

    <table class="layout" width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="leftedge" valign="top">
	{section name=Menu loop=fetch(content,list,hash(parent_node_id,$DesignKeys:used.node,class_filter_type,include,class_filter_array,array(1,24))) show=eq($DesignKeys:used.viewmode,'full')}
	<p class="leftmenuitem"><a href={concat('content/view/full/',$Menu:item.node_id)|ezurl}>{$Menu:item.name}</a></p>
	{delimiter}

	{/delimiter}
	{/section}
	
       </td>

        <td class="maincontent" valign="top" width="99%">
	    {$module_result.content}
        </td>
    </tr>
    </table>

    </td>
    <td width="204" valign="top">

    <img src={"speed.jpg"|ezimage} width="204" height="116" alt="" /><br />

    <table class="menu" width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <th class="menuhead">
	    News
        </th>
    </tr>
    {section name=News loop=$news_list}
	 <tr>
        <td class="menuitem">

        <div class="menuname">
        <a href={concat('content/view/full/',$News:item.node_id)|ezurl}>{$News:item.name}</a>
        </div>
	    <div class="menudate">({$News:item.object.published|l10n(shortdate)})</div>

        </td>
    </tr>
	{delimiter}
	{/delimiter}
	{/section}
    </table>

<div class="credits" align="center">
<p>Copyright &copy; <a href="http://ez.no">eZ systems as</a><br />1999-2002</p>
<a href="http://developer.ez.no"><img src={"powered-by-ezpublish-100x35-trans-lgrey.gif"|ezimage} alt="Powered eZ publish" border="0" width="100" height="35" /></a>
</div>

    </td>
</tr>
</table>

</body>
</html>
{/let}
{/let}