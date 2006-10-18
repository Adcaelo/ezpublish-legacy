<?php
//
// Created on: <23-Jan-2003 11:37:30 amos>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.x
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

/*! \file view.php
*/

$Module =& $Params['Module'];
$ViewMode = $Params['ViewMode'];

$Offset = $Params['Offset'];
if ( !is_numeric( $Offset ) )
    $Offset = 0;

include_once( 'kernel/classes/ezcollaborationviewhandler.php' );

if ( !eZCollaborationViewHandler::exists( $ViewMode ) )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

$view =& eZCollaborationViewHandler::instance( $ViewMode );

$template = $view->template();

include_once( 'kernel/classes/ezcollaborationitemhandler.php' );

// $collaborationHandlers =& eZCollaborationItemHandler::fetchList();

$viewParameters = array( 'offset' => $Offset );

include_once( 'kernel/common/template.php' );
$tpl =& templateInit();

$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] = $tpl->fetch( $template );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/collaboration', 'Collaboration' ) ) );

?>
