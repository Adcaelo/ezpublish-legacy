<?php
//
// Definition of eZShopFunctionCollection class
//
// Created on: <06-���-2003 10:34:21 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.5.x
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

/*! \file ezshopfunctioncollection.php
*/

/*!
  \class eZShopFunctionCollection ezshopfunctioncollection.php
  \brief The class eZShopFunctionCollection does

*/

class eZShopFunctionCollection
{
    /*!
     Constructor
    */
    function eZShopFunctionCollection()
    {
    }

    function &fetchBasket( )
    {
        include_once( 'kernel/classes/ezbasket.php' );
        $http =& eZHTTPTool::instance();
        $sessionID = $http->sessionID();

        $basketList =& eZPersistentObject::fetchObjectList( eZBasket::definition(),
                                                          null, array( "session_id" => $sessionID
                                                                       ),
                                                          null, null,
                                                          true );

        $currentBasket = false;
        if ( count( $basketList ) == 0 )
        {
            // If we don't have a stored basket we create a temporary
            // one which can be returned.
            $collection =& eZProductCollection::create();

            $currentBasket = new eZBasket( array( "session_id" => $sessionID,
                                                  "productcollection_id" => 0 ) );
        }
        else
        {
            $currentBasket =& $basketList[0];
        }

        if ( $currentBasket === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => $currentBasket );
    }

    function fetchBestSellList( $topParentNodeID, $limit )
    {
        include_once( 'kernel/classes/ezcontentobject.php' );
        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

        $node = eZContentObjectTreeNode::fetch( $topParentNodeID );
        if ( !isset( $node ) ) return array( 'result' => null );
        $nodePath = $node->attribute( 'path_string' );

        $query="SELECT sum(ezproductcollection_item.item_count) as count, ezproductcollection_item.contentobject_id
                  FROM ezcontentobject_tree,
                       ezproductcollection_item,
                       ezorder
                 WHERE ezcontentobject_tree.contentobject_id=ezproductcollection_item.contentobject_id AND
                       ezorder.productcollection_id=ezproductcollection_item.productcollection_id AND
                       ezcontentobject_tree.path_string like '$nodePath%'
                 GROUP BY ezproductcollection_item.contentobject_id
                 ORDER BY count desc
                 LIMIT $limit";

        $db =& eZDB::instance();
        $topList=& $db->arrayQuery( $query );

        $contentObjectList = array();
        foreach ( array_keys ( $topList ) as $key )
        {
            $objectID = $topList[$key]['contentobject_id'];
            $contentObject =& eZContentObject::fetch( $objectID );
            if ( $contentObject === null )
                return array( 'error' => array( 'error_type' => 'kernel',
                                                'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
            $contentObjectList[] = $contentObject;
        }
        return array( 'result' => $contentObjectList );
    }

    function fetchRelatedPurchaseList( $contentObjectID, $limit )
    {
        include_once( 'kernel/classes/ezcontentobject.php' );
        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

        $contentObjectID = (int)$contentObjectID;
        $db =& eZDB::instance();
        $tmpTableName = $db->generateUniqueTempTableName( 'ezproductcoll_tmp_%' );
        $db->createTempTable( "CREATE TEMPORARY TABLE $tmpTableName( productcollection_id int )" );
        $db->query( "INSERT INTO $tmpTableName SELECT ezorder.productcollection_id
                                                           FROM ezorder, ezproductcollection_item
                                                          WHERE ezorder.productcollection_id=ezproductcollection_item.productcollection_id
                                                            AND ezproductcollection_item.contentobject_id=$contentObjectID" );

        $query="SELECT sum(ezproductcollection_item.item_count) as count, contentobject_id FROM ezproductcollection_item, $tmpTableName
                 WHERE ezproductcollection_item.productcollection_id=$tmpTableName.productcollection_id
                   AND ezproductcollection_item.contentobject_id<>$contentObjectID
              GROUP BY ezproductcollection_item.contentobject_id
              ORDER BY count desc";

        $db =& eZDB::instance();
        $objectList=& $db->arrayQuery( $query, array( 'limit' => $limit ) );

        $db->dropTempTable( "DROP TABLE $tmpTableName" );
        $contentObjectList = array();
        foreach ( array_keys ( $objectList ) as $key )
        {
            $objectID = $objectList[$key]['contentobject_id'];
            $contentObject =& eZContentObject::fetch( $objectID );
            if ( $contentObject === null )
                return array( 'error' => array( 'error_type' => 'kernel',
                                                'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
            $contentObjectList[] = $contentObject;
        }
        return array( 'result' => $contentObjectList );
    }

    function &fetchWishList( $production_id, $offset = false, $limit = false )
    {
        include_once( 'kernel/classes/ezwishlist.php' );

        $result =& eZWishList::items( true, $production_id, $offset, $limit );
        return array( 'result' => $result );
    }

    function &fetchWishListCount( $production_id )
    {
        include_once( 'kernel/classes/ezwishlist.php' );

        $result =& eZWishList::itemCount( $production_id );
        return array( 'result' => $result );
    }
}

?>
