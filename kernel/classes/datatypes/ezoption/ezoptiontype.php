<?php
//
// Definition of eZOptionType class
//
//Created on: <28-Jun-2002 11:12:51 sp>
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

//!! eZKernel
//! The class eZOptionType does
/*!

*/

include_once( "kernel/classes/ezdatatype.php" );

include_once( "kernel/classes/datatypes/ezoption/ezoption.php" );

define( "EZ_DATATYPESTRING_OPTION", "ezoption" );

class eZOptionType extends eZDataType
{
    function eZOptionType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_OPTION, "Option" );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
     \todo Use external integer validator
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_option_id_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();
            $idList = $http->postVariable( $base . "_data_option_id_" . $contentObjectAttribute->attribute( "id" ) );
            $valueList = $http->postVariable( $base . "_data_option_value_" . $contentObjectAttribute->attribute( "id" ) );
            if ( $classAttribute->attribute( "is_required" ) == true )
            {
                if ( trim( $valueList[0] ) == "" )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'At least one option is requied.',
                                                                         'eZOptionType' ) );
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
                }
            }
            if ( trim( $valueList[0] ) != "" )
            {
                for ( $i=0;$i<count( $idList );$i++ )
                {
                    $value =  $valueList[$i];
                    if ( trim( $value )== "" )
                    {
                        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                             'Option value should be provided.',
                                                                             'eZOptionType' ) );
                        return EZ_INPUT_VALIDATOR_STATE_INVALID;

                    }
                }
            }
        }
        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        eZDebug::writeNotice( "Validating option $data" );
    }

    /*!
     Store content
    */
    function storeObjectAttribute( &$contentObjectAttribute )
    {
        $option =& $contentObjectAttribute->content();
        $contentObjectAttribute->setAttribute( "data_text", $option->xmlString() );
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        $option = new eZOption( "" );

        $option->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );

        return $option;
    }


    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        $optionName =& $http->postVariable( $base . "_data_option_name_" . $contentObjectAttribute->attribute( "id" ) );
        $optionIDArray =& $http->postVariable( $base . "_data_option_id_" . $contentObjectAttribute->attribute( "id" ) );
        $optionValueArray =& $http->postVariable( $base . "_data_option_value_" . $contentObjectAttribute->attribute( "id" ) );

        $option = new eZOption( $optionName );

        $i = 0;
        foreach ( $optionIDArray as $id )
        {
            $option->addOption( $optionValueArray[$i]  );
            $i++;
        }
        $contentObjectAttribute->setContent( $option );
    }

    /*!
    */
    function customObjectAttributeHTTPAction( $http, $action, &$contentObjectAttribute )
    {
        switch ( $action )
        {
            case "new_option" :
            {
                $option =& $contentObjectAttribute->content( );

                $option->addOption( "" );
                $contentObjectAttribute->setContent( $option );
            }break;
            case "remove_selected" :
            {
                $option =& $contentObjectAttribute->content( );
                $postvarname = "ContentObjectAttribute" . "_data_option_remove_" . $contentObjectAttribute->attribute( "id" );
                $array_remove = $http->postVariable( $postvarname );
                $option->removeOptions( $array_remove );
                $contentObjectAttribute->setContent( $option );
            }break;
            default :
            {
                eZDebug::writeError( "Unknown custom HTTP action: " . $action, "eZOptionType" );
            }break;
        }
    }

    /*!
     Returns the integer value.
    */
    function title( &$contentObjectAttribute, $name = "name" )
    {
        $option =& $contentObjectAttribute->content( );

        $value = $option->attribute( $name );

        return $value;
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
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'type', 'ezoption' ) );

        $option = new eZOption( "" );

        $option->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );

        return $node;
    }
}

eZDataType::register( EZ_DATATYPESTRING_OPTION, "ezoptiontype" );

?>
