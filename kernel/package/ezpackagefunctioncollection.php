<?php
//
// Definition of eZPackageFunctionCollection class
//
// Created on: <11-Aug-2003 18:30:26 amos>
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

/*! \file ezpackagefunctioncollection.php
*/

/*!
  \class eZPackageFunctionCollection ezpackagefunctioncollection.php
  \brief The class eZPackageFunctionCollection does

*/

include_once( 'kernel/error/errors.php' );

class eZPackageFunctionCollection
{
    /*!
     Constructor
    */
    function eZPackageFunctionCollection()
    {
    }

    function &fetchList( $filterArray = false, $offset, $limit )
    {
        $filterParams = array();
        $filterList = false;
        if ( isset( $filterArray ) and
             is_array( $filterArray ) and
             count( $filterArray ) > 0 )
        {
            $filterList = $filterArray;
            if ( count( $filterArray ) > 1 and
                 !is_array( $filterArray[0] ) )
            {
                $filterList = array( $filterArray );
            }
        }
        if ( $filterList !== false )
        {
            foreach ( $filterList as $filter )
            {
                if ( is_array( $filter ) and count( $filter ) > 0 )
                {
                    $filterName = $filter[0];
                    switch ( $filterName )
                    {
                        case 'type':
                        {
                            $typeValue = $filter[1];
                            $typeParam = array( 'type' => $typeValue );
                            $filterParams = array_merge( $filterParams, $typeParam );
                        } break;
                        case 'priority':
                        {
                            $priorityValue = $filter[1];
                            $priorityParam = array( 'priority' => $priorityValue );
                            $filterParams = array_merge( $filterParams, $priorityParam );
                        } break;
                        case 'vendor':
                        {
                            $vendorValue = $filter[1];
                            $vendorParam = array( 'vendor' => $vendorValue );
                            $filterParams = array_merge( $filterParams, $vendorParam );
                        } break;
                        case 'extension':
                        {
                            $extensionValue = $filter[1];
                            $extensionParam = array( 'extension' => $extensionValue );
                            $filterParams = array_merge( $filterParams, $extensionParam );
                        } break;
                        default:
                        {
                            eZDebug::writeWarning( 'Unknown package filter name: ' . $filterName );
                            continue;
                        };
                    }
                }
            }
        }

        include_once( 'kernel/classes/ezpackage.php' );
        $packageList =& eZPackage::fetchPackages( array( 'offset' => $offset,
                                                         'limit' => $limit ),
                                                  $filterParams );
        if ( $packageList === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => $packageList );
    }

    function &fetchPackage( $packageName )
    {
        include_once( 'kernel/classes/ezpackage.php' );
        $package =& eZPackage::fetch( $packageName );
        if ( $package === false )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => $package );
    }

    function &fetchDependentPackageList( $packageName, $filterArray = false )
    {
        $filterParams = array();
        $filterList = false;
        if ( isset( $filterArray ) and
             is_array( $filterArray ) and
             count( $filterArray ) > 0 )
        {
            $filterList = $filterArray;
            if ( count( $filterArray ) > 1 and
                 !is_array( $filterArray[0] ) )
            {
                $filterList = array( $filterArray );
            }
        }
        if ( $filterList !== false )
        {
            foreach ( $filterList as $filter )
            {
                if ( is_array( $filter ) and count( $filter ) > 0 )
                {
                    $filterName = $filter[0];
                    switch ( $filterName )
                    {
                        case 'type':
                        {
                            $typeValue = $filter[1];
                            $typeParam = array( 'type' => $typeValue );
                            $filterParams = array_merge( $filterParams, $typeParam );
                        } break;
                        case 'name':
                        {
                            $nameValue = $filter[1];
                            $nameParam = array( 'name' => $nameValue );
                            $filterParams = array_merge( $filterParams, $nameParam );
                        } break;
                        case 'priority':
                        {
                            $priorityValue = $filter[1];
                            $priorityParam = array( 'priority' => $priorityValue );
                            $filterParams = array_merge( $filterParams, $priorityParam );
                        } break;
                        case 'vendor':
                        {
                            $vendorValue = $filter[1];
                            $vendorParam = array( 'vendor' => $vendorValue );
                            $filterParams = array_merge( $filterParams, $vendorParam );
                        } break;
                        case 'extension':
                        {
                            $extensionValue = $filter[1];
                            $extensionParam = array( 'extension' => $extensionValue );
                            $filterParams = array_merge( $filterParams, $extensionParam );
                        } break;
                        default:
                        {
                            eZDebug::writeWarning( 'Unknown package filter name: ' . $filterName );
                            continue;
                        };
                    }
                }
            }
        }
        include_once( 'kernel/classes/ezpackage.php' );
        $package =& eZPackage::fetch( $packageName );
        $packageList =& $package->fetchDependentPackages( $filterParams );
        if ( $packageList === false )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => $packageList );
    }
}

?>
