{default with_children=false()
         is_editable=true()
	 is_standalone=true()
         content_object=$node.object
         content_version=$node.contentobject_version_object
         node_name=$node.name
         parent_nodes=$content_object.parent_nodes}
{let user_attribute=$node.object.data_map.user_account
     has_extra_groups=false()}

{section show=$is_standalone}
    <form method="post" action={"content/action"|ezurl}>
{/section}


<div class="objectheader">
    <h2>{$node_name|wash} &lt;{$user_attribute.content.email|wash}&gt; [{'User'|i18n('design/admin/node/view')}]</h2>
</div>

<div class="object">
    <p>{node_view_gui view=text_linked content_node=$content_object.main_node.parent}</p>
    <p>Login: <i>{$user_attribute.content.login|wash}</i></p>
    <p>User ID: <i>{$user_attribute.content.contentobject_id}</i></p>

    <input type="hidden" name="TopLevelNode" value="{$content_object.main_node_id}" />
    <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />

    {section loop=$parent_nodes}
    {section-exclude match=eq($:item,$content_object.main_node.parent_node_id)}
        {set has_extra_groups=true()}
    {/section}

    {section show=$has_extra_groups}
        <p>{'Also part of these groups'|i18n('design/admin/node/view')}</p>
        {section name=Parent loop=$parent_nodes}
        {section-exclude match=eq($:item,$content_object.main_node.parent_node_id)}
            <p>{node_view_gui view=text_linked content_node=fetch(content,node,hash(node_id,$:item))}</p>
        {/section}
    {/section}

    <div class="buttonblock">
    {section show=and($is_editable,$content_object.can_edit)}
        <input type="hidden" name="ContentObjectID" value="{$content_object.id}" />
        <input class="button" type="submit" name="EditButton" value="{'Edit'|i18n( 'design/standard/node/view' )}" />
    {/section}
    <input class="button" type="submit" name="ActionRemove" value="Remove" />
    </div>
</div>

{section show=$is_standalone}
    </form>
{/section}

{/let}
{/default}
