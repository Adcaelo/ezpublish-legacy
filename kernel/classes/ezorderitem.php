<?php
//
// Created on: <05-Dec-2002 09:12:43 bf>
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
  \class eZOrderItem ezorderitem.php
  \brief eZOrderItem handles custom order items
  \ingroup eZKernel

  Custom order items are used to automatically add new items to
  a specific order. You can use it to e.g. specify shipping and
  handling, special discount or wrapping costs.

  The order items is different from the product collection items
  in the way that there is no product for each order item.

  \sa eZProductCollection eZBasket eZOrder
*/

include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezvattype.php" );

class eZOrderItem extends eZPersistentObject
{
    function eZOrderItem( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function &definition()
    {
        return array( "fields" => array( 'id' => 'ID',
                                         'order_id' => 'OrderID',
                                         'description' => 'Description',
                                         'price' => 'Price',
                                         'vat_is_included' => 'VATIsIncluded',
                                         'vat_type_id' => 'VATTypeID'
                                         ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZOrderItem',
                      'name' => 'ezorder_item' );
    }

    function &fetchList( $orderID, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZOrderItem::definition(),
                                                    null,
                                                    array( "order_id" => $orderID ),
                                                    null,
                                                    null,
                                                    $asObject );
    }

    function attribute( $attr )
    {
        if ( $attr == "vat_value" )
            return $this->vatValue();
        else if ( $attr == "price_inc_vat" )
            return $this->priceIncVAT();
        else if ( $attr == "price_ex_vat" )
            return $this->priceExVAT();
        else
            return eZPersistentObject::attribute( $attr );
    }

    function hasAttribute( $attr )
    {
        if ( $attr == "vat_value" )
            return true;
        else if ( $attr == "price_inc_vat" )
            return true;
        else if ( $attr == "price_ex_vat" )
            return true;
        else
            return eZPersistentObject::hasAttribute( $attr );
    }

    function &vatValue()
    {
        if ( $this->VATValue === false )
        {
            $vatType =& eZVATType::fetch( $this->VATTypeID );
            $this->VATValue = $vatType->attribute( 'percentage' );
        }

        return $this->VATValue;
    }

    function &priceIncVAT()
    {
        if ( $this->IsVATIncluded )
        {
            return $this->Price;
        }
        else
        {
            $incVATPrice = $this->Price * ( $this->vatValue() + 100 ) / 100;
            return $incVATPrice;
        }

    }

    function &priceExVAT()
    {
        if ( $this->IsVATIncluded )
        {
            $exVATPrice = $this->Price / ( $this->vatValue() + 100 ) * 100;
            return $exVATPrice;
        }
        else
            return $this->Price;

    }

    /// Cached value of the vat percentage
    var $VATValue = false;
}

?>
