<?php
//
// Definition of eZRangeOptionType class
//
// Created on: <17-���-2003 16:24:57 sp>
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

/*! \file ezrangeoptiontype.php
*/

/*!
  \class eZRangeOptionType ezrangeoptiontype.php
  \brief The class eZRangeOptionType does

*/
include_once( "kernel/classes/ezdatatype.php" );
include_once( "kernel/classes/datatypes/ezrangeoption/ezrangeoption.php" );

define( "EZ_RANGEOPTION_DEFAULT_NAME_VARIABLE", "_ezrangeoption_default_name_" );


define( "EZ_DATATYPESTRING_RANGEOPTION", "ezrangeoption" );

class eZRangeOptionType extends eZDataType
{
    /*!
     Constructor
    */
    function eZRangeOptionType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_RANGEOPTION, "Range option" );
    }

    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {

        $optionName =& $http->postVariable( $base . "_data_rangeoption_name_" . $contentObjectAttribute->attribute( "id" ) );
        $optionIDArray =& $http->postVariable( $base . "_data_rangeoption_id_" . $contentObjectAttribute->attribute( "id" ) );
        $optionStartValue =& $http->postVariable( $base . "_data_rangeoption_start_value_" . $contentObjectAttribute->attribute( "id" ) );
        $optionStopValue =& $http->postVariable( $base . "_data_rangeoption_stop_value_" . $contentObjectAttribute->attribute( "id" ) );
        $optionStepValue =& $http->postVariable( $base . "_data_rangeoption_step_value_" . $contentObjectAttribute->attribute( "id" ) );

        $option = new eZRangeOption( $optionName );

        $option->setStartValue( $optionStartValue );
        $option->setStopValue( $optionStopValue );
        $option->setStepValue( $optionStepValue );

/*        $i = 0;
        foreach ( $optionIDArray as $id )
        {
            $option->addOption( array( 'value' => $optionValueArray[$i],
                                       'additional_price' => $optionAdditionalPriceArray[$i] ) );
            $i++;
        }
*/
        $contentObjectAttribute->setContent( $option );
        return true;
    }

    function storeObjectAttribute( &$contentObjectAttribute )
    {
        $option =& $contentObjectAttribute->content();
        $contentObjectAttribute->setAttribute( "data_text", $option->xmlString() );
    }

    function &objectAttributeContent( &$contentObjectAttribute )
    {
        $option = new eZRangeOption( "" );
        $option->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );
        return $option;
    }

    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    function title( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /*!
     \return a DOM representation of the content object attribute
    */
    function &serializeContentObjectAttribute( $objectAttribute )
    {
        include_once( 'lib/ezxml/classes/ezdomdocument.php' );
        include_once( 'lib/ezxml/classes/ezdomnode.php' );

        $node = new eZDOMNode();
        $node->setName( 'attribute' );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'name', $objectAttribute->contentClassAttributeName() ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'type', 'ezrangeoption' ) );

        $option = new eZRangeOption( "" );

        $option->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );

        return $node;
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        $option =& $contentObjectAttribute->content();
        $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
        $option->setName( $contentClassAttribute->attribute( 'data_text1' ) );
        $contentObjectAttribute->setAttribute( "data_text", $option->xmlString() );
        $contentObjectAttribute->setContent( $option );
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $defaultValueName = $base . EZ_RANGEOPTION_DEFAULT_NAME_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $defaultValueName ) )
        {
            $defaultValueValue = $http->postVariable( $defaultValueName );

            if ($defaultValueValue == ""){
                $defaultValueValue = "";
            }
            $classAttribute->setAttribute( 'data_text1', $defaultValueValue );
            return true;
        }
        return false;
    }

}

eZDataType::register( EZ_DATATYPESTRING_RANGEOPTION, "ezrangeoptiontype" );

?>
