{default input_handler=$attribute.content.input}
  <textarea class="box" name="{$attribute_base}_data_text_{$attribute.id}" cols="97" rows="{$attribute.contentclass_attribute.data_int1}">{$input_handler.input_xml|wash(xhtml)}</textarea>
{/default}
