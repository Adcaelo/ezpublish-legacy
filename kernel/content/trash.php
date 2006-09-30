<?php
//
// Definition of Trash class
//
// Created on: <28-Jan-2003 13:19:47 sp>
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

/*! \file trash.php
*/

include_once( 'kernel/common/template.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( "lib/ezdb/classes/ezdb.php" );

$Module =& $Params['Module'];
$Offset = $Params['Offset'];
if ( isset( $Params['UserParameters'] ) )
{
    $UserParameters = $Params['UserParameters'];
}
else
{
    $UserParameters = array();
}
$viewParameters = array( 'offset' => $Offset, 'namefilter' => false );
$viewParameters = array_merge( $viewParameters, $UserParameters );

$http =& eZHTTPTool::instance();

$user =& eZUser::currentUser();
$userID = $user->id();

if ( $http->hasPostVariable( 'RemoveButton' )  )
{
    if ( $http->hasPostVariable( 'DeleteIDArray' ) )
    {
        $access = $user->hasAccessTo( 'content', 'cleantrash' );
        if ( $access['accessWord'] == 'yes' )
        {
            $deleteIDArray = $http->postVariable( 'DeleteIDArray' );

            $db =& eZDB::instance();
            $db->begin();
            foreach ( $deleteIDArray as $deleteID )
            {

                $objectList = eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                                    null,
                                                                    array( 'id' => $deleteID ),
                                                                    null,
                                                                    null,
                                                                    true );
                eZDebug::writeNotice( $deleteID, "deleteID" );
                foreach ( array_keys( $objectList ) as $key )
                {
                    $object =& $objectList[$key];
                    $object->purge();
                }
            }
            $db->commit();
        }
        else
        {
            return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
        }
    }
}

if ( $http->hasPostVariable( 'EmptyButton' )  )
{
    $access = $user->hasAccessTo( 'content', 'cleantrash' );
    if ( $access['accessWord'] == 'yes' )
    {
        $objectList = eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                             null,
                                             array( 'status' => EZ_CONTENT_OBJECT_STATUS_ARCHIVED ),
                                             null,
                                             null,
                                             true );

        $db =& eZDB::instance();
        $db->begin();
        foreach ( array_keys( $objectList ) as $key )
        {
            $object =& $objectList[$key];
            $object->purge();
        }
        $db->commit();
    }
    else
    {
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }
}

$tpl =& templateInit();
$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] =& $tpl->fetch( 'design:content/trash.tpl' );
$Result['path'] = array( array( 'text' => ezi18n( 'kernel/content', 'Trash' ),
                                'url' => false ) );


?>
