<?php
//
// Definition of eZBasket class
//
// Created on: <04-Jul-2002 15:28:58 bf>
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
  \class eZBasket ezbasket.php
  \brief eZBasket handles shopping baskets
  \ingroup eZKernel

  \sa eZProductCollection
*/

include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezproductcollection.php" );
include_once( "kernel/classes/ezproductcollectionitem.php" );
include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
include_once( "kernel/classes/ezuserdiscountrule.php" );
include_once( "kernel/classes/ezcontentobjecttreenode.php" );

class eZBasket extends eZPersistentObject
{
    /*!
    */
    function eZBasket( $row )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \return the persistent object definition for the eZCard class.
    */
    function &definition()
    {
        return array( "fields" => array( "id" => "ID",
                                         "session_id" => "SessionID",
                                         "productcollection_id" => "ProductCollectionID"
                                         ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZBasket",
                      "name" => "ezbasket" );
    }

    function attribute( $attr )
    {
        if ( $attr == "items" )
            return $this->items();
        else if ( $attr == "total_ex_vat" )
            return $this->totalExVAT();
        else if ( $attr == "total_inc_vat" )
            return $this->totalIncVAT();
        else if ( $attr == "is_empty" )
            return $this->isEmpty();
        else
            return eZPersistentObject::attribute( $attr );
    }

    function hasAttribute( $attr )
    {
        if ( $attr == "items" )
            return true;
        else if ( $attr == "total_ex_vat" )
            return true;
        else if ( $attr == "total_inc_vat" )
            return true;
        else if ( $attr == "is_empty" )
            return true;
        else
            return eZPersistentObject::hasAttribute( $attr );
    }

    function &items( $asObject=true )
    {
        $productItems =& eZPersistentObject::fetchObjectList( eZProductCollectionItem::definition(),
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

            if ( $contentObject !== null )
            {
                $attributes = $contentObject->contentObjectAttributes();
                foreach ( $attributes as $attribute )
                {
                    $dataType =& $attribute->dataType();
                    if ( $dataType->isA() == "ezprice" )
                    {
                        $classAttribute =& $attribute->attribute( 'contentclass_attribute' );
                        $VATID =  $classAttribute->attribute( EZ_DATATYPESTRING_VAT_ID_FIELD );
                        $VATIncludeValue = $classAttribute->attribute( EZ_DATATYPESTRING_INCLUDE_VAT_FIELD );
                        if ( $VATIncludeValue==0 or $VATIncludeValue==1 )
                            $isVATIncluded = true;
                        else
                            $isVATIncluded = false;
                        $VATType =& eZVatType::fetch( $VATID );
                        $VATValue = $VATType->attribute( 'percentage' );

                        $priceObj =& $attribute->content();
                        $discountPercent = $priceObj->discount();
                    }
                }
                $nodeID = $contentObject->attribute( 'main_node_id' );
                $objectName = $contentObject->attribute( 'name' );
                $count = $productItem->attribute( 'item_count' );
                $price = $productItem->attribute( 'price' );
                if ( $isVATIncluded )
                {
                    $priceExVAT = $price / ( 100 + $VATValue ) * 100;
                    $priceIncVAT = $price;
                    $totalPriceExVAT = $count * $priceExVAT * ( 100 - $discountPercent ) / 100;
                    $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                }
                else
                {
                    $priceExVAT = $price;
                    $priceIncVAT = $price * ( 100 + $VATValue ) / 100;
                    $totalPriceExVAT = $count * $priceExVAT  * ( 100 - $discountPercent ) / 100;
                    $totalPriceIncVAT = $count * $priceIncVAT * ( 100 - $discountPercent ) / 100 ;
                }
                $addedProduct = array( "id" => $id,
                                       "vat_value" => $VATValue,
                                       "item_count" => $count,
                                       "node_id" => $nodeID,
                                       "object_name" => $objectName,
                                       "price_ex_vat" => $priceExVAT,
                                       "price_inc_vat" => $priceIncVAT,
                                       "discount_percent" => $discountPercent,
                                       "total_price_ex_vat" => $totalPriceExVAT,
                                       "total_price_inc_vat" => $totalPriceIncVAT );
                $addedProducts[] = $addedProduct;
            }
        }
        return $addedProducts;
    }

    function &totalIncVAT()
    {
        $items =& $this->items();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item['total_price_inc_vat'];
        }
        return $total;
    }

    function &totalExVAT()
    {
        $items =& $this->items();

        $total = 0.0;
        foreach ( $items as $item )
        {
            $total += $item['total_price_ex_vat'];
        }
        return $total;
    }

    function removeItem( $itemID )
    {
        $item = eZProductCollectionItem::fetch( $itemID );
        $item->remove();
    }

    function isEmpty()
    {
        $items =& eZPersistentObject::fetchObjectList( eZProductCollectionItem::definition(),
                                                       null,
                                                       array( "productcollection_id" => $this->ProductCollectionID ),
                                                       null,
                                                       null,
                                                       false );
        if ( count( $items ) > 0 )
            return false;
        else
            return true;
    }

    /*!
     Will return the basket for the current session. If a basket does not exist one will be created.
     \return current eZBasket object
    */
    function &currentBasket( $asObject=true )
    {
        $http =& eZHTTPTool::instance();
        $sessionID = $http->sessionID();

        $basketList =& eZPersistentObject::fetchObjectList( eZBasket::definition(),
                                                          null, array( "session_id" => $sessionID
                                                                       ),
                                                          null, null,
                                                          $asObject );

        $currentBasket = false;
        if ( count( $basketList ) == 0 )
        {
            $collection =& eZProductCollection::create();
            $collection->store();

            $currentBasket = new eZBasket( array( "session_id" => $sessionID,
                                              "productcollection_id" => $collection->attribute( "id" ) ) );
            $currentBasket->store();
        }
        else
        {
            $currentBasket =& $basketList[0];
        }
        return $currentBasket;
    }
}

?>
