<?php
//
// Created on: <17-Apr-2002 10:34:48 bf>
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
// http://ez.no/home/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentclassattribute.php' );

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjectversion.php' );
include_once( 'kernel/classes/ezcontentobjectattribute.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

include_once( 'lib/ezutils/classes/ezhttptool.php' );

include_once( 'kernel/common/template.php' );

function checkNodeCorrectness( &$module, $objectID, $editVersion, $editLanguage )
{
    $object =& eZContentObject::fetch( $objectID );
    if ( $object === null )
        return;
    if ( $object->attribute( 'main_node_id' ) == 0 )
    {
//        $module->setViewResult( $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' ) );
//        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }
}

function checkNodeAssignments( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage )
{
    $http =& eZHTTPTool::instance();
    $ObjectID = $object->attribute( 'id' );
    // Assign to nodes
    if ( $module->isCurrentAction( 'AddNodeAssignment' ) )
    {
        $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
        $assignedNodes =& $version->nodeAssignments();
        $assignedIDArray =array();
        foreach ( $assignedNodes as  $assignedNode )
        {
            $assignedNodeID = $assignedNode->attribute( 'parent_node' );
            $assignedIDArray[] = $assignedNodeID;
        }

        foreach ( $selectedNodeIDArray as $nodeID )
        {
            if ( !in_array( $nodeID, $assignedIDArray ) )
            {
                $version->assignToNode( $nodeID );
            }
        }
    }
}

function checkNodeMovements( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage )
{
    $http =& eZHTTPTool::instance();
    $ObjectID = $object->attribute( 'id' );
    // Move to another node
    if ( $module->isCurrentAction( 'MoveNodeAssignment' ) )
    {
        $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
        $fromNodeID = $http->sessionVariable( "FromNodeID" );
        $oldAssignmentParentID = $http->sessionVariable( 'OldAssignmentParentID' );

        if ( $selectedNodeIDArray != null )
        {
            $assignedNodes =& $version->nodeAssignments();
            $assignedIDArray =array();
            foreach ( $assignedNodes as  $assignedNode )
            {
                $assignedNodeID = $assignedNode->attribute( 'parent_node' );
                $assignedIDArray[] = $assignedNodeID;
            }
            foreach ( $selectedNodeIDArray as $nodeID )
            {
                if ( !in_array( $nodeID, $assignedIDArray ) )
                {
                    $oldAssignment =& eZPersistentObject::fetchObject( eZNodeAssignment::definition(),
                                                                       null,
                                                                       array( 'contentobject_id' => $object->attribute( 'id' ),
                                                                              'parent_node' => $oldAssignmentParentID,
                                                                              'contentobject_version' => $version->attribute( 'version' )
                                                                              ),
                                                                       true );
//                    var_dump( $oldAssignment );
                    $originalNode =& eZContentObjectTreeNode::fetchNode( $originalObjectID, $fromNodeID );

                    $realNode = & eZContentObjectTreeNode::fetchNode( $version->attribute( 'contentobject_id' ), $oldAssignment->attribute( 'parent_node' ) );
                    
                    if ( is_null( $realNode ) )
                    {
                        $fromNodeID = 0;
                    }
                    if ( $oldAssignment->attribute( 'is_main' ) == '1' )
                    {
                        $version->assignToNode( $nodeID, 1, $fromNodeID );
                    }
                    else
                    {
                        $version->assignToNode( $nodeID, 0, $fromNodeID );
                    }

                    $version->removeAssignment( $oldAssignmentParentID );
                }
            }
        }
    }
}

function storeNodeAssignments( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage )
{
    $http =& eZHTTPTool::instance();
    $mainNodeID = $http->postVariable( 'MainNodeID' );
    $nodesID = array();
    if ( $http->hasPostVariable( 'NodesID' ) )
        $nodesID = $http->postVariable( 'NodesID' );

    $nodeID = eZContentObjectTreeNode::findNode( $mainNodeID, $object->attribute('id') );
    eZDebug::writeNotice( $nodeID, 'nodeID' );
//    $object->setAttribute( 'main_node_id', $nodeID );
    $nodeAssignments =& eZNodeAssignment::fetchForObject( $object->attribute( 'id' ), $version->attribute( 'version' ) ) ;
    eZDebug::writeNotice( $mainNodeID, "mainNodeID" );

    $setPlacementNodeIDArray = array();
    if ( $http->hasPostVariable( 'SetPlacementNodeIDArray' ) )
        $setPlacementNodeIDArray = $http->postVariable( 'SetPlacementNodeIDArray' );

    $setPlacementNodeIDArray = array_unique( $setPlacementNodeIDArray );
    eZDebug::writeDebug( $setPlacementNodeIDArray, '$setPlacementNodeIDArray' );
    $remoteIDFieldMap = array();
    if ( $http->hasPostVariable( 'SetRemoteIDFieldMap' ) )
        $remoteIDFieldMap = $http->postVariable( 'SetRemoteIDFieldMap' );
    $remoteIDOrderMap = array();
    if ( $http->hasPostVariable( 'SetRemoteIDOrderMap' ) )
        $remoteIDOrderMap = $http->postVariable( 'SetRemoteIDOrderMap' );
    if ( count( $setPlacementNodeIDArray ) > 0 )
    {
        foreach ( $setPlacementNodeIDArray as $setPlacementRemoteID => $setPlacementNodeID )
        {
            $hasAssignment = false;
            foreach ( array_keys( $nodeAssignments ) as $key )
            {
                $nodeAssignment =& $nodeAssignments[$key];
                if ( $nodeAssignment->attribute( 'remote_id' ) == $setPlacementRemoteID )
                {
                    eZDebug::writeDebug( "Remote ID $setPlacementRemoteID already in use for node " . $nodeAssignment->attribute( 'parent_node' ), 'node_edit' );
                    if ( isset( $remoteIDFieldMap[$setPlacementRemoteID] ) )
                        $nodeAssignment->setAttribute( 'sort_field',  $remoteIDFieldMap[$setPlacementRemoteID] );
                    if ( isset( $remoteIDOrderMap[$setPlacementRemoteID] ) )
                        $nodeAssignment->setAttribute( 'sort_order', $remoteIDOrderMap[$setPlacementRemoteID] );
                    $nodeAssignment->setAttribute( 'parent_node', $setPlacementNodeID );
                    $nodeAssignment->sync();
                    $hasAssignment = true;
                    break;
                }
            }
            if ( !$hasAssignment )
            {
                eZDebug::writeDebug( "Adding to node $setPlacementNodeID", 'node_edit' );
                $sortField = null;
                $sortOrder = null;
                if ( isset( $remoteIDFieldMap[$setPlacementRemoteID] ) )
                    $sortField = $remoteIDFieldMap[$setPlacementRemoteID];
                if ( isset( $remoteIDOrderMap[$setPlacementRemoteID] ) )
                    $sortOrder = $remoteIDOrderMap[$setPlacementRemoteID];
                $version->assignToNode( $setPlacementNodeID, 0, 0, $sortField, $sortOrder, $setPlacementRemoteID );
            }
        }
        $nodeAssignments =& eZNodeAssignment::fetchForObject( $object->attribute( 'id' ), $version->attribute( 'version' ) );
    }

    $sortOrderMap = false;
    if ( $http->hasPostVariable( 'SortOrderMap' ) )
        $sortOrderMap = $http->postVariable( 'SortOrderMap' );
    $sortFieldMap = false;
    if ( $http->hasPostVariable( 'SortFieldMap' ) )
        $sortFieldMap = $http->postVariable( 'SortFieldMap' );

//     $assigedNodes =& eZContentObjectTreeNode::fetchByContentObjectID( $object->attribute('id') );
    foreach ( array_keys( $nodeAssignments ) as $key )
    {
        $nodeAssignment =& $nodeAssignments[$key];
        eZDebug::writeNotice( $nodeAssignment, "nodeAssignment" );
        if ( $sortFieldMap !== false )
        {
            if ( isset( $sortFieldMap[$nodeAssignment->attribute( 'id' )] ) )
                $nodeAssignment->setAttribute( 'sort_field', $sortFieldMap[$nodeAssignment->attribute( 'id' )] );
        }

        if ( $sortOrderMap !== false )
        {
            $sortOrder = 1;
            if ( isset( $sortOrderMap[$nodeAssignment->attribute( 'id' )] ) and
                 $sortOrderMap[$nodeAssignment->attribute( 'id' )] == 1 )
                $sortOrder = $sortOrderMap[$nodeAssignment->attribute( 'id' )];
            else
                $sortOrder = 0;

            $nodeAssignment->setAttribute( 'sort_order', $sortOrder );
        }


        if ( $nodeAssignment->attribute( 'is_main' ) == 1 and
             $nodeAssignment->attribute( 'parent_node' ) != $mainNodeID )
        {
            $nodeAssignment->setAttribute( 'is_main', 0 );
        }
        else if ( $nodeAssignment->attribute( 'is_main' ) == 0 and
                  $nodeAssignment->attribute( 'parent_node' ) == $mainNodeID )
        {
            $nodeAssignment->setAttribute( 'is_main', 1 );
        }
        $nodeAssignment->store();
    }
//    $version->setAttribute( 'parent_node', $mainNodeID );
//    $version->store();
//         $object->store();

//    $node = eZContentObjectTreeNode::fetch( $nodeID );

//    $node->setAttribute( 'path_identification_string', $node->pathWithNames() );
//    $node->setAttribute( 'crc32_path', crc32 ( $node->attribute( 'path_identification_string' ) ) );
//    eZDebug::writeNotice( $node->attribute( 'path_identification_string' ), 'path_identification_string' );
//    eZDebug::writeNotice( $node->attribute( 'crc32_path' ), 'CRC32' );

//    $node->store();
}

function checkNodeActions( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage )
{
    $http =& eZHTTPTool::instance();



    if ( $module->isCurrentAction( 'ConfirmAssignmentDelete' ) && $http->hasPostVariable( 'RemoveNodeID' ) )
    {

        $nodeID = $http->postVariable( 'RemoveNodeID' ) ;
//        $mainNodeID = $http->postVariable( 'MainNodeID' );

//        if ( $nodeID != $mainNodeID )
//        {
        $version->removeAssignment( $nodeID );
//        }
    }

    if ( $module->isCurrentAction( 'BrowseForNodes' ) )
    {
        $objectID = $object->attribute( 'id' );
//         $http->setSessionVariable( 'BrowseFromPage', "/content/edit/$objectID/$editVersion/" );
        $http->setSessionVariable( 'BrowseFromPage', $module->redirectionURI( 'content', 'edit', array( $objectID, $editVersion, $editLanguage ) ) );
        $http->setSessionVariable( 'BrowseActionName', 'AddNodeAssignment' );
        $http->setSessionVariable( 'BrowseReturnType', 'NodeID' );
        $http->setSessionVariable( 'BrowseSelectionType', 'Multiple' );
        $mainParentID = $version->attribute( 'main_parent_node_id' );
        $node = eZContentObjectTreeNode::fetch( $mainParentID );
        $nodePath =  $node->attribute( 'path' );
        $rootNodeForObject = $nodePath[0];
        if ( $rootNodeForObject != null )
        {
            $nodeID = $rootNodeForObject->attribute( 'node_id' );
        }else
        {
            $nodeID = $mainParentID;
        }
        $module->redirectToView( 'browse', array( $nodeID, $objectID, $editVersion ) );
        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }
    if ( $module->isCurrentAction( 'DeleteNode' ) )
    {

        if ( $http->hasPostVariable( 'RemoveNodeID' ) )
        {
            $nodeID = $http->postVariable( 'RemoveNodeID' );
        }

        $mainNodeID = $http->postVariable( 'MainNodeID' );

        if ( $nodeID != $mainNodeID )
        {
            $objectID = $object->attribute( 'id' );
            $publishedNode =& eZContentObjectTreeNode::fetchNode( $objectID, $nodeID );
            if ( $publishedNode != null )
            {
                $childrenCount =& $publishedNode->childrenCount();
                if ( $childrenCount != 0 )
                {
                    $module->redirectToView( 'removenode', array( $objectID, $editVersion, $editLanguage, $nodeID ) );
                    return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
                }
                else
                {
                    $version->removeAssignment( $nodeID );

                }
            }else
            {
                $nodeAssignment =& eZNodeAssignment::fetch( $objectID, $version->attribute( 'version' ), $nodeID );
                if ( $nodeAssignment->attribute( 'from_node_id' ) != 0 )
                {
                    $publishedNode =& eZContentObjectTreeNode::fetchNode( $objectID, $nodeAssignment->attribute( 'from_node_id' ) );
                    $childrenCount = 0;
                    if ( $publishedNode !== null )
                        $childrenCount =& $publishedNode->childrenCount();
                    if ( $childrenCount != 0 )
                    {
                        $module->redirectToView( 'removenode', array( $objectID, $editVersion, $editLanguage, $nodeID ) );
                        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
                    }
                }
                $version->removeAssignment( $nodeID );
            }
        }
    }

    if ( $module->isCurrentAction( 'MoveNode' ) )
    {
        $objectID = $object->attribute( 'id' );
/*        if ( $http->hasPostVariable( 'DeleteParentIDArray' ) )
        {
            $sourceNodeID = $http->postVariable( 'DeleteParentIDArray' );
        }
*/
        if ( $http->hasPostVariable( 'MoveNodeID' ) )
        {
            $fromNodeID = $http->postVariable( 'MoveNodeID' ); //$sourceNodeID[0];
            $oldAssignmentParentID = $fromNodeID;
            $fromNodeAssignment =& eZNodeAssignment::fetch( $objectID, $version->attribute( 'version' ), $fromNodeID );
            if( $fromNodeAssignment->attribute( 'from_node_id' ) != 0 )
            {
                $fromNodeID = $fromNodeAssignment->attribute( 'from_node_id' );
                $oldAssignmentParentID = $fromNodeAssignment->attribute( 'parent_node' );
            }

            $http->setSessionVariable( 'BrowseFromPage', $module->redirectionURI( 'content', 'edit', array( $objectID, $editVersion, $editLanguage ) ) );
            $http->setSessionVariable( 'BrowseActionName', 'MoveNodeAssignment' );
            $http->setSessionVariable( 'FromNodeID', $fromNodeID );
            $http->setSessionVariable( 'OldAssignmentParentID', $oldAssignmentParentID );
            $http->setSessionVariable( 'BrowseReturnType', 'NodeID' );
            $http->setSessionVariable( 'BrowseSelectionType', 'Single' );
            $mainParentID = $version->attribute( 'main_parent_node_id' );
            $node = eZContentObjectTreeNode::fetch( $mainParentID );
            $nodePath =  $node->attribute( 'path' );
            $rootNodeForObject = $nodePath[0];
            if ( $rootNodeForObject != null )
            {
                $nodeID = $rootNodeForObject->attribute( 'node_id' );
            }else
            {
                $nodeID = $mainParentID;
            }
            $module->redirectToView( 'browse', array( $nodeID, $objectID, $editVersion, $fromNodeID ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
    }
}

function handleNodeTemplate( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $editVersion, $editLanguage, &$tpl )
{
    $assignedNodeArray =& $version->attribute( 'parent_nodes' );
    $remoteMap = array();
    foreach ( array_keys( $assignedNodeArray ) as $assignedNodeKey )
    {
        $assignedNode =& $assignedNodeArray[$assignedNodeKey];
        $remoteID = $assignedNode->attribute( 'remote_id' );
        if ( $remoteID > 0 )
            $remoteMap[$remoteID] =& $assignedNode;
    }
    $currentVersion =& $object->currentVersion();
    $publishedNodeArray =& $currentVersion->attribute( 'parent_nodes' );
    $mainParentNodeID = $version->attribute( 'main_parent_node_id' );
    $tpl->setVariable( 'assigned_node_array', $assignedNodeArray );
    $tpl->setVariable( 'assigned_remote_map', $remoteMap );
    $tpl->setVariable( 'published_node_array', $publishedNodeArray );
    $tpl->setVariable( 'main_node_id', $mainParentNodeID );
}

function initializeNodeEdit( &$module )
{
    $module->addHook( 'pre_fetch', 'checkNodeCorrectness', -10 );
    $module->addHook( 'post_fetch', 'checkNodeAssignments' );
    $module->addHook( 'post_fetch', 'checkNodeMovements' );
    $module->addHook( 'pre_commit', 'storeNodeAssignments' );
    $module->addHook( 'action_check', 'checkNodeActions' );
    $module->addHook( 'pre_template', 'handleNodeTemplate' );
}

?>
