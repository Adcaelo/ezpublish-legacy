<?php
//
// Definition of eZEnum class
//
// Created on: <24-��-2002 16:07:05 wy>
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

/*! \file ezenumobjectvalue.php
*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezcontentclassattribute.php" );

/*!
  \class eZEnumObjectValue ezenumobjectvalue.php
  \brief The class eZEnumObjectValue stores chosen enum values of an object attribute

*/

class eZEnumObjectValue extends eZPersistentObject
{
    /*!
     Constructor
    */
    function eZEnumObjectValue( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function &definition()
    {
        return array( "fields" => array( "contentobject_attribute_id" => "ContentObjectAttributeID",
                                         "contentobject_attribute_version" => "ContentObjectAttributeVersion",
                                         "enumid" => "EnumID",
                                         "enumelement" => "EnumElement",
                                         "enumvalue" => "EnumValue"),
                      "keys" => array( "contentobject_attribute_id", "contentobject_attribute_version", "enumid" ),
                      "sort" => array( "contentobject_attribute_id" => "asc" ),
                      "class_name" => "eZEnumObjectValue",
                      "name" => "ezenumobjectvalue" );
    }

    function &create( $contentObjectAttributeID, $contentObjectAttributeVersion, $enumID, $enumElement, $enumValue )
    {
        $row = array( "contentobject_attribute_id" => $contentObjectAttributeID,
                      "contentobject_attribute_version" => $contentObjectAttributeVersion,
                      "enumid" => $enumID,
                      "enumelement" =>  $enumElement,
                      "enumvalue" => $enumValue );
        return new eZEnumObjectValue( $row );
    }

    function &removeAllElements( $contentObjectAttributeID, $contentObjectAttributeVersion )
    {
        if( $contentObjectAttributeVersion == null )
        {
            eZPersistentObject::removeObject( eZEnumObjectValue::definition(),
                                              array( "contentobject_attribute_id" => $contentObjectAttributeID ) );
        }
        else
        {
            eZPersistentObject::removeObject( eZEnumObjectValue::definition(),
                                              array( "contentobject_attribute_id" => $contentObjectAttributeID,
                                                     "contentobject_attribute_version" => $contentObjectAttributeVersion ) );
        }
    }

    function &remove( $contentObjectAttributeID, $contentObjectAttributeVersion, $enumid )
    {
        eZPersistentObject::removeObject( eZEnumObjectValue::definition(),
                                          array( "enumid" => $enumid,
                                                 "contentobject_attribute_id" => $contentObjectAttributeID,
                                                 "contentobject_attribute_version" => $contentObjectAttributeVersion ) );
    }

    function &fetch( $contentObjectAttributeID, $contentObjectAttributeVersion, $enumid, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZEnumObjectValue::definition(),
                                                null,
                                                array(  "contentobject_attribute_id" => $contentObjectAttributeID,
                                                        "contentobject_attribute_version" => $contentObjectAttributeVersion,
                                                        "enumid" => $enumid ),
                                                $asObject );
    }

    function &fetchAllElements( $contentObjectAttributeID, $contentObjectAttributeVersion, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZEnumObjectValue::definition(),
                                                    null,
                                                    array( "contentobject_attribute_id" => $contentObjectAttributeID,
                                                           "contentobject_attribute_version" => $contentObjectAttributeVersion ),
                                                    null,
                                                    null,
                                                    $asObject );
    }

    var $ContentObjectAttributeID;
    var $ContentObjectAttributeVersion;
    var $EnumID;
    var $EnumElement;
    var $EnumValue;
}

?>
