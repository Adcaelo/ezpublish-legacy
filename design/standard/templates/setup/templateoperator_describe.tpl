<form method="post" action={'setup/templateoperator'|ezurl}>

<h1>{'Template operator wizard'|i18n('design/standard/setup')}</h1>

<h2>{'Optional information'|i18n('design/standard/setup')}</h2>

<div class="block">
<label>{'Name of class'|i18n('design/standard/setup','Template operator')}</label>
</div>
<input type="text" name="ClassName" value="{$class_name|wasah}" size="40" />

<div class="block">
<label>{'Description of your operator'|i18n('design/standard/setup','Template operator')}</label>
</div>
<p>{'The first line will be used as the brief description and the rest are operator documentation.'|i18n('design/standard/setup','Template operator')}</p>
<textarea class="box" name="Description" cols="60" rows="6">{'Handles template operator %operatorname
By using %operatorname you can ...'|i18n('design/standard/setup','Template operator default description',hash('%operatorname',$operator_name))}</textarea>

<div class="block">
<label>{'The creator of the operator'|i18n('design/standard/setup','Template operator')}</label>
</div>
<input type="text" name="CreatorName" value="{fetch(user,current_user).contentobject.name|wash}" size="40" />

<div class="block">
<label>{'Example code'|i18n('design/standard/setup','Template operator')}</label>
</div>
<p>{'If you wish you can add some example code to explain how your operator should work.
The default code was made from the basic parameters you chose.'|i18n('design/standard/setup','Template operator')}</p>
<textarea class="box" name="ExampleCode" cols="60" rows="6">{$example_code|wash}</textarea>

<p>{'Once the download button is clicked the code will be generated and the browser will ask you to store the generated file.'|i18n('design/standard/setup','Template operator')}</p>

<div class="buttonblock">
<input type="hidden" value="download" name="OperatorStep" />
<input class="defaultbutton" type="submit" value="{'Download'|i18n('design/standard/setup','Template operator download')} {'>>'|wash}" name="TemplateOperatorStepButton" />
<input class="button" type="submit" value="{'Restart'|i18n('design/standard/setup','Template operator restart')}" name="TemplateOperatorRestartButton" />
</div>

{section name=Persistence loop=$persistent_data}
<input type="hidden" name="PersistentData[{$:key|wash}]" value="{$:item|wash}" />
{/section}

</form>
