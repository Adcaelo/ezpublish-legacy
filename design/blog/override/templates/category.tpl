{let item_limit=10
     item_count=fetch( content, list_count, hash( parent_node_id, $node.node_id,
                                           class_filter_type, exclude,
                                           class_filter_array, array( 'folder', 'comment' ) ) )
     item_list=fetch( content, list, hash( parent_node_id, $node.node_id,
                                          class_filter_type, exclude,
                                          class_filter_array, array( 'folder', 'comment' ),
                                          offset, $view_parameters.offset,
                                          limit, $item_limit,
                                          sort_by, array( 'published', false() ) ) )}
<div id="category">

  <div class="header">
    <h1><span>{$node.data_map.archive_title.content|wash} by Category</span></h1>
    <em>{$node.name|wash}</em>
    <p><strong>{"Description:"|i18n("design/blog/layout")}</strong> {attribute_view_gui attribute=$node.data_map.description}</p>
  </div>


  <div id="itemlist">
  {section var=log loop=$item_list}
    {node_view_gui view=line content_node=$log.item}
  {/section}

  {include name=navigator
           uri='design:navigator/google.tpl'
           page_uri=concat( '/content/view/full/', $node.node_id )
           item_count=$item_count
           view_parameters=$view_parameters
           item_limit=$item_limit}
  </div>


</div>

{/let}
