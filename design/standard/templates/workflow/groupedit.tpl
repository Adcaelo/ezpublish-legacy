<form action={concat($module.functions.groupedit.uri,"/",$workflow_group.id)|ezurl} method="post" name="WorkflowGroupEdit">

<div class="maincontentheader">
<h1>{"Editing workflow group"|i18n("design/standard/workflow")} - {$workflow_group.name}</h1>
</div>

<div class="byline">
<p class="modified">{"Modified by"|i18n("design/standard/workflow")} {content_view_gui view=text_linked content_object=$workflow_group.modifier.contentobject} {"on"|i18n("design/standard/workflow")} {$workflow_group.modified|l10n(shortdatetime)}</p>
</div>

<div class="block">
<label>{"Name"|i18n("design/standard/workflow")}</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" name=Name id_name=WorkflowGroup_name value=$workflow_group.name}
</div>

<div class="buttonblock">
{include uri="design:gui/button.tpl" name=Store id_name=StoreButton value="Store"|i18n("design/standard/workflow")}
{include uri="design:gui/button.tpl" name=Discard id_name=DiscardButton value="Discard"|i18n("design/standard/workflow")}
</div>

</form>
