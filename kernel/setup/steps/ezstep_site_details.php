<?php
//
// Definition of eZStepSiteDetails class
//
// Created on: <12-Aug-2003 18:30:57 kk>
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

/*! \file ezstep_site_details.php
*/
include_once( 'kernel/setup/steps/ezstep_installer.php');
include_once( "kernel/common/i18n.php" );

if ( !defined( 'EZ_SETUP_DB_ERROR_NOT_EMPTY' ) )
    define( 'EZ_SETUP_DB_ERROR_NOT_EMPTY', 4 );
if ( !defined( 'EZ_SETUP_DB_ERROR_ALREADY_CHOSEN' ) )
    define( 'EZ_SETUP_DB_ERROR_ALREADY_CHOSEN', 10 );
if ( !defined( 'EZ_SETUP_SITE_ACCESS_ILLEGAL' ) )
    define( 'EZ_SETUP_SITE_ACCESS_ILLEGAL', 11 );

if ( !defined( 'EZ_SETUP_SITE_ACCESS_DEFAULT_REGEXP' ) )
    define( 'EZ_SETUP_SITE_ACCESS_DEFAULT_REGEXP', '/^([a-zA-Z0-9_]*)$/' );
if ( !defined( 'EZ_SETUP_SITE_ACCESS_HOSTNAME_REGEXP' ) )
    define( 'EZ_SETUP_SITE_ACCESS_HOSTNAME_REGEXP', '/^([a-zA-Z0-9.\-:]*)$/' );
if ( !defined( 'EZ_SETUP_SITE_ACCESS_PORT_REGEXP' ) )
    define( 'EZ_SETUP_SITE_ACCESS_PORT_REGEXP', '/^([0-9]*)$/' );

/*!
  \class eZStepSiteDetails ezstep_site_details.php
  \brief The class eZStepSiteDetails does

*/

class eZStepSiteDetails extends eZStepInstaller
{
    /*!
     Constructor
    */
    function eZStepSiteDetails( &$tpl, &$http, &$ini, &$persistenceList )
    {
        $this->eZStepInstaller( $tpl, $http, $ini, $persistenceList,
                                'site_details', 'Site details' );
    }

    /*!
     \reimp
    */
    function processPostData()
    {

        include_once( 'lib/ezdb/classes/ezdbtool.php' );
        $databaseMap = eZSetupDatabaseMap();

        $databaseInfo = $this->PersistenceList['database_info'];
        $databaseInfo['info'] = $databaseMap[$databaseInfo['type']];
        $regionalInfo = $this->PersistenceList['regional_info'];

        $dbStatus = array();
        $dbDriver = $databaseInfo['info']['driver'];
        $dbServer = $databaseInfo['server'];
        $dbUser = $databaseInfo['user'];
        $dbSocket = $databaseInfo['socket'];
        $dbPwd = $databaseInfo['password'];

        $chosenDatabases = array();
        $siteAccessValues = array();
        $siteAccessValues['admin'] = 1; // Add user and admin as illegal site access values
        $siteAccessValues['user'] = 1;

        $siteTypes = $this->chosenSiteTypes();
        unset( $this->PersistenceList['regional_info']['site_charset'] );

        $counter = 0;
        foreach ( array_keys( $siteTypes ) as $siteTypeKey )
        {
            $siteType =& $siteTypes[$siteTypeKey];
            $siteType['title'] = $this->Http->postVariable( 'eZSetup_site_templates_' . $counter.'_title' );
            $siteType['url'] = $this->Http->postVariable( 'eZSetup_site_templates_' . $counter.'_url' );

            $error = false;
            $userPath = $this->Http->postVariable( 'eZSetup_site_templates_'.$counter.'_value' );

            $regexp = EZ_SETUP_SITE_ACCESS_DEFAULT_REGEXP;
            if ( $siteType['access_type'] == 'port' )
            {
                $regexp = EZ_SETUP_SITE_ACCESS_PORT_REGEXP;
            }
            elseif ( $siteType['access_type'] == 'hostname' )
            {
                $regexp =  EZ_SETUP_SITE_ACCESS_HOSTNAME_REGEXP;
            }
            $validateUserPath = preg_match( $regexp, $userPath );

            if ( isset( $siteAccessValues[$userPath] ) or !$validateUserPath ) // check for equal and correct site access values
            {
                $this->Error[$counter] = EZ_SETUP_SITE_ACCESS_ILLEGAL;
                /* Check for valid host name */
                $userPath = ( ( $siteType['access_type'] == 'hostname' ) and ( strpos( $userPath, '_' ) !== false ) ) ? strtr( $userPath, '_', '-' ) : $userPath;
                $error = true;
            }
            $siteType['access_type_value'] = $userPath;
            $siteAccessValues[$siteType['access_type_value']] = 1;
            $adminPath = $this->Http->postVariable( 'eZSetup_site_templates_'.$counter.'_admin_value' );
            $validateAdminPath = preg_match( $regexp, $adminPath );

            if ( isset( $siteAccessValues[$adminPath] ) or !$validateAdminPath ) // check for equal and correct site access values
            {
                $this->Error[$counter] = EZ_SETUP_SITE_ACCESS_ILLEGAL;
                /* Check for valid host name */
                $adminPath = ( ( $siteType['access_type'] == 'hostname' ) and ( strpos( $adminPath, '_' ) !== false ) ) ? strtr( $adminPath, '_', '-' ) : $adminPath;
                $error = true;
            }

            $siteType['admin_access_type_value'] = $adminPath;
            $siteAccessValues[$siteType['admin_access_type_value']] = 1;

            $siteType['database'] = $this->Http->postVariable( 'eZSetup_site_templates_' . $counter . '_database' );

            if ( isset( $chosenDatabases[$siteType['database']] ) )
            {
                $this->Error[$counter] = EZ_SETUP_DB_ERROR_ALREADY_CHOSEN;
                $error = true;
            }

            $chosenDatabases[$siteType['database']] = 1;

            if ( $error )
                continue;

            // Check database connection
            $result = $this->checkDatabaseRequirements( false,
                                                        array( 'database' => $siteType['database'] ) );

            if ( !$result['status'] )
            {
                $this->Error[$counter] = array( 'type' => 'db',
                                                'error_code' => $result['error_code'] );
                continue;
            }
            // Store charset if found
            if ( $result['site_charset'] )
            {
                $this->PersistenceList['regional_info']['site_charset'] = $result['site_charset'];
            }

            $db =& $result['db_instance'];

            $dbStatus['connected'] = $result['connected'];

            $dbError = false;
            $demoDataResult = true;
            if ( $dbStatus['connected'] )
            {

                if ( count( $db->eZTableList() ) != 0 )
                {
                    if ( $this->Http->hasPostVariable( 'eZSetup_site_templates_'.$counter.'_existing_database' ) &&
                         $this->Http->postVariable( 'eZSetup_site_templates_'.$counter.'_existing_database' ) != EZ_SETUP_DB_DATA_CHOOSE )
                    {
                        $siteType['existing_database'] = $this->Http->postVariable( 'eZSetup_site_templates_' . $counter . '_existing_database' );
                    }
                    else
                    {
                        $this->Error[$counter] = EZ_SETUP_DB_ERROR_NOT_EMPTY ;
                    }
                }
            }
            else
            {
                return 'DatabaseInit';
            }

            ++$counter;
        }
        $this->storeSiteTypes( $siteTypes );

        return ( count( $this->Error ) == 0 );
    }

    /*!
     \reimp
    */
    function init()
    {
        if ( $this->hasKickstartData() )
        {
            $data = $this->kickstartData();

            $siteTypes = $this->chosenSiteTypes();

            $counter = 0;
            $portCounter = 8080;

            foreach ( array_keys( $siteTypes ) as $siteTypeKey )
            {
                $siteType =& $siteTypes[$siteTypeKey];
                $identifier = $siteType['identifier'];
                $siteType['title'] = isset( $data['Title'][$identifier] ) ? $data['Title'][$identifier] : false;
                if ( !$siteType['title'] )
                    $siteType['title'] = $siteType['name'];
                $siteType['url'] = isset( $data['URL'][$identifier] ) ? $data['URL'][$identifier] : false;
                if ( strlen( $siteType['url'] ) == 0 )
                    $siteType['url'] = 'http://' . eZSys::hostName() . eZSys::indexDir( false );

                switch ( $siteType['access_type'] )
                {
                    case 'port':
                        {
                            // Change access port for user site, if not use default which is the current value of $portCoutner
                            if ( isset( $data['AccessPort'][$identifier] ) )
                                $siteType['access_type_value'] = $data['AccessPort'][$identifier];
                            else
                                $siteType['access_type_value'] = $portCounter++;

                            // Change access port for admin site, if not use default which is the current value of $portCoutner
                            if ( isset( $data['AdminAccessPort'][$identifier] ) )
                                $siteType['admin_access_type_value'] = $data['AdminAccessPort'][$identifier];
                            else
                                $siteType['admin_access_type_value'] = $portCounter++;
                        }
                        break;

                    case 'hostname':
                        {
                            if ( isset( $data['AccessHostname'][$identifier] ) )
                                $siteType['access_type_value'] = $data['AccessHostname'][$identifier];
                            else
                                $siteType['access_type_value'] = $siteType['identifier'] . '.' . eZSys::hostName();

                            if ( isset( $data['AdminAccessHostname'][$identifier] ) )
                                $siteType['admin_access_type_value'] = $data['AdminAccessHostname'][$identifier];
                            else
                                $siteType['admin_access_type_value'] = $siteType['identifier'] . '-admin.' . eZSys::hostName();
                        }
                        break;

                    default:
                        {
                            // Change access name for user site, if not use default which is the identifier
                            if ( isset( $data['Access'][$identifier] ) )
                                $siteType['access_type_value'] = $data['Access'][$identifier];
                            else
                                $siteType['access_type_value'] = $siteType['identifier'];

                            // Change access name for admin site, if not use default which is the identifier + _admin
                            if ( isset( $data['AdminAccess'][$identifier] ) )
                                $siteType['admin_access_type_value'] = $data['AdminAccess'][$identifier];
                            else
                                $siteType['admin_access_type_value'] = $siteType['identifier'] . '_admin';
                        }
                        break;
                };

                $siteType['database'] = $data['Database'][$identifier];
                $action = EZ_SETUP_DB_DATA_APPEND;
                $map = array( 'ignore' => 1,
                              'remove' => 2,
                              'skip' => 3 );
                // Figure out what to do with database, do we need cleanup etc?
                if ( isset( $map[$data['DatabaseAction'][$identifier]] ) )
                    $action = $map[$data['DatabaseAction'][$identifier]];
                $siteType['existing_database'] = $action;

                $chosenDatabases[$siteType['database']] = 1;

                $result = $this->checkDatabaseRequirements( false,
                                                            array( 'database' => $siteType['database'] ) );

                if ( !$result['status'] )
                {
                    $this->Error[$counter] = array( 'type' => 'db',
                                                    'error_code' => $result['error_code'] );
                    continue;
                }

                // Store charset if found
                if ( $result['site_charset'] )
                {
                    $this->PersistenceList['regional_info']['site_charset'] = $result['site_charset'];
                }

                ++$counter;
            }
            $this->storeSiteTypes( $siteTypes );

            return $this->kickstartContinueNextStep();
        }

        include_once( 'lib/ezdb/classes/ezdbtool.php' );

        // Get available databases
        $databaseMap = eZSetupDatabaseMap();
        $databaseInfo = $this->PersistenceList['database_info'];
        $databaseInfo['info'] = $databaseMap[$databaseInfo['type']];
        $regionalInfo = $this->PersistenceList['regional_info'];

        $demoDataResult = false;

        $dbStatus = array();
        $dbDriver = $databaseInfo['info']['driver'];
        $dbServer = $databaseInfo['server'];
        $dbName = $databaseInfo['dbname'];
        $dbUser = $databaseInfo['user'];
        $dbSocket = $databaseInfo['socket'];
        if ( trim( $dbSocket ) == '' )
            $dbSocket = false;
        $dbPwd = $databaseInfo['password'];
        $dbCharset = 'iso-8859-1';
        $dbParameters = array( 'server' => $dbServer,
                               'user' => $dbUser,
                               'password' => $dbPwd,
                               'socket' => $dbSocket,
                               'database' => false,
                               'charset' => $dbCharset );
        $db =& eZDB::instance( $dbDriver, $dbParameters, true );
        $availDatabases = $db->availableDatabases();

        if ( count( $availDatabases ) > 0 ) // login succeded, and at least one database available
        {
            $this->PersistenceList['database_info_available'] = $availDatabases;
        }

        return false; // Always show site details
    }

    /*!
     \reimp
    */
    function &display()
    {
        $config =& eZINI::instance( 'setup.ini' );

        $siteTypes = $this->chosenSiteTypes();

        $availableDatabaseList = false;
        if ( isset( $this->PersistenceList['database_info_available'] ) )
        {
            $availableDatabaseList = $this->PersistenceList['database_info_available'];
        }
        $databaseList = $availableDatabaseList;
        $databaseCounter = 0;
        foreach ( array_keys( $siteTypes ) as $siteTypeKey )
        {
            $siteType =& $siteTypes[$siteTypeKey];
            if ( !isset( $siteType['title'] ) )
                $siteType['title'] = $siteType['name'];
            $siteType['errors'] = array();
            if ( !isset( $siteType['url'] ) )
                $siteType['url'] = 'http://' . eZSys::hostName() . eZSys::indexDir( false );
            if ( !isset( $siteType['site_access_illegal'] ) )
                $siteType['site_access_illegal'] = false;
            if ( !isset( $siteType['db_already_chosen'] ) )
                $siteType['db_already_chosen'] = false;
            if ( !isset( $siteType['db_not_empty'] ) )
                $siteType['db_not_empty'] = 0;
            if ( !isset( $siteType['database'] ) )
            {
                if ( is_array( $databaseList ) )
                {
                    $matchedDBName = false;
                    // First try database name match
                    foreach ( $databaseList as $databaseName )
                    {
                        $dbName = trim( strtolower( $databaseName ) );
                        $identifier = trim( strtolower( $siteType['identifier'] ) );
                        if ( $dbName == $identifier )
                        {
                            $matchedDBName = $databaseName;
                            break;
                        }
                    }
                    if ( !$matchedDBName )
                        $matchedDBName = $databaseList[$databaseCounter++];
                    $databaseList = array_values( array_diff( $databaseList, array( $matchedDBName ) ) );
                    $siteType['database'] = $matchedDBName;
                }
                else
                {
                    $siteType['database'] = '';
                }
            }
            if ( !isset( $siteType['existing_database'] ) )
            {
                $siteType['existing_database'] = EZ_SETUP_DB_DATA_APPEND;
            }
        }

        $this->Tpl->setVariable( 'db_not_empty', 0 );
        $this->Tpl->setVariable( 'db_already_chosen', 0 );
        $this->Tpl->setVariable( 'db_charset_differs', 0 );
        $this->Tpl->setVariable( 'site_access_illegal', 0 );
        $this->Tpl->setVariable( 'site_access_illegal_name', 0 );
        foreach ( $this->Error as $key => $error )
        {
            $type = 'site';
            if ( is_array( $error ) )
            {
                $type = $error['type'];
                $error = $error['error_code'];
            }
            if ( $type == 'site' )
            {
                switch ( $error )
                {
                    case EZ_SETUP_DB_ERROR_NOT_EMPTY:
                    {
                        $this->Tpl->setVariable( 'db_not_empty', 1 );
                        $siteTypes[$key]['db_not_empty'] = 1;
                    } break;

                    case EZ_SETUP_DB_ERROR_ALREADY_CHOSEN:
                    {
                        $this->Tpl->setVariable( 'db_already_chosen', 1 );
                        $siteTypes[$key]['db_already_chosen'] = 1;
                    } break;

                    case EZ_SETUP_SITE_ACCESS_ILLEGAL:
                    {
                        $this->Tpl->setVariable( 'site_access_illegal', 1 );
                        $siteTypes[$key]['site_access_illegal'] = 1;
                    } break;
                }
            }
            else if ( $type == 'db' )
            {
                if ( $error == EZ_SETUP_DB_ERROR_CHARSET_DIFFERS )
                    $this->Tpl->setVariable( 'db_charset_differs', 1 );
                $siteTypes[$key]['errors'][] = $this->databaseErrorInfo( array( 'error_code' => $error,
                                                                                'database_info' => $this->PersistenceList['database_info'] ) );
            }
        }
        $this->storeSiteTypes( $siteTypes );

        $this->Tpl->setVariable( 'database_default', $config->variable( 'DatabaseSettings', 'DefaultName' ) );
        $this->Tpl->setVariable( 'database_available', $availableDatabaseList );
        $this->Tpl->setVariable( 'site_types', $siteTypes );

        // Return template and data to be shown
        $result = array();
        // Display template
        $result['content'] = $this->Tpl->fetch( 'design:setup/init/site_details.tpl' );
        $result['path'] = array( array( 'text' => ezi18n( 'design/standard/setup/init',
                                                          'Site details' ),
                                        'url' => false ) );
        return $result;
    }

    var $Error = array();
}

?>
