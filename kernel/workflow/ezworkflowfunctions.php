<?php
//
// Created on: <27-Sep-2004 11:41:73 jk>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.6.x
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

/*! \file ezworkflowfunctions.php
*/

class eZWorkflowFunctions
{
    function addGroup( $workflowID, $workflowVersion, $selectedGroup )
    {
        include_once( "kernel/classes/ezworkflowgrouplink.php" );

        list ( $groupID, $groupName ) = split( "/", $selectedGroup );
        $ingroup =& eZWorkflowGroupLink::create( $workflowID, $workflowVersion, $groupID, $groupName );
        $ingroup->store();
        return true;
    }

    function removeGroup( $workflowID, $workflowVersion, $selectedGroup )
    {
        include_once( "kernel/classes/ezworkflow.php" );
        include_once( "kernel/classes/ezworkflowgrouplink.php" );

        $workflow =& eZWorkflow::fetch( $workflowID );
        if ( !$workflow )
            return false;
        $groups = $workflow->attribute( 'ingroup_list' );
        foreach ( array_keys( $groups ) as $key )
        {
            if ( in_array( $groups[$key]->attribute( 'group_id' ), $selectedGroup ) )
            {
                unset( $groups[$key] );
            }
        }

        if ( count( $groups ) == 0 )
        {
            return false;
        }
        else
        {
            $db =& eZDB::instance();
            $db->begin();
            foreach(  $selectedGroup as $group_id )
            {
                eZWorkflowGroupLink::remove( $workflowID, $workflowVersion, $group_id );
            }
            $db->commit();
        }
        return true;
    }
}

?>
