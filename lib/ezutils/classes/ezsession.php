<?php
//
// Definition of eZSession class
//
// Created on: <19-Aug-2002 12:49:18 bf>
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
// http://ez.no/home/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*!
  Re-implementation of PHP session management using database.
*/

function eZSessionOpen( )
{
    // do nothing eZDB will open connection when needed.
}

function eZSessionClose( )
{
    // eZDB will handle closing the database
}

function &eZSessionRead( $key )
{
    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();

    $sessionRes =& $db->arrayQuery( "SELECT data FROM ezsession WHERE session_key='$key'" );

    if ( count( $sessionRes ) == 1 )
    {
        $data =& $sessionRes[0]['data'];

        return $data;
    }
    else
    {
        return false;
    }
}

/*!
  Will write the session information to database.
*/
function eZSessionWrite( $key, $value )
{
    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();
    $ini =& eZIni::instance();
    $expirationTime = time() + $ini->variable( 'Session', 'SessionTimeout' );

    $value =& $db->escapeString( $value );
    // check if session already exists

    $sessionRes =& $db->arrayQuery( "SELECT session_key FROM ezsession WHERE session_key='$key'" );

    if ( count( $sessionRes ) == 1 )
    {
        $updateQuery = "UPDATE ezsession
                    SET expiration_time='$expirationTime', data='$value'
                    WHERE session_key='$key'";

        $ret = $db->query( $updateQuery );
    }
    else
    {
        $insertQuery = "INSERT INTO ezsession
                    ( session_key, expiration_time, data )
                    VALUES ( '$key', '$expirationTime', '$value' )";

        $ret = $db->query( $insertQuery );
    }
}

/*!
  Will remove a session from the database.
*/
function eZSessionDestroy( $key )
{
    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();
    $query = "DELETE FROM ezsession WHERE session_key='$key'";

    $db->query( $query );
}

/*!
  Handles session cleanup. Will delete timed out sessions from the database.
*/
function eZSessionGarbageCollector()
{
    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();
    $query = "DELETE FROM ezsession WHERE expiration_time < " . time();

    $db->query( $query );
}

/*!
 Register the needed session functions.
 Call this only once.
*/
function eZRegisterSessionFunctions()
{
    session_module_name( 'user' );
    session_set_save_handler(
        'ezsessionopen',
        'ezsessionclose',
        'ezsessionread',
        'ezsessionwrite',
        'ezsessiondestroy',
        'ezsessiongarbagecollector' );
}

/*!
 Makes sure that the session is started properly.
 Multiple calls will just be ignored.
*/
function eZSessionStart()
{
    $hasStarted =& $GLOBALS['eZSessionIsStarted'];
    if ( isset( $hasStarted ) and
         $hasStarted )
         return false;
    include_once( 'lib/ezdb/classes/ezdb.php' );
    $db =& eZDB::instance();
    if ( !$db->isConnected() )
        return false;
    eZRegisterSessionFunctions();
    session_start();
//     eZDebug::writeDebug( "Session is started" );
    $hasStarted = true;
    return true;
}

?>
