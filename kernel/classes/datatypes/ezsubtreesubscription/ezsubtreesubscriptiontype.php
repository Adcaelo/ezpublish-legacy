<?php
//
// Definition of eZSubtreeSubscriptionType class
//
// Created on: <20-May-2003 11:35:43 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.5.x
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

/*! \file ezsubtreesubscriptiontype.php
*/

/*!
  \class eZSubtreeSubscriptionType ezsubtreesubscriptiontype.php
  \ingroup eZDatatype
  \brief The class eZSubtreeSubscriptionType does

*/
include_once( "kernel/classes/ezdatatype.php" );

define( "EZ_DATATYPESTRING_SUBTREESUBSCRIPTION", "ezsubtreesubscription" );

class eZSubtreeSubscriptionType extends eZDataType
{
    /*!
     Constructor
    */
    function eZSubtreeSubscriptionType()
    {
        $this->eZDataType(  EZ_DATATYPESTRING_SUBTREESUBSCRIPTION, ezi18n( 'kernel/classes/datatypes', "Subtree subscription", 'Datatype name' ),
                            array( 'serialize_supported' => true ) );
    }


    /*!
     Store content
    */
    function onPublish( &$attribute, &$contentObject, &$publishedNodes )
    {
        include_once( 'kernel/classes/notification/handler/ezsubtree/ezsubtreenotificationrule.php' );
        $user =& eZUser::currentUser();
        $address = $user->attribute( 'email' );
        $userID = $user->attribute( 'contentobject_id' );

        $nodeIDList =& eZSubtreeNotificationRule::fetchNodesForUserID( $user->attribute( 'contentobject_id' ), false );

        if ( $attribute->attribute( 'data_int' ) == '1' )
        {
            $newSubscriptions = array();
            foreach ( array_keys( $publishedNodes ) as $key )
            {
                $node =& $publishedNodes[$key];
                if ( !in_array( $node->attribute( 'node_id' ), $nodeIDList ) )
                {
                    $newSubscriptions[] = $node->attribute( 'node_id' );
                }
            }
//             eZDebug::writeDebug( $newSubscriptions, "New subscriptions shell be created" );

            foreach ( $newSubscriptions as $nodeID )
            {

                $rule =& eZSubtreeNotificationRule::create( $nodeID, $userID );
                $rule->store();
            }
        }
        else
        {
            foreach ( array_keys( $publishedNodes ) as $key )
            {
                $node =& $publishedNodes[$key];
                if ( in_array( $node->attribute( 'node_id' ), $nodeIDList ) )
                {
                    eZSubtreeNotificationRule::removeByNodeAndUserID( $user->attribute( 'contentobject_id' ), $node->attribute( 'node_id' ) );
                }
            }
        }
        return true;
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_subtreesubscription_" . $contentObjectAttribute->attribute( "id" ) ))
        {
            $data = $http->postVariable( $base . "_data_subtreesubscription_" . $contentObjectAttribute->attribute( "id" ) );
            if ( isset( $data ) )
                $data = 1;
        }
        else
        {
            $data = 0;
        }
        $contentObjectAttribute->setAttribute( "data_int", $data );
        return true;
    }

    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        return true;
    }

}

eZDataType::register( EZ_DATATYPESTRING_SUBTREESUBSCRIPTION, "ezsubtreesubscriptiontype" );

?>
