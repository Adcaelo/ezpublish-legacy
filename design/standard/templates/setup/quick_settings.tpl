{* DO NOT EDIT THIS FILE! Use an override template instead. *}

{let settings_list=ezini( 'DebugSettings', 'QuickSettingsList', 'site.ini' )}

<form name="quicksettings" action={'setup/settingstoolbar'|ezurl} method="post">
<input type=hidden name="SiteAccess" value="{$siteaccess}"/>

{section show=eq( $siteaccess, 'global_override' )}
   {section loop=$settings_list}
     <input type=hidden name="AllSettingsList[]" value="{$:item}"/>
     {let setting=$:item|explode( ';' )}
     {let debug_output=ezini( $setting.0, $setting.1, $setting.2, 'settings/override', true )}
      <label>
      <input type=checkbox {eq( $debug_output, 'enabled' )|choose( '', 'checked ' )}name="SelectedList[]" value="{$:index}"/>
         {section show=eq( $debug_output, '' )}
            <span class="disabled">{$setting.3}</span>
         {section-else}
            {$setting.3}
         {/section}
     </label>
     {/let}
     {/let}
   {/section}
{section-else}
   {section loop=$settings_list}
      <input type=hidden name="AllSettingsList[]" value="{$:item}"/>
      {let setting=$:item|explode( ';' )}
      {let debug_output=ezini( $setting.0, $setting.1, $setting.2, concat( 'settings/siteaccess/', $siteaccess ), true )
           debug_output_override=ezini( $setting.0, $setting.1, $setting.2, 'settings/override', true )}
         <label>
      {section show=ne( $debug_output_override, '' )}
         <input type=checkbox{eq( $ui_context, 'edit' )|choose( '', ' disabled' )} {eq( $debug_output_override, 'enabled' )|choose( '', 'checked ' )}name="SelectedList[]" value="{$:index}"/>
         <span class="overriden">{$setting.3}</span>
      {section-else}
         {section show=eq( $debug_output, '' )}
            <input type=checkbox{eq( $ui_context, 'edit' )|choose( '', ' disabled' )} {eq( ezini( $setting.0, $setting.1, $setting.2 ), 'enabled' )|choose( '', 'checked ' )}name="SelectedList[]" value="{$:index}"/>
         {section-else}
            <input type=checkbox{eq( $ui_context, 'edit' )|choose( '', ' disabled' )} {eq( $debug_output, 'enabled' )|choose( '', 'checked ' )}name="SelectedList[]" value="{$:index}"/>
         {/section}
            {$setting.3}
      {/section}
         </label>
      {/let}
      {/let}
   {/section}   
{/section}
{section show=eq( $select_siteaccess, true )}
    <label>Siteaccess:</label>
    {let siteaccesslist=ezini( 'SiteAccessSettings', 'AvailableSiteAccessList' )}
    <select name="siteaccesslist"{eq( $ui_context, 'edit' )|choose( '', ' disabled' )}>
            <option onclick='location.href={'/user/preferences/set/admin_quicksettings_siteaccess/global_override'|ezurl}'{section show=eq( $siteaccess, 'global_override')} selected{/section}>Global (override)</option>
    {section loop=$siteaccesslist}
    		<option onclick='location.href={concat( '/user/preferences/set/admin_quicksettings_siteaccess/', $:item )|ezurl}'{section show=eq( $siteaccess, $:item )} selected{/section}>{$:item}</option>
    {/section}
    </select><br/>
    {/let}
{/section}

<input {eq( $ui_context, 'edit' )|choose( "class='button'", "class='button-disabled'" )}{eq( $ui_context, 'edit' )|choose( '', ' disabled' )} type=submit name="SetButton" value="Set"/>
</form>
{let}
