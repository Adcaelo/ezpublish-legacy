<div class="folder_line">

<tr>
    <td class="maintopics" colspan="2">
    {$node.name}
    </td>
    <td class="maintopics">
    &nbsp;
    </td>
    <td class="maintopics">
    &nbsp;
    </td>
</tr>
{let page_limit=0
    children=fetch('content','list',hash(parent_node_id,$node.node_id,sort_by,$node.sort_array,limit,$page_limit,offset,$view_parameters.offset))    list_count=fetch('content','list_count',hash(parent_node_id,$node.node_id))}
{section name=Child loop=$children sequence=array(bglightforum,bgdarkforum)}
<tr class="{$Child:sequence}">
    <td class="forumimage">
    {attribute_view_gui attribute=$Child:item.object.data_map.image image_class=original}
    </td>
    <td class="forumtopics">
    <a href={concat( "/content/view/full/", $Child:item..node_id, "/")|ezurl}>{$Child:item.name}</a>
    </td>
    <td class="topiccount">
    {fetch('content','list_count',hash(parent_node_id,$Child:item.node_id))}
    </td>
    <td class="replycount">
    {fetch('content','tree_count',hash(parent_node_id,$Child:item.node_id))}
    </td>
</tr>
{/section}

{/let}


</div>