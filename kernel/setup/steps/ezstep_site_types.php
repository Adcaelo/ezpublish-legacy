<?php
//
// Definition of eZStepSiteTypes class
//
// Created on: <16-Apr-2004 09:56:02 amos>
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

include_once( 'kernel/setup/steps/ezstep_installer.php');
include_once( "kernel/common/i18n.php" );

/*!
  \class eZStepSiteTypes ezstep_site_types.php
  \brief The class eZStepSiteTypes does

*/

class eZStepSiteTypes extends eZStepInstaller
{
    /*!
     Constructor
    */
    function eZStepSiteTypes( &$tpl, &$http, &$ini, &$persistenceList )
    {
        $this->eZStepInstaller( $tpl, $http, $ini, $persistenceList,
                                'site_types', 'Site types' );
    }

    /**
     * Downloads file.
     *
     * Sets $this->ErrorMsg in case of an error.
     *
     * \private
     * \param $url            URL.
     * \param $outDir         Directory where to put downloaded file to.
     * \param $forcedFileName Force saving downloaded file under this name.
     * \return false on error, path to downloaded package otherwise.
     */
    function downloadFile( $url, $outDir, $forcedFileName = false )
    {
        $fileName = $outDir . "/" . ( $forcedFileName ? $forcedFileName : basename( $url ) );

        /* Do nothing if the file already exists (no need to download).
        if ( file_exists( $fileName ) )
        {
            eZDebug::writeNotice( "Skipping download to '$fileName': file already exists." );
            return $fileName;
        }
        */
        eZDebug::writeNotice( "Downloading file '$fileName' from $url" );

        // Create the out directory if not exists.
        if ( !file_exists( $outDir ) )
            eZDir::mkdir( $outDir, eZDir::directoryPermission(), true );

        $ch = curl_init( $url );
        $fp = eZStepSiteTypes::fopen( $fileName, 'wb' );

        if ( $fp === false )
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init', 'Cannot write to file' ) .
                ': ' . $this->FileOpenErrorMsg;
            return false;
        }

        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );

        if ( ! curl_exec( $ch ) )
        {
            $this->ErrorMsg = curl_error( $ch );
            return false;
        }

        curl_close( $ch );
        fclose( $fp );

        return $fileName;
    }

    /**
     * Downloads and imports package.
     *
     * Sets $this->ErrorMsg in case of an error.
     *
     * \param $forceDownload  download even if this package already exists.
     * \private
     * \return false on error, package object otherwise.
     */
    function downloadAndImportPackage( $packageName, $packageUrl, $forceDownload = false )
    {
        include_once( 'kernel/classes/ezpackage.php' );
        $package = eZPackage::fetch( $packageName, false, false, false );

        if ( is_object( $package ) && $forceDownload )
        {
            $package->remove();
        }
        else
        {
            eZDebug::writeNotice( "Skipping download of package '$packageName': package already exists." );
            return $package;
        }

        $archiveName = $this->downloadFile( $packageUrl, /* $outDir = */ eZStepSiteTypes::tempDir() );
        if ( $archiveName === false )
        {
            eZDebug::writeWarning( "Download of package '$packageName' from '$packageUrl' failed: $this->ErrorMsg" );
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                      'Download of package \'%pkg\' failed. You may upload the package manually.',
                                      false, array( '%pkg' => $packageName ) );
            return false;
        }

        $package = eZPackage::import( $archiveName, $packageName, false );

        // Remove downloaded ezpkg file
        include_once( 'lib/ezfile/classes/ezfilehandler.php' );
        eZFileHandler::unlink( $archiveName );

        if ( !is_object( $package ) )
        {
            eZDebug::writeError( "Invalid package" );
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init', 'Invalid package' );
            return false;
        }

        return $package;
    }


    /*!
     * Download packages required by the given package.
     *
     * \private
     */
    function downloadDependantPackages( $sitePackage )
    {
        $dependencies = $sitePackage->attribute( 'dependencies' );
        $requirements = $dependencies['requires'];
        $remotePackagesInfo = $this->retreiveRemotePackagesList();

        foreach ( $requirements as $req )
        {
            $requiredPackageName    = $req['name'];
            $requiredPackageVersion = $req['min-version'];

            if ( !isset( $remotePackagesInfo[$requiredPackageName]['url'] ) )
            {
                eZDebug::writeWarning( "Download of package '$requiredPackageName' failed: the URL is unknown." );
                $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                          'Download of package \'%pkg\' failed. You may upload the package manually.',
                                          false, array( '%pkg' => $requiredPackageName ) );
                return false;
            }

            $requiredPackageURL = $remotePackagesInfo[$requiredPackageName]['url'];


            $downloadNewPackage   = false;
            $removeCurrentPackage = false;

            // try to fetch the required package
            $package = eZPackage::fetch( $requiredPackageName, false, false, false );

            // if it already exists
            if ( is_object( $package ) )
            {
                // check its version
                $currentPackageVersion = $package->getVersion();

                // if existing package's version is less than required one
                // we remove the package and download newer one.

                if ( version_compare( $currentPackageVersion, $requiredPackageVersion ) < 0 )
                {
                    $downloadNewPackage   = true;
                    $removeCurrentPackage = true;
                }

                // else (if the version is greater or equal to the required one)
                // then do nothing (skip downloading)
            }
            else
                // if the package does not exist, we download it.
                $downloadNewPackage   = true;

            if ( $removeCurrentPackage )
            {
                $package->remove();
                unset( $package );
            }

            if ( $downloadNewPackage )
            {
                $rc = $this->downloadAndImportPackage( $requiredPackageName, $requiredPackageURL );
                if( !is_object( $rc ) )
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Upload local package.
     *
     * \private
     */
    function uploadPackage()
    {
        include_once( "lib/ezutils/classes/ezhttpfile.php" );
        include_once( "kernel/classes/ezpackage.php" );


        if ( !eZHTTPFile::canFetch( 'PackageBinaryFile' ) )
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                      'No package selected for upload' ) . '.';
            return;
        }

        $file =& eZHTTPFile::fetch( 'PackageBinaryFile' );
        if ( !$file )
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                      'Failed fetching upload package file' );
            return;
        }

        $packageFilename = $file->attribute( 'filename' );
        $packageName = $file->attribute( 'original_filename' );
        if ( preg_match( "#^(.+)-[0-9](\.[0-9]+)-[0-9].ezpkg$#", $packageName, $matches ) )
            $packageName = $matches[1];
        $packageName = preg_replace( array( "#[^a-zA-Z0-9]+#",
                                            "#_+#",
                                            "#(^_)|(_$)#" ),
                                     array( '_',
                                            '_',
                                            '' ), $packageName );
        $package = eZPackage::import( $packageFilename, $packageName );

        if ( is_object( $package ) )
        {
            // package successfully imported
            return;
        }
        elseif ( $package == EZ_PACKAGE_STATUS_ALREADY_EXISTS )
            eZDebug::writeWarning( "Package '$packageName' already exists." );
        else
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                  'Uploaded file is not an eZ publish package' );
        }
    }

    /**
     * Process POST data.
     *
     * \reimp
     */
    function processPostData()
    {
        if ( $this->Http->hasPostVariable( 'UploadPackageButton' ) )
        {
            $this->uploadPackage();
            return false; // force displaying the same step.
        }

        if ( !$this->Http->hasPostVariable( 'eZSetup_site_type' ) )
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                      'No site package choosen.' );
            return false;
        }

        $sitePackageInfo = $this->Http->postVariable( 'eZSetup_site_type' );

        if ( preg_match( '/^(\w+)\|(.+)$/', $sitePackageInfo, $matches ) )
        {
            // remote site package chosen: download it.
            $sitePackageName = $matches[1];
            $sitePackageURL  = $matches[2];
            
            // we already know that we should download the package anyway as it has newer version
            // so use force download mode
            $package = $this->downloadAndImportPackage( $sitePackageName, $sitePackageURL, true );
        }
        else
        {
            // local (already imported) site package chosen: just fetch it.
            $sitePackageName = $sitePackageInfo;

            include_once( 'kernel/classes/ezpackage.php' );
            $package = eZPackage::fetch( $sitePackageName, false, false, false );
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init', 'Invalid package' ) . '.';
        }

        // Verify package.
        if ( !is_object( $package ) || !$this->selectSiteType( $sitePackageName ) )
            return false;

        // Download packages that the site package requires.
        return $this->downloadDependantPackages( $package );
    }

    /*!
     \reimp
     */
    function init()
    {
        if ( $this->hasKickstartData() )
        {
            $data = $this->kickstartData();

            $chosenSitePackage = $data['Sites'][0];

            // TODO: Download site package and it's related packages
            //       in case of remote package has been choosen.


            if ( $this->selectSiteType( $chosenSitePackage ) )
            {
                return $this->kickstartContinueNextStep();
            }
        }

        if ( !isset( $this->ErrorMsg ) )
            $this->ErrorMsg = false;

        return false; // Always show site template selection
    }

    /**
     * \private
     */
    function createSitePackagesList( $remoteSitePackages, $importedSitePackages, $dependenciesStatus )
    {
        $sitePackages = array();

        foreach ( $remoteSitePackages as $packageInfo )
        {
            $packageName = $packageInfo['name'];
            $sitePackages[$packageName] = $packageInfo;
        }

        foreach ( $importedSitePackages as $package )
        {
            $packageName = $package->attribute( 'name' );
            $packageVersion = $package->getVersion();

            if ( isset( $sitePackages[$packageName] ) )
            {
                $remoteVersion = $sitePackages[$packageName]['version'];
                $localVersion = $packageVersion;

                if ( version_compare( $remoteVersion, $localVersion ) > 0 )
                    continue;
            }

            $thumbnails = $package->attribute( 'thumbnail-list' );

            $thumbnailPath = false;
            if ( $thumbnails )
            {
                $thumbnailFile = $thumbnails[0];
                $thumbnailPath = $package->fileItemPath( $thumbnailFile, 'default' );
            }

            $dependencies = $package->attribute( 'dependencies' );
            $requirements = $dependencies['requires'];

            $packageInfo = array(
                'name' => $packageName,
                'version' => $package->getVersion(),
                'type' => $package->attribute( 'type' ),
                'summary' => $package->attribute( 'summary' ),
                'description' => $package->attribute( 'description' ),
                'requires' => $dependenciesStatus[$packageName],
                );

            if ( $thumbnailPath )
                $packageInfo['thumbnail_path'] = $thumbnailPath;

            $sitePackages[$packageName] = $packageInfo;
        }

        // Set availability status for each package.
        foreach ( $sitePackages as $idx => $packageInfo )
            $sitePackages[$idx]['status'] = !isset( $packageInfo['url'] );

        return $sitePackages;
    }

    /*!
     \reimp
    */
    function &display()
    {
        $remoteSitePackages = $this->retreiveRemoteSitePackagesList();
        $importedSitePackages = $this->fetchAvailableSitePackages();
        $dependenciesStatus = array();

        // check site package dependencies to show their status in the template
        foreach ( $importedSitePackages as $sitePackage )
        {
            $sitePackageName = $sitePackage->attribute( 'name' );
            $dependencies = $sitePackage->attribute( 'dependencies' );
            $requirements = $dependencies['requires'];

            foreach ( $requirements as $req )
            {
                $requiredPackageName    = $req['name'];
                $requiredPackageVersion = $req['min-version'];
                $packageOK = false;

                $package = eZPackage::fetch( $requiredPackageName, false, false, false );
                if ( is_object( $package ) )
                {
                    $currentPackageVersion = $package->getVersion();
                    if ( version_compare( $currentPackageVersion, $requiredPackageVersion ) >= 0 )
                        $packageOK = true;
                }

                $dependenciesStatus[$sitePackageName][$requiredPackageName] = array( 'version' => $requiredPackageVersion,
                                                                                     'status'  => $packageOK );
            }
        }

        $sitePackages = $this->createSitePackagesList( $remoteSitePackages, $importedSitePackages, $dependenciesStatus );

        $chosenSitePackage = $this->chosenSitePackage();

        $this->Tpl->setVariable( 'site_packages', $sitePackages );
        $this->Tpl->setVariable( 'dependencies_status', $dependenciesStatus );
        $this->Tpl->setVariable( 'chosen_package', $chosenSitePackage );
        $this->Tpl->setVariable( 'error', $this->ErrorMsg );

        // Return template and data to be shown
        $result = array();
        // Display template
        $result['content'] = $this->Tpl->fetch( 'design:setup/init/site_types.tpl' );
        $result['path'] = array( array( 'text' => ezi18n( 'design/standard/setup/init',
                                                          'Site selection' ),
                                        'url' => false ) );
        return $result;
    }

    /**
     * Fetches list of site packages already available locally.
     *
     * \private
     */
    function fetchAvailableSitePackages()
    {
        include_once( 'kernel/classes/ezpackage.php' );
        $packageList = eZPackage::fetchPackages( array( 'db_available' => false ), array( 'type' => 'site' ) );

        return $packageList;
    }

    /**
     * Fetches list of packages already available locally.
     *
     * \private
     */
    function fetchAvailablePackages( $type = false )
    {
        $typeArray  = array();
        if ( $type )
            $typeArray['type'] = $type;

        include_once( 'kernel/classes/ezpackage.php' );
        $packageList = eZPackage::fetchPackages( array( 'db_available' => false ), $typeArray );

        return $packageList;
    }


    /**
     * Retreive list of packages available to download.
     *
     * Example of return value:
     * array(
     *  'packages' => array(
     *                      '<package_name1>' => array( "name" =>... , "version" =>... , "summary" => ... "url" =>... ),
     *                      '<package_name2>' => array( "name" =>... , "version" =>... , "summary" => ... "url" =>... )
     *                     )
     *      );
     *
     */
    function retreiveRemotePackagesList( $onlySitePackages = false )
    {
        // Get the URL.
        $ini =& eZINI::instance( 'package.ini' );
        $indexURL = $ini->variable( 'RepositorySettings', 'RemotePackagesIndexURL' );

        // Download index file.
        $idxFileName = $this->downloadFile( $indexURL, /* $outDir = */ eZStepSiteTypes::tempDir(), 'index.xml' );

        if ( $idxFileName === false )
        {
            $this->ErrorMsg = ezi18n( 'design/standard/setup/init',
                                      'Retreiving remote site packages list failed. ' .
                                      'You may upload packages manually.' );
            eZDebug::writeError( "Cannot download remote packages index file from '$indexURL'." );
            return false;
        }

        // Parse it.
        include_once( 'lib/ezfile/classes/ezfile.php' );
        include_once( "lib/ezxml/classes/ezxml.php" );

        $xmlString = eZFile::getContents( $idxFileName );
        @unlink( $idxFileName );
        $xml = new eZXML();
        $domDocument = $xml->domTree( $xmlString );

        if ( !is_object( $domDocument ) )
        {
            eZDebug::writeError( "Malformed index file." );
            return false;
        }

        $root = $domDocument->root();

        if ( $root->name() != 'packages' )
        {
            eZDebug::writeError( "Malformed index file." );
            return false;
        }

        $packageList = array();
        foreach ( $root->children() as $packageNode )
        {
            if ( $packageNode->name() != 'package' ) // skip unwanted chilren
                continue;
            if ( $onlySitePackages && $packageNode->getAttribute( 'type' ) != 'site' )  // skip non-site packages
                continue;
            $packageAttributes = $packageNode->attributeValues();
            $packageList[$packageAttributes['name']] = $packageAttributes;
        }

        return $packageList;
    }

    /**
     * Retreive list of site packages available to download.
     * \private
     */
    function retreiveRemoteSitePackagesList()
    {
        return $this->retreiveRemotePackagesList( true );
    }

    /**
     * Wrapper for standard fopen() doing error checking.
     *
     * \private
     * \static
     */
    function fopen( $fileName, $mode )
    {
        $savedTrackErrorsFlag = ini_get( 'track_errors' );
        ini_set( 'track_errors', 1 );

        if ( ( $handle = @fopen( $fileName, 'wb' ) ) === false )
            $this->FileOpenErrorMsg = $php_errormsg;

        ini_set( 'track_errors', $savedTrackErrorsFlag );

        return $handle;
    }

    /**
     * Returns temporary directory used to download files to.
     *
     * \static
     */
    function tempDir()
    {
        return eZDir::path( array( eZSys::cacheDirectory(),
                                    'packages' ) );
    }

    var $Error = 0;
    var $ErrorMsg = false;
    var $FileOpenErrorMsg = false;
}

?>
