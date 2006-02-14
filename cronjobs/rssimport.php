<?php
//
// Created on: <24-Sep-2003 16:09:21 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.7.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file rssimport.php
*/

include_once( 'kernel/classes/ezrssimport.php' );
include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezpersistentobject.php' );
include_once( 'lib/ezxml/classes/ezxml.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
include_once( 'kernel/classes/ezcontentobjectversion.php' );
include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
include_once( "lib/ezdb/classes/ezdb.php" );

//For ezUser, we would make this the ezUser class id but otherwise just pick and choose.

//fetch this class
$rssImportArray = eZRSSImport::fetchActiveList();

// Loop through all configured and active rss imports. If something goes wrong while processing them, continue to next import
foreach ( array_keys( $rssImportArray ) as $rssImportKey )
{
    // Get RSSImport object
    $rssImport =& $rssImportArray[$rssImportKey];
    $rssSource =& $rssImport->attribute( 'url' );
    $addCount = 0;

    if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Starting.' );
    }

    // Open and read RSSImport url
    $fid = fopen( $rssSource, 'r' );
    if ( $fid === false )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Failed to open RSS feed file: '.$rssSource );
        }
        continue;
    }

    $xmlData = "";
    do {
        $data = fread($fid, 8192);
        if (strlen($data) == 0) {
            break;
        }
        $xmlData .= $data;
    } while(true);

    fclose( $fid );

    // Create DomDocumnt from http data
    $xmlObject = new eZXML();
    $domDocument =& $xmlObject->domTree( $xmlData );

    if ( $domDocument == null or $domDocument === false )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Invalid RSS document.' );
        }
        continue;
    }

    $root =& $domDocument->root();

    if ( $root == null )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Invalid RSS document.' );
        }
        continue;
    }

    switch( $root->attributeValue( 'version' ) )
    {
        default:
        case '1.0':
        {
            rssImport1( $root, $rssImport, $cli );
        } break;

        case '0.91':
        case '0.92':
        case '2.0':
        {
            rssImport2( $root, $rssImport, $cli );
        } break;
    }

}

include_once( 'kernel/classes/ezstaticcache.php' );
eZStaticCache::executeActions();

/*!
  Parse RSS 1.0 feed

  \param DOM root node
  \param RSS Import item
  \param cli
*/
function rssImport1( &$root, &$rssImport, &$cli )
{
    global $isQuiet;

    $addCount = 0;

    // Get all items in rss feed
    $itemArray = $root->elementsByName( 'item' );

    // Loop through all items in RSS feed
    foreach ( $itemArray as $item )
    {
        $addCount += importRSSItem( $item, $rssImport, $cli );
    }

    if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': End. '.$addCount.' objects added' );
    }

}

/*!
  Parse RSS 2.0 feed

  \param DOM root node
  \param RSS Import item
  \param cli
*/
function rssImport2( &$root, &$rssImport, &$cli )
{
    global $isQuiet;

    $addCount = 0;

    // Get all items in rss feed
    $channel =& $root->elementByName( 'channel' );

    // Loop through all items in RSS feed
    foreach ( $channel->elementsByName( 'item' ) as $item )
    {
        $addCount += importRSSItem( $item, $rssImport, $cli );
    }

    if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': End. '.$addCount.' objects added' );
    }

}

/*!
 Import specifiec rss item into content tree

 \param RSS item xml element
 \param $rssImport Object
 \param cli

 \return 1 if object added, 0 if not
*/
function importRSSItem( $item, &$rssImport, &$cli )
{
    global $isQuiet;
    $rssImportID =& $rssImport->attribute( 'id' );
    $rssOwnerID =& $rssImport->attribute( 'object_owner_id' ); // Get owner user id
    $parentContentObjectTreeNode = eZContentObjectTreeNode::fetch( $rssImport->attribute( 'destination_node_id' ) ); // Get parent treenode object

    if ( $parentContentObjectTreeNode == null )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Destination tree node seems to be unavailable' );
        }
        return 0;
    }

    $parentContentObject =& $parentContentObjectTreeNode->attribute( 'object' ); // Get parent content object

    $title = $item->elementByName( 'title' );
    $link = $item->elementByName( 'link' );
    $description = $item->elementByName( 'description' );

    $md5Sum = md5( $link->textContent() );

    // Try to fetch RSSImport object with md5 sum matching link.
    $existingObject = eZPersistentObject::fetchObject( eZContentObject::definition(), null,
                                                       array( 'remote_id' => 'RSSImport_'.$rssImportID.'_'.$md5Sum ) );

    // if object exists, continue to next import item
    if ( $existingObject != null )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Object ( ' . $existingObject->attribute( 'id' ) . ' ) with URL: '.$link->textContent().' already exists' );
        }
        unset( $existingObject ); // delete object to preserve memory
        return 0;
    }

    // Fetch class, and create ezcontentobject from it.
    $contentClass = eZContentClass::fetch( $rssImport->attribute( 'class_id' )  );

    // Instantiate the object with user $rssOwnerID and use section id from parent. And store it.
    $contentObject =& $contentClass->instantiate( $rssOwnerID, $parentContentObject->attribute( 'section_id' ) );

    $db =& eZDB::instance();
    $db->begin();
    $contentObject->store();

    // Create node assignment
    $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
                                                        'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                        'is_main' => 1,
                                                        'parent_node' => $parentContentObjectTreeNode->attribute( 'node_id' ) ) );
    $nodeAssignment->store();

    $version =& $contentObject->version( 1 );
    $version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
    $version->store();

    // Get object attributes, and set their values and store them.
    $dataMap =& $contentObject->dataMap();

    // set title
    $attributeTitle =& $dataMap[$rssImport->attribute( 'class_title' )];
    if ( $attributeTitle != null && $title != null )
    {
        if ( $attributeTitle->attribute( 'data_type_string' ) == 'ezxmltext' )
        {
            setEZXMLAttribute( $attributeTitle, $title->textContent() );
        }
        else
        {
            $attributeTitle->setAttribute( 'data_text', $title->textContent() );
        }
        $attributeTitle->store();
    }
    else if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Could not find title map for : '.$rssImport->attribute( 'class_title' ) );
    }

    // set url
    $attributeLink =& $dataMap[$rssImport->attribute( 'class_url' )];
    if ( $attributeLink != null && $link != null )
    {
        $dataType = $attributeLink->attribute( 'data_type_string' );
        if ( $dataType == 'ezxmltext' )
        {
            setEZXMLAttribute( $attributeLink, $link->textContent() );
        }
        elseif ( $dataType == 'ezurl' )
        {
            $attributeLink->setContent( $link->textContent() );
        }
        else
        {
            $attributeLink->setAttribute( 'data_text', $link->textContent() );
        }

        $attributeLink->store();
        unset( $dataType );
    }
    else if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Could not find link map for : '.$rssImport->attribute( 'class_link' ) );
    }

    // set description
    $attributeDescription =& $dataMap[$rssImport->attribute( 'class_description' )];
    if ( $attributeDescription != null && $description != null )
    {
        if ( $attributeDescription->attribute( 'data_type_string' ) == 'ezxmltext' )
        {
            setEZXMLAttribute( $attributeDescription, $description->textContent() );
        }
        else
        {
            $attributeDescription->setAttribute( 'data_text', $description->textContent() );
        }
        $attributeDescription->store();
    }
    else if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Could not find description map for : '.$rssImport->attribute( 'class_descriptione' ) );
    }

    $contentObject->setAttribute( 'remote_id', 'RSSImport_'.$rssImportID.'_'.md5( $link->textContent() ) );
    $contentObject->store();
    $db->commit();

    //publish new object
    $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObject->attribute( 'id' ),
                                                                                 'version' => 1 ) );
    if ( !isset( $operationResult['status'] ) || $operationResult['status'] != EZ_MODULE_OPERATION_CONTINUE )
    {
        if ( isset( $operationResult['result'] ) && isset( $operationResult['result']['content'] ) )
            $failReason = $operationResult['result']['content'];
        else
            $failReason = "unknown error";
        $cli->error( "Publishing failed: $failReason" );
        unset( $failReason );
    }

    if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Object created; '.$title->textContent() );
    }

    return 1;
}

function setEZXMLAttribute( &$attribute, &$attributeValue, $link = false )
{
    include_once( "kernel/classes/datatypes/ezxmltext/handlers/input/ezsimplifiedxmlinput.php" );
    $inputData = "<?xml version=\"1.0\"?>";
    $inputData .= "<section>";
    $inputData .= "<paragraph>";
    $inputData .= $attributeValue;
    $inputData .= "</paragraph>";
    $inputData .= "</section>";

    $dumpdata = "";
    $simplifiedXMLInput = new eZSimplifiedXMLInput( $dumpdata, null, null );
    $inputData = $simplifiedXMLInput->convertInput( $inputData );

    $domString = eZXMLTextType::domString( $inputData[0] );

    $domString = str_replace( "<paragraph> </paragraph>", "", $domString );
    $domString = str_replace ( "<paragraph />" , "", $domString );
    $domString = str_replace ( "<line />" , "", $domString );
    $domString = str_replace ( "<paragraph></paragraph>" , "", $domString );
    $domString = str_replace( "<paragraph>&nbsp;</paragraph>", "", $domString );
    $domString = str_replace( "<paragraph></paragraph>", "", $domString );

    $domString = preg_replace( "#[\n]+#", "", $domString );
    $domString = preg_replace( "#&lt;/line&gt;#", "\n", $domString );
    $domString = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $domString );

    $xml = new eZXML();
    $tmpDom =& $xml->domTree( $domString, array( 'CharsetConversion' => false ) );
    $description = eZXMLTextType::domString( $tmpDom );

    $attribute->setAttribute( 'data_text', $description );
    $attribute->store();
}

?>
