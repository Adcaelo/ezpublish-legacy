<?php
//
// Definition of eZForgotPassword class
//
// Created on: <17-���-2003 11:40:49 sp>
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

/*! \file ezforgotpassword.php
*/

/*!
  \class eZForgotPassword ezforgotpassword.php
  \ingroup eZDatatype
  \brief The class eZForgotPassword does

*/

class eZForgotPassword extends eZPersistentObject
{
    /*!
     Constructor
    */
    function eZForgotPassword( $row = array() )
    {
        $this->eZPersistentObject( $row );
    }

    function &definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "user_id" => array( 'name' => "UserID",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "hash_key" => array( 'name' => "HashKey",
                                                              'datatype' => 'string',
                                                              'default' => '',
                                                              'required' => true ),
                                         "time" => array( 'name' => "Time",
                                                          'datatype' => 'integer',
                                                          'default' => 0,
                                                          'required' => true ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( ),
                      "increment_key" => "id",
                      "sort" => array( "id" => "asc" ),
                      "class_name" => "eZForgotPassword",
                      "name" => "ezforgot_password" );
    }

    function &createNew( $userID, $hashKey, $time)
    {
        return new eZForgotPassword( array( "user_id" => $userID,
                                            "hash_key" => $hashKey,
                                            "time" => $time ) );
    }

    function &fetchByKey( $hashKey )
    {
        return eZPersistentObject::fetchObject( eZForgotPassword::definition(),
                                                null,
                                                array( "hash_key" => $hashKey ),
                                                true );
    }

    /*!
     \static
     Removes all password reminders in the database.
    */
    function cleanup()
    {
        $db =& eZDB::instance();
        $db->query( "DELETE FROM ezforgot_password" );
    }

    /*!
     Remove forgot password entries belonging to user \a $userID
    */
    function &remove( $userID = false )
    {
        if ( $userID === false )
        {
            if ( get_class( $this ) == 'ezforgotpassword' )
            {
                eZPersistentObject::removeObject( eZForgotPassword::definition(),
                                                  array( 'id' => $this->attribute( 'id' ) ) );
            }
        }
        else
        {
            eZPersistentObject::removeObject( eZForgotPassword::definition(),
                                              array( 'user_id' => $userID ) );
        }
    }

}

?>
