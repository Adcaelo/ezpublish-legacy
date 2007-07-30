<?php
//
// Definition of Settings class
//
// Created on: <14-May-2003 16:30:26 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.10.x
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

/*! \file settings.php
*/

include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
include_once( 'kernel/common/template.php' );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( "lib/ezdb/classes/ezdb.php" );

$http = eZHTTPTool::instance();

$Module =& $Params['Module'];

$user = eZUser::currentUser();

include_once( 'kernel/classes/notification/eznotificationeventfilter.php' );
$availableHandlers =& eZNotificationEventFilter::availableHandlers();


$db = eZDB::instance();
$db->begin();
if ( $http->hasPostVariable( 'Store' ) )
{
    foreach ( array_keys( $availableHandlers ) as $key )
    {
        $handler =& $availableHandlers[$key];
        $handler->storeSettings( $http, $Module );
    }

}

foreach ( array_keys( $availableHandlers ) as $key )
{
    $handler =& $availableHandlers[$key];

    $handler->fetchHttpInput( $http, $Module );
}
$db->commit();

$viewParameters = array( 'offset' => $Params['Offset'] );

$tpl = templateInit();
$tpl->setVariable( 'user', $user );
$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:notification/settings.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/notification', 'Notification settings' ) ) );


?>
