<?php
//
// Definition of eZStringType class
//
// Created on: <16-Apr-2002 11:08:14 amos>
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
  \class eZStringType ezstringtype.php
  \ingroup eZKernel
  \brief A content datatype which handles text lines

  It provides the functionality to work as a text line and handles
  class definition input, object definition input and object viewing.

  It uses the spare field data_text in a content object attribute for storing
  the attribute data.

  \todo Create the class function handlers
*/

include_once( 'kernel/classes/ezdatatype.php' );
include_once( 'lib/ezutils/classes/ezintegervalidator.php' );
include_once( 'kernel/common/i18n.php' );

define( 'EZ_DATATYPESTRING_STRING', 'ezstring' );
define( 'EZ_DATATYPESTRING_MAX_LEN_FIELD', 'data_int1' );
define( 'EZ_DATATYPESTRING_MAX_LEN_VARIABLE', '_ezstring_max_string_length_' );
define( "EZ_DATATYPESTRING_DEFAULT_STRING_FIELD", "data_text1" );
define( "EZ_DATATYPESTRING_DEFAULT_STRING_VARIABLE", "_ezstring_default_value_" );

class eZStringType extends eZDataType
{
    /*!
     Initializes with a string id and a description.
    */
    function eZStringType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_STRING, 'Text line' );
        $this->MaxLenValidator = new eZIntegerValidator();
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion )
    {
        if ( $currentVersion != false )
        {
            $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
            $currentObjectAttribute =& eZContentObjectAttribute::fetch( $contentObjectAttributeID,
                                                                        $currentVersion );
            $dataText = $currentObjectAttribute ->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
        else
        {
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $default = $contentClassAttribute->attribute( "data_text1" );
            if ( $default !== "" )
            {
                $contentObjectAttribute->setAttribute( "data_text", $default );
            }
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data =& $http->postVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();
            if( $classAttribute->attribute( "is_required" ) == true )
            {
                if( $data == "" )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'content/datatypes',
                                                                         'eZStringType',
                                                                         'Text line is empty, content required.' ) );
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
                }
            }
            $maxLen = $classAttribute->attribute( EZ_DATATYPESTRING_MAX_LEN_FIELD );
            if ( (strlen( $data ) <= $maxLen ) || ( $maxLen == 0 ) )
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            $contentObjectAttribute->setValidationError( ezi18n( 'content/datatypes',
                                                                 'eZStringType',
                                                                 'Text line too long, maximum allowed is %1.' ),
                                                         $maxLen );
        }
        else
        {
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        }
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data =& $http->postVariable( $base . '_ezstring_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $contentObjectAttribute->setAttribute( 'data_text', $data );
            return true;
        }
        return false;
    }

    /*!
     Does nothing since it uses the data_text field in the content object attribute.
     See fetchObjectAttributeHTTPInput for the actual storing.
    */
    function storeObjectAttribute( &$attribute )
    {
    }

    function storeClassAttribute( &$attribute, $version )
    {
        eZDebug::writeWarning( "Storing ezstring with version $version" );
    }

    function storeDefinedClassAttribute( &$attribute )
    {
        eZDebug::writeWarning( "Storing defined ezstring" );
    }

    /*!
     \reimp
    */
    function validateClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $maxLenName = $base . EZ_DATATYPESTRING_MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $maxLenValue = str_replace(" ", "", $maxLenValue );
            if( ( $maxLenValue == "" ) ||  ( $maxLenValue == 0 ) )
            {
                $maxLenValue = 0;
                $http->setPostVariable( $maxLenName, $maxLenValue );
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else
            {
                $this->MaxLenValidator->setRange( 1, false );
                return $this->MaxLenValidator->validate( $maxLenValue );
            }
        }
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }

    /*!
     \reimp
    */
    function fixupClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $maxLenName = $base . EZ_DATATYPESTRING_MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $this->MaxLenValidator->setRange( 1, false );
            $maxLenValue = $this->MaxLenValidator->fixup( $maxLenValue );
            $http->setPostVariable( $maxLenName, $maxLenValue );
        }
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $maxLenName = $base . EZ_DATATYPESTRING_MAX_LEN_VARIABLE . $classAttribute->attribute( 'id' );
        $defaultValueName = $base . EZ_DATATYPESTRING_DEFAULT_STRING_VARIABLE . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $maxLenName ) )
        {
            $maxLenValue = $http->postVariable( $maxLenName );
            $classAttribute->setAttribute( EZ_DATATYPESTRING_MAX_LEN_FIELD, $maxLenValue );
        }
        if ( $http->hasPostVariable( $defaultValueName ) )
        {
            $defaultValueValue = $http->postVariable( $defaultValueName );

            if ($defaultValueValue == ""){
                $defaultValueValue = "";
            }
            $classAttribute->setAttribute( EZ_DATATYPESTRING_DEFAULT_STRING_FIELD, $defaultValueValue );
        }
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     \reuturn the collect information action if enabled
    */
    function contentActionList( &$classAttribute )
    {
        if ( $classAttribute->attribute( 'is_information_collector' ) == true )
        {
            return array( array( 'name' => 'Send',
                                 'action' => 'ActionCollectInformation'
                                 ) );
        }
        else
            return array();
    }

    /*!
     Returns the content of the string for use as a title
    */
    function title( &$contentObjectAttribute )
    {
        return  $contentObjectAttribute->attribute( 'data_text' );
    }

    /// \privatesection
    /// The max len validator
    var $MaxLenValidator;
}

eZDataType::register( EZ_DATATYPESTRING_STRING, 'ezstringtype' );

?>
