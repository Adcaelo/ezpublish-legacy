<?php
//
// Created on: <11-Aug-2003 18:12:39 amos>
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

include_once( "kernel/common/template.php" );
include_once( "kernel/classes/ezpackage.php" );

$module =& $Params['Module'];
$offset = (int)$Params['Offset'];

if ( $module->isCurrentAction( 'InstallPackage' ) )
{
    return $module->redirectToView( 'upload' );
}

if ( $module->isCurrentAction( 'RemovePackage' ) )
{
    if ( $module->hasActionParameter( 'PackageSelection' ) )
    {
        $packageSelection = $module->actionParameter( 'PackageSelection' );
        foreach ( $packageSelection as $packageID )
        {
            $package =& eZPackage::fetch( $packageID );
            if ( $package )
            {
                $package->remove();
            }
        }
    }
}

if ( $module->isCurrentAction( 'CreatePackage' ) )
{
    return $module->redirectToView( 'create' );
}

$tpl =& templateInit();

$viewParameters = array( 'offset' => $offset );

$tpl->setVariable( 'view_parameters', $viewParameters );

$Result = array();
$Result['content'] =& $tpl->fetch( "design:package/list.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/package', 'Packages' ) ) );

?>
