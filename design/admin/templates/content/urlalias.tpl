{* Errors START *}

{switch match=$info_code}
{case match='feedback-removed'}
<div class="message-feedback">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The selected aliases were successfully removed.'|i18n( 'design/admin/content/urlalias' )}</h2>
</div>
{/case}
{case match='feedback-removed-all'}
<div class="message-feedback">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'All aliases for this node were successfully removed.'|i18n( 'design/admin/content/urlalias' )}</h2>
</div>
{/case}
{case match='error-invalid-language'}
<div class="message-warning">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The specified language code <%language> is not valid.'|i18n( 'design/admin/content/urlalias',, hash('%language', $info_data['language']) )|wash}</h2>
</div>
{/case}
{case match='error-no-alias-text'}
<div class="message-warning">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'Text is missing for the URL alias'|i18n( 'design/admin/content/urlalias' )}</h2>
<ul>
    <li>{'You will need to fill in some text in the input box to create a new alias.'|i18n( 'design/admin/content/urlalias' )}</li>
</ul>
</div>
{/case}
{case match='feedback-alias-cleanup'}
<div class="message-warning">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The URL alias was successfully created, but was modified by the system to <%new_alias>'|i18n( 'design/admin/content/urlalias',, hash('%new_alias', $info_data['new_alias'] ) )|wash}</h2>
<ul>
    <li>{'Invalid characters will be removed or transformed to valid characters.'|i18n( 'design/admin/content/urlalias' )}</li>
    <li>{'Existing objects or functionality with the same name will get precedence on the name.'|i18n( 'design/admin/content/urlalias' )}</li>
</ul>
</div>
{/case}
{case match='feedback-alias-created}
<div class="message-feedback">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The URL alias <%new_alias> was successfully created'|i18n( 'design/admin/content/urlalias',, hash('%new_alias', $info_data['new_alias'] ) )|wash}</h2>
</div>
{/case}
{case match='feedback-alias-exists}
<div class="message-warning">
<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'The URL alias %new_alias already exists, and it points to %action_url'|i18n( 'design/admin/content/urlalias',, hash( '%new_alias', concat( "<"|wash, '<a href=', $info_data['url']|ezurl, '>', $info_data['new_alias'], '</a>', ">"|wash ), '%action_url', concat( "<"|wash, '<a href=', $info_data['action_url']|ezurl, '>', $info_data['action_url']|wash, '</a>', ">"|wash ) ) )}</h2>
</div>
{/case}
{case}
{/case}
{/switch}

{* Errors END *}


{def $aliasList=$filter.items}

<form name="aliasform" method="post" action={concat('content/urlalias/', $node.node_id)|ezurl}>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'URL aliases for <%node_name> [%alias_count]'|i18n( 'design/admin/content/urlalias',, hash( '%node_name', $node.name, '%alias_count', $filter.count ) )|wash}</h1>
{* DESIGN: Mainline *}<div class="header-mainline"></div>
{* DESIGN: Header END *}</div></div></div></div></div></div>
{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{* list here *}
{if eq( count( $aliasList ), 0)}
<div class="block">
<p>{"The current item does not have any aliases associated with it."|i18n( 'design/admin/content/urlalias' )}</p>
</div>
{else}
<table class="list" cellspacing="0" >
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Invert selection.'|i18n( 'design/admin/content/urlalias' )}" title="{'Invert selection.'|i18n( 'design/admin/content/urlalias' )}" onclick="ezjs_toggleCheckboxes( document.aliasform, 'ElementList[]' ); return false;"/></th>
    <th>{'Path'|i18n( 'design/admin/content/urlalias' )}</th>
    <th>{'Language'|i18n( 'design/admin/content/urlalias' )}</th>
</tr>
{foreach $aliasList as $element sequence array('bglight', 'bgdark') as $seq}
    <tr class="{$seq}">
        {* Remove. *}
        <td>
            <input type="checkbox" name="ElementList[]" value="{$element.parent}.{$element.text_md5}.{$element.language_object.locale}" />
        </td>

        <td>
            {foreach $element.path_array as $el}
            {if ne( $el.action, "nop:" )}
            <a href={concat("/",$el.path)|ezurl}>
            {/if}
            {$el.text|wash}
            {if ne( $el.action, "nop:" )}
            </a>
            {/if}
            {delimiter}/{/delimiter}
            {/foreach}
        </td>

        <td>
            <img src="{$element.language_object.locale|flag_icon}" alt="{$element.language_object.locale|wash}" />
            &nbsp;
            {$element.language_object.name|wash}
        </td>
    </tr>
{/foreach}
 </table>

<div class="context-toolbar">
    {include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=concat('content/urlalias/', $node.node_id)
         item_count=$filter.count
         view_parameters=$view_parameters
         node_id=$node.node_id
         item_limit=$filter.limit}
</div>
{/if}


{* DESIGN: Content END *}</div></div></div>

<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">

{* buttons here *}
<div class="block">
<div class="button-left">
{if $node.can_edit}
    {if $aliasList|count|gt( 0 )}
    <input class="button" type="submit" name="RemoveAliasButton" value="{'Remove selected'|i18n( 'design/admin/content/urlalias' )}" title="{'Remove selected alias from the list above.'|i18n( 'design/admin/content/urlalias' )}" onclick="return confirm( '{'Are you sure you want to remove the selected aliases?'|i18n( 'design/admin/content/urlalias' )}' );" />
    <input class="button" type="submit" name="RemoveAllAliasesButton" value="{'Remove all'|i18n( 'design/admin/content/urlalias' )}" title="{'Remove all aliases for this node.'|i18n( 'design/admin/content/urlalias' )}" onclick="return confirm( '{'Are you sure you want to remove all aliases for this node?'|i18n( 'design/admin/content/urlalias' )}' );" />
    {else}
    <input class="button-disabled" type="submit" name="RemoveAliasButton" value="{'Remove selected'|i18n( 'design/admin/content/urlalias' )}" title="{'There are no removable aliases.'|i18n( 'design/admin/content/urlalias' )}" disabled="disabled" />
    <input class="button-disabled" type="submit" name="RemoveAllAliasesButton" value="{'Remove all'|i18n( 'design/admin/content/urlalias' )}" title="{'There are no removable aliases.'|i18n( 'design/admin/content/urlalias' )}" disabled="disabled" )}' );" />
    {/if}
{else}
    <input class="button-disabled" type="submit" name="" value="{'Remove selected'|i18n( 'design/admin/content/urlalias' )}" disabled="disabled" title="{'You can not remove any aliases because you do not have permissions to edit the current item.'|i18n( 'design/admin/content/urlalias' )}" />
    <input class="button-disabled" type="submit" name="RemoveAllAliasesButton" value="{'Remove all'|i18n( 'design/admin/content/urlalias' )}" title="{'You can not remove any aliases because you do not have permissions to edit the current item.'|i18n( 'design/admin/content/urlalias' )}" disabled="disabled" />
{/if}
</div>
<div class="break"></div>

</div>

<div class="block">

<div class="left">
{* Language dropdown. *}
{section show=$node.can_edit}
    <select name="LanguageCode" title="{'Choose the language for the new URL alias.'|i18n( 'design/admin/content/urlalias' )}">
    {foreach $languages as $language}
               <option value="{$language.locale}"{if $language.locale|eq($node.object.current_language)} selected="selected"{/if}>{$language.name|wash}</option>
    {/foreach}
    </select>
{section-else}
    <select name="LanguageCode" disabled="disabled">
        <option value="">{'Not available'|i18n( 'design/admin/content/urlalias')}</option>
    </select>
{/section}

{* Name field. *}
    <input class="text" type="text" name="AliasText" value="{$aliasText|wash}" title="{'Enter the URL for the new alias. Use forward slashes (/) to create subentries.'|i18n( 'design/admin/content/urlalias' )}" />

{* Relative flag. *}
    <input type="checkbox" name="RelativeAlias" id="relative-alias" value="{$node.node_id}" checked="checked" /><label for="relative-alias" title="{'Relative aliases start from the parent of the current node while non-relative ones starts from the root of the site.'|i18n( 'design/admin/content/urlalias' )}">{'Relative to parent'|i18n( 'design/admin/content/urlalias' )}</label>

{* Create button. *}
    <input class="button" type="submit" name="NewAliasButton" value="{'Create'|i18n( 'design/admin/content/urlalias' )}" title="{'Create a new URL alias for this node.'|i18n( 'design/admin/content/urlalias' )}" />

</div>

<div class="break"></div>

</div>
</div>


{* DESIGN: Control bar END *}</div></div></div></div></div></div>

</div>



{* Generated aliases context block start *}
{* Generated aliases window. *}
<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h2 class="context-title">{'Generated aliases [%count]'|i18n( 'design/admin/content/urlalias',, hash('%count', count( $elements ) ) )}</h2>
{* DESIGN: Subline *}<div class="header-subline"></div>
{* DESIGN: Header END *}</div></div></div></div></div></div>
{* DESIGN: Content START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<div class="block">
<p>{"Note: These entries are automatically created from the name of the object. To change these names you will need to edit the object in the specific language and publish the changes."|i18n( 'design/admin/content/urlalias' )}</p>
</div>

<table class="list" cellspacing="0" >
<tr>
    <th>{'Name'|i18n( 'design/admin/content/urlalias' )}</th>
    <th>{'Language'|i18n( 'design/admin/content/urlalias' )}</th>
    <th class="tight">&nbsp;</th>
</tr>
{def $isCurrentLanguage=false()
     $language_obj=false()
     $locale=false()
     $img_title=false()}
{foreach $elements as $element sequence array('bglight', 'bgdark') as $seq}
    {set $language_obj=$element.language_object
         $locale=$language_obj.locale
         $isCurrentLanguage=eq( $locale, $node.object.current_language )}
    <tr class="{$seq}">
        {* URL text. *}
        <td>
            <a href={concat("/",$element.path)|ezurl}>
            {if $isCurrentLanguage}<b>{/if}
            {$element.text|wash}
            {if $isCurrentLanguage}</b>{/if}
            </a>
        </td>

        {* Language. *}
        <td>
        <img src="{$element.language_object.locale|flag_icon}" alt="{$element.language_object.locale|wash}" />
        &nbsp;
        {$element.language_object.name|wash}
        </td>

        {* Edit button. *}
        <td>
            {set $img_title='Edit the contents for language %language.'|i18n( 'design/admin/content/urlalias',, hash( '%language', $language_obj.name ) )}
            {if fetch( content, access, hash( access, 'edit', contentobject, $node, language, $locale ) )}
    			<a href={concat('/content/edit/', $node.contentobject_id, '/f/', $locale)|ezurl}><img src={'edit.gif'|ezimage} alt="{$img_title}" title="{$img_title}" /></a>
            {else}
    			<img src={'edit-disabled.gif'|ezimage} title="{'You can not edit the contents for language %language because you do not have permissions to edit the object.'|i18n( 'design/admin/content/urlalias',, hash( '%language', $language_obj.name ) )}" />
            {/if}
        </td>
    </tr>
{/foreach}
</table>


{* DESIGN: Content END *}</div></div></div></div></div></div>
</div>
{* Generated aliases context block end *}


</form>

{literal}
<script language="JavaScript" type="text/javascript">
<!--
    window.onload=function()
    {
        with( document.aliasform )
        {
            for( var i=0; i<elements.length; i++ )
            {
                if( elements[i].type == 'text' && elements[i].name == 'AliasText' )
                {
                    elements[i].select();
                    elements[i].focus();
                    return;
                }
            }
        }
    }
-->
</script>
{/literal}
