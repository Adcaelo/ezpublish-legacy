<?php
//
// Copyright (C) 1999-2002 eZ systems as. All rights reserved.
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

include_once( "lib/ezutils/classes/ezhttptool.php" );

/*!
 Checks if the installation is valid and returns a module redirect if required.
 If CheckValidity in SiteAccessSettings is false then no check is done.
*/

function eZCheckValidity( &$siteBasics )
{
    eZDebug::writeDebug( "Checking validity" );
    $ini =& eZINI::instance();
    $checkValidity = ( $ini->variable( "SiteAccessSettings", "CheckValidity" ) == "true" );
    $check = null;
    if ( $checkValidity )
    {
        eZDebug::writeDebug( "Setup required" );
        $check = array( "module" => "setup",
                        'function' => 'init' );
        // Turn off some features that won't bee needed yet
//        $siteBasics['policy-check-required'] = false;
        $siteBasics['policy-check-omit-list'][] = 'setup';
        $siteBasics['url-translator-allowed'] = false;
        $siteBasics['show-page-layout'] = $ini->variable( 'SetupSettings', 'PageLayout' );
        $siteBasics['validity-check-required'] = true;
        $siteBasics['user-object-required'] = false;
        $siteBasics['session-required'] = false;
        $siteBasics['db-required'] = false;
    }
    return $check;
}

/*!
 Checks if user is logged in, if not and the site requires user login for access
 a module redirect is returned.
*/
function eZCheckUser( &$siteBasics )
{
    eZDebug::writeDebug( "Checking user" );
    if ( !$siteBasics['user-object-required'] )
    {
        eZDebug::writeDebug( "Skipping user requirements" );
        return null;
    }
    $ini =& eZINI::instance();
    $requireUserLogin = ( $ini->variable( "SiteAccessSettings", "RequireUserLogin" ) == "true" );
    $check = null;
    $http =& eZHTTPTool::instance();
    if ( !$requireUserLogin )
        return null;
    $check = array( "module" => "user",
                    "function" => "login" );
    if ( !$http->hasSessionVariable( "eZUserLoggedInID" ) )
        return $check;
    if ( $http->sessionVariable( "eZUserLoggedInID" ) == '' )
        return $check;
    if ( $http->sessionVariable( "eZUserLoggedInID" ) == $ini->variable( 'UserSettings', 'AnonymousUserID' ) )
        return $check;
    return null;
}

/*!
 \return an array with items to run a check on, each items
 is an associative array. The item must contain:
 - function - name of the function to run
*/
function eZCheckList()
{
    $checks = array();
    $checks["validity"] = array( "function" => "eZCheckValidity" );
    $checks["user"] = array( "function" => "eZCheckUser" );
    return $checks;
}

/*!
 \return an array with check items in the order they should be checked.
*/
function eZCheckOrder()
{
    $checkOrder = array( 'validity', 'user' );
    return $checkOrder;
}

/*!
 Does pre checks and returns a structure with redirection information,
 returns null if nothing should be done.
*/
function eZHandlePreChecks( &$siteBasics )
{
    $checks = eZCheckList();
    precheckAllowed( $checks );
    $checkOrder = eZCheckOrder();
    foreach( $checkOrder as $checkItem )
    {
        if ( !isset( $checks[$checkItem] ) )
            continue;
        $check = $checks[$checkItem];
        if ( !isset( $check["allow"] ) or $check["allow"] )
        {
            $func = $check["function"];
            $check = $func( $siteBasics );
            if ( $check !== null )
                return $check;
        }
    }
    return null;
}


?>
