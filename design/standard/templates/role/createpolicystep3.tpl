<div class="maincontentheader">
<h1>{"Create policy for"|i18n("design/standard/role")} {$role.name}</h1>
</div>

<form action={concat($module.functions.edit.uri,"/",$role.id,"/")|ezurl} method="post" >

<div class="step">
<table cellspacing="0" cellpadding="4" border="0">
<tr>
  <td>

<h2>{"Step 1"|i18n("design/standard/role")}</h2>
<div class="block">
   	<div class="element">
	<label>{"Module"|i18n("design/standard/role")}</label><div class="labelbreak"></div>
    <p class="box">{$current_module}</p>
    </div>
   	<div class="element">
	<label>{"Access"|i18n("design/standard/role")}</label><div class="labelbreak"></div>
    <p class="box">{"Limited"|i18n("design/standard/role")}</p>
    </div>
    <div class="break"></div>
</div>
<div class="buttonblock">
  	<input class="button" type="submit" name="Step1" value="{'Go back to step 1'|i18n('design/standard/role')}" />
</div>

  </td>
  <td>

<h2>{"Step 2"|i18n("design/standard/role")}</h2>
<div class="block">
   	<div class="element">
	<label>{"Function"|i18n("design/standard/role")}</label><div class="labelbreak"></div>
    <p class="box">{$current_function}</p>
    </div>
   	<div class="element">
	<label>{"Access"|i18n("design/standard/role")}</label><div class="labelbreak"></div>
    <p class="box">{"Limited"|i18n("design/standard/role")}</p>
    </div>
    <div class="break"></div>
</div>
<div class="buttonblock">
  	<input class="button" type="submit" name="Step2" value="{'Go back to step 2'|i18n('design/standard/role')}" />
</div>

  </td>
</tr>
</table>
</div>

<h2>{"Step 3"|i18n("design/standard/role")}</h2>
<p>
    {"Specify limitations for function %functionname in module %modulename. 'Any' means no limitation by this parameter"
     |i18n("design/standard/role",,
           hash('%functionname',concat('<b>',$current_function,'</b>'),
                '%modulename',concat('<b>',$current_module,'</b>')))}
</p>
<div class="objectheader">
<h2>{'Limitations'|i18n('design/standard/role')}</h2>
</div>
<div class="object">

     {section name=Limitations loop=$function_limitations}
     {section-exclude match=$Limitations:item.name|eq('Subtree')}
     {section-exclude match=$Limitations:item.name|eq('Node')}
<div class="element">
    <label>{$Limitations:item.name}</label><div class="labelbreak"/>
     <select name="{$Limitations:item.name}[]" size="8" multiple >
     <option value="-1" {switch match=$current_limitation_list[$Limitations:item.name]}
     {case match=-1} selected="selected"{/case}{case}{/case}{/switch}>{"Any"|i18n("design/standard/role")}</option>
     {section name=LimitationValues loop=$Limitations:item.values}
     <option value="{$Limitations:LimitationValues:item.value}" {switch match=$Limitations:LimitationValues:item.value}
     {case in=$current_limitation_list[$Limitations:item.name]}selected="selected"{/case}{case}{/case}{/switch}>{$Limitations:LimitationValues:item.Name}</option>
     {/section}   
     </select>
</div>
     {/section}  

{section name=Limitations loop=$function_limitations}
{switch match=$Limitations:item.name} 
  {case match="Node"}
   <div class="element">
    <label>Node</label><div class="labelbreak"></div>
    <table>
     {section show=$node_list name=NodeList loop=$node_list}
     <tr>
     <td>
     {$Limitations:NodeList:item.name}
     </td>
     <td>
     <input type="checkbox" name="DeleteNodeIDArray[]" value={$Limitations:NodeList:item.node_id} />
     </td>
     </tr>
     {section-else}
     <tr>
     <td>
      Not specified.
     </td>
     </tr>
     {/section}
     </table>
     <input class="menubutton" type="image" name="BrowseLimitationNodeButton" value="{'Find'|i18n('design/standard/shop')}" src={"find.png"|ezimage} />
     <input class="menubutton" type="image" name="DeleteNodeButton" value="{'Remove'|i18n('design/standard/shop')}" src={"trash.png"|ezimage} />
   </div>
  {/case}
  {case match="Subtree"}
   <div class="element">
    <label>Subtree</label><div class="labelbreak"></div>
    <table>
     {section show=$subtree_list name=SubtreeList loop=$subtree_list}
     <tr>
     <td>
     {$Limitations:SubtreeList:item.name}
     </td>
     <td>
     <input type="checkbox" name="DeleteSubtreeIDArray[]" value={$Limitations:SubtreeList:item.node_id} />
     </td>
     </tr>
     {section-else}
     <tr>
     <td>
      Not specified.
     </td>
     </tr>
     {/section}
     </table>
     <input class="menubutton" type="image" name="BrowseLimitationSubtreeButton" value="{'Find'|i18n('design/standard/shop')}" src={"find.png"|ezimage} />
     <input class="menubutton" type="image" name="DeleteSubtreeButton" value="{'Remove'|i18n('design/standard/shop')}" src={"trash.png"|ezimage} />
   </div>
  {/case}
  {case}
  {/case}
{/switch}
{/section} 

<div class="break"></div>
</div>
<div class="buttonblock">
<input class="button" type="submit" name="AddLimitation" value="{'Ok'|i18n('design/standard/role')}" />
<input type="hidden" name="CurrentModule" value="{$current_module}" />
<input type="hidden" name="CurrentFunction" value="{$current_function}" />

{*<input type="submit" name="DiscardLimitation" value="Return to functions" />*}
<input class="button" type="submit" value="{'Cancel'|i18n('design/standard/role')}" />
</div>
</form>
