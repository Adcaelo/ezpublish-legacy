<?php
//
// eZSetup - init part initialization
//
// Created on: <18-Sep-2003 14:49:54 kk>
//
// Copyright (C) 1999-2003 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

$Module =& $Params["Module"];

include_once( 'kernel/rss/edit_functions.php' );
include_once( "kernel/common/template.php" );
include_once( 'kernel/classes/ezrssexport.php' );
include_once( 'kernel/classes/ezrssexportitem.php' );
include_once( 'lib/ezutils/classes/ezhttppersistence.php' );

$http =& eZHTTPTool::instance();

if ( isset( $Params['RSSExportID'] ) )
    $RSSExportID = $Params['RSSExportID'];
else
    $RSSExportID = false;

if ( $http->hasPostVariable( 'RSSExport_ID' ) )
    $RSSExportID = $http->postVariable( 'RSSExport_ID' );


if ( $Module->isCurrentAction( 'Store' ) )
{
    return storeRSSExport( $Module, $http, true );
}
else if ( $Module->isCurrentAction( 'UpdateItem' ) )
{
    storeRSSExport( $Module, $http );
}
else if ( $Module->isCurrentAction( 'AddItem' ) )
{
    $rssExportItem = eZRSSExportItem::create( $RSSExportID );
    $rssExportItem->store();
    storeRSSExport( $Module, $http );
}
else if ( $Module->isCurrentAction( 'Remove' ) )
{
    $rssExport =& eZRSSExport::fetch( $RSSExportID );
    $rssExport->remove();
    return $Module->run( 'list', array() );
}

if ( $http->hasPostVariable( 'Item_Count' ) )
{
    for ( $itemCount = 0; $itemCount < $http->postVariable( 'Item_Count' ); $itemCount++ )
    {
        if ( $http->hasPostVariable( 'SourceBrowse_'.$itemCount ) )
        {
            storeRSSExport( $Module, $http );
            include_once( 'kernel/classes/ezcontentbrowse.php' );
            eZContentBrowse::browse( array( 'action_name' => 'AssignSection',
                                            'description_template' => 'design:rss/browse_source.tpl',
                                            'from_page' => '/rss/edit_export/'.$RSSExportID.'/'.$http->postVariable( 'Item_ID_'.$itemCount ) ),
                                     $Module );
            break;
        }
    }
}

if ( is_numeric( $RSSExportID ) )
{
    $rssExport =& eZRSSExport::fetch( $RSSExportID );
    $rssExportID = $RSSExportID;

    if ( isset( $Params['RSSExportItemID'] ) && $http->hasPostVariable( 'SelectedNodeIDArray' ) )
    {
        include_once( 'kernel/classes/ezcontentbrowse.php' );
        $nodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
        if ( isset( $nodeIDArray ) )
        {
            $rssExportItem = eZRSSExportItem::fetch( $Params['RSSExportItemID'] );
            $rssExportItem->setAttribute( 'source_node_id', $nodeIDArray[0] );
            $rssExportItem->store();
        }
    }
}
else // New RSSExport
{
    include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
    $user =& eZUser::currentUser();
    $user_id = $user->attribute( "contentobject_id" );

    // Create default rssExport object to use
    $rssExport =& eZRSSExport::create( $user_id );
    $rssExport->store();
    $rssExportID = $rssExport->attribute( 'id' );

    // Create Obne empty export item
    $rssExportItem = eZRSSExportItem::create( $rssExportID );
    $rssExportItem->store();
}

$tpl =& templateInit();

// Populate site access list
$config =& eZINI::instance( 'site.ini' );
$siteAccess = $config->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );

// Get Classes and class attributes
$classArray =& eZContentClass::fetchList();

$tpl->setVariable( 'rss_site_access', $siteAccess );
$tpl->setVariable( 'rss_class_array', $classArray );
$tpl->setVariable( 'rss_export', $rssExport );
$tpl->setVariable( 'rss_export_id', $rssExportID );

$Result = array();
$Result['content'] =& $tpl->fetch( "design:rss/edit_export.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );


?>
