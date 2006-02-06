<?php
//
// Definition of eZCollaborationEventType class
//
// Created on: <12-May-2003 13:29:25 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.7.x
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

/*! \file ezcollaborationeventtype.php
*/

/*!
  \class eZCollaborationEventType ezcollaborationeventtype.php
  \brief The class eZCollaborationEventType does

*/
define( 'EZ_NOTIFICATIONTYPESTRING_COLLABORATION', 'ezcollaboration' );

include_once( 'kernel/classes/notification/eznotificationeventtype.php' );
include_once( 'kernel/classes/ezcollaborationitem.php' );

class eZCollaborationEventType extends eZNotificationEventType
{
    /*!
     Constructor
    */
    function eZCollaborationEventType()
    {
        $this->eZNotificationEventType( EZ_NOTIFICATIONTYPESTRING_COLLABORATION );
    }

    function initializeEvent( &$event, $params )
    {
        eZDebugSetting::writeDebug( 'kernel-notification', $params, 'params for type collaboration' );
        $event->setAttribute( 'data_int1', $params['collaboration_id'] );
        $event->setAttribute( 'data_text1', $params['collaboration_identifier'] );
    }

    function attributes()
    {
        return array_merge( array( 'collaboration_identifier',
                                   'collaboration_id' ),
                            eZNotificationEventType::attributes() );
    }

    function hasAttribute( $attributeName )
    {
        return in_array( $attributeName, $this->attributes() );
    }

    function &attribute( $attributeName )
    {
        if ( $attributeName == 'collaboration_identifier' )
            return eZNotificationEventType::attribute( 'data_text1' );
        else if ( $attributeName == 'collaboration_id' )
            return eZNotificationEventType::attribute( 'data_int1' );
        else
            return eZNotificationEventType::attribute( $attributeName );
    }

    function eventContent( &$event )
    {
        return eZCollaborationItem::fetch( $event->attribute( 'data_int1' ) );
    }
}

eZNotificationEventType::register( EZ_NOTIFICATIONTYPESTRING_COLLABORATION, 'ezcollaborationeventtype' );

?>
