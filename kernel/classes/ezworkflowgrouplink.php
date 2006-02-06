<?php
//
// Definition of eZContentClass class
//
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

//!! eZKernel
//! The class eZWorkflowGroupLink
/*!

*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezworkflowgroup.php" );

class eZWorkflowGroupLink extends eZPersistentObject
{
    function eZWorkflowGroupLink( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function &definition()
    {
        return array( "fields" => array( "workflow_id" => array( 'name' => "WorkflowID",
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         "workflow_version" => array( 'name' => "WorkflowVersion",
                                                                      'datatype' => 'integer',
                                                                      'default' => 0,
                                                                      'required' => true ),
                                         "group_id" => array( 'name' => "GroupID",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "group_name" => array( 'name' => "GroupName",
                                                                'datatype' => 'string',
                                                                'default' => '',
                                                                'required' => true ) ),
                      "keys" => array( "workflow_id", "workflow_version", "group_id" ),
                      "class_name" => "eZWorkflowGroupLink",
                      "sort" => array( "workflow_id" => "asc" ),
                      "name" => "ezworkflow_group_link" );
    }

    function &create( $workflow_id, $workflow_version, $group_id, $group_name )
    {
        $row = array("workflow_id" => $workflow_id,
                     "workflow_version" => $workflow_version,
                     "group_id" => $group_id,
                     "group_name" => $group_name);
        return new eZWorkflowGroupLink( $row );
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function &remove( $workflow_id, $workflow_version, $group_id )
    {
        eZPersistentObject::removeObject( eZWorkflowGroupLink::definition(),
                                          array("workflow_id" => $workflow_id,
                                                "workflow_version" =>$workflow_version,
                                                "group_id" => $group_id ) );
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function &removeGroupMembers( $group_id )
    {
        eZPersistentObject::removeObject( eZWorkflowGroupLink::definition(),
                                          array( "group_id" => $group_id ) );
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function &removeWorkflowMembers( $workflow_id, $workflow_version )
    {
        eZPersistentObject::removeObject( eZWorkflowGroupLink::definition(),
                                          array( "workflow_id" =>$workflow_id,
                                                 "workflow_version" =>$workflow_version ) );
    }

    function &fetch( $workflow_id, $workflow_version, $group_id, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZWorkflowGroupLink::definition(),
                                                null,
                                                array("workflow_id" => $workflow_id,
                                                      "workflow_version" =>$workflow_version,
                                                      "group_id" => $group_id ),
                                                $asObject );
    }

    function &fetchWorkflowList( $workflow_version, $group_id, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZWorkflowGroupLink::definition(),
                                                    null,
                                                    array( "workflow_version" =>$workflow_version,
                                                           "group_id" => $group_id ),
                                                    null,
                                                    null,
                                                    $asObject);
    }

    function &fetchGroupList( $workflow_id, $workflow_version, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZWorkflowGroupLink::definition(),
                                                    null,
                                                    array( "workflow_id" => $workflow_id,
                                                           "workflow_version" =>$workflow_version ),
                                                    null,
                                                    null,
                                                    $asObject);
    }

    /// \privatesection
    var $WorkflowID;
    var $WorkflowVersion;
    var $GroupID;
    var $GroupName;
}

?>
