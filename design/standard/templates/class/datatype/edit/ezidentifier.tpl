<div class="block">
<div class="element">
<label>{"Pretext"|i18n("design/standard/class/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentClass_ezidentifier_pretext_value_{$class_attribute.id}" value="{$class_attribute.data_text1}" size="8" maxlength="20" />
</div>
<div class="element">
<label>{"Posttext"|i18n("design/standard/class/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentClass_ezidentifier_posttext_value_{$class_attribute.id}" value="{$class_attribute.data_text2}" size="8" maxlength="20" />
</div>
<div class="element">
<label>{"Current value: "|i18n("design/standard/class/datatype")}{$class_attribute.data_int3}{" (This value is a snapshot and may have been incremented)"|i18n("design/standard/class/datatype")}</label>
</div>
<div class="break"></div>
<div class="element">
<label>{"Digits"|i18n("design/standard/class/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentClass_ezidentifier_digits_integer_value_{$class_attribute.id}" value="{$class_attribute.data_int2}" size="8" maxlength="20" />
</div>
<div class="element">
<label>{"Start value"|i18n("design/standard/class/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentClass_ezidentifier_start_integer_value_{$class_attribute.id}" value="{$class_attribute.data_int1}" size="8" maxlength="20" />
</div>
<div class="break"></div>
<div class="buttonblock">
<input class="button" type="submit" name="CustomActionButton[{$class_attribute.id}_update_start_value]" value="{'Update start value'|i18n('design/standard/class/datatype')}" />
</div>
<div class="break"></div>
</div>
