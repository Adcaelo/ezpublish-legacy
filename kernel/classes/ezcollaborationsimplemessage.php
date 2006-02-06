<?php
//
// Definition of eZCollaborationSimpleMessage class
//
// Created on: <24-Jan-2003 15:38:57 amos>
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

/*! \file ezcollaborationsimplemessage.php
*/

/*!
  \class eZCollaborationSimpleMessage ezcollaborationsimplemessage.php
  \brief The class eZCollaborationSimpleMessage does

*/

include_once( 'kernel/classes/ezpersistentobject.php' );

class eZCollaborationSimpleMessage extends eZPersistentObject
{
    /*!
     Constructor
    */
    function eZCollaborationSimpleMessage( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'message_type' => array( 'name' => 'MessageType',
                                                                  'datatype' => 'string',
                                                                  'default' => '',
                                                                  'required' => true ),
                                         'data_text1' => array( 'name' => 'DataText1',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text2' => array( 'name' => 'DataText2',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text3' => array( 'name' => 'DataText3',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_int1' => array( 'name' => 'DataInt1',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_int2' => array( 'name' => 'DataInt2',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_int3' => array( 'name' => 'DataInt3',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_float1' => array( 'name' => 'DataFloat1',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_float2' => array( 'name' => 'DataFloat2',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_float3' => array( 'name' => 'DataFloat3',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'creator_id' => array( 'name' => 'CreatorID',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'created' => array( 'name' => 'Created',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'modified' => array( 'name' => 'Modified',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ) ),
                      'keys' => array( 'id' ),
                      'function_attributes' => array( 'participant' => 'participant' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZCollaborationSimpleMessage',
                      'name' => 'ezcollab_simple_message' );
    }

    function &create( $type, $text = false, $creatorID = false )
    {
        $date_time = time();
        if ( $creatorID === false )
        {
            include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
            $user =& eZUser::currentUser();
            $creatorID =& $user->attribute( 'contentobject_id' );
        }
        $row = array( 'message_type' => $type,
                      'data_text1' => $text,
                      'creator_id' => $creatorID,
                      'created' => $date_time,
                      'modified' => $date_time );
        $object = new eZCollaborationSimpleMessage( $row );
        return $object;
    }

    function fetch( $id, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZCollaborationSimpleMessage::definition(),
                                                null,
                                                array( "id" => $id ),
                                                $asObject );
    }

    function &participant()
    {
        // TODO: Get participant trough participant link from item
        $retValue = null;
        return $retValue;
    }

    /// \privatesection
    var $ID;
    var $ParticipantID;
    var $Created;
    var $Modified;
    var $DataText1;
    var $DataText2;
    var $DataText3;
    var $DataInt1;
    var $DataInt2;
    var $DataInt3;
    var $DataFloat1;
    var $DataFloat2;
    var $DataFloat3;
}

?>
