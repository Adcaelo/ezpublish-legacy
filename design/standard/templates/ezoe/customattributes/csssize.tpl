{set $custom_attribute_classes = $custom_attribute_classes|append( 'int' )}
{if ezini_hasvariable( $custom_attribute_settings, 'Minimum', 'ezoe_customattributes.ini' )}
    {set $custom_attribute_classes = $custom_attribute_classes|append( concat('min', ezini($custom_attribute_settings, 'Minimum', 'ezoe_customattributes.ini') ) )}
{/if}
{if ezini_hasvariable( $custom_attribute_settings, 'Maximum', 'ezoe_customattributes.ini' )}
    {set $custom_attribute_classes = $custom_attribute_classes|append( concat('max', ezini($custom_attribute_settings, 'Maximum', 'ezoe_customattributes.ini') ) )}
{/if}
<input type="text" size="3" name="{$custom_attribute}" id="{$custom_attribute_id}_source" value="{$custom_attribute_default|wash}"{if $custom_attribute_disabled} disabled="disabled"{/if} class="{$custom_attribute_classes|implode(' ')}" title="{$custom_attribute_titles|wash}" />
<select id="{$custom_attribute_id}_sizetype"{if $custom_attribute_disabled} disabled="disabled"{/if} class="mceItemSkip sizetype_margin_fix">
{if ezini_hasvariable( $custom_attribute_settings, 'CssSizeType', 'ezoe_customattributes.ini' )}
{foreach ezini( $custom_attribute_settings, 'CssSizeType', 'ezoe_customattributes.ini' ) as $key => $value}
    <option value="{$key}">{$value}</option>
{/foreach}
{else}
    <option value="px">px</option>
    <option value="em">em</option>
    <option value="%">%</option>
{/if}
</select>
<script type="text/javascript">
<!--

eZOEPopupUtils.settings.customAttributeInitHandler['{$custom_attribute_id}_source'] = {literal} function( el, value )
{
    el.value = ez.num( value, 0, 'int' );
    var selid = el.id.replace('_source', '_sizetype');
    ez.$( selid ).el.selectedIndex = ez.$$('#' + selid + ' option').map(function( o ){
        return o.el.value;
    }).indexOf( value.replace( el.value, '' ) );
};{/literal}

eZOEPopupUtils.settings.customAttributeSaveHandler['{$custom_attribute_id}_source'] = {literal} function( el, value )
{
    var sel = document.getElementById( el.id.replace('_source', '_sizetype') );
    return value + ( sel.selectedIndex !== -1 ? sel.options[sel.selectedIndex].value : '' );
};{/literal}

//-->
</script>