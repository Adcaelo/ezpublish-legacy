<?php
//
// Definition of eZTemplateFileResource class
//
// Created on: <01-Mar-2002 13:49:18 amos>
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

/*!
 \class eZTemplateFileResource eztemplatefileresource.php
 \brief Handles filesystem retrieval of templates.

 Templates are loaded from the disk and returned to the template system.
 The name of the resource is "file:".
*/

include_once( "lib/ezi18n/classes/eztextcodec.php" );
include_once( "lib/eztemplate/classes/eztemplatetreecache.php" );
include_once( "lib/eztemplate/classes/eztemplateprocesscache.php" );

class eZTemplateFileResource
{
    /*!
     Initializes with a default resource name "file".
     Also sets whether the resource servers static data files, this is needed
     for the cache system.
    */
    function eZTemplateFileResource( $name = "file", $servesStaticData = true )
    {
        $this->Name = $name;
        $this->ServesStaticData = $servesStaticData;
        $this->TemplateCache = array();
    }

    /*!
     Returns the name of the resource.
    */
    function resourceName()
    {
        return $this->Name;
    }

    /*
     \return true if this resource handler servers static data,
     this means that the data can be cached by the template system.
    */
    function servesStaticData()
    {
        return $this->ServesStaticData;
    }

    /*!
     Generates a unique key string from the input data and returns it.
     The key will be used for storing cached data and retrieving cache files.
     When implementing file resource handlers this key must be reimplemented if
     the current code does not generate correct keys. However most file based
     resource handlers can simple reuse this class.

     Default implementation returns an md5 of the \a $keyData.
    */
    function cacheKey( $keyData, $res, $templatePath, &$extraParameters )
    {
        $key = md5( $keyData );
        return $key;
    }

    /*!
     \return the cached node tree for the selected template.
    */
    function hasCachedProcessTree( $keyData, $uri, $res, $templatePath, &$extraParameters, $timestamp )
    {
        return false;
        $key = $this->cacheKey( $keyData, $res, $templatePath, $extraParameters );
        if ( eZTemplateTreeCache::canRestoreCache( $key, $timestamp ) )
            eZTemplateTreeCache::restoreCache( $key );
        return eZTemplateTreeCache::cachedTree( $key, $uri, $res, $templatePath, $extraParameters );
    }

    /*!
     Sets the cached node tree for the selected template to \a $root.
    */
    function generateProcessCache( $keyData, $uri, $res, $templatePath, &$extraParameters, &$resourceData )
    {
        eZDebug::writeDebug( 'generateProcessCache( $keyData, $uri, $res, $templatePath, &$extraParameters, &$resourceData )', 'eztemplatefileresource' );
        $key = $this->cacheKey( $keyData, $res, $templatePath, $extraParameters );
        return eZTemplateProcessCache::generateCache( $key, $resourceData );
    }

    function canGenerateProcessCache()
    {
        return eZTemplateProcessCache::isCacheEnabled();
    }

    /*!
     \return the cached node tree for the selected template.
    */
    function &cachedTemplateTree( $keyData, $uri, $res, $templatePath, &$extraParameters, $timestamp )
    {
        $key = $this->cacheKey( $keyData, $res, $templatePath, $extraParameters );
        if ( eZTemplateTreeCache::canRestoreCache( $key, $timestamp ) )
            eZTemplateTreeCache::restoreCache( $key );
        return eZTemplateTreeCache::cachedTree( $key, $uri, $res, $templatePath, $extraParameters );
    }

    /*!
     Sets the cached node tree for the selected template to \a $root.
    */
    function setCachedTemplateTree( $keyData, $uri, $res, $templatePath, &$extraParameters, &$root )
    {
        $key = $this->cacheKey( $keyData, $res, $templatePath, $extraParameters );
        eZTemplateTreeCache::setCachedTree( $key, $uri, $res, $templatePath, $extraParameters, $root );
        eZTemplateTreeCache::storeCache( $key );
    }

    /*!
     Loads the template file if it exists, also sets the modification timestamp.
     Returns true if the file exists.
    */
//     function handleResource( &$tpl, &$templateRoot, &$text, &$tstamp, $uri, $resourceName, &$path, &$keyData, $method, &$extraParameters )
    function handleResource( &$tpl, &$resourceData, $method, &$extraParameters )
    {
        return $this->handleResourceData( $tpl, $this, $resourceData, $method, $extraParameters );
    }

    /*!
     \static
     Reusable function for handling file based loading.
     Call this with the resource handler object in \a $handler.
     It will load the template file and handle any charsets conversion if necessary.
     It will also handle tree node caching if one is found.
    */
//     function handleResourceData( &$tpl, &$handler, &$templateRoot, &$text, &$tstamp, $uri, $resourceName, &$path, &$keyData, $method, &$extraParameters )
    function handleResourceData( &$tpl, &$handler, &$resourceData, $method, &$extraParameters )
    {
        // &$templateRoot, &$text, &$tstamp, $uri, $resourceName, &$path, &$keyData
        $templateRoot =& $resourceData['root-node'];
        $text =& $resourceData['text'];
        $tstamp =& $resourceData['time-stamp'];
        $uri =& $resourceData['uri'];
        $resourceName =& $resourceData['resource'];
        $path =& $resourceData['template-filename'];
        $keyData =& $resourceData['key-data'];

        if ( !file_exists( $path ) )
            return false;
        $tstamp = filemtime( $path );
        $result = false;
        $canCache = true;
        $templateRoot = null;
        if ( !$handler->servesStaticData() )
            $canCache = false;
        $keyData = 'file:' . $path;
        if ( $method == EZ_RESOURCE_FETCH )
        {
            if ( $canCache )
            {
                if ( $handler->hasCachedProcessTree( $keyData, $uri, $resourceName, $path, $extraParameters, $tstamp ) )
                     $resourceData['process-cache'] = true;
            }
            if ( $canCache )
                $templateRoot = $handler->cachedTemplateTree( $keyData, $uri, $resourceName, $path, $extraParameters, $tstamp );

            if ( $templateRoot !== null )
                return true;

            $fd = fopen( $path, "r" );
            if ( $fd )
            {
                $text = fread( $fd, filesize( $path ) );
                $charset = "utf8";
                $pos = strpos( $text, "\n" );
                if ( $pos !== false )
                {
                    $line = substr( $text, 0, $pos );
                    if ( preg_match( "/^\{\*\?template(.+)\?\*\}/", $line, $tpl_arr ) )
                    {
                        $args = explode( " ", trim( $tpl_arr[1] ) );
                        foreach ( $args as $arg )
                        {
                            $vars = explode( '=', trim( $arg ) );
                            if ( $vars[0] == "charset" )
                            {
                                $val = $vars[1];
                                if ( $val[0] == '"' and
                                     strlen( $val ) > 0 and
                                     $val[strlen($val)-1] == '"' )
                                    $val = substr( $val, 1, strlen($val) - 2 );
                                $charset = $val;
                            }
                        }
                    }
                }
                if ( eZTemplate::isDebugEnabled() )
                    eZDebug::writeNotice( "$path, $charset" );
                $codec =& eZTextCodec::instance( $charset );
                eZDebug::accumulatorStart( 'templage_resource_conversion', 'template_total', 'String conversion in template resource' );
                $text = $codec->convertString( $text );
                eZDebug::accumulatorStop( 'templage_resource_conversion', 'template_total', 'String conversion in template resource' );
                $result = true;
                if ( eZTemplate::isDebugEnabled() )
                    $text = "<p class=\"small\">$path</p><br/>\n" . $text;
            }
        }
        else if ( $method == EZ_RESOURCE_QUERY )
            $result = true;
        return $result;
    }

    /// \privatesection
    /// The name of the resource
    var $Name;
    /// True if the data served from this resource is static, ie it can be cached properly
    var $ServesStaticData;
    /// The cache for templates
    var $TemplateCache;
}

?>
