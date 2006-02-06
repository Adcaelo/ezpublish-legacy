<?php
//
// Created on: <05-Dec-2002 09:12:43 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.7.x
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

    function definition()
    {
        return array( "fields" => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'order_id' => array( 'name' => 'OrderID',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         'description' => array( 'name' => 'Description',
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         'price' => array( 'name' => 'Price',
                                                           'datatype' => 'float',
                                                           'default' => 0,
                                                           'required' => true ),
                                         'vat_value' => array( 'name' => 'VATValue',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ) ),
                      'keys' => array( 'id' ),
                      'function_attributes' => array( 'vat_value' => 'vatValue',
                                                      'price_inc_vat' => 'priceIncVat',
                                                      'price_ex_vat' => 'priceExVAT' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZOrderItem',
                      'name' => 'ezorder_item' );
    }

    function fetchList( $orderID, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZOrderItem::definition(),
                                                    null,
                                                    array( "order_id" => $orderID ),
                                                    null,
                                                    null,
                                                    $asObject );
    }

    function &vatValue()
    {
        if ( $this->VATValue === false )
        {
            $vatType = eZVATType::fetch( $this->VATTypeID );
            $this->VATValue =& $vatType->attribute( 'percentage' );
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

    /*!
     \static
     Removes all order items from the database.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function cleanup()
    {
        $db =& eZDB::instance();
        $db->query( "DELETE FROM ezorder_item" );
    }

    /// Cached value of the vat percentage
    var $VATValue = false;
    var $IsVATIncluded = false;
}

?>
