<?php
//
// Created on: <23-Sen-2005 13:42:58 vd>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
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

include_once( "kernel/classes/ezcontentobject.php" );
include_once( "lib/ezutils/classes/ezhttptool.php" );
include_once( "kernel/common/template.php" );

$http =& eZHTTPTool::instance();

$Module =& $Params['Module'];
$contentObjectID =& $Params['ObjectID'];

if ( $http->hasPostVariable( "BackButton" ) )
{
    $userRedirectURI = $http->sessionVariable( 'userRedirectURIReverseObjects' );
    $http->removeSessionVariable( 'userRedirectURIReverseObjects' );
    return $Module->redirectTo( $userRedirectURI );
}

$contentObject = eZContentObject::fetch( $contentObjectID );
if ( !$contentObject )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

$contentObjectName = $contentObject->attribute( 'name' );

$reverseRelatedObjectList = $contentObject->reverseRelatedObjectList( false, false, false, 1 );
$reverseRelatedObjectCount = $contentObject->reverseRelatedObjectCount( false, false, false, 1 );

$tpl =& templateInit();

$tpl->setVariable( 'content_object_name', $contentObjectName );
$tpl->setVariable( 'reverse_related_object_count', $reverseRelatedObjectCount );
$tpl->setVariable( 'reverse_related_object_list', $reverseRelatedObjectList );

$Result = array();
$Result['content'] =& $tpl->fetch( "design:content/view/viewreverseobjects.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/content', "Objects referring to $contentObjectName" ) ) );

?>
