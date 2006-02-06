<?php
//
// Created on: <26-May-2003 09:04:14 sp>
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

/*! \file notification.php
*/

include_once( 'kernel/classes/notification/eznotificationeventfilter.php' );
include_once( 'kernel/classes/notification/eznotificationevent.php' );
include_once( "lib/ezdb/classes/ezdb.php" );

$event =& eZNotificationEvent::create( 'ezcurrenttime', array() );

$db =& eZDB::instance();
$db->begin();

$event->store();
if ( !$isQuiet )
    $cli->output( "Starting notification event processing" );
eZNotificationEventFilter::process();

$db->commit();


if ( !$isQuiet )
    $cli->output( "Done" );

?>
