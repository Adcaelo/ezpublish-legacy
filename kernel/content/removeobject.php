<?php
//
//
// Created on: <08-Nov-2002 16:02:26 wy>
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

/*! \file removeobject.php
*/
include_once( "kernel/classes/ezcontentobject.php" );
include_once( "kernel/classes/ezcontentobjecttreenode.php" );
include_once( "kernel/classes/ezcontentcache.php" );

include_once( "lib/ezutils/classes/ezhttptool.php" );

include_once( "kernel/common/template.php" );
include_once( 'kernel/common/i18n.php' );

$Module =& $Params["Module"];

$http =& eZHTTPTool::instance();

$viewMode = $http->sessionVariable( "CurrentViewMode" );
$deleteIDArray = $http->sessionVariable( "DeleteIDArray" );
if ( count( $deleteIDArray ) <= 0 )
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );

if ( array_key_exists( 'Limitation', $Params ) )
{
    $Limitation =& $Params['Limitation'];
    foreach ( $Limitation as $policy )
    {
        $limitationList[] = $policy->attribute( 'limitations' );
    }
}

$contentObjectID = $http->sessionVariable( 'ContentObjectID' );
$contentNodeID = $http->sessionVariable( 'ContentNodeID' );

$deleteResult = array();
$ChildObjectsCount = 0;
foreach ( $deleteIDArray as $deleteID )
{
    $node =& eZContentObjectTreeNode::fetch( $deleteID );
    if ( $node != null )
    {
        $object = $node->attribute( 'object' );
        $NodeName = $object->attribute( 'name' );
        $contentObject = $node->attribute( 'object' );
        $nodeID = $node->attribute( 'node_id' );
        $additionalWarning = '';
        if ( $node->attribute( 'main_node_id' ) == $nodeID )
        {
            $allAssignedNodes =& $object->attribute( 'assigned_nodes' );
            if ( count( $allAssignedNodes ) > 1 )
            {
                $additionalWarning = ezi18n( 'kernel/content/removeobject',
                                             'And also it will remove the nodes:' ) . ' ';
                $additionalNodeIDList = array();
                foreach( array_keys( $allAssignedNodes ) as $key )
                {
                    $assignedNode =& $allAssignedNodes[$key];
                    $assignedNodeID = $assignedNode->attribute( 'node_id' );
                    if ( $assignedNodeID != $nodeID )
                    {
                        $additionalNode =& eZContentObjectTreeNode::fetch( $assignedNodeID );
                        $additionalChildrenCount = $additionalNode->subTreeCount( array( 'MainNodeOnly' => true ) ) . " ";
                        if (  $additionalChildrenCount == 0 )
                            $additionalNodeIDList[] = $assignedNodeID;
                        else if (  $additionalChildrenCount == 1 )
                            $additionalNodeIDList[] = $assignedNodeID . " and its 1 child";
                        else
                            $additionalNodeIDList[] = $assignedNodeID . " and its " . $additionalChildrenCount . " children";
                    }
                }
                $additionalWarning .= implode( ', ',  $additionalNodeIDList );
            }
        }

        if ( !$object->attribute( 'can_remove' ) )
            return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
        if ( $object === null )
            return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
        $ChildObjectsCount = $node->subTreeCount( array( 'MainNodeOnly' => true ) ) . " ";
        if ( $ChildObjectsCount == 1 )
            $ChildObjectsCount .= ezi18n( 'kernel/content/removeobject',
                                          'child',
                                          '1 child' );
        else
            $ChildObjectsCount .= ezi18n( 'kernel/content/removeobject',
                                          'children',
                                          'several children' );
        $item = array( "nodeName" => $NodeName,
                       "childCount" => $ChildObjectsCount,
                       "additionalWarning" => $additionalWarning );
        $deleteResult[] = $item;
    }
}

if ( $http->hasPostVariable( "ConfirmButton" ) )
{
    foreach ( $deleteIDArray as $deleteID )
    {
        $node =& eZContentObjectTreeNode::fetch( $deleteID );
        $object = $node->attribute( 'object' );
        if ( $node != null )
        {
            $parentNodeID = $node->attribute( 'parent_node_id' );
            $nodeList = array();
            $nodeList[] = $node->attribute( 'node_id' );
            $nodeList[] = $node->attribute( 'parent_node_id' );

            if ( eZContentCache::cleanup( $nodeList ) )
            {
                eZDebug::writeDebug( 'cache cleaned up', 'content, remove' );
            }

            if ( $node->attribute( 'main_node_id' ) == $deleteID )
            {
                $object = $node->attribute( 'object' );
                $allAssignedNodes =& $object->attribute( 'assigned_nodes' );
                foreach( array_keys( $allAssignedNodes ) as $key )
                {
                    $assignedNode =& $allAssignedNodes[$key];
                    $children =& $assignedNode->subTree();
                    foreach ( array_keys( $children ) as $childKey )
                    {
                        $child =& $children[$childKey];
                        if( $child->attribute( 'node_id' ) == $child->attribute( 'main_node_id' ) )
                        {
                            $childObject =& $child->attribute( 'object' );
                            $childNodeID = $child->attribute( 'node_id' );
                            $childObject->remove( true, $childNodeID );
                        }
                        else
                        {
                            $child->remove();
                        }
                    }
                }
                $object->remove( true, $deleteID );
            }
            else
            {
                $children =& $node->subTree();
                foreach ( array_keys( $children ) as $childKey )
                {
                    $child =& $children[$childKey];
                    if( $child->attribute( 'node_id' ) == $child->attribute( 'main_node_id' ) )
                    {
                        $childObject =& $child->attribute( 'object' );
                        $childNodeID = $child->attribute( 'node_id' );
                        $childObject->remove( true, $childNodeID );
                    }
                    else
                    {
                        $child->remove();
                    }
                }
                $node->remove();
            }
        }
    }
    $Module->redirectTo( '/content/view/' . $viewMode . '/' . $contentNodeID . '/'  );
}

if ( $http->hasPostVariable( "CancelButton" ) )
{
    $Module->redirectTo( '/content/view/' . $viewMode . '/' . $contentNodeID . '/'  );
}
$Module->setTitle( ezi18n( 'kernel/content', 'Remove' ) . ' ' . $NodeName );

$tpl =& templateInit();

$tpl->setVariable( "module", $Module );
//$tpl->setVariable( "NodeID", $NodeID );
//$tpl->setVariable( "NodeName", $NodeName );
$tpl->setVariable( "ChildObjectsCount", $ChildObjectsCount );
$tpl->setVariable( "DeleteResult",  $deleteResult );
$Result = array();
$Result['content'] =& $tpl->fetch( "design:node/removeobject.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/content', 'Remove object' ) ) );
?>
