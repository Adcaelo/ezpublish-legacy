<?php
//
// Created on: <22-Aug-2002 16:38:41 sp>
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

/*! \file view.php
*/

include_once( "kernel/classes/ezmodulemanager.php" );
include_once( "kernel/classes/ezrole.php" );
include_once( "kernel/classes/ezsearch.php" );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( "lib/ezutils/classes/ezhttppersistence.php" );
include_once( "lib/ezutils/classes/ezmodule.php" );
include_once( "kernel/classes/ezcontentobjecttreenode.php" );

include_once( "kernel/common/template.php" );

$http =& eZHTTPTool::instance();
$Module =& $Params["Module"];
$roleID =& $Params["RoleID"];

$role =& eZRole::fetch( $roleID );

// Redirect to content node browse in the user tree
if ( $http->hasPostVariable( "AssignRoleButton" )  )
{
    $http->setSessionVariable( "BrowseFromPage", "/role/view/" . $roleID . "/" );

    $http->setSessionVariable( "BrowseActionName", "AssignRole" );
    $http->setSessionVariable( "BrowseReturnType", "ObjectID" );

    $Module->redirectTo( "/content/browse/5/" );
    return;
}

// Assign the role for a user or group
if ( $http->hasPostVariable( "BrowseActionName" ) and
     $http->postVariable( "BrowseActionName" ) == "AssignRole" )
{
    $selectedObjectIDArray = $http->postVariable( "SelectedObjectIDArray" );

    $assignedUserIDArray =& $role->fetchUserID();
    foreach ( $selectedObjectIDArray as $objectID )
    {
        if ( !in_array(  $objectID, $assignedUserIDArray ) )
        {
            $role->assignToUser( $objectID );
        }
    }
}

// Remove the role assignment
if ( $http->hasPostVariable( "RemoveRoleAssignmentButton" )  )
{
    $userIDArray = $http->postVariable( "UserIDArray" );

    foreach ( $userIDArray as $userID )
    {
        $role->removeUserAssignment( $userID );
    }
}

$tpl =& templateInit();

$userArray =& $role->fetchUserByRole();

$policies = $role->attribute( 'policies' );
$tpl->setVariable( "policies", $policies );
$tpl->setVariable( "module", $Module );
$tpl->setVariable( "role", $role );

$tpl->setVariable( "user_array", $userArray );

$Module->setTitle( "View role - " . $role->attribute( "name" ) );

$Result = array();
$Result['content'] =& $tpl->fetch( 'design:role/view.tpl' );
$Result['path'] = array( array( 'text' => 'Role',
                                'url' => 'role/list' ),
                         array( 'text' => $role->attribute( 'name' ),
                                'url' => false ) );

?>
