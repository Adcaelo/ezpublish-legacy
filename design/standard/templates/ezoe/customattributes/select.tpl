<select name="{$custom_attribute}" id="{$custom_attribute_id}_source"{if $custom_attribute_disabled} disabled="disabled"{/if} title="{$custom_attribute_titles|wash}">
{if ezini_hasvariable( $custom_attribute_settings, 'Selection', 'ezoe_customattributes.ini' )}
{foreach ezini( $custom_attribute_settings, 'Selection', 'ezoe_customattributes.ini' ) as $custom_value => $custom_name}
    <option value="{$custom_value|wash}"{if $custom_value|eq( $custom_attribute_default )} selected="selected"{/if}>{$custom_name|wash}</option>
{/foreach}
{else}
{foreach $custom_attribute_selection as $custom_value => $custom_name}
    <option value="{if $custom_value|ne('0')}{$custom_value|wash}{/if}"{if $custom_value|eq( $custom_attribute_default )} selected="selected"{/if}>{$custom_name|wash}</option>
{/foreach}
{/if}
</select>