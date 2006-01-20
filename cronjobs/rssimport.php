<?php
//
// Created on: <24-Sep-2003 16:09:21 sp>
//
// Copyright (C) 1999-2006 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
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
            $version = '1.0';
        } break;

        case '0.91':
        case '0.92':
        case '2.0':
        {
            $version = $root->attributeValue( 'version' );
        } break;
    }

    $importDescription = $rssImport->importDescription();
    if ( $version != $importDescription['rss_version'] )
    {
        if ( !$isQuiet )
        {
            $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Invalid RSS version missmatch. Please reconfigure import.' );
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
    $channel = $root->elementByName( 'channel' );

    // Loop through all items in RSS feed
    foreach ( $itemArray as $item )
    {
        $addCount += importRSSItem( $item, $rssImport, $cli, $channel );
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
        $addCount += importRSSItem( $item, $rssImport, $cli, $channel );
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
 \param channel

 \return 1 if object added, 0 if not
*/
function importRSSItem( $item, &$rssImport, &$cli, $channel )
{
    global $isQuiet;
    $rssImportID = $rssImport->attribute( 'id' );
    $rssOwnerID = $rssImport->attribute( 'object_owner_id' ); // Get owner user id
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

    $title = $item->elementTextContentByName( 'title' );
    $link = $item->elementByName( 'link' );
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
    $contentObjectID = $contentObject->attribute( 'id' );

    // Create node assignment
    $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObjectID,
                                                       'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                       'is_main' => 1,
                                                       'parent_node' => $parentContentObjectTreeNode->attribute( 'node_id' ) ) );
    $nodeAssignment->store();

    $version =& $contentObject->version( 1 );
    $version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
    $version->store();

    // Get object attributes, and set their values and store them.
    $dataMap =& $contentObject->dataMap();
    $importDescription = $rssImport->importDescription();

    // Set content object attribute values.
    $classAttributeList = $contentClass->fetchAttributes();
    foreach( $classAttributeList as $classAttribute )
    {
        $classAttributeID = $classAttribute->attribute( 'id' );
        if ( isset( $importDescription['class_attributes'][$classAttributeID] ) )
        {
            if ( $importDescription['class_attributes'][$classAttributeID] == '-1' )
            {
                continue;
            }

            $importDescriptionArray = explode( ' - ', $importDescription['class_attributes'][$classAttributeID] );
            if ( count( $importDescriptionArray ) < 1 )
            {
                $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Invalid import definition. Please redit.' );
                break;
            }

            $elementType = $importDescriptionArray[0];
            array_shift( $importDescriptionArray );
            switch( $elementType )
            {
                case 'item':
                {
                    setObjectAttributeValue( $dataMap[$classAttribute->attribute( 'identifier' )],
                                             recursiveFindRSSElementValue( $importDescriptionArray,
                                                                           $item ) );
                } break;

                case 'channel':
                {
                    setObjectAttributeValue( $dataMap[$classAttribute->attribute( 'identifier' )],
                                             recursiveFindRSSElementValue( $importDescriptionArray,
                                                                           $channel ) );
                } break;
            }
        }
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

    $db->begin();
    unset( $contentObject );
    unset( $version );
    $contentObject = eZContentObject::fetch( $contentObjectID );
    $version = $contentObject->attribute( 'current' );
    // Set object Attributes like modified and published timestamps
    $objectAttributeDescription = $importDescription['object_attributes'];
    foreach( $objectAttributeDescription as $identifier => $objectAttributeDefinition )
    {
        if ( $objectAttributeDefinition == '-1' )
        {
            continue;
        }

        $importDescriptionArray = explode( ' - ', $objectAttributeDefinition );

        $elementType = $importDescriptionArray[0];
        array_shift( $importDescriptionArray );
        switch( $elementType )
        {
            default:
            case 'item':
            {
                $domNode = $item;
            } break;

            case 'channel':
            {
                $domNode = $channel;
            } break;
        }

        switch( $identifier )
        {
            case 'modified':
            {
                $dateTime = recursiveFindRSSElementValue( $importDescriptionArray,
                                                          $domNode );
                if ( !$dateTime )
                {
                    break;
                }
                $contentObject->setAttribute( $identifier, strtotime( $dateTime ) );
                $version->setAttribute( $identifier, strtotime( $dateTime ) );
            } break;

            case 'published':
            {
                $dateTime = recursiveFindRSSElementValue( $importDescriptionArray,
                                                          $domNode );
                if ( !$dateTime )
                {
                    break;
                }
                $contentObject->setAttribute( $identifier, strtotime( $dateTime ) );
                $version->setAttribute( 'created', strtotime( $dateTime ) );
            } break;
        }
    }
    $version->store();
    $contentObject->store();
    $db->commit();

    if ( !$isQuiet )
    {
        $cli->output( 'RSSImport '.$rssImport->attribute( 'name' ).': Object created; ' . $title );
    }

    return 1;
}

function recursiveFindRSSElementValue( $importDescriptionArray, $xmlDomNode )
{
    if ( !is_array( $importDescriptionArray ) )
    {
        return false;
    }

    $valueType = $importDescriptionArray[0];
    array_shift( $importDescriptionArray );
    switch( $valueType )
    {
        case 'elements':
        {
            if ( count( $importDescriptionArray ) == 1 )
            {
                return $xmlDomNode->elementTextContentByName( $importDescriptionArray[0] );
            }
            else
            {
                $elementName = $importDescriptionArray[0];
                array_shift( $importDescriptionArray );
                return recursiveFindRSSElementValue( $importDescriptionArray, $xmlDomNode->elementByName( $elementName ) );
            }
        }

        case 'attributes':
        {
            return $xmlDomNode->attributeValue( $importDescriptionArray[0] );
        } break;
    }
}

function setObjectAttributeValue( &$objectAttribute, $value )
{
    if ( $value === false )
    {
        return;
    }

    $dataType = $objectAttribute->attribute( 'data_type_string' );
    if ( $dataType == 'ezxmltext' )
    {
        setEZXMLAttribute( $objectAttribute, $value );
    }
    elseif ( $dataType == 'ezurl' )
    {
        $objectAttribute->setContent( $value );
    }
    else
    {
        $objectAttribute->setAttribute( 'data_text', $value );
    }

    $objectAttribute->store();
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
