<?php
//
// Definition of eZProductCollectionItemOption class
//
// Created on: <10-���-2003 16:04:18 sp>
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

/*! \file ezproductcollectionitemoption.php
*/

/*!
  \class eZProductCollectionItemOption ezproductcollectionitemoption.php
  \brief The class eZProductCollectionItemOption does

*/

class eZProductCollectionItemOption extends eZPersistentObject
{
    /*!
     Constructor
    */
    function eZProductCollectionItemOption( $row )
    {
        $this->eZPersistentObject( $row );

    }

    function &definition()
    {
        return array( "fields" => array( "id" => "ID",
                                         'item_id' => 'ItemID',
                                         'option_item_id' => 'OptionItemID',
                                         'object_attribute_id' => 'ObjectAttributeID',
                                         'name' => 'Name',
                                         'value' => 'Value',
                                         'price' => 'Price' ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZProductCollectionItemOption",
                      "name" => "ezproductcollection_item_opt" );
    }

    function &create( $productCollectionItemID, $optionItemID, $optionName, $optionValue, $optionPrice, $attributeID )
    {
        $row = array( 'item_id' => $productCollectionItemID,
                      'option_item_id' => $optionItemID,
                      'name' => $optionName,
                      'value' => $optionValue,
                      'price' => $optionPrice,
                      'object_attribute_id' => $attributeID );
        return new eZProductCollectionItemOption( $row );
    }

    function &fetchList( $productCollectionItemID, $asObject = true )
    {
        $productItemOptions =& eZPersistentObject::fetchObjectList( eZProductCollectionItemOption::definition(),
                                                                    null, array( "item_id" => $productCollectionItemID,
                                                                                 ),
                                                                    array( "id" => "ASC"  ),
                                                                    null,
                                                                    $asObject );
        return $productItemOptions;
    }
}

?>
