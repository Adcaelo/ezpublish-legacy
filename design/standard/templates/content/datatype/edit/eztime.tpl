<div class="block">
<div class="element">
<label>{"Hour"|i18n("design/standard/content/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentObjectAttribute_time_hour_{$attribute.id}" size="3" value="{section show=$attribute.content.is_valid}{$attribute.content.hour}{/section}" /> 
</div>
<div class="element">
<label>{"Minute"|i18n("design/standard/content/datatype")}</label><div class="labelbreak"></div>
<input type="text" name="ContentObjectAttribute_time_minute_{$attribute.id}" size="4" value="{section show=$attribute.content.is_valid}{$attribute.content.minute}{/section}" />
</div>
<div class="break"></div>
</div>
