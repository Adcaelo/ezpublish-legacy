<?php
//
// Definition of eZUser class
//
// Created on: <10-Jun-2002 17:03:15 bf>
//
// Copyright (C) 1999-2004 eZ systems as. All rights reserved.
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

/*!
  \class eZUser ezuser.php
  \brief eZUser handles eZ publish user accounts
  \ingroup eZKernel

*/

include_once( 'kernel/classes/ezpersistentobject.php' );
include_once( 'kernel/classes/ezrole.php' );
include_once( 'lib/ezutils/classes/ezhttptool.php' );
include_once( "kernel/classes/datatypes/ezuser/ezusersetting.php" );
include_once( "kernel/classes/ezcontentobject.php" );

$ini =& eZINI::instance();
define( 'EZ_USER_ANONYMOUS_ID', $ini->variable( 'UserSettings', 'AnonymousUserID' ) );

/// MD5 of password
define( 'EZ_USER_PASSWORD_HASH_MD5_PASSWORD', 1 );
/// MD5 of user and password
define( 'EZ_USER_PASSWORD_HASH_MD5_USER', 2 );
/// MD5 of site, user and password
define( 'EZ_USER_PASSWORD_HASH_MD5_SITE', 3 );
/// Legacy support for mysql hashed passwords
define( 'EZ_USER_PASSWORD_HASH_MYSQL', 4 );
/// Passwords in plaintext, should not be used for real sites
define( 'EZ_USER_PASSWORD_HASH_PLAINTEXT', 5 );

/// Authenticate by matching the login field
define( 'EZ_USER_AUTHENTICATE_LOGIN', 1 << 0 );
/// Authenticate by matching the email field
define( 'EZ_USER_AUTHENTICATE_EMAIL', 1 << 1 );

define( 'EZ_USER_AUTHENTICATE_ALL', EZ_USER_AUTHENTICATE_LOGIN | EZ_USER_AUTHENTICATE_EMAIL );

$GLOBALS['eZUserBuiltins'] = array( EZ_USER_ANONYMOUS_ID );

class eZUser extends eZPersistentObject
{
    function eZUser( $row )
    {
        $this->eZPersistentObject( $row );
        $this->OriginalPassword = false;
        $this->OriginalPasswordConfirm = false;
    }

    function &definition()
    {
        return array( 'fields' => array( 'contentobject_id' => array( 'name' => 'ContentObjectID',
                                                                      'datatype' => 'integer',
                                                                      'default' => 0,
                                                                      'required' => true ),
                                         'login' => array( 'name' => 'Login',
                                                           'datatype' => 'string',
                                                           'default' => '',
                                                           'required' => true ),
                                         'email' => array( 'name' => 'Email',
                                                           'datatype' => 'string',
                                                           'default' => '',
                                                           'required' => true ),
                                         'password_hash' => array( 'name' => 'PasswordHash',
                                                                   'datatype' => 'string',
                                                                   'default' => '',
                                                                   'required' => true ),
                                         'password_hash_type' => array( 'name' => 'PasswordHashType',
                                                                        'datatype' => 'integer',
                                                                        'default' => 1,
                                                                        'required' => true ) ),
                      'keys' => array( 'contentobject_id' ),
                      'function_attributes' => array( 'contentobject' => 'contentObject',
                                                      'groups' => 'groups',
                                                      'has_stored_login' => 'hasStoredLogin',
                                                      'original_password' => 'originalPassword',
                                                      'original_password_confirm' => 'originalPasswordConfirm',
                                                      'roles' => 'roles',
                                                      'role_id_list' => 'roleIDList',
                                                      'is_logged_in' => 'isLoggedIn'
                                                      ),
                      'relations' => array( 'contentobject_id' => array( 'class' => 'ezcontentobject',
                                                                         'field' => 'id' ) ),
                      'class_name' => 'eZUser',
                      'name' => 'ezuser' );
    }

    function attribute( $name )
    {
        if ( $name == 'groups')
        {
            return $this->groups();
        }
        else if ( $name == 'is_logged_in')
        {
            return $this->isLoggedIn();
        }
        else if ( $name == 'roles')
        {
            return $this->roles();
        }
        else if ( $name == 'role_id_list')
        {
            return $this->roleIDList();
        }
        else if ( $name == 'has_stored_login')
        {
            return $this->hasStoredLogin();
        }
        else if ( $name == 'original_password' )
        {
            return $this->originalPassword();
        }
        else if ( $name == 'original_password_confirm' )
        {
            return $this->originalPasswordConfirm();
        }
        else if ( $name == 'contentobject' )
        {
            if ( $this->ContentObjectID == 0 )
                return null;
            include_once( 'kernel/classes/ezcontentobject.php' );
            return eZContentObject::fetch( $this->ContentObjectID );
        }
        else
            return eZPersistentObject::attribute( $name );
    }

    function &create( $contentObjectID )
    {
        $row = array(
            'contentobject_id' => $contentObjectID,
            'login' => null,
            'email' => null,
            'password_hash' => null,
            'password_hash_type' => null
            );
        return new eZUser( $row );
    }

    function store()
    {
        $this->Email = trim( $this->Email );
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $handler->setTimestamp( 'user-info-cache', mktime() );
        $handler->setTimestamp( 'user-groups-cache', mktime() );
        $handler->setTimestamp( 'user-role-cache', mktime() );
        $handler->store();
        // Clear memory cache
        unset( $GLOBALS["eZUserObject_$userID"] );
        eZPersistentObject::store();
    }

    function &originalPassword()
    {
        return $this->OriginalPassword;
    }

    function setOriginalPassword( $password )
    {
        $this->OriginalPassword = $password;
    }

    function &originalPasswordConfirm()
    {
        return $this->OriginalPasswordConfirm;
    }

    function setOriginalPasswordConfirm( $password )
    {
        $this->OriginalPasswordConfirm = $password;
    }

    function hasStoredLogin()
    {
        $db =& eZDB::instance();
        $contentObjectID = $this->attribute( 'contentobject_id' );
        $sql = "SELECT * FROM ezuser WHERE contentobject_id='$contentObjectID' AND login!=''";
        $rows = $db->arrayQuery( $sql );
        $hasStoredLogin = count( $rows ) > 0;
        eZDebug::writeDebug( $hasStoredLogin, 'hasStoredLogin' );
        return $hasStoredLogin;
    }

    /*!
     Fills in the \a $id, \a $login, \a $email and \a $password for the user
     and creates the proper password hash.
    */
    function setInformation( $id, $login, $email, $password, $passwordConfirm = false )
    {
        $this->setAttribute( "contentobject_id", $id );
        $this->setAttribute( "email", $email );
        $this->setAttribute( "login", $login );
        if ( $password !== false and
             $password !== null and
             $password == $passwordConfirm and
             strlen( $password ) >= 3 ) // Cannot change login or password_hash without login and password
        {
            $this->setAttribute( "password_hash", eZUser::createHash( $login, $password, eZUser::site(),
                                                                      eZUser::hashType() ) );
            $this->setAttribute( "password_hash_type", eZUser::hashType() );
        }
        else
        {
            $this->setOriginalPassword( $password );
            $this->setOriginalPasswordConfirm( $passwordConfirm );
        }
    }

    function &fetch( $id, $asObject = true )
    {
        $user =& eZPersistentObject::fetchObject( eZUser::definition(),
                                                  null,
                                                  array( 'contentobject_id' => $id ),
                                                  $asObject );
        return $user;
    }

    function &fetchByName( $login, $asObject = true )
    {
        $user =& eZPersistentObject::fetchObject( eZUser::definition(),
                                                  null,
                                                  array( 'login' => $login ),
                                                  $asObject );
        return $user;
    }

    function &fetchByEmail( $email, $asObject = true )
    {
        $user =& eZPersistentObject::fetchObject( eZUser::definition(),
                                                  null,
                                                  array( 'email' => $email ),
                                                  $asObject );
        return $user;
    }

    function &removeUser( $userID )
    {
        eZPersistentObject::removeObject( eZUser::definition(),
                                          array( 'contentobject_id' => $userID ) );
    }

    /*!
     \return a list of valid and enabled users, the data returned is an array
             with ezcontentobject database data.
    */
    function &fetchContentList()
    {
        $contentObjectStatus = EZ_CONTENT_OBJECT_STATUS_PUBLISHED;
        $query = "SELECT ezcontentobject.*
                  FROM ezuser, ezcontentobject, ezuser_setting
                  WHERE ezcontentobject.status = '$contentObjectStatus' AND
                        ezuser_setting.is_enabled = 1 AND
                        ezcontentobject.id = ezuser.contentobject_id AND
                        ezuser_setting.user_id = ezuser.contentobject_id";
        $db =& eZDB::instance();
        $rows =& $db->arrayQuery( $query );
        return $rows;
    }

    /*!
     \static
     \return the default hash type which is specified in UserSettings/HashType in site.ini
    */
    function hashType()
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $ini =& eZINI::instance();
        $type = strtolower( $ini->variable( 'UserSettings', 'HashType' ) );
        if ( $type == 'md5_site' )
            return EZ_USER_PASSWORD_HASH_MD5_SITE;
        else if ( $type == 'md5_user' )
            return EZ_USER_PASSWORD_HASH_MD5_USER;
        else if ( $type == 'plaintext' )
            return EZ_USER_PASSWORD_HASH_PLAINTEXT;
        else
            return EZ_USER_PASSWORD_HASH_MD5_PASSWORD;
    }

    /*!
     \static
     \return the site name used in password hashing.
    */
    function site()
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $ini =& eZINI::instance();
        return $ini->variable( 'UserSettings', 'SiteName' );
    }

    /*!
     Fetches a builtin user and returns it, this helps avoid special cases where
     user is not logged in.
    */
    function &fetchBuiltin( $id )
    {
        if ( !in_array( $id, $GLOBALS['eZUserBuiltins'] ) )
            $id = EZ_USER_ANONYMOUS_ID;
        $builtinInstance =& $GLOBALS["eZUserBuilitinInstance-$id"];
        if ( get_class( $builtinInstance ) != 'ezuser' )
        {
            include_once( 'lib/ezutils/classes/ezini.php' );
            $builtinInstance =  eZUser::fetch( EZ_USER_ANONYMOUS_ID );
        }
        return $builtinInstance;
    }


    /*!
     \return the user id.
    */
    function id()
    {
        return $this->ContentObjectID;
    }

    /*!
     \return a bitfield which decides the authenticate methods.
    */
    function authenticationMatch()
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $ini =& eZINI::instance();
        $matchArray = $ini->variableArray( 'UserSettings', 'AuthenticateMatch' );
        $match = 0;
        foreach ( $matchArray as $matchItem )
        {
            switch ( $matchItem )
            {
                case "login":
                {
                    $match = ( $match | EZ_USER_AUTHENTICATE_LOGIN );
                } break;
                case "email":
                {
                    $match = ( $match | EZ_USER_AUTHENTICATE_EMAIL );
                } break;
            }
        }
        return $match;
    }

    /*!
     \return \c true if there can only be one instance of an email address on the site.
    */
    function requireUniqueEmail()
    {
        $ini =& eZINI::instance();
        return $ini->variable( 'UserSettings', 'RequireUniqueEmail' ) == 'true';
    }

    /*!
    \static
     Logs in the user if applied username and password is
     valid. The userID is returned if succesful, false if not.
    */
    function &loginUser( $login, $password, $authenticationMatch = false )
    {
        $http =& eZHTTPTool::instance();
        $db =& eZDB::instance();

        if ( $authenticationMatch === false )
            $authenticationMatch = eZUser::authenticationMatch();

        $loginEscaped = $db->escapeString( $login );

        $loginArray = array();
        if ( $authenticationMatch & EZ_USER_AUTHENTICATE_LOGIN )
            $loginArray[] = "login='$loginEscaped'";
        if ( $authenticationMatch & EZ_USER_AUTHENTICATE_EMAIL )
            $loginArray[] = "email='$loginEscaped'";
        if ( count( $loginArray ) == 0 )
            $loginArray[] = "login='$loginEscaped'";
        $loginText = implode( ' OR ', $loginArray );

        $contentObjectStatus = EZ_CONTENT_OBJECT_STATUS_PUBLISHED;

        $ini =& eZINI::instance();
        $databaseImplementation = $ini->variable( 'DatabaseSettings', 'DatabaseImplementation' );
        // if mysql
        if ( $databaseImplementation == "ezmysql" )
        {
            $query = "SELECT contentobject_id, password_hash, password_hash_type, email, login
                      FROM ezuser, ezcontentobject
                      WHERE ( $loginText ) AND
                        ezcontentobject.status='$contentObjectStatus' AND
                        ( ezcontentobject.id=contentobject_id OR ( password_hash_type=4 AND ( $loginText ) AND password_hash=PASSWORD('$password') ) )";
        }
        else
        {
            $query = "SELECT contentobject_id, password_hash, password_hash_type, email, login
                      FROM ezuser, ezcontentobject
                      WHERE ( $loginText ) AND
                            ezcontentobject.status='$contentObjectStatus' AND
                            ezcontentobject.id=contentobject_id";
        }

        $users =& $db->arrayQuery( $query );
        $exists = false;
        if ( count( $users ) >= 1 )
        {
            include_once( 'lib/ezutils/classes/ezini.php' );
            $ini =& eZINI::instance();
            foreach ( array_keys( $users ) as $key )
            {
                $userRow =& $users[$key];
                $userID = $userRow['contentobject_id'];
                $hashType = $userRow['password_hash_type'];
                $hash = $userRow['password_hash'];
                $exists = eZUser::authenticateHash( $userRow['login'], $password, eZUser::site(),
                                                    $hashType,
                                                    $hash );

                // If hash type is MySql
                if ( $hashType == EZ_USER_PASSWORD_HASH_MYSQL and $databaseImplementation == "ezmysql" )
                {
                    $queryMysqlUser = "SELECT contentobject_id, password_hash, password_hash_type, email, login
                              FROM ezuser, ezcontentobject
                              WHERE ezcontentobject.status='$contentObjectStatus' AND
                                    password_hash_type=4 AND ( $loginText ) AND password_hash=PASSWORD('$password') ";
                    $mysqlUsers =& $db->arrayQuery( $queryMysqlUser );
                    if ( count( $mysqlUsers ) >= 1 )
                        $exists = true;
                }

                eZDebugSetting::writeDebug( 'kernel-user', eZUser::createHash( $userRow['login'], $password, eZUser::site(),
                                                                               $hashType ), "check hash" );
                eZDebugSetting::writeDebug( 'kernel-user', $hash, "stored hash" );
                if ( $exists )
                {
                    $userSetting = eZUserSetting::fetch( $userID );
                    $isEnabled = $userSetting->attribute( "is_enabled" );
                    if ( $hashType != eZUser::hashType() and
                         strtolower( $ini->variable( 'UserSettings', 'UpdateHash' ) ) == 'true' )
                    {
                        $hashType = eZUser::hashType();
                        $hash = eZUser::createHash( $login, $password, eZUser::site(),
                                                    $hashType );
                        $db->query( "UPDATE ezuser SET password_hash='$hash', password_hash_type='$hashType' WHERE contentobject_id='$userID'" );
                    }
                    break;
                }
            }
        }
        if ( $exists and $isEnabled )
        {
            eZDebugSetting::writeDebug( 'kernel-user', $userRow, 'user row' );
            $user =& new eZUser( $userRow );
            eZDebugSetting::writeDebug( 'kernel-user', $user, 'user' );
            $userID = $user->attribute( 'contentobject_id' );
            $GLOBALS["eZUserGlobalInstance_$userID"] =& $user;
            $http->setSessionVariable( 'eZUserLoggedInID', $userRow['contentobject_id'] );
            eZSessionRegenerate();
            $user->cleanup();
            return $user;
        }
        else
            return false;
    }

    /*!
     Cleans up any cache or session variables that are set.
     This at least called on login and logout but can be used other places
     where you must ensure that the cache user values are refetched.
     \note If $contentObjectID is \c false or not supplied the ID will be fetch from \c $this.
    */
    function cleanup( $contentObjectID = false )
    {
        $http =& eZHTTPTool::instance();
        $http->setSessionVariable( 'eZUserGroupsCache_Timestamp', false );
        if ( !$contentObjectID )
            $contentObjectID = $this->attribute( 'contentobject_id' );
        $http->removeSessionVariable( 'eZUserGroupsCache' );

        $http->removeSessionVariable( 'eZUserInfoCache' );

        $http->removeSessionVariable( 'UserPolicies' );
        $http->removeSessionVariable( 'UserRoles' );
        $http->removeSessionVariable( 'UserLimitations' );
        $http->removeSessionVariable( 'UserLimitationValues' );
        $http->removeSessionVariable( 'CanInstantiateClassesCachedForUser' );
        $http->removeSessionVariable( 'CanInstantiateClassList' );
        $http->removeSessionVariable( 'ClassesCachedForUser' );

        // Note: This must be done more generic with an internal
        //       callback system.
        include_once( 'kernel/classes/ezpreferences.php' );
        eZPreferences::sessionCleanup();
    }

    /*!
     \return logs in the current user object
    */
    function loginCurrent()
    {
        eZHTTPTool::setSessionVariable( 'eZUserLoggedInID', $this->ContentObjectID );
        $this->cleanup();
    }

    /*!
     Logs out the current user
    */
    function logoutCurrent()
    {
        $http =& eZHTTPTool::instance();
        $id = false;
        $GLOBALS["eZUserGlobalInstance_$id"] = false;
        $contentObjectID = $http->sessionVariable( "eZUserLoggedInID" );
        $http->removeSessionVariable( "eZUserLoggedInID" );
        if ( $contentObjectID )
            eZUser::cleanup( $contentObjectID );
    }

    /*!
     Finds the user with the id \a $id and returns the unique instance of it.
     If the user instance is not created yet it tries to either fetch it from the
     database with eZUser::fetch(). If $id is false or the user was not found, the
     default user is returned. This is a site.ini setting under UserSettings:AnonymousUserID.
     The instance is then returned.
     If \a $id is false then the current user is fetched.
    */
    function &instance( $id = false )
    {
        $currentUser =& $GLOBALS["eZUserGlobalInstance_$id"];
        if ( get_class( $currentUser ) == 'ezuser' )
        {
            return $currentUser;
        }

        $http =& eZHTTPTool::instance();
        // If not specified get the current user
        if ( $id === false )
        {
            $id = $http->sessionVariable( 'eZUserLoggedInID' );

            if ( !is_numeric( $id ) )
                $id = EZ_USER_ANONYMOUS_ID;
        }

        // Check cache
        $fetchFromDB = true;

        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $expiredTimeStamp = 0;
        if ( $handler->hasTimestamp( 'user-info-cache' ) )
            $expiredTimeStamp = $handler->timestamp( 'user-info-cache' );

        $userArrayTimestamp =& $http->sessionVariable( 'eZUserInfoCache_Timestamp' );

        if ( $userArrayTimestamp > $expiredTimeStamp )
        {
            $userInfo = array();
            if ( $http->hasSessionVariable( 'eZUserInfoCache' ) )
                $userInfo =& $http->sessionVariable( 'eZUserInfoCache' );

            if ( isset( $userInfo[$id] ) )
            {
                $userArray =& $userInfo[$id];

                if ( is_numeric( $userArray['contentobject_id'] ) )
                {
                    $currentUser = new eZUser( $userArray );
                    $fetchFromDB = false;
                }
            }
        }

        if ( $fetchFromDB == true )
        {
            $currentUser =& eZUser::fetch( $id );

            if ( $currentUser )
            {
                $userInfo = array();
                $userInfo[$id] = array( 'contentobject_id' => $currentUser->attribute( 'contentobject_id' ),
                                        'login' => $currentUser->attribute( 'login' ),
                                        'email' => $currentUser->attribute( 'email' ),
                                        'password_hash' => $currentUser->attribute( 'password_hash' ),
                                        'password_hash_type' => $currentUser->attribute( 'password_hash_type' )
                                        );
                $http->setSessionVariable( 'eZUserInfoCache', $userInfo );
                $http->setSessionVariable( 'eZUserInfoCache_Timestamp', mktime()  );
            }
        }

        if ( !$currentUser )
        {
            $currentUser = new eZUser( array( 'id' => -1, 'login' => 'NoUser' ) );

            eZDebug::writeWarning( 'User not found, returning anonymous' );
        }

        return $currentUser;
    }

    /*!
     \return \c true if the user is enabled and can be used on the site.
    */
    function isEnabled()
    {
        $setting =& eZUserSetting::fetch( $this->attribute( 'contentobject_id' ) );
        if ( $setting and !$setting->attribute( 'is_enabled' ) )
        {
            return false;
        }
        return true;
    }

    /*!
     \static
     Returns the currently logged in user.
    */
    function &currentUser()
    {
        $user =& eZUser::instance();
        return $user;
    }

    /*!
     \static
     Returns the ID of the currently logged in user.
    */
    function &currentUserID()
    {
        $user =& eZUser::instance();
        if ( !$user )
            return 0;
        return $user->attribute( 'contentobject_id' );
    }

    /*!
     \static
     Creates a hash out of \a $user, \a $password and \a $site according to the type \a $type.
     \return true if the generated hash is equal to the supplied hash \a $hash.
    */
    function authenticateHash( $user, $password, $site, $type, $hash )
    {
        return eZUser::createHash( $user, $password, $site, $type ) == $hash;
    }

    /*!
     \static
     \return an array with characters which are allowed in password.
    */
    function passwordCharacterTable()
    {
        $table =& $GLOBALS['eZUserPasswordCharacterTable'];
        if ( isset( $table ) )
            return $table;
        $table = array();
        for ( $i = ord( 'a' ); $i <= ord( 'z' ); ++$i )
        {
            $char = chr( $i );
            $table[] = $char;
            $table[] = strtoupper( $char );
        }
        for ( $i = 0; $i <= 9; ++$i )
        {
            $table[] = "$i";
        }
        $ini =& eZINI::instance();
        if ( $ini->variable( 'UserSettings', 'UseSpecialCharacters' ) == 'true' )
        {
            $specialCharacters = '!#%&{[]}+?;:*';
            for ( $i = 0; $i < strlen( $specialCharacters ); ++$i )
            {
                $table[] = $specialCharacters[$i];
            }
        }
        // Remove some characters that are too similar visually
        $table = array_diff( $table, array( 'I', 'l', 'o', 'O', '0' ) );
        $tableTmp = $table;
        $table = array();
        foreach ( $tableTmp as $item )
        {
            $table[] = $item;
        }
        return $table;
    }

    /*!
     \static
     Creates a password with number of characters equal to \a $passwordLength and returns it.
     If you want pass a value in \a $seed it will be used as basis for the password, if not
     it will use the current time value as seed.
     \note If \a $passwordLength exceeds 16 it will need to generate new seed for the remaining
           characters.
    */
    function createPassword( $passwordLength, $seed = false )
    {
        $chars = 0;
        $password = '';
        if ( $passwordLength < 1 )
            $passwordLength = 1;
        $decimal = 0;
        while ( $chars < $passwordLength )
        {
            if ( $seed == false )
                $seed = mktime();
            $text = md5( $seed );
            $characterTable = eZUser::passwordCharacterTable();
            $tableCount = count( $characterTable );
            for ( $i = 0; ( $chars < $passwordLength ) and $i < 32; ++$chars, $i += 2 )
            {
                $decimal += hexdec( substr( $text, $i, 2 ) );
                $index = ( $decimal % $tableCount );
                $character = $characterTable[$index];
                $password .= $character;
            }
            $seed = false;
        }
        return $password;
    }

    /*!
     \static
     Will create a hash of the given string. This is used to store the passwords in the database.
    */
    function createHash( $user, $password, $site, $type )
    {
        $str = '';
//         eZDebugSetting::writeDebug( 'kernel-user', "'$user' '$password' '$site'", "ezuser($type)" );
        if( $type == EZ_USER_PASSWORD_HASH_MD5_USER )
        {
            $str = md5( "$user\n$password" );
        }
        else if ( $type == EZ_USER_PASSWORD_HASH_MD5_SITE )
        {
            $str = md5( "$user\n$password\n$site" );
        }
        else if ( $type == EZ_USER_PASSWORD_HASH_MYSQL )
        {
            // Do some MySQL stuff here
        }
        else if ( $type == EZ_USER_PASSWORD_HASH_PLAINTEXT )
        {
            $str = $password;
        }
        else // EZ_USER_PASSWORD_HASH_MD5_PASSWORD
        {
            $str = md5( $password );
        }
        eZDebugSetting::writeDebug( 'kernel-user', $str, "ezuser($type)" );
        return $str;
    }

    function &hasAccessTo( $module, $function )
    {
        $roles =& $this->attribute( 'roles' );
        $access = 'no';
        $limitationPolicyList = array();
        reset( $roles );
        foreach ( array_keys( $roles ) as $key )
        {
            $role =& $roles[$key];
            $policies =& $role->attribute( 'policies');
            foreach ( array_keys( $policies ) as $policy_key )
            {
                $policy =& $policies[$policy_key];
                if ( $policy->attribute( 'module_name' ) == '*' )
                {
                    return array( 'accessWord' => 'yes' );
                }
                elseif ( $policy->attribute( 'module_name' ) == $module )
                {
                    if ( $policy->attribute( 'function_name' ) == '*' )
                    {
                        return array( 'accessWord' => 'yes' );
                    }
                    elseif ( $policy->attribute( 'function_name' ) == $function )
                    {
                        if ( $policy->attribute( 'limitation' ) == '*' )
                        {
                            return array( 'accessWord' => 'yes' );
                        }
                        else
                        {
                            $access = 'limited';
                            $limitationPolicyList[] =& $policy;
                        }
                    }
                }
            }
        }
        return array( 'accessWord' => $access, 'policies' => $limitationPolicyList );
    }

    function &policies()
    {
        $roles =& $this->attribute( 'roles' );
        $limitationPolicyList = array();
        reset( $roles );
        foreach ( array_keys( $roles ) as $key )
        {
            $role =& $roles[$key];
            $policies =& $role->attribute( 'policies');
            foreach ( array_keys( $policies ) as $policy_key )
            {
                $policy =& $policies[$policy_key];
                $limitationPolicyList[] =& $policy;
            }
        }
        return $limitationPolicyList;
    }

    /*!
     \return an array of roles which the user is assigned to
    */
    function &roles()
    {
        if ( !isset( $this->Roles ) )
        {
            $groups = $this->attribute( 'groups' );
            $groups[] = $this->attribute( 'contentobject_id' );
            $roles =& eZRole::fetchByUser( $groups );
            $this->Roles =& $roles;
        }
        return $this->Roles;
    }

    /*!
     \return an array of role ids which the user is assigned to
    */
    function &roleIDList()
    {
        $groups = $this->attribute( 'groups' );
        $groups[] = $this->attribute( 'contentobject_id' );
        $roleList = eZRole::fetchIDListByUser( $groups );
        return $roleList;
    }

    /*!
     Returns true if it's a real user which is logged in. False if the user
     is the default user or the fallback buildtin user.
    */
    function &isLoggedIn()
    {
        $return = true;
        if ( $this->ContentObjectID == EZ_USER_ANONYMOUS_ID or
             $this->ContentObjectID == -1
             )
            $return = false;
        return $return;
    }

    /*!
     \return an array of id's with all the groups the user belongs to.
    */
    function &groups( $asObject = false, $userID = false )
    {
        $db =& eZDB::instance();
        $http =& eZHTTPTool::instance();

        if ( $asObject == true )
        {
            $this->Groups = array();
            if ( !isset( $this->GroupsAsObjects ) )
            {
                if ( $userID )
                {
                    $contentobjectID = $userID;
                }
                else
                {
                    $contentobjectID = $this->attribute( 'contentobject_id' );
                }
                $userGroups =& $db->arrayQuery( "SELECT d.*
                                                FROM ezcontentobject_tree  b,
                                                     ezcontentobject_tree  c,
                                                     ezcontentobject d
                                                WHERE b.contentobject_id='$contentobjectID' AND
                                                      b.parent_node_id = c.node_id AND
                                                      d.id = c.contentobject_id
                                                ORDER BY c.contentobject_id  ");
                $userGroupArray = array();

                foreach ( $userGroups as $group )
                {
                    $userGroupArray[] = new eZContentObject( $group );
                }
                $this->GroupsAsObjects =& $userGroupArray;
            }
            return $this->GroupsAsObjects;
        }
        else
        {
            if ( !isset( $this->Groups ) )
            {
                if ( $userID )
                {
                    $contentobjectID = $userID;
                }
                else
                {
                    $contentobjectID = $this->attribute( 'contentobject_id' );
                }

                $userGroups = false;

                $userGroupTimestamp =& $http->sessionVariable( 'eZUserGroupsCache_Timestamp' );

                include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
                $handler =& eZExpiryHandler::instance();
                $expiredTimeStamp = 0;
                if ( $handler->hasTimestamp( 'user-info-cache' ) )
                    $expiredTimeStamp = $handler->timestamp( 'user-info-cache' );

                // check for cached version
                if ( $userGroupTimestamp > $expiredTimeStamp )
                {
                    $userGroupsInfo = array();
                    if ( $http->hasSessionVariable( 'eZUserGroupsCache' ) )
                        $userGroupsInfo =& $http->sessionVariable( 'eZUserGroupsCache' );

                    if ( isset( $userGroupsInfo[$contentobjectID] ) )
                    {
                        $userGroupsTmp =& $userGroupsInfo[$contentobjectID];

                        if ( count( $userGroupsTmp ) > 0 )
                        {
                            $userGroups =& $userGroupsTmp;
                        }
                    }
                }

                if ( $userGroups === false or
                     count( $userGroups ) == 0 )
                {
                    $userGroupsInfo = array();
                    $userGroups =& $db->arrayQuery( "SELECT  c.contentobject_id as id
                                                FROM ezcontentobject_tree  b,
                                                     ezcontentobject_tree  c
                                                WHERE b.contentobject_id='$contentobjectID' AND
                                                      b.parent_node_id = c.node_id
                                                ORDER BY c.contentobject_id  ");
                    $userGroupsInfo[$contentobjectID] =& $userGroups;

                    $http->setSessionVariable( 'eZUserGroupsCache', $userGroupsInfo );
                    $http->setSessionVariable( 'eZUserGroupsCache_Timestamp', mktime() );
                }

                $userGroupArray = array();

                foreach ( $userGroups as $group )
                {
                    $userGroupArray[] = $group['id'];
                }
                $this->Groups =& $userGroupArray;
            }
            return $this->Groups;
        }
    }

    /// \privatesection
    var $Login;
    var $Email;
    var $PasswordHash;
    var $PasswordHashType;
    var $Groups;
    var $Roles;
    var $OriginalPassword;
    var $OriginalPasswordConfirm;
}

?>
