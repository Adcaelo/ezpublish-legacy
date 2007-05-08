<script language="JavaScript" type="text/javascript" src={"javascript/lib/ezjslibcookiesupport.js"|ezdesign}></script>
{if ezini('TreeMenu','PreloadClassIcons','contentstructuremenu.ini')|eq('enabled')}
    <script language="JavaScript" type="text/javascript" src={"javascript/lib/ezjslibimagepreloader.js"|ezdesign}></script>
{/if}

{def $click_action=ezini('TreeMenu','ItemClickAction','contentstructuremenu.ini')}
{if and( is_set( $csm_menu_item_click_action ), $click_action|not )}
    {set $click_action=$csm_menu_item_click_action}
{/if}

{if $click_action}
    {set $click_action=$click_action|ezurl(no)}
{/if}

{literal}
<script type="text/javascript">
<!--

if( !Array.prototype.inArray )
{
    Array.prototype.inArray = function( value )
    {
        for ( var i = 0; i < this.length; i++ )
        {
            if ( this[i] == value )
            {
                return true;
            }
        }
        return false;
    }
}

Array.prototype.removeFirst = function( value )
{
    for ( var i = 0; i < this.length; i++ )
    {
        if ( this[i] == value )
        {
            this.splice( i, 1 );
            return true;
        }
    }

    return false;
}

function ContentStructureMenu()
{
    this.cookieName = "contentStructureMenu";
    this.cookieValidity = 3650; // days
    this.cookie = ezjslib_getCookie( this.cookieName );
    this.open = ( this.cookie )? this.cookie.split( '/' ): [];
{/literal}

    this.action = "{$click_action}";
    this.context = "{$ui_context}";
    this.expiry = "{fetch('content','content_tree_menu_expiry')}";

{cache-block expiry="0"}
    this.languages = {*
        *}{ldelim}{*
            *}{foreach fetch('content','translation_list') as $language}{*
                *}"{$language.locale_code|wash(javascript)}":"{$language.intl_language_name|wash(javascript)}"{*
                *}{delimiter},{/delimiter}{*
            *}{/foreach}{*
        *}{rdelim};
    this.classes = {*
        *}{ldelim}{*
            *}{foreach fetch('class','list',hash('sort_by',array('name',true()))) as $class}{*
                *}"{$class.id}":{ldelim}name:"{$class.name|wash(javascript)}",identifier:"{$class.identifier|wash(javascript)}"{rdelim}{*
                *}{delimiter},{/delimiter}{*
            *}{/foreach}{*
        *}{rdelim};

{def $iconInfo = icon_info('class')
     $classIconsSize = ezini('TreeMenu','ClassIconsSize','contentstructuremenu.ini')}

    this.iconsList = new Array();
    var wwwDirPrefix = "{ezsys('wwwdir')}/{$iconInfo.theme_path}/{$iconInfo.size_path_list[$classIconsSize]}/";
    {foreach $iconInfo.icons as $class => $icon}{*
        *}this.iconsList['{$class}'] = wwwDirPrefix + "{$icon}";
    {/foreach}

    this.iconsList['__default__'] = wwwDirPrefix + "{$iconInfo.default}";
{/cache-block}

    {if ezini('TreeMenu','PreloadClassIcons','contentstructuremenu.ini')|eq('enabled')}
        ezjslib_preloadImageList( this.iconsList );
    {/if}

    this.showTips = {if ezini('TreeMenu','ToolTips','contentstructuremenu.ini')|eq('enabled')}true{else}false{/if};
    this.createHereMenu = "{ezini('TreeMenu','CreateHereMenu','contentstructuremenu.ini')}";
    this.autoOpen = {if ezini('TreeMenu','AutoopenCurrentNode','contentstructuremenu.ini')|eq('enabled')}true{else}false{/if};

{def $current_user=fetch('user','current_user')}
    this.perm = "{concat($current_user.role_id_list|implode(','),'|',$current_user.limited_assignment_value_list|implode(','))|md5}";

{literal}
    this.updateCookie = function()
    {
        this.cookie = this.open.join('/');
        expireDate = new Date();
        expireDate.setTime( expireDate.getTime() + this.cookieValidity * 86400000 );
        ezjslib_setCookie( this.cookieName, this.cookie, expireDate );
    }

    this.setOpen = function( nodeID )
    {
        if ( this.open.inArray( nodeID ) )
        {
            return;
        }
        this.open[this.open.length] = nodeID;
        this.updateCookie();
    }

    this.setClosed = function( nodeID )
    {
        if ( this.open.removeFirst( nodeID ) )
        {
            this.updateCookie();
        }
    }

    this.generateEntry = function( item, lastli )
    {
        var liclass = '';
        if ( lastli )
        {
            liclass += ' lastli';
        }
        if ( path && ( path[path.length-1] == item.node_id || ( !item.has_children && path.inArray( item.node_id ) ) ) )
        {
            liclass += ' currentnode';
        }
        var html = '<li id="n'+item.node_id+'"'
            + ( ( liclass )? ' class="' + liclass + '"':
                             '' )
            + '>';
        if ( item.has_children )
        {
            html += '<a class="openclose-open" id="a'
                + item.node_id
                + '" href="#" onclick="this.blur(); return treeMenu.load( this, '
                + item.node_id
                + ', '
                + item.modified_subnode
                +' )"></a>';
        }
        
        var languages = "[";
        var firstLanguage = true;
        for ( var j = 0; j < item.languages.length; j++ )
        {
            if ( this.languages[item.languages[j]] )
            {
                if ( !firstLanguage )
                {
                    languages += ",";
                }
                firstLanguage = false;
                languages += "{locale:'"
                    + item.languages[j].replace(/'/,"\\'")
                    + "',name:'"
                    + this.languages[item.languages[j]].replace(/'/,"\\'")
                    + "'}";
            }
        }
        languages += "]";

        var canCreateClasses = false;
        var classes = "[";
        if ( this.createHereMenu != 'disabled' )
        {
            if ( this.createHereMenu == 'full' )
            {
                var classList = item.class_list;

                for ( var j = 0; j < classList.length; j++ )
                {
                    if ( this.classes[classList[j]] )
                    {
                        if ( canCreateClasses )
                        {
                            classes += ",";
                        }
                        canCreateClasses = true;
                        classes += "{classID:'"
                            + classList[j]
                            + "',name:'"
                            + this.classes[classList[j]].name.replace(/'/,"\\'")
                            + "'}";
                    }
                }
            }
            else
            {
                for ( j in this.classes )
                {
                    if ( canCreateClasses )
                    {
                        classes += ",";
                    }
                    canCreateClasses = true;
                    classes += "{classID:'"
                        + j
                        + "',name:'"
                        + this.classes[j].name.replace(/'/,"\\'")
                        + "'}";
                }
            }
        }
        classes += "]";

        var classIdentifier = this.classes[item.class_id].identifier;
        var icon = ( this.iconsList[classIdentifier] )? this.iconsList[classIdentifier]: this.iconsList['__default__'];
        if ( this.context != 'browse' )
        {
            html += '<a class="nodeicon" href="#" onclick="ezpopmenu_showTopLevel( event, \'ContextMenu\', {\'%nodeID%\':'
                + item.node_id
                + ', \'%objectID%\':'
                + item.object_id
                + ', \'%languages%\':'
                + languages
                + ', \'%classList%\':'
                + classes
                + ' }, \''
                + item.name.replace(/'/,"\\'")
                + '\', '
                + item.node_id
                + ', '
                + ( ( canCreateClasses )? '-1':
                                          '\'menu-create-here\'' )
                + ' ); return false"><img src="'
                + icon
                + '" alt="" title="['
                + this.classes[item.class_id].name
{/literal}
                + '] {"Click on the icon to get a context sensitive menu."|i18n('design/admin/contentstructuremenu')}" /></a>';
{literal}
        }
        else
        {
            html += '<img src="'
                + icon
                + '" alt="" />';
        }
        html += '&nbsp;<a class="nodetext" href="'
            + ( ( this.action )? this.action + '/' + item.node_id:
                                 item.url )
            + '"';
        
        if ( this.showTips )
        {
{/literal}
            html += ' title="{"Node ID"|i18n('design/admin/contentstructuremenu')}: ' 
                + item.node_id
                + ' {"Visibility"|i18n('design/admin/contentstructuremenu')}: '
                + ( ( item.is_hidden )? '{"Hidden"|i18n('design/admin/contentstructuremenu')}':
                                        ( item.is_invisible )? '{"Hidden by superior"|i18n('design/admin/contentstructuremenu')}':
                                                               '{"Visible"|i18n('design/admin/contentstructuremenu')}' )
                + '"';
{literal}
        }
        
        html += '><span class="node-name-'
            + ( ( item.is_hidden )? 'hidden': 
                                    ( item.is_invisible )? 'hiddenbyparent':
                                                           'normal' )
            + '">'
            + item.name
            + '</span>';

        if ( item.is_hidden )
        {
{/literal}
            html += '<span class="node-hidden"> ({"Hidden"|i18n('design/admin/contentstructuremenu')})</span>';
{literal}
        }
        else if ( item.is_invisible )
        {
{/literal}
            html += '<span class="node-hiddenbyparent"> ({"Hidden by superior"|i18n('design/admin/contentstructuremenu')})</span>';
{literal}
        }

        html += '</a>';
        html += '<div id="c'
            + item.node_id
            + '"></div>';
        html += '</li>';

        return html;
    }

    this.load = function( aElement, nodeID, modifiedSubnode )
    {
{/literal}
        var url = "{"content/treemenu"|ezurl(no)}?node_id=" + nodeID 
            + "&modified=" + modifiedSubnode
            + "&expiry=" + this.expiry
            + "&perm=" + this.perm;
{literal}
        var request = false;
        var divElement = document.getElementById( 'c' + nodeID );

        if ( !divElement )
        {
            return false;
        }

        if ( divElement.className == 'hidden' )
        {
            divElement.style.display = '';
            divElement.className = 'loaded';
            if ( aElement )
            {
                aElement.className = 'openclose-close';
            }

            this.setOpen( nodeID );

            return false;
        }

        if ( divElement.className == 'loaded' )
        {
            divElement.style.display = 'none';
            divElement.className = 'hidden';
            if ( aElement )
            {
                aElement.className = 'openclose-open';
            }

            this.setClosed( nodeID );
        
            return false;
        }

        if ( divElement.className == 'busy' )
        {
            return false;
        }

        if ( window.XMLHttpRequest )
        {
            request = new XMLHttpRequest();
        }
        else
        {
/*@cc_on
    @if (@_jscript_version >= 5)
            var xmlObjects = ['Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.4.0', 'Msxml2.XMLHTTP.3.0', 
                              'Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'];
            for ( var i = 0; i < xmlObjects.length; i++ )
            {
                try
                {
                    if ( request = new ActiveXObject( xmlObjects[i] ) )
                    {
                        break;
                    }
                }
                catch( e )
                {
                }
            }
    @end
@*/
        }

        if ( !request )
        {
            return;
        }

        divElement.className = 'busy';
        if ( aElement )
        {
            aElement.className = "openclose-busy";
        }

        request.open( 'GET', url, true );

        var thisThis = this;

        request.onreadystatechange = function()
        {
            if ( request.readyState == 4 )
            {
                var result = false;

                if ( request.status == 200 ) // the following does not work in Konqueror with cached documents: && request.getResponseHeader( 'Content-Type' ).substr( 0, 16 ) == 'application/json' )
                {
                    try
                    {
                        result = eval( '(' + request.responseText + ')' );
                    }
                    catch ( err )
                    {
                        result = false;
                    }

                    if ( result && result.error_code == 0 )
                    {
                        var html = '<ul>';
                        for ( var i = 0; i < result.children.length; i++ )
                        {
                            var item = result.children[i];
                            html += thisThis.generateEntry( item, i == result.children.length - 1 );
                        }
                        html += '</ul>';

                        divElement.innerHTML += html;
                        divElement.className = 'loaded';
                        if ( aElement )
                        {
                            aElement.className = 'openclose-close';
                        }

                        thisThis.setOpen( nodeID );
                        thisThis.openUnder( nodeID );

                        return;
                    }
                }

                if ( aElement )
                {
                    aElement.className = 'openclose-error';
                    if ( result && result.error_code != 0 )
                    {
                        aElement.title = result.error_message;
                    }
                    else
                    {
                        switch( request.status )
                        {
                            case 403:
                            {
{/literal}
                                aElement.title = '{"Dynamic tree not allowed for this siteaccess."|i18n('design/admin/contentstructuremenu')}';
{literal}
                            } break;
                        
                            case 404:
                            {
{/literal}
                                aElement.title = '{"Node does not exist."|i18n('design/admin/contentstructuremenu')}';
{literal}
                            } break;
                        
                            case 500:
                            {
{/literal}
                                aElement.title = '{"Internal error."|i18n('design/admin/contentstructuremenu')}';
{literal}
                            } break;
                        }
                    }
                    aElement.onclick = function()
                    {
                        return false;
                    }
                }
            }
        };

        request.send( null );

        return false;
    }


    this.openUnder = function( parentNodeID )
    {
        var divElement = document.getElementById( 'c' + parentNodeID );
        if ( !divElement )
        {
            return;
        }

        var ul = divElement.getElementsByTagName( 'ul' )[0];
        if ( !ul )
        {
            return;
        }

        var children = ul.childNodes;
        for ( var i = 0; i < children.length; i++ )
        {
            var liCandidate = children[i];
            if ( liCandidate.nodeType == 1 && liCandidate.id )
            {
                var nodeID = parseInt( liCandidate.id.substr( 1 ) );
                if ( this.autoOpen && autoOpenPath.inArray( nodeID ) )
                {
                    autoOpenPath.removeFirst( nodeID );
                    this.setOpen( nodeID );
                }
                if ( this.open.inArray( nodeID ) )
                {
                    var aElement = document.getElementById( 'a' + nodeID );
                    if ( aElement )
                    {
                        aElement.onclick();
                    }
                }
            }
        }
    }
}

// -->
</script>
{/literal}

{def $root_node_id=ezini('TreeMenu','RootNodeID','contentstructuremenu.ini')}
{if is_set( $custom_root_node_id )}
    {set $root_node_id=$custom_root_node_id}
{/if}

<script type="text/javascript">
<!--
    var path = [{foreach $module_result.path as $element}{$element.node_id}{delimiter}, {/delimiter}{/foreach}];
    var autoOpenPath = path;

    var treeMenu = new ContentStructureMenu();

{cache-block expiry="0"}
{def $root_node=fetch('content','node',hash('node_id',$root_node_id))}
    var rootNode = {ldelim}{*
        *}"node_id":{$root_node.node_id},{*
        *}"object_id":{$root_node.object.id},{*
        *}"class_id":{$root_node.object.contentclass_id},{*
        *}"has_children":{if $root_node.children_count}true{else}false{/if},{*
        *}"name":"{$root_node.name|wash(javascript)}",{*
        *}"url":{$root_node.url|ezurl},{*
        *}"modified_subnode":{$root_node.modified_subnode},{*
        *}"languages":["{$root_node.object.language_codes|implode('", "')}"],{*
        *}"class_list":[{foreach fetch('content','can_instantiate_class_list',hash('parent_node',$child)) as $class}{$class.id}{delimiter},{/delimiter}{/foreach}]{rdelim};

    document.writeln( '<ul id="content_tree_menu">' );
    document.writeln( treeMenu.generateEntry( rootNode, false ) );
    document.writeln( '</ul>' );

    treeMenu.load( false, {$root_node.node_id}, {$root_node.modified_subnode} );
{/cache-block}
// -->
</script>
