{let package_list=fetch(package,list,hash(filter_array,array(array(type,$attribute.contentclass_attribute.data_text1) ) ) )}

{switch match=$attribute.contentclass_attribute.data_int1}
{case match=1}
<div class="block">
{section name=Package loop=$:package_list}
 <div class="package_element" align="bottom">
      <img src={$:item|ezpackage(filepath,"thumbnail")|ezroot} />
      <br />
      <input type="radio" name="ContentObjectAttribute_ezpackage_data_text_{$attribute.id}" value="{$:item.name}" 
      {section show=eq($:item.name,$attribute.data_text)} checked{/section} /><label>{$:item.name}</label>
 </div>
 {delimiter modulo=2}
    </div>
    <div class="block">
 {/delimiter}
{/section}
</div>
{/case}
{case}
<select name="ContentObjectAttribute_ezpackage_data_text_{$attribute.id}" size="1">
      <option value="0">[none]</option>
      {section name=Package loop=$:package_list}
          <option value="{$:item.name}" {section show=eq($:item.name,$attribute.data_text)}selected{/section}>{$:item.name}</option>
      {/section} 
</select>
{/case}
{/switch}
{/let}