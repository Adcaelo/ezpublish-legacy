<?php
//
// Definition of eZOrder class
//
// Created on: <31-Jul-2002 14:00:03 bf>
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

/*!
  \class eZOrder ezorder.php
  \brief eZOrder handles orders
  \ingroup eZKernel

  \sa eZProductCollection eZBasket
*/

include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezproductcollection.php" );
include_once( "kernel/classes/ezproductcollectionitem.php" );
include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
include_once( "kernel/classes/ezuserdiscountrule.php" );
include_once( "kernel/classes/ezcontentobjecttreenode.php" );
include_once( "kernel/classes/ezorderitem.php" );


define ( "SHOW_NORMAL_ORDERS",   0 );
define ( "SHOW_ARCHIVED_ORDERS", 1 );
define ( "SHOW_ALL_ORDERS",      2 );

class eZOrder extends eZPersistentObject
{
    /*!
    */
    function eZOrder( $row )
    {
        $this->eZPersistentObject( $row );
        $this->Status = null;
    }

    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "order_nr" => array( 'name' => "OrderNr",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "is_temporary" => array( 'name' => "IsTemporary",
                                                                  'datatype' => 'integer',
                                                                  'default' => 1,
                                                                  'required' => true ),
                                         "user_id" => array( 'name' => "UserID",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "productcollection_id" => array( 'name' => "ProductCollectionID",
                                                                          'datatype' => 'integer',
                                                                          'default' => 0,
                                                                          'required' => true ),
                                         "data_text_1" => array( 'name' => "DataText1",
                                                                 'datatype' => 'text',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         "data_text_2" => array( 'name' => "DataText2",
                                                                 'datatype' => 'text',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         "account_identifier" => array( 'name' => "AccountIdentifier",
                                                                        'datatype' => 'string',
                                                                        'default' => 'default',
                                                                        'required' => true ),
                                         "created" => array( 'name' => "Created",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "ignore_vat" => array( 'name' => "IgnoreVAT",
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         "email" => array( 'name' => "Email",
                                                           'datatype' => 'string',
                                                           'default' => '',
                                                           'required' => true ),
                                         "status_id" => array( 'name' => 'StatusID',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => false ),
                                         "status_modified" => array( 'name' => "StatusModified",
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         "status_modifier_id" => array( 'name' => "StatusModifierID",
                                                                        'datatype' => 'integer',
                                                                        'default' => 0,
                                                                        'required' => true ),
                                         "is_archived" => array( 'name' => "IsArchived",
                                                                        'datatype' => 'integer',
                                                                        'default' => 0,
                                                                        'required' => true ) ),
                      'function_attributes' => array( 'status_name' => 'statusName',
                                                      'status' => 'statusObject',
                                                      'status_modification_list' => 'statusModificationList',
                                                      'product_items' => 'productItems',
                                                      'order_items' => 'orderItems',
                                                      'product_total_inc_vat' => 'productTotalIncVAT',
                                                      'product_total_ex_vat' => 'productTotalExVAT',
                                                      'total_inc_vat' => 'totalIncVAT',
                                                      'total_ex_vat' => 'totalExVAT',
                                                      'user' => 'user',
                                                      'account_view_template' => 'accountViewTemplate',
                                                      'account_information' => 'accountInformation',
                                                      'account_name' => 'accountName',
                                                      'account_email' => 'accountEmail',
                                                      'productcollection' => 'productCollection' ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZOrder",
                      "name" => "ezorder" );
    }


    /*!
     Makes a copy of the product collection it currently points to
     and sets the copied collection as the current collection.
     \note This will store the order with the new product collection.
     \return the new collection or \c false if something failed.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function &detachProductCollection()
    {
        $collection =& $this->productCollection();
        if ( !$collection )
        {
            $retValue = false;
            return $retValue;
        }

        $db =& eZDB::instance();
        $db->begin();
        $newCollection =& $collection->copy();
        if ( !$newCollection )
        {
            $db->commit();
            $retValue = false;
            return $retValue;
        }
        $this->setAttribute( 'productcollection_id', $newCollection->attribute( 'id' ) );
        $this->store();

        $db->commit();
        return $newCollection;
    }

    /*!
     \return the product collection which this order uses.
    */
    function &productCollection()
    {
        $collection = eZProductCollection::fetch( $this->attribute( 'productcollection_id' ) );
        return $collection;
    }

    function fetch( $id, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZOrder::definition(),
                                                null,
                                                array( "id" => $id ),
                                                $asObject );
    }

    function fetchList( $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZOrder::definition(),
                                                    null, null,
                                                    array( "created" => "desc" ), null,
                                                    $asObject );
    }

    function activeByUserID( $userID, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZOrder::definition(),
                                                    null,
                                                    array( "user_id" => $userID,
                                                           'is_temporary' => 0 ),
                                                    array( "created" => "desc" ), null,
                                                    $asObject );
    }

    function getShowOrdersQuery( $show, $table = null )
    {
        $table = ( is_null( $table ) ? "" : $table . "." );

        switch( $show )
        {
            case SHOW_NORMAL_ORDERS   : return $table."is_archived = '0'"; break;
            case SHOW_ARCHIVED_ORDERS : return $table."is_archived = '1'"; break;
            case SHOW_ALL_ORDERS      :
            default                   : return $table."is_archived IN (0, 1)"; break;
        }
    }


    /*!
     \return the active orders
    */
    function &active( $asObject = true, $offset, $limit, $sortField = "created", $sortOrder = "asc", $show = SHOW_NORMAL_ORDERS )
    {
        if ( $sortField == "user_name" )
        {
            $db =& eZDB::instance();

            $db_params = array();
            $db_params["offset"] =(int) $offset;
            $db_params["limit"] =(int) $limit;
            $sortOrder = $db->escapeString( $sortOrder );

            $query = "SELECT ezorder.*
                      FROM
                            ezorder,
                            ezcontentobject
                      WHERE
                            ".eZOrder::getShowOrdersQuery( $show, "ezorder" )." AND
                            ezorder.is_temporary = '0' AND
                            ezcontentobject.id = ezorder.user_id
                      ORDER BY ezcontentobject.name $sortOrder";
            $orderArray = $db->arrayQuery( $query, $db_params );
            if ( $asObject )
            {
                $retOrders = array();
                foreach ( $orderArray as $order )
                {
                    $order = new eZOrder( $order );
                    $retOrders[] = $order;
                }
                return $retOrders;
            }
            else
                return $orderArray;
        }
        else
        {
            $where['is_temporary'] = 0;
            if ( $show != SHOW_ALL_ORDERS )
            {
                $where['is_archived'] = $show;
            }

            $objectList = eZPersistentObject::fetchObjectList( eZOrder::definition(),
                                                               null,
                                                               $where ,
                                                               array( $sortField => $sortOrder ),
                                                               array( 'offset' => $offset,
                                                                      'length' => $limit ), $asObject );
            return $objectList;
        }
    }

    /*!
     \return the number of active orders
    */
    function activeCount( $offset, $limit, $show = SHOW_NORMAL_ORDERS )
    {
        $db =& eZDB::instance();

        $query = "SELECT count( * ) AS count FROM ezorder WHERE ".eZOrder::getShowOrdersQuery( $show )." AND is_temporary='0'";
        $countArray = $db->arrayQuery( $query );
        $result = isset( $countArray[0]['count'] ) ? $countArray[0]['count'] : 0;
        return $result;
    }

    /*!
     \return the number of active orders
    */
    function &orderStatistics( $year = false, $month = false )
    {
        if ( $year == false )
        {
            $startDate = 0;
            $stopDate = mktime( 0, 0, 0, 12, 31, 2037 );
        }
        else if ( $year != false and $month == false )
        {
            $nextYear = $year + 1;
            $startDate = mktime( 0, 0, 0, 1, 1, $year );
            $stopDate = mktime( 0, 0, 0, 1, 1, $nextYear );
        }
        else if ( $year != false and $month != false )
        {
            $nextMonth = $month + 1;
            $startDate = mktime( 0, 0, 0, $month, 1, $year );
            $stopDate = mktime( 23, 59, 59, $nextMonth, 0, $year );
        }

        $db =& eZDB::instance();
        $productArray = $db->arrayQuery(  "SELECT ezproductcollection_item.*,  ignore_vat, ezorder.created, currency_code FROM ezorder, ezproductcollection_item, ezproductcollection
                                                WHERE ezproductcollection.id=ezproductcollection_item.productcollection_id
                                                  AND ezproductcollection_item.productcollection_id=ezorder.productcollection_id
                                                  AND is_temporary='0'
                                                  AND ezorder.created >= '$startDate' AND ezorder.created < '$stopDate'
                                             ORDER BY contentobject_id, currency_code" );
        $currentContentObjectID = 0;
        $productItemArray = array();
        $statisticArray = array();
        $productObject = null;
        $itemCount = 0;
        $totalSumIncVAT = array();
        $totalSumExVAT = array();
        $name = false;
        $productCount = count( $productArray );
        $productInfo = array();
        $totalSumInfo = array();
        foreach( $productArray as $productItem )
        {
            $itemCount++;
            $contentObjectID = $productItem['contentobject_id'];

            if (  $productObject == null )
            {
                $productObject =& eZContentObject::fetch( $contentObjectID );
                $currentContentObjectID = $contentObjectID;
            }

            if ( $currentContentObjectID != $contentObjectID && $itemCount != 1 )
            {
                $productItemArray[] = array( 'name' => $name,
                                             'product' => $productObject,
                                             'product_info' => $productInfo );
                $productInfo = array();
                unset( $productObject );
                $name = $productItem['name'];
                $currentContentObjectID = $contentObjectID;
                $productObject =& eZContentObject::fetch( $currentContentObjectID );
            }

            $currencyCode = $productItem['currency_code'];
            if ( !isset( $productInfo[$currencyCode] ) )
            {
                $productInfo[$currencyCode] = array( 'sum_count' => 0,
                                                     'sum_ex_vat' => 0,
                                                     'sum_inc_vat' => 0 );
            }
            if ( !isset( $totalSumInfo[$currencyCode] ) )
            {
                $totalSumInfo[$currencyCode] = array( 'sum_ex_vat' => 0,
                                                      'sum_inc_vat' => 0 );
            }

            if ( !isset( $totalSumIncVAT[$currencyCode] ) )
                $totalSumIncVAT[$currencyCode] = 0;

            if ( !isset( $totalSumExVAT[$currencyCode] ) )
                $totalSumExVAT[$currencyCode] = 0;

            if ( $productItem['ignore_vat']== true )
            {
                $vatValue = 0;
            }
            else
            {
                $vatValue = $productItem['vat_value'];
            }

            $count = $productItem['item_count'];
            $discountPercent = $productItem['discount'];

            $isVATIncluded = $productItem['is_vat_inc'];
            $price = $productItem['price'];

            if ( $isVATIncluded )
            {
                $priceExVAT = $price / ( 100 + $vatValue ) * 100;
                $priceIncVAT = $price;
                $totalPriceExVAT = $count * $priceExVAT * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
                $totalSumInfo[$currencyCode]['sum_ex_vat'] += $totalPriceExVAT;
                $totalSumInfo[$currencyCode]['sum_inc_vat'] += $totalPriceIncVAT;
            }
            else
            {
                $priceExVAT = $price;
                $priceIncVAT = $price * ( 100 + $vatValue ) / 100;
                $totalPriceExVAT = $count * $priceExVAT  * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
                $totalSumInfo[$currencyCode]['sum_ex_vat'] += $totalPriceExVAT;
                $totalSumInfo[$currencyCode]['sum_inc_vat'] += $totalPriceIncVAT;
            }

            $productInfo[$currencyCode]['sum_count'] += $count;
            $productInfo[$currencyCode]['sum_ex_vat'] += $totalPriceExVAT;
            $productInfo[$currencyCode]['sum_inc_vat'] += $totalPriceIncVAT;
        }

        // add last product info
        if ( $productCount != 0 )
            $productItemArray[] = array( 'name' => $name,
                                         'product' => $productObject,
                                         'product_info' => $productInfo );

        $statisticArray[] = array( 'product_list' => $productItemArray,
                                   'total_sum_info' => $totalSumInfo );
        return $statisticArray;
    }

    /*!
     \return list of products for a custom
    */
    function &orderList( $CustomID, $Email )
    {
        $db =& eZDB::instance();
        $CustomID =(int) $CustomID;
        $Email = $db->escapeString( $Email );
        if ( $Email == false )
        {
            $orderArray = $db->arrayQuery( "SELECT ezorder.* FROM ezorder
                                            WHERE user_id='$CustomID'
                                              AND is_archived='0'
                                              AND is_temporary='0'
                                         ORDER BY order_nr" );
        }
        else
        {
            $orderArray = $db->arrayQuery( "SELECT ezorder.* FROM ezorder
                                            WHERE user_id='$CustomID'
                                              AND is_archived='0'
                                              AND is_temporary='0'
                                              AND email='$Email'
                                         ORDER BY order_nr" );
        }
        $retOrders = array();
        for( $i=0; $i < count( $orderArray ); $i++ )
        {
            $order =& $orderArray[$i];
            $order = new eZOrder( $order );
            $retOrders[] = $order;
        }
        return $retOrders;
    }

    /*!
     \return list of products for a custom
    */
    function &productList( $CustomID, $Email )
    {
        $db =& eZDB::instance();
        $CustomID =(int) $CustomID;
        $Email = $db->escapeString( $Email );
        if ( $Email == false )
        {
            $productArray = $db->arrayQuery(  "SELECT ezproductcollection_item.*, ignore_vat, currency_code FROM ezorder, ezproductcollection_item, ezproductcollection
                                                WHERE ezproductcollection.id=ezproductcollection_item.productcollection_id
                                                  AND ezproductcollection_item.productcollection_id=ezorder.productcollection_id
                                                  AND user_id='$CustomID'
                                                  AND is_archived='0'
                                                  AND is_temporary='0'
                                             ORDER BY contentobject_id, currency_code" );
        }
        else
        {
            $productArray = $db->arrayQuery(  "SELECT ezproductcollection_item.*, ignore_vat, currency_code FROM ezorder, ezproductcollection_item, ezproductcollection
                                                WHERE ezproductcollection.id=ezproductcollection_item.productcollection_id
                                                  AND ezproductcollection_item.productcollection_id=ezorder.productcollection_id
                                                  AND user_id='$CustomID'
                                                  AND is_archived='0'
                                                  AND is_temporary='0'
                                                  AND email='$Email'
                                             ORDER BY contentobject_id, currency_code" );
        }
        $currentContentObjectID = 0;
        $productItemArray = array();
        $productObject = null;
        $itemCount = 0;
        $name = false;
        $productInfo = array();

        for( $i=0; $i < count( $productArray ); $i++ )
        {
            $productItem =& $productArray[$i];
            $itemCount++;
            $contentObjectID = $productItem['contentobject_id'];
            if ( $productObject == null )
            {
                if ( $contentObjectID != 0 )
                {
                    $productObject =& eZContentObject::fetch( $contentObjectID );
                }
                else
                {
                    $productObject = null;
                    $name = $productItem['name'];
                }
                $currentContentObjectID = $contentObjectID;
            }
            if ( $currentContentObjectID != $contentObjectID && $itemCount != 1 )
            {
                $productItemArray[] = array( 'name' => $name,
                                             'product' => $productObject,
                                             'product_info' => $productInfo );
                unset( $productObject );
                $productInfo = array();
                $name = $productItem['name'];
                $currentContentObjectID = $contentObjectID;
                if ( $contentObjectID != 0 )
                {
                    $productObject =& eZContentObject::fetch( $currentContentObjectID );
                }
                else
                {
                    $productObject = null;
                }
            }

            $currencyCode = $productItem['currency_code'];
            if ( !isset( $productInfo[$currencyCode] ) )
            {
                $productInfo[$currencyCode] = array( 'sum_count' => 0,
                                                     'sum_ex_vat' => 0,
                                                     'sum_inc_vat' => 0 );
            }

            if ( $productItem['ignore_vat'] == true )
            {
                $vatValue = 0;
            }
            else
            {
                $vatValue = $productItem['vat_value'];
            }

            $count = $productItem['item_count'];
            $discountPercent = $productItem['discount'];

            $isVATIncluded = $productItem['is_vat_inc'];
            $price = $productItem['price'];

            if ( $isVATIncluded )
            {
                $priceExVAT = $price / ( 100 + $vatValue ) * 100;
                $priceIncVAT = $price;
                $totalPriceExVAT = $count * $priceExVAT * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }
            else
            {
                $priceExVAT = $price;
                $priceIncVAT = $price * ( 100 + $vatValue ) / 100;
                $totalPriceExVAT = $count * $priceExVAT  * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }

            $productInfo[$currencyCode]['sum_count'] += $count;
            $productInfo[$currencyCode]['sum_ex_vat'] += $totalPriceExVAT;
            $productInfo[$currencyCode]['sum_inc_vat'] += $totalPriceIncVAT;
        }
        if ( count( $productArray ) != 0 )
        {
            $productItemArray[] = array( 'name' => $name,
                                         'product' => $productObject,
                                         'product_info' => $productInfo );
        }
        return $productItemArray;
    }

    /*!
     \returns number of customers.
    */
    function &customerCount( )
    {
        $db =& eZDB::instance();
        $countArray = $db->arrayQuery(  "SELECT count( DISTINCT email) AS count FROM ezorder WHERE is_temporary='0'" );
        $count = $countArray[0]['count'];
        return $count;
    }

    /*!
     \return the list customers.
    */
    function &customerList( $offset, $limit )
    {
        $db =& eZDB::instance();

        $db_params = array();
        $db_params["offset"] =(int) $offset;
        $db_params["limit"] =(int) $limit;

        $customEmailResult = $db->arrayQuery( "SELECT DISTINCT email FROM ezorder WHERE is_temporary='0' ORDER BY email", $db_params );
        $customEmailArray = array();

        foreach( $customEmailResult as $customEmailRow )
        {
            $customEmail = $customEmailRow['email'];
            $customEmailArray[] = "'" . $customEmail . "'";
        }

        $emailString = implode( ", ", $customEmailArray );
        if ( !strlen( $emailString ) )
        {
            $emailString = "''";
        }

        $productItemArray = $db->arrayQuery(  "SELECT ezorder.id as order_id, user_id, email, ignore_vat, currency_code, ezproductcollection_item.*
                                                 FROM ezorder, ezproductcollection_item, ezproductcollection
                                                WHERE ezproductcollection_item.productcollection_id=ezorder.productcollection_id
                                                  AND is_temporary='0'
                                                  AND ezproductcollection_item.productcollection_id=ezproductcollection.id
                                                  AND email in ( $emailString )
                                             ORDER BY user_id, email, order_id" );


        $siteIni =& eZINI::instance();
        $anonymousUserID = $siteIni->variable( 'UserSettings', 'AnonymousUserID' );

        $currentUserID = 0;
        $currentOrderID = 0;
        $currentUserEmail = "";
        $customArray = array();
        $accountName = null;
        $itemCount = 0;
        $hash = 0;
        $currencyCode = '';
        $ordersInfo = array();

        foreach( $productItemArray as $productItem )
        {
            $itemCount++;
            $currencyCode = $productItem['currency_code'];
            $userID = $productItem['user_id'];
            $orderID = $productItem['order_id'];
            $order = eZOrder::fetch( $orderID );

            if ( $currentUserID != $userID && $itemCount != 1 )
            {
                $customArray[] = array( 'account_name' => $accountName,
                                        'orders_info' => $ordersInfo,
                                        'user_id' => $currentUserID,
                                        'email' => urlencode( $currentUserEmail ) );

                $ordersInfo = array();
                $accountName = $order->attribute( 'account_name' );
                $accountEmail = $order->attribute( 'account_email' );
            }

            $currentUserID = $userID;

            // If the custom is anoymous user
            if ( $currentUserID == $anonymousUserID )
            {
                $accountEmail = $order->attribute( 'email' );
                if ( $currentUserEmail == "" )
                {
                    $accountName = $order->attribute( 'account_name' );
                    $currentUserEmail = $accountEmail;
                }

                if ( $currentUserEmail != $accountEmail )
                {
                    $customArray[] = array( 'account_name' => $accountName,
                                            'orders_info' => $ordersInfo,
                                            'user_id' => $currentUserID,
                                            'email' => urlencode( $currentUserEmail ) );

                    $ordersInfo = array();
                    $accountName = $order->attribute( 'account_name' );
                    $accountEmail = $order->attribute( 'account_email' );
                    $currentUserEmail = $accountEmail;
                }
                $currentUserEmail = $accountEmail;
            }
            else
            {
                $currentUserEmail = 0;
            }

            $accountName = $order->attribute( 'account_name' );

            if ( !isset( $ordersInfo[$currencyCode] ) )
            {
                $ordersInfo[$currencyCode] = array( 'order_count' => 0,
                                                    'sum_ex_vat' => 0,
                                                    'sum_inc_vat' => 0 );
            }

            if (  $currentOrderID != $orderID )
            {
                $ordersInfo[$currencyCode]['order_count']++;
            }
            $currentOrderID = $orderID;

            if ( $productItem['ignore_vat'] == true )
            {
                $vatValue = 0;
            }
            else
            {
                $vatValue = $productItem['vat_value'];
            }

            $count = $productItem['item_count'];
            $discountPercent = $productItem['discount'];

            $isVATIncluded = $productItem['is_vat_inc'];
            $price = $productItem['price'];

            if ( $isVATIncluded )
            {
                $priceExVAT = $price / ( 100 + $vatValue ) * 100;
                $priceIncVAT = $price;
                $totalPriceExVAT = $count * $priceExVAT * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }
            else
            {
                $priceExVAT = $price;
                $priceIncVAT = $price * ( 100 + $vatValue ) / 100;
                $totalPriceExVAT = $count * $priceExVAT  * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }

            $ordersInfo[$currencyCode]['sum_ex_vat'] += $totalPriceExVAT;
            $ordersInfo[$currencyCode]['sum_inc_vat'] += $totalPriceIncVAT;
        }
        if ( count( $productItemArray ) != 0 )
            $customArray[] = array( 'account_name' => $accountName,
                                    'orders_info' => $ordersInfo,
                                    'user_id' => $currentUserID,
                                    'email' => urlencode( $currentUserEmail ) );
        return $customArray;
    }

    /*!
     \returns the discountrules for a user
    */
    function discount( $userID, &$object )
    {
        include_once( 'kernel/classes/ezdiscount.php' );

        $user = eZUser::fetch( $userID );
        $bestMatch = eZDiscount::discountPersent( $user, array( 'contentclass_id' => $object->attribute( 'contentclass_id'),
                                                                'contentobject_id' => $object->attribute( 'id' ),
                                                                'section_id' => $object->attribute( 'section_id') ) );
        return $bestMatch;
    }

    function &productItems( $asObject=true )
    {
        $productItems = eZPersistentObject::fetchObjectList( eZProductCollectionItem::definition(),
                                                       null, array( "productcollection_id" => $this->ProductCollectionID
                                                                    ),
                                                       null,
                                                       null,
                                                       $asObject );

        $addedProducts = array();
        foreach ( $productItems as  $productItem )
        {
            $discountPercent = 0.0;
            $isVATIncluded = true;
            $id = $productItem->attribute( 'id' );
            $contentObject = $productItem->attribute( 'contentobject' );

            if ( $this->IgnoreVAT == true )
            {
                $vatValue = 0;
            }
            else
            {
                $vatValue = $productItem->attribute( 'vat_value' );
            }
            $count = $productItem->attribute( 'item_count' );
            $discountPercent = $productItem->attribute( 'discount' );
            if ( $contentObject )
            {
                $nodeID = $contentObject->attribute( 'main_node_id' );
                $objectName = $contentObject->attribute( 'name' );
            }
            else
            {
                $nodeID = false;
                $objectName = $productItem->attribute( 'name' );
            }

            $isVATIncluded = $productItem->attribute( 'is_vat_inc' );
            $price = $productItem->attribute( 'price' );

            if ( $isVATIncluded )
            {
                $priceExVAT = $price / ( 100 + $vatValue ) * 100;
                $priceIncVAT = $price;
                $totalPriceExVAT = $count * $priceExVAT * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }
            else
            {
                $priceExVAT = $price;
                $priceIncVAT = $price * ( 100 + $vatValue ) / 100;
                $totalPriceExVAT = $count * $priceExVAT  * ( 100 - $discountPercent ) / 100;
                $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                $totalPriceExVAT = round( $totalPriceExVAT, 2 );
                $totalPriceIncVAT = round( $totalPriceIncVAT, 2 );
            }

            $addedProduct = array( "id" => $id,
                                   "vat_value" => $vatValue,
                                   "item_count" => $count,
                                   "node_id" => $nodeID,
                                   "object_name" => $objectName,
                                   "price_ex_vat" => $priceExVAT,
                                   "price_inc_vat" => $priceIncVAT,
                                   "discount_percent" => $discountPercent,
                                   "total_price_ex_vat" => $totalPriceExVAT,
                                   "total_price_inc_vat" => $totalPriceIncVAT,
                                   'item_object' => $productItem );
            $addedProducts[] = $addedProduct;
        }
        return $addedProducts;
    }

    function &productTotalIncVAT()
    {
        $items =& $this->productItems();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item['total_price_inc_vat'];
        }
        $total = round( $total, 2 );
        return $total;
    }

    function &productTotalExVAT()
    {
        $items =& $this->productItems();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item['total_price_ex_vat'];
        }
        $total = round( $total, 2 );
        return $total;
    }

    function orderTotalIncVAT()
    {
        $items =& $this->orderItems();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item->attribute( 'price_inc_vat' );
        }
        $total = round( $total, 2 );
        return $total;
    }

    function orderTotalExVAT()
    {
        $items =& $this->orderItems();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item->attribute( 'price_ex_vat' );
        }
        $total = round( $total, 2 );
        return $total;
    }

    function &totalIncVAT()
    {
        $totalIncVAT = $this->productTotalIncVAT() + $this->orderTotalIncVAT();
        return $totalIncVAT;
    }

    function &totalExVAT()
    {
        $totalExVAT = $this->productTotalExVAT() + $this->orderTotalExVAT();
        return $totalExVAT;
    }

    /*!
     Removes the order.

     shop/confirmorder calls this method passing $removeCollection set to \c false
     to purge the order if the user doesn't confirm it.
     Removing the product collection in this case would cause fatal error.

     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     \param $removeCollection (bool) specifies if we should remove product collection the order uses.
    */
    function purge( $removeCollection = true )
    {
        $db =& eZDB::instance();
        $db->begin();
        if ( $removeCollection )
            $this->removeCollection();
        $this->removeHistory();
        $this->removeOrderItems();
        $this->remove();
        $db->commit();
    }

    /*!
     Removes the product collection this order uses.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function removeCollection()
    {
        $collection = eZProductCollection::fetch( $this->attribute( 'productcollection_id' ) );
        $collection->remove();
    }

    /*!
     Removes the order status history for this order.
     \note transaction unsafe
    */
    function removeHistory()
    {
        $db =& eZDB::instance();
        $orderID = (int)$this->OrderNr;
        $db->query( "DELETE FROM ezorder_status_history WHERE order_id=$orderID" );
    }

    function removeOrderItems()
    {
        $db =& eZDB::instance();
        $orderID = (int) $this->ID;
        $db->query( "DELETE FROM ezorder_item WHERE order_id=$orderID" );
    }

    /*!
     Removes the product collection item \a $itemID.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function removeItem( $itemID )
    {
        $item = eZProductCollectionItem::fetch( $itemID );
        $item->remove();
    }

    /*!
     \return the total VAT value of the order
    */
    function &totalVAT()
    {
        $retValue = false;
        return $retValue;
    }

    function &orderItems()
    {
        $items = eZOrderItem::fetchList( $this->ID );
        return $items;
    }

    /*!
    \param $type Order item type
    */
    function &orderItemsByType( $itemType )
    {
        $items = eZOrderItem::fetchListByType( $this->ID, $itemType );
        return $items;
    }

    /*!
     \return the user who has created the order.
    */
    function &user()
    {
        $user = eZUser::fetch( $this->UserID );
        return $user;
    }

    /*!
     \return the account information
     The shop account handler decides what is returned here
    */
    function &accountInformation()
    {
        // Fetch the shop account handler
        include_once( 'kernel/classes/ezshopaccounthandler.php' );
        $accountHandler =& eZShopAccountHandler::instance();
        $accountInformation = $accountHandler->accountInformation( $this );
        return $accountInformation;
    }


    /*!
     \return the custom name
     The shop account handler decides what is returned here
    */
    function &accountName()
    {
        // Fetch the shop account handler
        include_once( 'kernel/classes/ezshopaccounthandler.php' );
        $accountHandler =& eZShopAccountHandler::instance();

        $accountName = $accountHandler->accountName( $this );
        return $accountName;
    }

    /*!
     \return the account email
     The shop account handler decides what is returned here
    */
    function &accountEmail()
    {
        // Fetch the shop account handler
        include_once( 'kernel/classes/ezshopaccounthandler.php' );
        $accountHandler =& eZShopAccountHandler::instance();

        $email = $accountHandler->email( $this );
        return $email;
    }

    /*!
     \return The status object if \a $asObject is \c true, otherwise the status ID.
    */
    function status( $asObject = false )
    {
        if ( $asObject )
            return eZOrderStatus::fetch( $this->StatusID );
        else
            return $this->StatusID;
    }

    /*!
      \return \c true if the user \a $user can modify the status to $statusID
    */
    function canModifyStatus( $statusID, $user = false )
    {
        if ( $user === false )
            $user =& eZUser::currentUser();
        else if ( is_numeric( $user ) )
            $user = eZUser::fetch( $user );

        if ( !is_object( $user ) )
        {
            eZDebug::writeError( "Cannot check status access without a user", 'eZOrder::canModifyStatus' );
            return false;
        }

        $accessResult = $user->hasAccessTo( 'shop' , 'setstatus' );
        $accessWord = $accessResult['accessWord'];
        $access = false;

        $currentStatusID = $this->attribute( "status_id" );

        if ( $accessWord == 'yes' )
            $access = true;

        if ( $accessWord == 'limited' )
        {
            $limitationList =& $accessResult['policies'];
            $access = true;
            foreach ( $limitationList as $pid => $limit )
            {
                $access = true;
                foreach ( $limit as $name => $value )
                {
                    if ( $name == 'FromStatus' )
                    {
                        if ( !in_array( $currentStatusID, $value )  )
                            $access = false;
                    }
                    if ( $name == 'ToStatus' )
                    {
                        if ( !in_array( $statusID, $value ) )
                            $access = false;
                    }
                    if ( !$access )
                        break;
                }
                if ( $access )
                    break;
            }
        }
        return $access;
    }

    /*!
     \return A list of status items that the current user can set this order to.
     \note If the user doesn't have any access at all for this order it will
           return an empty array.
    */
    function &statusModificationList( $user = false )
    {
        if ( $user === false )
            $user =& eZUser::currentUser();
        else if ( is_numeric( $user ) )
            $user = eZUser::fetch( $user );

        if ( !is_object( $user ) )
        {
            eZDebug::writeError( "Cannot calculate status access list without a user", 'eZOrder::canModifyStatus' );
            $retValue = false;
            return $retValue;
        }

        $accessResult = $user->hasAccessTo( 'shop' , 'setstatus' );
        $accessWord = $accessResult['accessWord'];
        $access = false;

        $currentStatusID = $this->attribute( "status_id" );

        $statusList = array();
        if ( $accessWord == 'yes' )
        {
            // We have full access so we return all of them
            include_once( 'kernel/classes/ezorderstatus.php' );
            $statusList = eZOrderStatus::fetchOrderedList( true, false );
            return $statusList;
        }

        if ( $accessWord == 'limited' )
        {
            $limitationList =& $accessResult['policies'];
            $access = true;
            // All 'to' statues will be appended to this array
            $accessList = array();
            foreach ( $limitationList as $pid => $limit )
            {
                $access = true;
                foreach ( $limit as $name => $value )
                {
                    if ( $name == 'FromStatus' )
                    {
                        if ( !in_array( $currentStatusID, $value )  )
                            $access = false;
                    }
                    if ( !$access )
                        break;
                }
                if ( $access )
                {
                    if ( isset( $limit['ToStatus'] ) )
                    {
                        $accessList = array_merge( $accessList, $limit['ToStatus'] );
                    }
                    else
                    {
                        // We have full access for the current status so we return all of them
                        include_once( 'kernel/classes/ezorderstatus.php' );
                        $statusList = eZOrderStatus::fetchOrderedList( true, false );
                        return $statusList;
                    }
                }
            }
            if ( count( $accessList ) > 0 )
            {
                $accessList = array_unique( array_merge( $accessList, array( $currentStatusID ) ) );
                include_once( 'kernel/classes/ezorderstatus.php' );
                $statuses = eZOrderStatus::fetchOrderedList( true, false );
                foreach ( $statuses as $status )
                {
                    if ( in_array( $status->attribute( 'status_id' ), $accessList ) )
                        $statusList[] = $status;
                }
            }
        }
        return $statusList;
    }

    /*!
     Modifies the status on the order to $statusID.
     It will store the previous status in the history list using \a $userID.
     \param $statusID The ID of the status that is to be set, this must be the global ID not the DB ID.
     \param $userID The ID of the user that did the change, if \c false it will fetch the current user ID.

     \note transaction safe
     \note If you only want to change the status ID use the setStatus() function instead.
    */
    function modifyStatus( $statusID, $userID = false )
    {
        $db =& eZDB::instance();
        $db->begin();

        $time = mktime();
        if ( $userID === false )
            $userID = eZUser::currentUserID();

        include_once( 'kernel/classes/ezorderstatushistory.php' );
        $history = eZOrderStatusHistory::create( $this->OrderNr, $statusID, $userID, $time );
        $history->store();

        $this->StatusID = $statusID;
        $this->StatusModified = $time;
        $this->StatusModifierID = $userID;

        $this->store();

        $db->commit();
    }

    /*!
     Creates a status history element from the the current status information
     and stores it in the database.
     \return The new history element that was stored in the database.
     \note This is usually only needed the first time an order is created.
     \note transaction unsafe
    */
    function createStatusHistory()
    {
        include_once( 'kernel/classes/ezorderstatushistory.php' );
        $history = eZOrderStatusHistory::create( $this->OrderNr, // Note: Use the order nr, not id
                                                 $this->StatusID,
                                                 $this->StatusModifierID,
                                                 $this->StatusModified );
        $history->store();
        return $history;
    }

    /*!
     Sets the status ID to \a $status and updates the status modification timestamp.
     \note This does not create a status history element, use modifyStatus() instead.
    */
    function setStatus( $status )
    {
        if ( get_class( $status ) == "ezorderstatus" )
            $this->StatusID = $status->attribute( 'id' );
        else
            $this->StatusID = $status;
        $this->setStatusModified( mktime() );
    }

    /*!
     Sets the modification time of the status change to \a $timestamp.
    */
    function setStatusModified( $timestamp )
    {
        $this->StatusModified = $timestamp;
    }

    /*!
     \return The name of the current status.
     \note It will cache the current status object in the $Status member variable
           to make multiple calls to this function fast.
    */
    function &statusName()
    {
        if ( $this->Status === null )
        {
            include_once( 'kernel/classes/ezorderstatus.php' );
            $this->Status = eZOrderStatus::fetchByStatus( $this->StatusID );
        }
        $name = $this->Status->attribute( 'name' );;
        return $name;
    }

    /*!
     \return The current status object.
     \note It will cache the current status object in the $Status member variable
           to make multiple calls to this function fast.
    */
    function &statusObject()
    {
        if ( $this->Status === null )
        {
            include_once( 'kernel/classes/ezorderstatus.php' );
            $this->Status = eZOrderStatus::fetchByStatus( $this->StatusID );
        }
        return $this->Status;
    }

    /*!
     Creates a real order from the temporary state.

     The ezorder has an internal (id) and an external (order_nr) ID.
     The ID will be set as soon as a customer (who buys a product), approves the account information.
     A row is added to the ezorder table (the ID (auto_increment) is generated).
     This ID is needed for extensions, such as PaynetTerminal (The ID is given.)

     The active (confirmed and paid) orders have an order_nr.
     This order_nr is generated as soon as the order is processed.

     We use order_nr instead of the id to (externally) rever to an order:
     - We don't have gaps in the order_nr's.
     - The lowest order_nr goes to the order which is accepted first. (Not started first.)


     Another solution would be to use an temporary_order table, instead of adding two IDs.
     I think (rb) this is a cleaner solution.

     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function activate()
    {
        $db =& eZDB::instance();
        $db->lock( 'ezorder' );

        $this->setAttribute( 'is_temporary', 0 );
        $nextIDArray = $db->arrayQuery(  "SELECT ( max( order_nr ) + 1 ) AS next_nr FROM ezorder" );
        $nextID = $nextIDArray[0]['next_nr'];
        $this->setAttribute( 'order_nr', $nextID );
        $this->store();

        $db->unlock();

        // Create an order status history element that matches the current status
        $this->createStatusHistory();
    }

    /*!
     \return the template to use for account view
    */
    function &accountViewTemplate()
    {
        return $this->AccountIdentifier;
    }

    /*!
     \static
     Removes an order and its related data from the database.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function cleanupOrder( $orderID )
    {
        $db =& eZDB::instance();
        $orderID =(int) $orderID;
        $rows = $db->arrayQuery( "SELECT productcollection_id, order_nr FROM ezorder WHERE id='$orderID'" );
        if ( count( $rows ) > 0 )
        {
            // Who deletes which order in shop should be logged.
            include_once( "kernel/classes/ezaudit.php" );
            eZAudit::writeAudit( 'order-delete', array( 'Order ID' => $orderID,
                                                        'Comment' => 'Removed the order and its related data from the database: eZOrder::cleanupOrder()' ) );

            $productCollectionID = $rows[0]['productcollection_id'];
            $orderNr = (int)$rows[0]['order_nr'];
            $db =& eZDB::instance();
            $db->begin();
            $db->query( "DELETE FROM ezorder where id='$orderID'" );
            $db->query( "DELETE FROM ezproductcollection where id='$productCollectionID'" );
            $db->query( "DELETE FROM ezproductcollection_item where productcollection_id='$productCollectionID'" );
            $db->query( "DELETE FROM ezorder_status_history WHERE order_id=$orderNr" );
            $db->commit();
        }
    }

    /*!
     \static
     Archive an order.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function archiveOrder( $orderID )
    {
        $db =& eZDB::instance();
        $orderID =(int) $orderID;
        $db->query( "UPDATE ezorder SET is_archived='1' WHERE id='$orderID' " );
    }

    /*!
     \static
     Unarchive an order, make it 'normal' again.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function unArchiveOrder( $orderID )
    {
        $db =& eZDB::instance();
        $orderID =(int) $orderID;
        $db->query( "UPDATE ezorder SET is_archived='0' WHERE id='$orderID' " );
    }

    /*!
     \static
     Removes all orders from the database.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function cleanup()
    {
        $db =& eZDB::instance();
        $rows = $db->arrayQuery( "SELECT productcollection_id FROM ezorder" );

        $db =& eZDB::instance();
        $db->begin();
        if ( count( $rows ) > 0 )
        {
            $productCollectionIDList = array();
            foreach ( $rows as $row )
            {
                $productCollectionIDList[] = $row['productcollection_id'];
            }
            eZProductCollection::cleanupList( $productCollectionIDList );
        }
        // Who deletes which order in shop should be logged.
        include_once( "kernel/classes/ezaudit.php" );
        eZAudit::writeAudit( 'order-delete', array( 'Comment' => 'Removed all orders from the database: eZOrder::cleanup()' ) );

        include_once( 'kernel/classes/ezorderitem.php' );
        eZOrderItem::cleanup();
        $db->query( "DELETE FROM ezorder_status_history" );
        $db->query( "DELETE FROM ezorder" );
        $db->commit();
    }

    /// \privatesection
    /// The cached status object or \c null if not cached yet.
    var $Status;

}

?>
