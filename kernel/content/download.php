<?php
//
// Created on: <15-Aug-2002 16:40:11 sp>
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
include_once( "kernel/classes/ezcontentobject.php" );
include_once( "kernel/classes/ezcontentobjectattribute.php" );
//include_once( "kernel/classes/ezcontentobjecttreenode.php" );
//include_once( "kernel/classes/ezcontentobjecttreenode.php" );
include_once( "kernel/classes/datatypes/ezbinaryfile/ezbinaryfile.php" );
include_once( "kernel/classes/ezbinaryfilehandler.php" );
include_once( "kernel/classes/datatypes/ezmedia/ezmedia.php" );
//include_once( "kernel/common/template.php" );

//$tpl =& templateInit();

$contentObjectID = $Params['ContentObjectID'];
$contentObjectAttributeID = $Params['ContentObjectAttributeID'];
$contentObject = eZContentObject::fetch( $contentObjectID );
if ( !is_object( $contentObject ) )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE );
}

if ( isset(  $Params['Version'] ) && is_numeric( $Params['Version'] ) )
     $version = $Params['Version'];
else
     $version = $contentObject->attribute( 'current_version' );
     
$contentObjectAttribute = eZContentObjectAttribute::fetch( $contentObjectAttributeID, $version, true );
if ( !is_object( $contentObjectAttribute ) )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE );
}
$contentObjectID = $contentObjectAttribute->attribute( 'contentobject_id' );

if ( ! $contentObject->attribute( 'can_read' ) )
{
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED );
}

$fileHandler =& eZBinaryFileHandler::instance();
$result = $fileHandler->handleDownload( $contentObject, $contentObjectAttribute, EZ_BINARY_FILE_TYPE_FILE );

if ( $result == EZ_BINARY_FILE_RESULT_UNAVAILABLE )
{
    eZDebug::writeError( "The specified file could not be found." );
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

?>
