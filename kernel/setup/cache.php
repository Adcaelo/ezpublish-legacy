<?php
//
// Created on: <15-Apr-2003 11:25:31 bf>
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

$http =& eZHTTPTool::instance();
$module =& $Params["Module"];

include_once( "kernel/common/template.php" );
include_once( 'lib/ezutils/classes/ezhttptool.php' );
include_once( 'lib/ezutils/classes/ezdir.php' );

$ini =& eZINI::instance( );
$tpl =& templateInit();

$viewCacheCleared = false;
if ( $module->isCurrentAction( 'ClearContentCache' ) )
{
    $cacheDir = eZSys::cacheDirectory() . "/" . $ini->variable( 'ContentSettings', 'CacheDir' );
    eZDir::recursiveDelete( $cacheDir );
    $viewCacheCleared = true;
}

$iniCacheCleared = false;
if ( $module->isCurrentAction( 'ClearINICache' ) )
{
    $cachedDir = eZSys::cacheDirectory() . '/ini';
    eZDir::recursiveDelete( $cachedDir );
    $iniCacheCleared = true;
}

$templateCacheCleared = false;
if ( $module->isCurrentAction( 'ClearTemplateCache' ) )
{
    $cacheSubDirs = array( 'template', 'template-block', 'override' );

    foreach( $cacheSubDirs as $cacheSubDir )
    {
        eZDir::recursiveDelete( eZSys::cacheDirectory() . '/' . $cacheSubDir );
    }
    $templateCacheCleared = true;
}

if ( $ini->variable( 'ContentSettings', 'ViewCaching' ) == 'enabled' )
    $tpl->setVariable( "view_cache_enabled", true );
else
    $tpl->setVariable( "view_cache_enabled", false );

$tpl->setVariable( "view_cache_cleared", $viewCacheCleared );
$tpl->setVariable( "ini_cache_cleared", $iniCacheCleared );
$tpl->setVariable( "template_cache_cleared", $templateCacheCleared );


$Result = array();
$Result['content'] =& $tpl->fetch( "design:setup/cache.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/setup', 'Cache admin' ) ) );

?>
