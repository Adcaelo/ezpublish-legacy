<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">
{let pagedesign=fetch_alias(by_identifier,hash(attr_id,gallery_package))}
<head>
{include uri="design:page_head.tpl" enable_glossary=false() enable_help=false()}

<style>
    @import url({"stylesheets/core.css"|ezdesign});
    {* @import url({$pagedesign.data_map.css.content|ezpackage(filepath,"cssfile")|ezroot});*}
    @import url("/design/gallery/stylesheets/gallery.css");
</style>

</head>
    {cache-block}

<body>

<div id="background">

    <div id="header">
        <div class="design">
        
           {let content=$pagedesign.data_map.image.content}
            <div id="logo">
                <a href={"/"|ezurl}><img src={$content[logo].full_path|ezroot} /></a>
            </div>
           {/let}
                  
        </div>
    </div>

    <div id="subheader">
        <div class="design">
        
            <div id="searchbox">
                <form action={"/content/search/"|ezurl} method="get">
                    <input class="searchtext" type="text" size="10" name="SearchText" id="Search" value="" />
                    <input class="searchbutton" name="SearchButton" type="submit" value="Search" />
                </form>
            </div>
        
        <div id="mainmenu">
            <div class="design">

                <h3 class="invisible">Main menu</h3>
                <ul>
                {let folder_list=fetch( content, list, hash( parent_node_id, 2, 
                                                             sort_by, array( array( priority ) ),
  							     class_filter_type, exclude, 
							     class_filter_array, array( 'gallery' ) ) )}
                {section var=folder loop=$folder_list}
                    <li><a href={$folder.item.url_alias|ezurl} title="{attribute_view_gui attribute=$folder.item.description}">{$folder.item.name|wash}</a></li>
                {/section}
                {/let}
    {/cache-block}

                <li>
                    {section show=$current_user.is_logged_in}
                        <a href={"user/logout"|ezurl}>Logout</a>
                    {section-else}
                        <a href={"user/login"|ezurl}>Login</a>
                    {/section}
                </li>
    {cache-block}

                </ul>
            
            </div>
        </div>
        
        <div class="break"></div> {* This is needed for proper flow of floating objects *}
        
        </div>
    </div>

    {/cache-block}

    {cache-block keys=array( $uri_string )}
    {include uri="design:infobox.tpl"}
    {/cache-block}

    <div id="maincontent">
        <div class="design">

    {section show=$module_result.content_info.node_id|ne( 2 )}
    {cache-block keys=array( $uri_string )}
    <div id="path">
        <div class="design">

           <p>
           &gt;
           {section var=path loop=$module_result.path }
               {section show=$path.item.url}
                   <a href={cond( is_set( $path.item.url_alias ), $path.item.url_alias|ezurl, $path.item.url|ezurl )}>{$path.item.text|wash}</a>
               {section-else}
                   {$path.item.text|wash}
               {/section}
    
               {delimiter}
                 /
               {/delimiter}
            {/section}
           </p>

        </div>
    </div>
    {/cache-block}
    {/section}

    <div class="content">
            {$module_result.content}
    </div>
        
        </div>
    </div>

    {cache-block}
    <div id="footer">
        <div class="design">
        
            <address>
            Copyright &copy; <a href="http://ez.no">eZ systems as</a> 1999-2003
            <a href="http://ez.no/">Powered by eZ publish Content Management System</a>
            </address>
        
        </div>
    </div>

</div>

</body>
    {/cache-block}


{/let}
</html>

