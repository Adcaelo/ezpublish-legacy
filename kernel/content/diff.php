<?php
//
//
// <creation-tag>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
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

//include_once( 'lib/ezutils/classes/ezhttptool.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/common/template.php' );
include_once( 'lib/ezdiff/classes/ezdiff.php' );

$Module =& $Params['Module'];
$objectID = $Params['ObjectID'];

$Offset = $Params['Offset'];

$viewParameters = array( 'offset' => $Offset );

$contentObject = eZContentObject::fetch( $objectID );

if ( !$contentObject )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( !$contentObject->canRead() )
{
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array( 'AccessList' => $contentObject->accessList( 'read' ) ) );
}

$http =& eZHTTPTool::instance();
$tpl =& templateInit();
$tpl->setVariable( 'object', $contentObject );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'module', $Module );

//Set default values
$previousVersion = 1;
$newestVersion = 1;

//By default, set preselect the previous and most recent version for diffing
if ( count( $contentObject->versions() ) > 1 )
{
    $versionArray = $contentObject->versions( false );
    $selectableVersions = array();
    foreach( $versionArray as $versionItem )
    {
        //Only return version numbers of archived or published items
        if ( in_array( $versionItem['status'], array( 0, 1, 3 ) ) )
        {
            $selectableVersions[] = $versionItem['version'];
        }
    }
    $newestVersion = array_pop( $selectableVersions );
    $previousVersion = array_pop( $selectableVersions );
}

$tpl->setVariable( 'selectOldVersion', $previousVersion );
$tpl->setVariable( 'selectNewVersion', $newestVersion );

$diff = array();

if ( $http->hasPostVariable( 'FromVersion' ) && $http->hasPostVariable( 'ToVersion' ) )
{
    $oldVersion = $http->postVariable( 'FromVersion' );
    $newVersion = $http->postVariable( 'ToVersion' );
    
    if ( is_numeric( $oldVersion ) && is_numeric( $newVersion ) )
    {
        $oldObject = $contentObject->version( $oldVersion );
        $newObject = $contentObject->version( $newVersion );

        $oldAttributes = $oldObject->dataMap();
        $newAttributes = $newObject->dataMap();

        foreach ( $oldAttributes as $attribute )
        {
            $newAttr = $newAttributes[$attribute->attribute( 'contentclass_attribute_identifier' )];
            $contentClassAttr = $newAttr->attribute( 'contentclass_attribute' );
            $diff[$contentClassAttr->attribute( 'id' )] = $contentClassAttr->diff( $attribute, $newAttr );
        }

        $tpl->setVariable( 'object', $contentObject );
        $tpl->setVariable( 'oldVersion', $oldVersion );
        $tpl->setVariable( 'oldVersionObject', $contentObject->version( $oldVersion ) );

        $tpl->setVariable( 'newVersion', $newVersion );
        $tpl->setVariable( 'newVersionObject', $contentObject->version( $newVersion ) );
        $tpl->setVariable( 'diff', $diff );
    }
}

$Result = array();
$Result['content'] =& $tpl->fetch( "design:content/diff.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/content', 'Differences' ) ) );

?>
