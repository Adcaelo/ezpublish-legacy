<?php
//
// Created on: <04-Jul-2002 13:06:30 bf>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
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

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
include_once( 'kernel/classes/ezcontentbrowse.php' );
include_once( 'kernel/classes/ezcontentbrowsebookmark.php' );
include_once( 'kernel/classes/ezcontentclass.php' );
include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( "lib/ezutils/classes/ezini.php" );
include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );

$http =& eZHTTPTool::instance();
$module =& $Params["Module"];

/* We retrieve the class ID for users as this is used in many places in this
 * code in order to be able to cleanup the user-policy cache. */
$ini =& eZINI::instance();
$userClassID = $ini->variable( "UserSettings", "UserClassID" );

if ( $http->hasPostVariable( 'BrowseCancelButton' ) )
{
    if ( $http->hasPostVariable( 'BrowseCancelURI' ) )
    {
        return $module->redirectTo( $http->postVariable( 'BrowseCancelURI' ) );
    }
}

if ( $http->hasPostVariable( 'NewButton' ) || $module->isCurrentAction( 'NewObjectAddNodeAssignment' )  )
{
    $hasClassInformation = false;
    $contentClassID = false;
    $contentClassIdentifier = false;
    $class = false;
    if ( $http->hasPostVariable( 'ClassID' ) )
    {
        $contentClassID = $http->postVariable( 'ClassID' );
        if ( $contentClassID )
            $hasClassInformation = true;
    }
    else if ( $http->hasPostVariable( 'ClassIdentifier' ) )
    {
        $contentClassIdentifier = $http->postVariable( 'ClassIdentifier' );
        $class =& eZContentClass::fetchByIdentifier( $contentClassIdentifier );
        if ( is_object( $class ) )
        {
            $contentClassID = $class->attribute( 'id' );
            if ( $contentClassID )
                $hasClassInformation = true;
        }
    }
    if ( ( $hasClassInformation && $http->hasPostVariable( 'NodeID' ) ) || $module->isCurrentAction( 'NewObjectAddNodeAssignment' ) )
    {
        if (  $module->isCurrentAction( 'NewObjectAddNodeAssignment' ) )
        {
            $selectedNodeIDArray = eZContentBrowse::result( 'NewObjectAddNodeAssignment' );
            if ( count( $selectedNodeIDArray ) == 0 )
                return $module->redirectToView( 'view', array( 'full', 2 ) );
            $node =& eZContentObjectTreeNode::fetch( $selectedNodeIDArray[0] );
        }
        else
        {
            $node =& eZContentObjectTreeNode::fetch( $http->postVariable( 'NodeID' ) );
        }

        if ( is_object( $node ) )
        {
            $parentContentObject =& $node->attribute( 'object' );
            if ( $parentContentObject->checkAccess( 'create', $contentClassID,  $parentContentObject->attribute( 'contentclass_id' ) ) == '1' )
            {
                $user =& eZUser::currentUser();
                $userID =& $user->attribute( 'contentobject_id' );
                $sectionID = $parentContentObject->attribute( 'section_id' );

                if ( !is_object( $class ) )
                    $class =& eZContentClass::fetch( $contentClassID );
                if ( is_object( $class ) )
                {
                    $db =& eZDB::instance();
                    $db->begin();
                    $contentObject =& $class->instantiate( $userID, $sectionID );
                    $nodeAssignment =& eZNodeAssignment::create( array(
                                                                     'contentobject_id' => $contentObject->attribute( 'id' ),
                                                                     'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                                     'parent_node' => $node->attribute( 'node_id' ),
                                                                     'is_main' => 1
                                                                     )
                                                                 );
                    if ( $http->hasPostVariable( 'AssignmentRemoteID' ) )
                    {
                        $nodeAssignment->setAttribute( 'remote_id', $http->postVariable( 'AssignmentRemoteID' ) );
                    }
                    $nodeAssignment->store();
                    $db->commit();

                    if ( $http->hasPostVariable( 'RedirectURIAfterPublish' ) )
                    {
                        $http->setSessionVariable( 'RedirectURIAfterPublish', $http->postVariable( 'RedirectURIAfterPublish' ) );
                    }
                    $module->redirectTo( $module->functionURI( 'edit' ) . '/' . $contentObject->attribute( 'id' ) . '/' . $contentObject->attribute( 'current_version' ) );
                    return;
                }
                else
                {
                    return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
                }
            }
            else
            {
                return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
            }
        }
        else
        {
            return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
        }
    }
    else if ( $hasClassInformation )
    {
        if ( !is_object( $class ) )
            $class =& eZContentClass::fetch( $contentClassID );
        eZContentBrowse::browse( array( 'action_name' => 'NewObjectAddNodeAssignment',
                                        'description_template' => 'design:content/browse_first_placement.tpl',
                                        'keys' => array( 'class' => $class->attribute( 'id' ),
                                                         'classgroup' => $class->attribute( 'ingroup_id_list' ) ),
                                        'persistent_data' => array( 'ClassID' => $class->attribute( 'id' ) ),
                                        'content' => array( 'class_id' => $class->attribute( 'id' ) ),
                                        'cancel_page' => $module->redirectionURIForModule( $module, 'view', array( 'full', 2 ) ),
                                        'from_page' => "/content/action" ),
                                 $module );
    }
}
else if ( $http->hasPostVariable( 'SetSorting' ) &&
          $http->hasPostVariable( 'ContentObjectID' ) && $http->hasPostVariable( 'ContentNodeID' ) &&
          $http->hasPostVariable( 'SortingField' )    && $http->hasPostVariable( 'SortingOrder' ) )
{
    $nodeID          = $http->postVariable( 'ContentNodeID' );
    $contentObjectID = $http->postVariable( 'ContentObjectID' );
    $sortingField    = $http->postVariable( 'SortingField' );
    $sortingOrder    = $http->postVariable( 'SortingOrder' );
    $node =& eZContentObjectTreeNode::fetch( $nodeID );
    $parentNode = $node->attribute( 'parent_node_id' );
    $contentObject =& eZContentObject::fetch( $contentObjectID );
    $contentObjectVersion =& $contentObject->attribute( 'current_version' );
    $nodeAssignment = eZNodeAssignment::fetch( $contentObjectID, $contentObjectVersion, $parentNode );

    // store new sorting info
    $nodeAssignment->setAttribute( 'sort_field', $sortingField );
    $nodeAssignment->setAttribute( 'sort_order', $sortingOrder );

    $db =& eZDB::instance();
    $db->begin();
    $nodeAssignment->store();
    $node->setAttribute( 'sort_field', $sortingField );
    $node->setAttribute( 'sort_order', $sortingOrder );
    $node->store();
    $db->commit();

    // invalidate node view cache
    include_once( 'kernel/classes/ezcontentcache.php' );
    eZContentCache::cleanup( array( $nodeID ) );
    eZContentObject::expireTemplateBlockCacheIfNeeded();

    return $module->redirectToView( 'view', array( 'full', $nodeID,
                                                   $languageCode = $module->actionParameter( 'LanguageCode' ) ) );
}
else if ( $module->isCurrentAction( 'MoveNode' ) )
{
    /* This action is used through the admin interface with the "Move" button,
     * or in the pop-up menu and will move a node to a different location. */

    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $node =& eZContentObjectTreeNode::fetch( $nodeID );
    if ( !$node )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );

    if ( !$node->checkAccess( 'move' ) )
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array() );

    $object =& $node->object();
    if ( !$object )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );
    $objectID = $object->attribute( 'id' );
    $class =& $object->contentClass();
    $classID = $class->attribute( 'id' );

    if ( $module->hasActionParameter( 'NewParentNode' ) )
    {
        $selectedNodeID = $module->actionParameter( 'NewParentNode' );
    }
    else
    {
        $selectedNodeIDArray = eZContentBrowse::result( 'MoveNode' );
        $selectedNodeID = $selectedNodeIDArray[0];
    }
    $selectedNode =& eZContentObjectTreeNode::fetch( $selectedNodeID );
    if ( !$selectedNode )
    {
        eZDebug::writeWarning( "Content node with ID $selectedNodeID does not exist, cannot use that as parent node for node $nodeID",
                               'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$selectedNode->checkAccess( 'create', $classID ) )
    {
        eZDebug::writeError( "Cannot move node $nodeID as child of parent node $selectedNodeID, the current user does not have create permission for class ID $classID",
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    // clear cache for old placement.
    include_once( 'kernel/content/ezcontentoperationcollection.php' );
    eZContentOperationCollection::clearObjectViewCache( $objectID, true );

    $oldParentNode = $node->fetchParent();
    $oldParentObject = $oldParentNode->object();

    // clear user policy cache if this was a user object
    if ( $object->attribute( 'contentclass_id' ) == $userClassID )
    {
        eZUser::cleanupCache();
    }

    $db =& eZDB::instance();
    $db->begin();

    $node->move( $selectedNodeID );

    $newNode =& eZContentObjectTreeNode::fetchNode( $objectID, $selectedNodeID );
    if ( $newNode )
    {
        $newNode->updateSubTreePath();
        if ( $newNode->attribute( 'main_node_id' ) == $newNode->attribute( 'node_id' ) )
        {
            // If the main node is moved we need to check if the section ID must change
            // If the section ID is shared with its old parent we must update with the
            //  id taken from the new parent, if not the node is the starting point of the section.
            if ( $object->attribute( 'section_id' ) == $oldParentObject->attribute( 'section_id' ) )
            {
                $newParentNode =& $newNode->fetchParent();
                $newParentObject =& $newParentNode->object();
                eZContentObjectTreeNode::assignSectionToSubTree( $newNode->attribute( 'main_node_id' ),
                                                                 $newParentObject->attribute( 'section_id' ),
                                                                 $oldParentObject->attribute( 'section_id' ) );
            }
        }

        // modify assignment
        $curVersion     =& $object->attribute( 'current_version' );
        $nodeAssignment =& eZNodeAssignment::fetch( $objectID, $curVersion, $oldParentNode->attribute( 'node_id' ) );

        if ( $nodeAssignment )
        {
            $nodeAssignment->setAttribute( 'parent_node', $selectedNodeID );
            $nodeAssignment->store();
        }
        else
        {
            eZDebug::writeDebug( 'kernel-content-action-MoveNode', 'invalid nodeAssignment' );
        }

        // clear cache for new placement.
        eZContentOperationCollection::clearObjectViewCache( $objectID, true );
        eZContentObject::expireTemplateBlockCacheIfNeeded();
    }
    else
    {
        eZDebug::writeError( "Failed to move node $nodeID as child of parent node $selectedNodeID",
                             'content/action' );
    }
    $db->commit();

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $module->isCurrentAction( 'MoveNodeRequest' ) )
{
    /* This action is started through the pop-up menu when a "Move" is
     * requested and through the use of the "Move" button. It will start the
     * browser to select where the node should be moved to. */

    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $node =& eZContentObjectTreeNode::fetch( $nodeID );
    if ( !$node )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );

    if ( !$node->checkAccess( 'move' ) )
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array() );

    $object =& $node->object();
    if ( !$object )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );
    $objectID = $object->attribute( 'id' );
    $class =& $object->contentClass();

    $ignoreNodesSelect = array();
    $ignoreNodesClick = array();

    $publishedAssigned = $object->assignedNodes( false );
    foreach ( $publishedAssigned as $element )
    {
        $ignoreNodesSelect[] = $element['node_id'];
        $ignoreNodesClick[]  = $element['node_id'];
        $ignoreNodesSelect[] = $element['parent_node_id'];
    }

    $ignoreNodesSelect = array_unique( $ignoreNodesSelect );
    $ignoreNodesClick = array_unique( $ignoreNodesClick );
    eZContentBrowse::browse( array( 'action_name' => 'MoveNode',
                                    'description_template' => 'design:content/browse_move_node.tpl',
                                    'keys' => array( 'class' => $class->attribute( 'id' ),
                                                     'class_id' => $class->attribute( 'identifier' ),
                                                     'classgroup' => $class->attribute( 'ingroup_id_list' ),
                                                     'section' => $object->attribute( 'section_id' ) ),
                                    'ignore_nodes_select' => $ignoreNodesSelect,
                                    'ignore_nodes_click'  => $ignoreNodesClick,
                                    'persistent_data' => array( 'ContentNodeID' => $nodeID,
                                                                'ViewMode' => $viewMode,
                                                                'ContentObjectLanguageCode' => $languageCode,
                                                                'MoveNodeAction' => '1' ),
                                    'permission' => array( 'access' => 'create',
                                                           'contentclass_id' => $class->attribute( 'id' ) ),
                                    'content' => array( 'object_id' => $objectID,
                                                        'object_version' => $object->attribute( 'current_version' ),
                                                        'object_language' => $languageCode ),
                                    'start_node' => $node->attribute( 'parent_node_id' ),
                                    'cancel_page' => $module->redirectionURIForModule( $module, 'view', array( $viewMode, $nodeID, $languageCode ) ),
                                    'from_page' => "/content/action" ),
                             $module );

    return;
}
else if ( $module->isCurrentAction( 'SwapNode' ) )
{
    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $node =& eZContentObjectTreeNode::fetch( $nodeID );

    if ( !$node )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );

    if ( !$node->checkAccess( 'move' ) )
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array() );

    $nodeParentNodeID = & $node->attribute( 'parent_node_id' );
    $object =& $node->object();
    if ( !$object )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );
    $objectID = $object->attribute( 'id' );
    $objectVersion = $object->attribute( 'current_version' );
    $class =& $object->contentClass();
    $classID = $class->attribute( 'id' );

    if ( $module->hasActionParameter( 'NewNode' ) )
    {
        $selectedNodeID = $module->actionParameter( 'NewNode' );
    }
    else
    {
         $selectedNodeIDArray = eZContentBrowse::result( 'SwapNode' );
         $selectedNodeID = $selectedNodeIDArray[0];
    }

    $selectedNode =& eZContentObjectTreeNode::fetch( $selectedNodeID );
    if ( !$selectedNode )
    {
        eZDebug::writeWarning( "Content node with ID $selectedNodeID does not exist, cannot use that as exchanging node for node $nodeID",
                               'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$selectedNode->checkAccess( 'edit' ) )
    {
        eZDebug::writeError( "Cannot use node $selectedNodeID as the exchanging node for $nodeID, the current user does not have edit permission for it",
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    // clear cache.
    include_once( 'kernel/content/ezcontentoperationcollection.php' );
    eZContentOperationCollection::clearObjectViewCache( $objectID, true );

    $selectedObject =& $selectedNode->object();
    $selectedObjectID =& $selectedObject->attribute( 'id' );
    $selectedObjectVersion =& $selectedObject->attribute( 'current_version' );
    $selectedNodeParentNodeID=& $selectedNode->attribute( 'parent_node_id' );
    $node->setAttribute( 'contentobject_id', $selectedObjectID );
    $node->setAttribute( 'contentobject_version', $selectedObjectVersion );

    $db =& eZDB::instance();
    $db->begin();
    $node->store();
    $selectedNode->setAttribute( 'contentobject_id', $objectID );
    $selectedNode->setAttribute( 'contentobject_version', $objectVersion );
    $selectedNode->store();

    // clear user policy cache if this was a user object
    if ( $object->attribute( 'contentclass_id' ) == $userClassID )
    {
        eZUser::cleanupCache();
    }

    // modify assignment
    $sourceNodeAssignment =& eZNodeAssignment::fetch( $objectID, $objectVersion, $nodeParentNodeID );
    if ( $sourceNodeAssignment )
    {
        $sourceNodeAssignment->setAttribute( 'contentobject_id', $selectedObjectID );
        $sourceNodeAssignment->setAttribute( 'contentobject_version', $selectedObjectVersion );
        $sourceNodeAssignment->store();
    }

    $targetNodeAssignment =& eZNodeAssignment::fetch( $selectedObjectID, $selectedObjectVersion, $selectedNodeParentNodeID );
    if ( $targetNodeAssignment )
    {
        $targetNodeAssignment->setAttribute( 'contentobject_id', $objectID );
        $targetNodeAssignment->setAttribute( 'contentobject_version', $objectVersion );
        $targetNodeAssignment->store();
    }

    // modify path string
    $changedOriginalNode =& eZContentObjectTreeNode::fetch( $nodeID );
    $changedOriginalNode->updateSubTreePath();
    $changedTargetNode =& eZContentObjectTreeNode::fetch( $selectedNodeID );
    $changedTargetNode->updateSubTreePath();
    $db->commit();

    // clear cache for new placement.
    eZContentOperationCollection::clearObjectViewCache( $objectID, true );

    // clear template block cache
    eZContentObject::expireTemplateBlockCacheIfNeeded();

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $module->isCurrentAction( 'SwapNodeRequest' ) )
{
    /* This action brings a browse screen up to select with which the selected
     * node should be swapped. It will not actually move the nodes. */

    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $node =& eZContentObjectTreeNode::fetch( $nodeID );
    if ( !$node )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );

    if ( !$node->checkAccess( 'move' ) )
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array() );

    $object =& $node->object();
    if ( !$object )
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel', array() );
    $objectID = $object->attribute( 'id' );
    $class =& $object->contentClass();

    $ignoreNodesSelect = array();
    $ignoreNodesClick = array();

    $publishedAssigned = $object->assignedNodes( false );
    foreach ( $publishedAssigned as $element )
    {
        $ignoreNodesSelect[] = $element['node_id'];
        $ignoreNodesClick[]  = $element['node_id'];
        $ignoreNodesSelect[] = $element['parent_node_id'];
    }
    $ignoreNodesSelect = array_unique( $ignoreNodesSelect );
    $ignoreNodesClick = array_unique( $ignoreNodesClick );
    eZContentBrowse::browse( array( 'action_name' => 'SwapNode',
                                    'description_template' => 'design:content/browse_swap_node.tpl',
                                    'keys' => array( 'class' => $class->attribute( 'id' ),
                                                     'class_id' => $class->attribute( 'identifier' ),
                                                     'classgroup' => $class->attribute( 'ingroup_id_list' ),
                                                     'section' => $object->attribute( 'section_id' ) ),
                                    'ignore_nodes_select' => $ignoreNodesSelect,
                                    'ignore_nodes_click'  => $ignoreNodesClick,
                                    'persistent_data' => array( 'ContentNodeID' => $nodeID,
                                                                'ViewMode' => $viewMode,
                                                                'ContentObjectLanguageCode' => $languageCode,
                                                                'SwapNodeAction' => '1' ),
                                    'permission' => array( 'access' => 'edit',
                                                           'contentclass_id' => $class->attribute( 'id' ) ),
                                    'content' => array( 'object_id' => $objectID,
                                                        'object_version' => $object->attribute( 'current_version' ),
                                                        'object_language' => $languageCode ),
                                    'start_node' => $node->attribute( 'parent_node_id' ),
                                    'cancel_page' => $module->redirectionURIForModule( $module, 'view', array( $viewMode, $nodeID, $languageCode ) ),
                                    'from_page' => "/content/action" ),
                             $module );

    return;
}
else if ( $module->isCurrentAction( 'UpdateMainAssignment' ) )
{
    /* This action selects a different main assignment node for the object. */

    if ( !$module->hasActionParameter( 'ObjectID' ) )
    {
        eZDebug::writeError( "Missing ObjectID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $objectID = $module->actionParameter( 'ObjectID' );
    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    if ( $module->hasActionParameter( 'MainAssignmentID' ) )
    {
        $mainAssignmentID = $module->actionParameter( 'MainAssignmentID' );

        $object =& eZContentObject::fetch( $objectID );
        if ( !$object )
        {
            return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
        }

        $existingMainNodeID = false;
        $existingMainNode =& $object->attribute( 'main_node' );
        if ( $existingMainNode )
            $existingMainNodeID = $existingMainNode->attribute( 'node_id' );
        if ( $existingMainNodeID === false or
             $existingMainNodeID != $mainAssignmentID )
        {
            if ( $existingMainNode and
                 !$existingMainNode->checkAccess( 'edit' ) )
            {
                return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array() );
            }

            $newMainNode =& eZContentObjectTreeNode::fetch( $mainAssignmentID );
            if ( !$newMainNode )
            {
                return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
            }

            if ( !$newMainNode->checkAccess( 'edit' ) )
            {
                return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
            }

            eZContentObjectTreeNode::updateMainNodeID( $mainAssignmentID, $objectID, false,
                                                       $newMainNode->attribute( 'parent_node_id' ) );

            include_once( 'kernel/classes/ezcontentcachemanager.php' );
            eZContentCacheManager::clearObjectViewCache( $objectID, true );
            eZContentObject::expireTemplateBlockCacheIfNeeded();
        }
    }
    else
    {
        eZDebug::writeError( "No MainAssignmentID found for action " . $module->currentAction(),
                             'content/action' );
    }

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $module->isCurrentAction( 'AddAssignment' ) or
          $module->isCurrentAction( 'SelectAssignmentLocation' ) )
{
    if ( !$module->hasActionParameter( 'ObjectID' ) )
    {
        eZDebug::writeError( "Missing ObjectID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $objectID = $module->actionParameter( 'ObjectID' );
    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $object =& eZContentObject::fetch( $objectID );
    if ( !$object )
    {
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    if ( !$object->checkAccess( 'edit' ) )
    {
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    $existingNode =& eZContentObjectTreeNode::fetch( $nodeID );
    if ( !$existingNode )
    {
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    $version =& $object->currentVersion();
    $class =& $object->contentClass();
    if ( $module->isCurrentAction( 'AddAssignment' ) )
    {
        $selectedNodeIDArray = eZContentBrowse::result( 'AddNodeAssignment' );
        $assignedNodes =& $version->nodeAssignments();
        $assignedIDArray = array();
        $setMainNode = false;
        $hasMainNode = false;
        foreach ( $assignedNodes as $assignedNode )
        {
            $assignedNodeID = $assignedNode->attribute( 'parent_node' );
            if ( $assignedNode->attribute( 'is_main' ) )
                $hasMainNode = true;
            $assignedIDArray[] = $assignedNodeID;
        }
        if ( !$hasMainNode )
            $setMainNode = true;

        $mainNodeID = $existingNode->attribute( 'main_node_id' );
        $objectName = $object->attribute( 'name' );

        $db =& eZDB::instance();
        $db->begin();
        foreach ( $selectedNodeIDArray as $selectedNodeID )
        {
            if ( !in_array( $selectedNodeID, $assignedIDArray ) )
            {
                $isPermitted = true;
                $parentNode =& eZContentObjectTreeNode::fetch( $selectedNodeID );
                $parentNodeObject =& $parentNode->attribute( 'object' );

                $canCreate = $parentNode->checkAccess( 'create', $class->attribute( 'id' ), $parentNodeObject->attribute( 'contentclass_id' ) ) == 1;
                if ( $isPermitted )
                {
                    if ( $object->attribute( 'contentclass_id' ) == $userClassID )
                    {
                        eZUser::cleanupCache();
                    }
                    $isMain = 0;
                    if ( $setMainNode )
                        $isMain = 1;
                    $setMainNode = false;
                    $nodeAssignment =& $version->assignToNode( $selectedNodeID, $isMain );
                    $newNode =& $parentNode->addChild( $object->attribute( 'id' ), 0, true );
                    $newNode->setAttribute( 'sort_field', $nodeAssignment->attribute( 'sort_field' ) );
                    $newNode->setAttribute( 'sort_order', $nodeAssignment->attribute( 'sort_order' ) );
                    $newNode->setAttribute( 'contentobject_version', $version->attribute( 'version' ) );
                    $newNode->setAttribute( 'contentobject_is_published', 1 );
                    $newNode->setAttribute( 'main_node_id', $mainNodeID );
                    $newNode->setName( $objectName );
                    $newNode->updateSubTreePath();
                    $newNode->store();
                    eZContentObjectTreeNode::updateNodeVisibility( $newNode, $parentNode, false );
                }
            }
        }
        $db->commit();
        include_once( 'kernel/content/ezcontentoperationcollection.php' );
        eZContentOperationCollection::clearObjectViewCache( $objectID, true );
        eZContentObject::expireTemplateBlockCacheIfNeeded();
    }
    else if ( $module->isCurrentAction( 'SelectAssignmentLocation' ) )
    {
        $ignoreNodesSelect = array();
        $ignoreNodesClick  = array();
        $assigned = $version->nodeAssignments();
        $publishedAssigned = $object->assignedNodes( false );
        $isTopLevel = false;
        foreach ( $publishedAssigned as $element )
        {
            $append = false;
            if ( $element['parent_node_id'] == 1 )
                $isTopLevel = true;
            foreach ( $assigned as $ass )
            {
                if ( $ass->attribute( 'parent_node' ) == $element['parent_node_id'] )
                {
                    $append = true;
                }
            }
            if ( $append )
            {
                $ignoreNodesSelect[] = $element['node_id'];
                $ignoreNodesClick[]  = $element['node_id'];
                $ignoreNodesSelect[] = $element['parent_node_id'];
            }
        }

        if ( !$isTopLevel )
        {
            $ignoreNodesSelect = array_unique( $ignoreNodesSelect );
            $objectID = $object->attribute( 'id' );
            eZContentBrowse::browse( array( 'action_name' => 'AddNodeAssignment',
                                            'description_template' => 'design:content/browse_placement.tpl',
                                            'keys' => array( 'class' => $class->attribute( 'id' ),
                                                             'class_id' => $class->attribute( 'identifier' ),
                                                             'classgroup' => $class->attribute( 'ingroup_id_list' ),
                                                             'section' => $object->attribute( 'section_id' ) ),
                                            'ignore_nodes_select' => $ignoreNodesSelect,
                                            'ignore_nodes_click'  => $ignoreNodesClick,
                                            'persistent_data' => array( 'ContentNodeID' => $nodeID,
                                                                        'ContentObjectID' => $objectID,
                                                                        'ViewMode' => $viewMode,
                                                                        'ContentObjectLanguageCode' => $languageCode,
                                                                        'AddAssignmentAction' => '1' ),
                                            'content' => array( 'object_id' => $objectID,
                                                                'object_version' => $version->attribute( 'version' ),
                                                                'object_language' => $languageCode ),
                                            'cancel_page' => $module->redirectionURIForModule( $module, 'view', array( $viewMode, $nodeID, $languageCode ) ),
                                            'from_page' => "/content/action" ),
                                     $module );

            return;
        }
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $module->isCurrentAction( 'RemoveAssignment' )  )
{
    if ( !$module->hasActionParameter( 'ObjectID' ) )
    {
        eZDebug::writeError( "Missing ObjectID parameter for action RemoveAssignment",
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action RemoveAssignment",
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $objectID = $module->actionParameter( 'ObjectID' );
    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $object =& eZContentObject::fetch( $objectID );
    if ( !$object )
    {
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    if ( !$object->checkAccess( 'edit' ) )
    {
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    $assignmentIDSelection = $module->actionParameter( 'AssignmentIDSelection' );

    $hasChildren = false;

    $nodeAssignments = eZNodeAssignment::fetchListByID( $assignmentIDSelection );
    $removeList = array();
    $nodeRemoveList = array();
    foreach ( $nodeAssignments as $key => $nodeAssignment )
    {
        $nodeAssignment =& $nodeAssignments[$key];
        $node =& $nodeAssignment->fetchNode();

        if ( $node )
        {
            // Security checks, removal of current node is not allowed
            // and we require removal rights
            if ( $node->attribute( 'node_id' ) == $nodeID )
                continue;
            if ( !$node->canRemove() )
                continue;

            $removeList[] = $node->attribute( 'node_id' );
            $nodeRemoveList[] =& $node;
            $count = $node->childrenCount( false );
            if ( $count > 0 )
            {
                $hasChildren = true;
            }
            unset( $node );
        }
    }

    if ( $hasChildren )
    {
        $http->setSessionVariable( 'CurrentViewMode', $viewMode );
        $http->setSessionVariable( 'DeleteIDArray', $removeList );
        $http->setSessionVariable( 'ContentObjectID', $objectID );
        $http->setSessionVariable( 'ContentNodeID', $nodeID );
        $http->setSessionVariable( 'ContentLanguage', $languageCode );
        return $module->redirectToView( 'removeobject' );
    }
    else
    {
        $mainNodeChanged = false;
        
        $db =& eZDB::instance();
        $db->begin();
        foreach ( $nodeRemoveList as $key => $node )
        {
            if ( $node->attribute( 'node_id' ) == $node->attribute( 'main_node_id' ) )
                $mainNodeChanged = true;
            $node->remove();
        }
        if ( $mainNodeChanged )
        {
            $allNodes =& $object->assignedNodes();
            $mainNode =& $allNodes[0];
            eZContentObjectTreeNode::updateMainNodeID( $mainNode->attribute( 'node_id' ), $objectID, false, $mainNode->attribute( 'parent_node_id' ) );
        }
        $db->commit();
    }

    include_once( 'kernel/content/ezcontentoperationcollection.php' );
    eZContentOperationCollection::clearObjectViewCache( $objectID, true );

    // clear user policy cache if this was a user object
    if ( $object->attribute( 'contentclass_id' ) == $userClassID )
    {
        eZUser::cleanupCache();
    }

    // we don't clear template block cache here since it's cleared in eZContentObjectTreeNode::remove()

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $http->hasPostVariable( 'EditButton' )  )
{
    if ( $http->hasPostVariable( 'ContentObjectID' ) )
    {
        $parameters = array( $http->postVariable( 'ContentObjectID' ) );
        if ( $http->hasPostVariable( 'ContentObjectVersion' ) )
        {
            $parameters[] = $http->postVariable( 'ContentObjectVersion' );
            if ( $http->hasPostVariable( 'ContentObjectLanguageCode' ) )
            {
                $parameters[] = $http->postVariable( 'ContentObjectLanguageCode' );
            }
        }
        else
        {
            if ( $http->hasPostVariable( 'ContentObjectLanguageCode' ) )
            {
                $parameters[] = 'f'; // this will be treatead as not entering the version number
                $parameters[]= $http->postVariable( 'ContentObjectLanguageCode' );
            }
        }

		if ( $http->hasPostVariable( 'RedirectURIAfterPublish' ) )
		{
			$http->setSessionVariable( 'RedirectURIAfterPublish', $http->postVariable( 'RedirectURIAfterPublish' ) );
		}
		
        $module->redirectToView( 'edit', $parameters );
        return;
    }
}
else if ( $http->hasPostVariable( 'PreviewPublishButton' )  )
{
    if ( $http->hasPostVariable( 'ContentObjectID' ) )
    {
        $parameters = array( $http->postVariable( 'ContentObjectID' ) );
        if ( $http->hasPostVariable( 'ContentObjectVersion' ) )
        {
            $parameters[] = $http->postVariable( 'ContentObjectVersion' );
            if ( $http->hasPostVariable( 'ContentObjectLanguageCode' ) )
            {
                $parameters[] = $http->postVariable( 'ContentObjectLanguageCode' );
            }
        }
        $module->setCurrentAction( 'Publish', 'edit' );
        return $module->run( 'edit', $parameters );
    }
}
else if ( $http->hasPostVariable( 'RemoveButton' ) )
{
    if ( $http->hasPostVariable( 'ViewMode' ) )
    {
        $viewMode = $http->postVariable( 'ViewMode' );
    }
    else
    {
        $viewMode = 'full';
    }
//     if ( $http->hasPostVariable( 'TopLevelNode' ) )
//     {
//         $topLevelNode = $http->postVariable( 'TopLevelNode' );
//     }
//     else
//     {
//         $topLevelNode = '2';
//     }
    $contentNodeID = 2;
    if ( $http->hasPostVariable( 'ContentNodeID' ) )
        $contentNodeID = $http->postVariable( 'ContentNodeID' );
    $contentObjectID = 1;
    if ( $http->hasPostVariable( 'ContentObjectID' ) )
        $contentObjectID = $http->postVariable( 'ContentObjectID' );

    if ( $http->hasPostVariable( 'DeleteIDArray' ) )
    {
        $deleteIDArray =& $http->postVariable( 'DeleteIDArray' );
        if ( is_array( $deleteIDArray ) && count( $deleteIDArray ) > 0 )
        {
            $http->setSessionVariable( 'CurrentViewMode', $viewMode );
            $http->setSessionVariable( 'ContentNodeID', $contentNodeID );
            $http->setSessionVariable( 'ContentObjectID', $contentObjectID );
            $http->setSessionVariable( 'DeleteIDArray', $deleteIDArray );
            $module->redirectTo( $module->functionURI( 'removeobject' ) . '/' );
        }
        else
        {
            $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $contentNodeID . '/' );
        }
    }
    else
    {
        $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $contentNodeID . '/' );
    }
}
else if ( $http->hasPostVariable( 'UpdatePriorityButton' ) )
{
    include_once( 'kernel/classes/ezcontentcache.php' );
    if ( $http->hasPostVariable( 'ViewMode' ) )
    {
        $viewMode = $http->postVariable( 'ViewMode' );
    }
    else
    {
        $viewMode = 'full';
    }

    if ( $http->hasPostVariable( 'ContentNodeID' ) )
    {
        $contentNodeID = $http->postVariable( 'ContentNodeID' );
    }
    else
    {
        eZDebug::writeError( "Variable 'ContentNodeID' can not be found in template." );
        $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $contentNodeID . '/' );
        return;
    }
    if ( $http->hasPostVariable( 'Priority' ) and $http->hasPostVariable( 'PriorityID' ) )
    {
        $contentNode = eZContentObjectTreeNode::fetch( $contentNodeID );
        if ( !$contentNode->attribute( 'can_edit' ) )
        {
            eZDebug::writeError( 'Current user can not update the priorities because he has no permissions to edit the node' );
            $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $contentNodeID . '/' );
            return;
        }
        $priorityArray =& $http->postVariable( 'Priority' );
        $priorityIDArray =& $http->postVariable( 'PriorityID' );

        $db =& eZDB::instance();
        $db->begin();
        for ( $i=0; $i<count( $priorityArray );$i++ )
        {
            $priority = (int) $priorityArray[$i];
            $nodeID = $priorityIDArray[$i];
            $db->query( "UPDATE ezcontentobject_tree SET priority=$priority WHERE node_id=$nodeID" );
        }
        $db->commit();
    }

    $clearNodeArray = array();
    if ( $http->hasPostVariable( 'ContentObjectID' ) )
    {
        $object =& eZContentObject::fetch( $http->postVariable( 'ContentObjectID' ) );
        $nodes =& $object->assignedNodes( false );
        foreach ( $nodes as $node )
        {
            $clearNodeArray[] = $node['node_id'];
        }
    }
    if ( eZContentCache::cleanup( $clearNodeArray ) )
    {
//                     eZDebug::writeDebug( 'cache cleaned up', 'content' );
    }
    eZContentObject::expireTemplateBlockCacheIfNeeded();
    $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $contentNodeID . '/' );
    return;
}
else if ( $http->hasPostVariable( "ActionAddToBookmarks" ) )
{
    $user =& eZUser::currentUser();
    $nodeID = false;
    if ( $http->hasPostVariable( 'ContentNodeID' ) )
    {
        $nodeID = $http->postVariable( 'ContentNodeID' );
        $node =& eZContentObjectTreeNode::fetch( $nodeID );
        $bookmark = eZContentBrowseBookmark::createNew( $user->id(), $nodeID, $node->attribute( 'name' ) );
    }
    if ( !$nodeID )
    {
        $contentINI =& eZINI::instance( 'content.ini' );
        $nodeID = $contentINI->variable( 'NodeSettings', 'RootNode' );
    }
    if ( $http->hasPostVariable( 'ViewMode' ) )
    {
        $viewMode = $http->postVariable( 'ViewMode' );
    }
    else
    {
        $viewMode = 'full';
    }
    $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $nodeID . '/' );
    return;
}
else if ( $http->hasPostVariable( "ActionAddToNotification" ) )
{
    $nodeID = $http->postVariable( 'ContentNodeID' );
    $module->redirectTo( 'notification/addtonotification/' . $nodeID . '/' );
    return;
}
else if ( $http->hasPostVariable( "ContentObjectID" )  )
{
    $objectID = $http->postVariable( "ContentObjectID" );
    $action = $http->postVariable( "ContentObjectID" );


    // Check which action to perform
    if ( $http->hasPostVariable( "ActionAddToBasket" ) )
    {
        $shopModule =& eZModule::exists( "shop" );
        $result =& $shopModule->run( "basket", array() );
        $module->setExitStatus( $shopModule->exitStatus() );
        $module->setRedirectURI( $shopModule->redirectURI() );

    }
    else if ( $http->hasPostVariable( "ActionAddToWishList" ) )
    {
        $user =& eZUser::currentUser();
        if ( !$user->isLoggedIn() )
            return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );

        $shopModule =& eZModule::exists( "shop" );
        $result =& $shopModule->run( "wishlist", array() );
        $module->setExitStatus( $shopModule->exitStatus() );
        $module->setRedirectURI( $shopModule->redirectURI() );
    }
    else if ( $http->hasPostVariable( "ActionPreview" ) )
    {
        $user =& eZUser::currentUser();
        $object =& eZContentObject::fetch(  $objectID );
        $module->redirectTo( $module->functionURI( 'versionview' ) . '/' . $objectID . '/' . $object->attribute( 'current_version' ) . '/' );
        return;

    }
    else if ( $http->hasPostVariable( "ActionRemove" ) )
    {
        if ( $http->hasPostVariable( 'ViewMode' ) )
        {
            $viewMode = $http->postVariable( 'ViewMode' );
        }
        else
        {
            $viewMode = 'full';
        }
        $parentNodeID = 2;
        $contentNodeID = null;
        if ( $http->hasPostVariable( 'ContentNodeID' ) )
        {
            $contentNodeID = $http->postVariable( 'ContentNodeID' );
            $node =& eZContentObjectTreeNode::fetch( $contentNodeID );
            $parentNodeID =& $node->attribute( 'parent_node_id' );
        }
        $contentObjectID = 1;
        if ( $http->hasPostVariable( 'ContentObjectID' ) )
            $contentObjectID = $http->postVariable( 'ContentObjectID' );

        if ( $contentNodeID != null )
        {
            $http->setSessionVariable( 'CurrentViewMode', $viewMode );
            $http->setSessionVariable( 'ContentNodeID', $parentNodeID );
            $http->setSessionVariable( 'ContentObjectID', $contentObjectID );
            $http->setSessionVariable( 'DeleteIDArray', array( $contentNodeID ) );
            $module->redirectTo( $module->functionURI( 'removeobject' ) . '/' );
        }
    }
    else if ( $http->hasPostVariable( "ActionCollectInformation" ) )
    {
        $Result =& $module->run( "collectinformation", array() );
        return $Result;
    }
    else
    {
        include_once( 'lib/ezutils/classes/ezextension.php' );
        $baseDirectory = eZExtension::baseDirectory();
        $contentINI =& eZINI::instance( 'content.ini' );
        $extensionDirectories = $contentINI->variable( 'ActionSettings', 'ExtensionDirectories' );
        foreach ( $extensionDirectories as $extensionDirectory )
        {
            $extensionPath = $baseDirectory . '/' . $extensionDirectory . '/actions/content_actionhandler.php';
            if ( file_exists( $extensionPath ) )
            {
                include_once( $extensionPath );
                $actionFunction = $extensionDirectory . '_ContentActionHandler';
                if ( function_exists( $actionFunction ) )
                {
                    $actionResult = $actionFunction( $Module, $http, $objectID );
                    if ( $actionResult )
                        return $actionResult;
                }
            }
        }
        eZDebug::writeError( "Unknown content object action", "kernel/content/action.php" );
    }
}
else if ( $http->hasPostVariable( 'RedirectButton' ) )
{
    if ( $http->hasPostVariable( 'RedirectURI' ) )
    {
        $module->redirectTo( $http->postVariable( 'RedirectURI' ) );
        return;
    }
}
else if ( $http->hasPostVariable( 'DestinationURL' ) )
{
    $postVariables = $http->attribute( 'post' );
    $destinationURL = $http->postVariable( 'DestinationURL' );
    $additionalParams = '';

    foreach( $postVariables as $key => $value )
    {
        if ( strpos( $key, 'Param' ) === 0 )
        {
            $destinationURL .= '/' . $value;
        }
        else if ( $key != 'DestinationURL' &&
                  $key != 'Submit' )
        {
            $additionalParams .= "/$key/$value";
        }
    }

    $module->redirectTo( '/' . $destinationURL . $additionalParams );
    return;
}
else if ( $module->isCurrentAction( 'ClearViewCache' ) or
          $module->isCurrentAction( 'ClearViewCacheSubtree' ) )
{
    if ( !$module->hasActionParameter( 'ObjectID' ) )
    {
        eZDebug::writeError( "Missing ObjectID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }
    if ( !$module->hasActionParameter( 'NodeID' ) )
    {
        eZDebug::writeError( "Missing NodeID parameter for action " . $module->currentAction(),
                             'content/action' );
        return $module->redirectToView( 'view', array( 'full', 2 ) );
    }

    $objectID = $module->actionParameter( 'ObjectID' );
    $nodeID = $module->actionParameter( 'NodeID' );
    $viewMode = 'full';
    if ( $module->hasActionParameter( 'ViewMode' ) )
        $viewMode = $module->actionParameter( 'ViewMode' );
    if ( $module->hasActionParameter( 'LanguageCode' ) )
    {
        $languageCode = $module->actionParameter( 'LanguageCode' );
    }
    else
    {
        $languageCode = eZContentObject::defaultLanguage();
    }

    $object =& eZContentObject::fetch( $objectID );
    if ( !$object )
    {
        return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }

    $user =& eZUser::currentUser();
    $result = $user->hasAccessTo( 'setup', 'managecache' );
    if ( $result['accessWord'] != 'yes' )
    {
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    include_once( 'kernel/classes/ezcontentcachemanager.php' );
    if ( $module->isCurrentAction( 'ClearViewCache' ) )
    {
        eZContentCacheManager::clearViewCache( $objectID, true );
    }
    else
    {
        $node =& eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node )
        {
            return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
        }
        $limit = 50;
        $offset = 0;
        $params = array( 'AsObject' => false,
                         'Depth' => false,
                         'Limitation' => array() ); // Empty array means no permission checking
        $subtreeCount = $node->subTreeCount( $params );
        while ( $offset < $subtreeCount )
        {
            $params['Offset'] = $offset;
            $params['Limit'] = $limit;
            $subtree =& $node->subTree( $params );
            $offset += count( $subtree );
            if ( count( $subtree ) == 0 )
            {
                break;
            }
            $objectIDList = array();
            foreach ( $subtree as $subtreeNode )
            {
                $objectIDList[] = $subtreeNode['contentobject_id'];
            }
            $objectIDList = array_unique( $objectIDList );
            unset( $subtree );

            foreach ( $objectIDList as $objectID )
            {
                eZContentCacheManager::clearViewCache( $objectID, true );
            }
        }
    }

    if ( $module->hasActionParameter( 'CurrentURL' ) )
    {
        $currentURL = $module->actionParameter( 'CurrentURL' );
        return $module->redirectTo( $currentURL );
    }

    return $module->redirectToView( 'view', array( $viewMode, $nodeID, $languageCode ) );
}
else if ( $module->isCurrentAction( 'UploadFile' ) )
{
    if ( !$module->hasActionParameter( 'UploadActionName' ) )
    {
        eZDebug::writeError( "Missing UploadActionName parameter for action " . $module->currentAction(),
                             'content/action' );
        include_once( 'kernel/classes/ezredirectmanager.php' );
        eZRedirectManager::redirectTo( $module, 'content/view/full/2', true );
        return;
    }

    $user =& eZUser::currentUser();
    $result = $user->hasAccessTo( 'content', 'create' );
    if ( $result['accessWord'] != 'yes' )
    {
        return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    $uploadActionName = $module->actionParameter( 'UploadActionName' );
    $parameters = array( 'action_name' => $uploadActionName );

    // Check for locations for the new object
    if ( $module->hasActionParameter( 'UploadParentNodes' ) )
    {
        $parentNodes = $module->actionParameter( 'UploadParentNodes' );
        if ( !is_array( $parentNodes ) )
            $parentNodes = array( $parentNodes );

        foreach ( $parentNodes as $parentNodeID )
        {
            $parentNode = eZContentObjectTreeNode::fetch( $parentNodeID );
            if ( !is_object( $parentNode ) )
            {
                eZDebug::writeError( "Cannot upload file as child of parent node $parentNodeID, the parent does not exist",
                                     'content/action:' . $module->currentAction() );
                return $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
            }
            if ( !$parentNode->canCreate() )
            {
                eZDebug::writeError( "Cannot upload file as child of parent node $parentNodeID, no permissions" . $module->currentAction(),
                                     'content/action:' . $module->currentAction() );
                return $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
            }
        }
        $parameters['parent_nodes'] = $parentNodes;
    }

    // Check for redirection to current page
    if ( $module->hasActionParameter( 'UploadRedirectBack' ) )
    {
        if ( $module->actionParameter( 'UploadRedirectBack' ) == 1 )
        {
            include_once( 'kernel/classes/ezredirectmanager.php' );
            $parameters['result_uri'] = eZRedirectManager::redirectURI( $module, 'content/view/full/2', true );
        }
        else if ( $module->actionParameter( 'UploadRedirectBack' ) == 2 )
        {
            include_once( 'kernel/classes/ezredirectmanager.php' );
            $parameters['result_uri'] = eZRedirectManager::redirectURI( $module, 'content/view/full/2', false );
        }
    }

    // Check for redirection to specific page
    if ( $module->hasActionParameter( 'UploadRedirectURI' ) )
    {
        $parameters['result_uri'] = $module->actionParameter( 'UploadRedirectURI' );
    }

    include_once( 'kernel/classes/ezcontentupload.php' );
    eZContentUpload::upload( $parameters, $module );
    return;
}
/*else if ( $http->hasPostVariable( 'RemoveObject' ) )
{
    $removeObjectID = $http->postVariable( 'RemoveObject' );
    if ( is_numeric( $removeObjectID ) )
    {
        $contentObject = eZContentObject::fetch( $removeObjectID );
        if ( $contentObject->attribute( 'can_remove' ) )
        {
            $contentObject->remove();
        }
    }
    $module->redirectTo( $module->functionURI( 'view' ) . '/' . $viewMode . '/' . $topLevelNode . '/' );
    return;
}*/


// return module contents
$Result = array();
$Result['content'] =& $result;

?>
