{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{section show=and( is_set( $module_result.content_info ),
                   is_set( $module_result.view_parameters ),
                   or( $module_result.content_info.url_alias|begins_with( $show_subtree ),
                       $show_classidentifiers|explode( ',' )|contains( $module_result.content_info.class_identifier ) ) )}

{let today_info=false()
     class_identifier_list=$show_classidentifiers|explode( ',' )
     cache_keys=array( $module_result.view_parameters.year, $module_result.view_parameters.month, $module_result.view_parameters.day )
     time_start=false()
     time_end=false()
     time_published=false()
     time_current=false()}

{switch match=cond( and( $module_result.view_parameters, $module_result.view_parameters.year, $module_result.view_parameters.month, $module_result.view_parameters.day )|ne( false() ), 1,
                    and( $module_result.view_parameters, $module_result.view_parameters.year, $module_result.view_parameters.month )|ne( false() ), 2,
                    and( $module_result, $module_result.content_info ), 3,
                    false() )}
{case match=1}
    {set today_info=$module_result.view_parameters
         time_start=maketime( 0, 0, 0, $today_info.month, 1, $today_info.year)
         time_end=maketime( 23, 59, 59, $today_info.month | inc, 0, $today_info.year )
         time_published=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )
         time_current=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )}
{/case}
{case match=2}
    {set today_info=$module_result.view_parameters
         cache_keys=array( $today_info.year, $today_info.month )
         time_start=maketime( 0, 0, 0, $today_info.month, 1, $today_info.year)
         time_end=maketime( 23, 59, 59, $today_info.month | inc, 0, $today_info.year )
         time_published=maketime( 0, 0, 0, $today_info.month, 1, $today_info.year )}
{/case}
{case match=3}
    {let current_node=cond( $module_result.content_info.node_id, fetch( content, node, hash( node_id, $module_result.content_info.node_id ) ),
                            false() )}
    {set today_info=cond( and( $current_node, $class_identifier_list|contains( $current_node.object.class_identifier ) ),
                              $current_node.object.published,
                          currentdate() )|gettime
         time_start=maketime( 0, 0, 0, $today_info.month, 1, $today_info.year )
         time_end=maketime( 23, 59, 59, $today_info.month | inc, 0, $today_info.year )
         time_published=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )
         time_current=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )
         cache_keys=false()}
    {/let}
{/case}
{case}
    {set today_info=currentdate()|gettime()
         time_start=maketime( 0, 0, 0, $today_info.month, 1, $today_info.year )
         time_end=maketime( 23, 59, 59, $today_info.month | inc, 0, $today_info.year )
         time_published=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )
         time_current=maketime( 0, 0, 0, $today_info.month, $today_info.day, $today_info.year )
         cache_keys=false()}
{/case}
{/switch}

{* Expire the content cache twice a day,
   This is required because of the current day marking *}
{*{cache-block keys=$cache_keys expiry=mul( 60, 60, 12 ) subtree_expiry=$show_subtree}*}

<div class="toolbox">
    <div class="toolbox-design">
    <h2>{'Calendar'|i18n( 'design/standard/toolbar' )}</h2>

    <div class="toolbox-content">
{*     {let log_node_id=$module_result.content_info.node_id *}
{*          log_node=fetch( content, node, hash( node_id, $log_node_id ) ) *}
    {let log_node=fetch( content, node, hash( node_path, $show_subtree ) )
         show_week=false()
         month_list=fetch( content, tree, hash( parent_node_id, $log_node.node_id,
                                                class_filter_type, include,
                                                class_filter_array, $class_identifier_list,
                                                attribute_filter, array( and, array( 'published', '>=',
                                                                                      $time_start ),
                                                                              array( 'published', '<=',
                                                                                      $time_end ) ),
                                                group_by, array( "published", "day" ),
                                                as_object, false() ) )
         month=$month_list|month_overview( 'published', $time_published,
                                           hash( current, $time_current,
                                                 current_class, 'selected',
                                                 today_class, 'today',
                                                 link, $log_node.url_alias,
                                                 month_link, true(), year_link, true(), day_link, true(),
                                                 next, hash( link, $log_node.url_alias ),
                                                 previous, hash( link, $log_node.url_alias ) ) )}
        {include name=Month uri="design:navigator/monthview.tpl" month=$month show_week=$show_week}
     {/let}

     </div>
     </div>
</div>


{*{cache-block}*}

{/let}

{/section}
