<?php
//
// Definition of eZExtensionPackageHandler class
//
// Created on: <15-Dec-2005 11:15:42 ks>
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

/*! \file ezextensionpackagehandler.php
*/

/*!
  \class eZExtensionPackageHandler ezextensionpackagehandler.php
  \brief Handles extenstions in the package system

*/

include_once( 'lib/ezxml/classes/ezxml.php' );
include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezpackagehandler.php' );

define( "EZ_PACKAGE_EXTENSION_ERROR_EXISTS", 1 );

define( "EZ_PACKAGE_EXTENSION_REPLACE", 1 );
define( "EZ_PACKAGE_EXTENSION_SKIP", 2 );


class eZExtensionPackageHandler extends eZPackageHandler
{
    /*!
     Constructor
    */
    function eZExtensionPackageHandler()
    {
        $this->eZPackageHandler( 'ezextension',
                                 array( 'extract-install-content' => true ) );
    }

    /*!
     \reimp
     Returns an explanation for the extension install item.
    */
    function explainInstallItem( &$package, $installItem )
    {
        if ( $installItem['filename'] )
        {
            $filename = $installItem['filename'];
            $subdirectory = $installItem['sub-directory'];
            if ( $subdirectory )
                $filepath = $subdirectory . '/' . $filename . '.xml';
            else
                $filepath = $filename . '.xml';

            $filepath = $package->path() . '/' . $filepath;

            $dom =& $package->fetchDOMFromFile( $filepath );
            if ( $dom )
            {
                $root =& $dom->root();
                $extensionName = $root->getAttribute( 'name' );
                return array( 'description' => ezi18n( 'kernel/package', 'Extension \'%extensionname\'', false,
                                                       array( '%extensionname' => $extensionName ) ) );
            }
        }
    }

    /*!
     \reimp
     Uninstalls extensions.
    */
    function uninstall( &$package, $installType, $parameters,
                      $name, $os, $filename, $subdirectory,
                      &$content, &$installParameters,
                      &$installData )
    {
        $extensionName = $content->getAttribute( 'name' );
        
        $siteINI = eZINI::instance();
        $extensionDir = $siteINI->variable( 'ExtensionSettings', 'ExtensionDirectory' ) . '/' . $extensionName;

        // TODO: don't delete modified files?

        if ( file_exists( $extensionDir ) )
            eZDir::recursiveDelete( $extensionDir );

        return true;
    }

    /*!
     \reimp
     Copy extension from the package to extension repository.
    */
    function install( &$package, $installType, $parameters,
                      $name, $os, $filename, $subdirectory,
                      &$content, &$installParameters,
                      &$installData )
    {
        //$this->Package =& $package;
        
        $extensionName = $content->getAttribute( 'name' );
        
        $siteINI = eZINI::instance();
        $extensionDir = $siteINI->variable( 'ExtensionSettings', 'ExtensionDirectory' ) . '/' . $extensionName;
        $packageExtensionDir = $package->path() . '/' . $parameters['sub-directory'] . '/' . $extensionName;

        // Error: extension already exists.
        if ( file_exists( $extensionDir ) )
        {
            $description = "Extension '$extensionName' already exists.";
            $choosenAction = $this->errorChoosenAction( EZ_PACKAGE_EXTENSION_ERROR_EXISTS,
                                                        $installParameters, $description );
            switch( $choosenAction )
            {
            case EZ_PACKAGE_EXTENSION_SKIP:
                return true;
        
            case EZ_PACKAGE_NON_INTERACTIVE:
            case EZ_PACKAGE_EXTENSION_REPLACE:
                eZDir::recursiveDelete( $extensionDir );
                break;

            default:
                $installParameters['error'] = array( 'error_code' => EZ_PACKAGE_EXTENSION_ERROR_EXISTS,
                                                     'element_id' => $extensionName,
                                                     'description' => $description,
                                                     'actions' => array( EZ_PACKAGE_EXTENSION_REPLACE => "Replace extension",
                                                                         EZ_PACKAGE_EXTENSION_SKIP => 'Skip' ) );
                return false;
            }
        }

        eZDir::mkdir( $extensionDir, eZDir::directoryPermission(), true );
        
        include_once( 'lib/ezfile/classes/ezfilehandler.php' );

        $files = $content->Children;
        foreach( $files as $file )
        {
            $path = $file->getAttribute( 'path' );
            $destPath = $extensionDir . $path . '/' . $file->getAttribute( 'name' );

            if ( $file->getAttribute( 'type' ) == 'dir' )
            {
                eZDir::mkdir( $destPath, eZDir::directoryPermission() );
            }
            else
            {
                $sourcePath = $packageExtensionDir . $path . '/' . $file->getAttribute( 'name' );
                eZFileHandler::copy( $sourcePath, $destPath );
            }
        }
        return true;
    }

    var $Package = null;
}

?>
