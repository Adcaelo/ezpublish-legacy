<?php
//
// Created on: <5-Jul-2007 00:00:00 ar>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Online Editor MCE extension for eZ Publish
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2008 eZ systems AS
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


/* For loading json data of a given object by object id */

include_once( 'extension/ezoe/classes/ezajaxcontent.php' );

$embedId         = 0;
$http            = eZHTTPTool::instance();

if ( isset( $Params['EmbedID'] )  && $Params['EmbedID'])
{
    $embedType = 'ezobject';
    if (  is_numeric( $Params['EmbedID'] ) )
        $embedId = $Params['EmbedID'];
    else
        list($embedType, $embedId) = explode('_', $Params['EmbedID']);

    if ( strcasecmp( $embedType  , 'eznode'  ) === 0 )
        $embedObject = eZContentObject::fetchByNodeID( $embedId );
    else
        $embedObject = eZContentObject::fetch( $embedId );
}

if ( !$embedObject )
{
   echo 'false';
   eZExecution::cleanExit();
}

$imageIni  = eZINI::instance( 'image.ini' );
$params    = array('loadImages' => true);
$params['imagePreGenerateSizes'] = array('small');

if ( isset( $Params['DataMap'] )  && $Params['DataMap'])
    $params['dataMap'] = array($Params['DataMap']);

if ( $http->hasPostVariable( 'imagePreGenerateSizes' ) )
    $params['imagePreGenerateSizes'][] = $http->postVariable( 'imagePreGenerateSizes' );
else if ( isset( $Params['ImagePreGenerateSizes'] )  && $Params['ImagePreGenerateSizes'])
    $params['imagePreGenerateSizes'][] = $Params['ImagePreGenerateSizes'];


echo eZAjaxContent::encode( $embedObject, $params );


eZExecution::cleanExit();

?>