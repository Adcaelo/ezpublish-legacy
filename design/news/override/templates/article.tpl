<div id="article">

<form method="post" action={"content/action"|ezurl}>

<input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
<input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
<input type="hidden" name="ViewMode" value="full" />


{default content_object=$node.object
         content_version=$node.contentobject_version_object}

<div class="object_title">
    <h1>{$node.name}</h1>
</div>

{section show=$node.object.can_edit}
   <input class="button" type="submit" name="EditButton" value="{'Edit'|i18n('design/standard/node/view')}" />
{/section}

<div class="byline">
  <p>
   ({$content_object.published|l10n( datetime )})
  </p>
</div>

<div class="imageright">
    {attribute_view_gui attribute=$content_version.data_map.thumbnail image_class=medium}
</div>

{attribute_view_gui attribute=$content_version.data_map.intro}
{attribute_view_gui attribute=$content_version.data_map.body}

{section show=$node.object.data_map.enable_comments.data_int}
    {let message_list=fetch( content, list, hash(
                                                parent_node_id, $node.object.main_node_id,
                                                limit, $page_limit, 
                                                offset, 0,
                                                class_filter_type, include, 
                                                class_filter_array,array( 'comment' ) ) )}

    {section show=$message_list}
        <h2>Comments</h2>
        {section name=Comment loop=$message_list}
            {node_view_gui view=line content_node=$:item}
        {/section}
    {/section}

    <h2>Comment this article!</h2>

    <div class="buttonblock">
        <form method="post" action={"content/action"|ezurl}>
        <input type="hidden" name="NodeID" value="{$node.main_node_id}" />
        <input type="hidden" name="ClassID" value="13" />
        <input class="button" type="submit" name="NewButton" value="New comment" />
        </form>
    </div>

    {/let}
{/section}

{/default}

</form>
</div>