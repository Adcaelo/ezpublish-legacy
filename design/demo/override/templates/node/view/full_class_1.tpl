{default with_children=true()}
{let page_limit=8
     list_count=and($with_children,fetch('content','list_count',hash(parent_node_id,$node.node_id,class_filter_type,exclude,class_filter_array,array(1,24))))}
{default content_version=$node.contentobject_version_object}

<div class="block">
{attribute_view_gui attribute=$content_version.data_map.description}
</div>

{section show=$with_children}

{section name=Child loop=fetch('content','list',hash(parent_node_id,$node.node_id,limit,$page_limit,offset,$view_parameters.offset,sort_by,$node.sort_array,class_filter_type,exclude,class_filter_array,array(1,24)))}

    {node_view_gui view=line content_node=$Child:item}

{/section}

{/section}

{/default}
{/let}
{/default}
