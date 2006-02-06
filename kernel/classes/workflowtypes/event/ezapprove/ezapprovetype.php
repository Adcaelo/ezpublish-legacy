<?php
//
// Definition of eZApproveType class
//
// Created on: <16-Apr-2002 11:08:14 amos>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
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

/*!
  \class eZApproveType ezapprovetype.php
  \brief Event type for user approvals

  WorkflowEvent storage fields : data_text1 - selected_sections
                                 data_text2 - selected_usergroups
                                 data_text3 - approve_users
                                 data_text4 - approve_groups


*/

include_once( "kernel/classes/ezworkflowtype.php" );
include_once( 'kernel/classes/collaborationhandlers/ezapprove/ezapprovecollaborationhandler.php' );

define( "EZ_WORKFLOW_TYPE_APPROVE_ID", "ezapprove" );

define( "EZ_APPROVE_COLLABORATION_NOT_CREATED", 0 );
define( "EZ_APPROVE_COLLABORATION_CREATED", 1 );

class eZApproveType extends eZWorkflowEventType
{
    function eZApproveType()
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_APPROVE_ID, ezi18n( 'kernel/workflow/event', "Approve" ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
    }

    function &attributeDecoder( &$event, $attr )
    {
        switch ( $attr )
        {
            case 'selected_sections':
            {
                $returnValue = explode( ',', $event->attribute( 'data_text1' ) );
                return $returnValue;
            } break;

            case 'approve_users':
            {
                if ( $event->attribute( 'data_text3' ) == '' )
                {
                    $returnValue = array();
                    return $returnValue;
                }
                $returnValue = explode( ',', $event->attribute( 'data_text3' ) );
                return $returnValue;
            }break;

            case 'approve_groups':
            {
                if ( $event->attribute( 'data_text4' ) == '' )
                {
                    $returnValue = array();
                    return $returnValue;
                }
                $returnValue = explode( ',', $event->attribute( 'data_text4' ) );
                return $returnValue;
            }break;

            case 'selected_usergroups':
            {
                if ( $event->attribute( 'data_text2' ) == '' )
                {
                    $returnValue = array();
                    return $returnValue;
                }
                $returnValue = explode( ',', $event->attribute( 'data_text2' ) );
                return $returnValue;
            } break;
        }
        $retValue = null;
        return $retValue;
    }

    function typeFunctionalAttributes( )
    {
        return array( 'selected_sections',
                      'approve_users',
                      'approve_groups',
                      'selected_usergroups' );
    }

    function attributes()
    {
        return array_merge( array( 'sections',
                                   'users',
                                   'usergroups' ),
                            eZWorkflowEventType::attributes() );

    }

    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() );
    }

    function &attribute( $attr )
    {
        switch( $attr )
        {
            case 'sections':
            {
                include_once( 'kernel/classes/ezsection.php' );
                $sections = eZSection::fetchList( false );
                foreach ( array_keys( $sections ) as $key )
                {
                    $section =& $sections[$key];
                    $section['Name'] = $section['name'];
                    $section['value'] = $section['id'];
                }
                return $sections;
            }break;
        }
        $eventValue =& eZWorkflowEventType::attribute( $attr );
        return $eventValue;
    }

    function execute( &$process, &$event )
    {
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $process, 'eZApproveType::execute' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'eZApproveType::execute' );
        $parameters = $process->attribute( 'parameter_list' );
        $versionID =& $parameters['version'];
        $object =& eZContentObject::fetch( $parameters['object_id'] );

        if ( !$object )
        {
            eZDebugSetting::writeError( 'kernel-workflow-approve', $parameters['object_id'], 'eZApproveType::execute' );
            return EZ_WORKFLOW_TYPE_STATUS_WORKFLOW_CANCELLED;
        }

        /*
          If we run event first time ( when we click publish in admin ) we do not have user_id set in workflow process,
          so we take current user and store it in workflow process, so next time when we run event from cronjob we fetch
          user_id from there.
         */
        if ( $process->attribute( 'user_id' ) == 0 )
        {
            $user =& eZUser::currentUser();
            $process->setAttribute( 'user_id', $user->id() );
        }
        else
        {
            $user =& eZUser::instance( $process->attribute( 'user_id' ) );
        }

        $userGroups = array_merge( $user->attribute( 'groups' ), $user->attribute( 'contentobject_id' ) );
        $workflowSections = explode( ',', $event->attribute( 'data_text1' ) );
        $workflowGroups = explode( ',', $event->attribute( 'data_text2' ) );
        $editors = explode( ',', $event->attribute( 'data_text3' ) ); //$event->attribute( 'data_int1' );
        $approveGroups = explode( ',', $event->attribute( 'data_text4' ) );

        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $user, 'eZApproveType::execute::user' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $userGroups, 'eZApproveType::execute::userGroups' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $editors, 'eZApproveType::execute::editor' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowSections, 'eZApproveType::execute::workflowSections' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowGroups, 'eZApproveType::execute::workflowGroups' );
        eZDebugSetting::writeDebug( 'kernel-workflow-approve', $object->attribute( 'section_id'), 'eZApproveType::execute::section_id' );

        $section = $object->attribute( 'section_id');
        $correctSection = false;

        if ( !in_array( $section, $workflowSections ) && !in_array( -1, $workflowSections ) )
        {
            $assignedNodes = $object->attribute( 'assigned_nodes' );
            if ( $assignedNodes )
            {
                foreach( $assignedNodes as $assignedNode )
                {
                    $parent =& $assignedNode->attribute( 'parent' );
                    $parentObject =& $parent->object();
                    $section = $parentObject->attribute( 'section_id');

                    if ( in_array( $section, $workflowSections ) )
                    {
                        $correctSection = true;
                        break;
                    }
                }
            }
        }
        else
            $correctSection = true;

        $inExcludeGroups = count( array_intersect( $userGroups, $workflowGroups ) ) != 0;

        $userIsEditor = ( in_array( $user->id(), $editors ) ||
                          count( array_intersect( $userGroups, $approveGroups ) ) != 0 );

        if ( !$userIsEditor and
             !$inExcludeGroups and
             $correctSection )
        {

            /* Get user IDs from approve user groups */
            $ini =& eZINI::instance();
            $userClassIDArray = array( $ini->variable( 'UserSettings', 'UserClassID' ) );
            $approveUserIDArray = array();
            foreach( $approveGroups as $approveUserGroupID )
            {
                if (  $approveUserGroupID != false )
                {
                    $approveUserGroup =& eZContentObject::fetch( $approveUserGroupID );
                    if ( isset( $approveUserGroup ) )
                        foreach( $approveUserGroup->attribute( 'assigned_nodes' ) as $assignedNode )
                        {
                            $userNodeArray =& $assignedNode->subTree( array( 'ClassFilterType' => 'include',
                                                                             'ClassFilterArray' => $userClassIDArray,
                                                                             'Limitation' => array() ) );
                            foreach( $userNodeArray as $userNode )
                            {
                                $approveUserIDArray[] = $userNode->attribute( 'contentobject_id' );
                            }
                        }
                }
            }
            $approveUserIDArray = array_merge( $approveUserIDArray, $editors );
            $approveUserIDArray = array_unique( $approveUserIDArray );

            $collaborationID = false;
            $db = & eZDb::instance();
            $taskResult = $db->arrayQuery( 'select workflow_process_id, collaboration_id from ezapprove_items where workflow_process_id = ' . $process->attribute( 'id' )  );
            if ( count( $taskResult ) > 0 )
                $collaborationID = $taskResult[0]['collaboration_id'];

            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $collaborationID, 'approve collaborationID' );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $process->attribute( 'event_state'), 'approve $process->attribute( \'event_state\')' );
            if ( $collaborationID === false )
            {
                $this->createApproveCollaboration( $process, $event, $user->id(), $object->attribute( 'id' ), $versionID, $approveUserIDArray );
                $this->setInformation( "We are going to create approval" );
                $process->setAttribute( 'event_state', EZ_APPROVE_COLLABORATION_CREATED );
                $process->store();
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $this, 'approve execute' );
                return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
            }
            else if ( $process->attribute( 'event_state') == EZ_APPROVE_COLLABORATION_NOT_CREATED )
            {
                eZApproveCollaborationHandler::activateApproval( $collaborationID );
                $process->setAttribute( 'event_state', EZ_APPROVE_COLLABORATION_CREATED );
                $process->store();
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $this, 'approve re-execute' );
                return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
            }
            else //EZ_APPROVE_COLLABORATION_CREATED
            {
                $this->setInformation( "we are checking approval now" );
                eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'check approval' );
                return $this->checkApproveCollaboration(  $process, $event );
            }
        }
        else
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowSections , "we are not going to create approval " . $object->attribute( 'section_id') );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $userGroups, "we are not going to create approval" );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $workflowGroups,  "we are not going to create approval" );
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $user->id(), "we are not going to create approval "  );
            return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
        }
    }

    function initializeEvent( &$event )
    {
    }

    function fetchHTTPInput( &$http, $base, &$event )
    {
        $sectionsVar = $base . "_event_ezapprove_section_" . $event->attribute( "id" );
        if ( $http->hasPostVariable( $sectionsVar ) )
        {
            $sectionsArray = $http->postVariable( $sectionsVar );
            if ( in_array( '-1', $sectionsArray ) )
            {
                $sectionsArray = array( -1 );
            }
            $sectionsString = implode( ',', $sectionsArray );
            $event->setAttribute( "data_text1", $sectionsString );
        }

        if ( $http->hasSessionVariable( 'BrowseParameters' ) )
        {
            $browseParameters = $http->sessionVariable( 'BrowseParameters' );
            if ( isset( $browseParameters['custom_action_data'] ) )
            {
                $customData = $browseParameters['custom_action_data'];
                if ( isset( $customData['event_id'] ) &&
                     $customData['event_id'] == $event->attribute( 'id' ) )
                {
                    switch( $customData['browse_action'] )
                    {
                        case 'AddApproveUsers':
                        {
                            if ( $http->hasPostVariable( 'SelectedObjectIDArray' ) and !$http->hasPostVariable( 'BrowseCancelButton' ) )
                            {
                                $userIDArray = $http->postVariable( 'SelectedObjectIDArray' );
                                foreach( $userIDArray as $key => $userID )
                                {
                                    if ( !eZUser::isUserObject( eZContentObject::fetch( $userID ) ) )
                                    {
                                        unset( $userIDArray[$key] );
                                    }
                                }
                                $event->setAttribute( 'data_text3', implode( ',',
                                                                             array_unique( array_merge( $this->attributeDecoder( $event, 'approve_users' ),
                                                                                                        $userIDArray ) ) ) );
                            }
                        } break;

                        case 'AddApproveGroups':
                        {
                            if ( $http->hasPostVariable( 'SelectedObjectIDArray' ) )
                            {
                                $userIDArray = $http->postVariable( 'SelectedObjectIDArray' );
                                $event->setAttribute( 'data_text4', implode( ',',
                                                                             array_unique( array_merge( $this->attributeDecoder( $event, 'approve_groups' ),
                                                                                                        $userIDArray ) ) ) );
                            }
                        } break;

                        case 'AddExcludeUser':
                        {
                            if ( $http->hasPostVariable( 'SelectedObjectIDArray' ) and !$http->hasPostVariable( 'BrowseCancelButton' ) )
                            {
                                $userIDArray = $http->postVariable( 'SelectedObjectIDArray' );
                                $event->setAttribute( 'data_text2', implode( ',',
                                                                             array_unique( array_merge( $this->attributeDecoder( $event, 'selected_usergroups' ),
                                                                                                        $userIDArray ) ) ) );
                            }
                        } break;

                    }

                    $http->removeSessionVariable( 'BrowseParameters' );
                }
            }
        }
    }

    function createApproveCollaboration( &$process, &$event, $userID, $contentobjectID, $contentobjectVersion, $editors )
    {
        if ( $editors === null )
            return false;
        $authorID = $userID;
        $collaborationItem = eZApproveCollaborationHandler::createApproval( $contentobjectID, $contentobjectVersion,
                                                                            $authorID, $editors );
        $db = & eZDb::instance();
        $db->query( 'INSERT INTO ezapprove_items( workflow_process_id, collaboration_id )
                       VALUES(' . $process->attribute( 'id' ) . ',' . $collaborationItem->attribute( 'id' ) . ' ) ' );
    }

    /*
     \reimp
    */
    function customWorkflowEventHTTPAction( &$http, $action, &$workflowEvent )
    {
        $eventID = $workflowEvent->attribute( "id" );
        $module =& $GLOBALS['eZRequestedModule'];

        switch ( $action )
        {
            case "AddApproveUsers" :
            {
                include_once( 'kernel/classes/ezcontentbrowse.php' );
                eZContentBrowse::browse( array( 'action_name' => 'SelectMultipleUsers',
                                                'from_page' => '/workflow/edit/' . $workflowEvent->attribute( 'workflow_id' ),
                                                'custom_action_data' => array( 'event_id' => $eventID,
                                                                               'browse_action' => $action ),
                                                'class_array' => array ( 'user' ) ),
                                         $module );
            } break;

            case "RemoveApproveUsers" :
            {
                if ( $http->hasPostVariable( 'DeleteApproveUserIDArray_' . $eventID ) )
                {
                    $workflowEvent->setAttribute( 'data_text3', implode( ',', array_diff( $this->attributeDecoder( $workflowEvent, 'approve_users' ),
                                                                                          $http->postVariable( 'DeleteApproveUserIDArray_' . $eventID ) ) ) );
                }
            } break;

            case "AddApproveGroups" :
            {
                include_once( 'kernel/classes/ezcontentbrowse.php' );
                eZContentBrowse::browse( array( 'action_name' => 'SelectMultipleUsers',
                                                'from_page' => '/workflow/edit/' . $workflowEvent->attribute( 'workflow_id' ),
                                                'custom_action_data' => array( 'event_id' => $eventID,
                                                                               'browse_action' => $action ),
                                                'class_array' => array ( 'user_group' ) ),
                                         $module );
            } break;

            case "RemoveApproveGroups" :
            {
                if ( $http->hasPostVariable( 'DeleteApproveGroupIDArray_' . $eventID ) )
                {
                    $workflowEvent->setAttribute( 'data_text4', implode( ',', array_diff( $this->attributeDecoder( $workflowEvent, 'approve_groups' ),
                                                                                          $http->postVariable( 'DeleteApproveGroupIDArray_' . $eventID ) ) ) );
                }
            } break;

            case "AddExcludeUser" :
            {
                include_once( 'kernel/classes/ezcontentbrowse.php' );
                eZContentBrowse::browse( array( 'action_name' => 'SelectMultipleUsers',
                                                'from_page' => '/workflow/edit/' . $workflowEvent->attribute( 'workflow_id' ),
                                                'custom_action_data' => array( 'event_id' => $eventID,
                                                                               'browse_action' => $action ),
                                                'class_array' => array ( 'user_group' ) ),
                                         $module );
            } break;

            case "RemoveExcludeUser" :
            {
                if ( $http->hasPostVariable( 'DeleteExcludeUserIDArray_' . $eventID ) )
                {
                    $workflowEvent->setAttribute( 'data_text2', implode( ',', array_diff( $this->attributeDecoder( $workflowEvent, 'selected_usergroups' ),
                                                                                          $http->postVariable( 'DeleteExcludeUserIDArray_' . $eventID ) ) ) );
                }
            } break;

        }
    }

    /*
     \reimp
    */
    function cleanupAfterRemoving( $attr = array() )
    {
        foreach ( array_keys( $attr ) as $attrKey )
        {
          switch ( $attrKey )
          {
              case 'DeleteContentObject':
              {
                     $contentObjectID = (int)$attr[ $attrKey ];
                     $db = & eZDb::instance();
                     // Cleanup "User who approves content"
                     $db->query( 'UPDATE ezworkflow_event
                                  SET    data_int1 = \'0\'
                                  WHERE  data_int1 = \'' . $contentObjectID . '\''  );
                     // Cleanup "Excluded user groups"
                     $excludedGroupsID = $db->arrayQuery( 'SELECT data_text2, id
                                                           FROM   ezworkflow_event
                                                           WHERE  data_text2 like \'%' . $contentObjectID . '%\'' );
                     if ( count( $excludedGroupsID ) > 0 )
                     {
                         foreach ( $excludedGroupsID as $groupID )
                         {
                             // $IDArray will contain IDs of "Excluded user groups"
                             $IDArray = split( ',', $groupID[ 'data_text2' ] );
                             // $newIDArray will contain  array without $contentObjectID
                             $newIDArray = array_filter( $IDArray, create_function( '$v', 'return ( $v != ' . $contentObjectID .' );' ) );
                             $newValues = implode( ',', $newIDArray );
                             $db->query( 'UPDATE ezworkflow_event
                                          SET    data_text2 = \''. $newValues .'\'
                                          WHERE  id = ' . $groupID[ 'id' ] );
                         }
                     }
              } break;
          }
        }
    }

    function checkApproveCollaboration( &$process, &$event )
    {
        $db = & eZDb::instance();
        $taskResult = $db->arrayQuery( 'select workflow_process_id, collaboration_id from ezapprove_items where workflow_process_id = ' . $process->attribute( 'id' )  );
        $collaborationID = $taskResult[0]['collaboration_id'];
        $collaborationItem = eZCollaborationItem::fetch( $collaborationID );
        $contentObjectVersion = eZApproveCollaborationHandler::contentObjectVersion( $collaborationItem );
        $approvalStatus = eZApproveCollaborationHandler::checkApproval( $collaborationID );
        if ( $approvalStatus == EZ_COLLABORATION_APPROVE_STATUS_WAITING )
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'approval still waiting' );
            return EZ_WORKFLOW_TYPE_STATUS_DEFERRED_TO_CRON_REPEAT;
        }
        else if ( $approvalStatus == EZ_COLLABORATION_APPROVE_STATUS_ACCEPTED )
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'approval was accepted' );
            $status = EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
        }
        else if ( $approvalStatus == EZ_COLLABORATION_APPROVE_STATUS_DENIED or
                  $approvalStatus == EZ_COLLABORATION_APPROVE_STATUS_DEFERRED )
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, 'approval was denied' );
            $contentObjectVersion->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
            $status = EZ_WORKFLOW_TYPE_STATUS_WORKFLOW_CANCELLED;
        }
        else
        {
            eZDebugSetting::writeDebug( 'kernel-workflow-approve', $event, "approval unknown status '$approvalStatus'" );
            $contentObjectVersion->setAttribute( 'status', EZ_VERSION_STATUS_REJECTED );
            $status = EZ_WORKFLOW_TYPE_STATUS_WORKFLOW_CANCELLED;
        }
        $contentObjectVersion->sync();
        if ( $approvalStatus != EZ_COLLABORATION_APPROVE_STATUS_DEFERRED )
            $db->query( 'DELETE FROM ezapprove_items WHERE workflow_process_id = ' . $process->attribute( 'id' )  );
        return $status;
    }
}

eZWorkflowEventType::registerType( EZ_WORKFLOW_TYPE_APPROVE_ID, "ezapprovetype" );

?>
