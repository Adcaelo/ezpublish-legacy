<?php
//
// Definition of eZIntegerType class
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

/*!
  \class eZIntegerType ezintegertype.php
  \ingroup eZKernel
  \brief A content datatype which handles integers

  It provides the functionality to work as an integer and handles
  class definition input, object definition input and object viewing.

  It uses the spare field data_int in a content object attribute for storing
  the attribute data.
*/

include_once( "kernel/classes/ezdatatype.php" );
include_once( "lib/ezutils/classes/ezintegervalidator.php" );

define( "EZ_DATATYPESTRING_INTEGER", "ezinteger" );
define( "EZ_DATATYPESTRING_MIN_VALUE_FIELD", "data_int1" );
define( "EZ_DATATYPESTRING_MIN_VALUE_VARIABLE", "_ezinteger_min_integer_value_" );
define( "EZ_DATATYPESTRING_MAX_VALUE_FIELD", "data_int2" );
define( "EZ_DATATYPESTRING_MAX_VALUE_VARIABLE", "_ezinteger_max_integer_value_" );
define( "EZ_DATATYPESTRING_DEFAULT_VALUE_FIELD", "data_int3" );
define( "EZ_DATATYPESTRING_DEFAULT_VALUE_VARIABLE", "_ezinteger_default_value_" );
define( "EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD", "data_int4" );
define( "EZ_INTEGER_NO_MIN_MAX_VALUE", 0 );
define( "EZ_INTEGER_HAS_MIN_VALUE", 1 );
define( "EZ_INTEGER_HAS_MAX_VALUE", 2 );
define( "EZ_INTEGER_HAS_MIN_MAX_VALUE", 3 );


class eZIntegerType extends eZDataType
{
    function eZIntegerType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_INTEGER, "Integer" );
        $this->IntegerValidator = new eZIntegerValidator();
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $data =& $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) );
            $data = str_replace(" ", "", $data );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();
            $min = $classAttribute->attribute( EZ_DATATYPESTRING_MIN_VALUE_FIELD );
            $max = $classAttribute->attribute( EZ_DATATYPESTRING_MAX_VALUE_FIELD );
            $input_state = $classAttribute->attribute( EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD );
            if( ( $classAttribute->attribute( "is_required" ) == false ) &&  ( $data == "" ) )
            {
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            switch( $input_state )
            {
                case EZ_INTEGER_NO_MIN_MAX_VALUE:
                {
                    $state = $this->IntegerValidator->validate( $data );
                    if( $state===1 )
                        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                    else
                        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                             'Input is not integer.',
                                                                             'eZIntegerType' ) );
                } break;
                case EZ_INTEGER_HAS_MIN_VALUE:
                {
                    $this->IntegerValidator->setRange( $min, false );
                    $state = $this->IntegerValidator->validate( $data );
                    if( $state===1 )
                        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                    else
                        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                             'Input must be greater than %1',
                                                                             'eZIntegerType' ),
                                                                     $min );
                } break;
                case EZ_INTEGER_HAS_MAX_VALUE:
                {
                    $this->IntegerValidator->setRange( false, $max );
                    $state = $this->IntegerValidator->validate( $data );
                    if( $state===1 )
                        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                    else
                        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                             'Input must be less than %1',
                                                                             'eZIntegerType' ),
                                                                     $max );
                } break;
                case EZ_INTEGER_HAS_MIN_MAX_VALUE:
                {
                    $this->IntegerValidator->setRange( $min, $max );
                    $state = $this->IntegerValidator->validate( $data );
                    if( $state===1 )
                        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                    else
                        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                             'Input is not in defined range %1 - %2',
                                                                             'eZIntegerType' ),
                                                                     $min, $max );
                } break;
            }
        }
        else
        {
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        }
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }

    function fixupObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
//             $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );
//             $currentObjectAttribute =& eZContentObjectAttribute::fetch( $contentObjectAttributeID,
//                                                                         $currentVersion );
            $dataInt = $originalContentObjectAttribute->attribute( "data_int" );
            $contentObjectAttribute->setAttribute( "data_int", $dataInt );
        }
        else
        {
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $default = $contentClassAttribute->attribute( "data_int3" );
            if ( $default !== 0 )
            {
                $contentObjectAttribute->setAttribute( "data_int", $default );
            }
        }
    }

    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $data =& $http->postVariable( $base . "_data_integer_" . $contentObjectAttribute->attribute( "id" ) );
            $contentObjectAttribute->setAttribute( "data_int", $data );
            return true;
        }else
        {
            return false;
        }
    }

    /*!
     Does nothing, the data is already present in the attribute.
    */
    function storeObjectAttribute( &$object_attribute )
    {
    }

    function storeClassAttribute( &$attribute, $version )
    {
        eZDebug::writeWarning( "Storing ezinteger with version $version" );
    }

    /*!
	 \reimp
	*/
	function validateClassAttributeHTTPInput( &$http, $base, &$classAttribute )
	{
		$minValueName = $base . EZ_DATATYPESTRING_MIN_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$maxValueName = $base . EZ_DATATYPESTRING_MAX_VALUE_VARIABLE . $classAttribute->attribute( "id" );
        $defaultValueName = $base . EZ_DATATYPESTRING_DEFAULT_VALUE_VARIABLE . $classAttribute->attribute( "id" );

        if ( $http->hasPostVariable( $minValueName ) and
             $http->hasPostVariable( $maxValueName ) and
             $http->hasPostVariable( $defaultValueName ) )
		{
			$minValueValue = $http->postVariable( $minValueName );
            $minValueValue = str_replace(" ", "", $minValueValue );
			$maxValueValue = $http->postVariable( $maxValueName );
            $maxValueValue = str_replace(" ", "", $maxValueValue );
            $defaultValueValue = $http->postVariable( $defaultValueName );
            $defaultValueValue = str_replace(" ", "", $defaultValueValue );

            if ( ( $minValueValue == "" ) && ( $maxValueValue == "") ){
                return  EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else if ( ( $minValueValue == "" ) && ( $maxValueValue !== "") )
            {
                $max_state = $this->IntegerValidator->validate( $maxValueValue );
                return  $max_state;
            }
            else if ( ( $minValueValue !== "" ) && ( $maxValueValue == "") )
            {
                $min_state = $this->IntegerValidator->validate( $minValueValue );
                return  $min_state;
            }
            else
            {
                $min_state = $this->IntegerValidator->validate( $minValueValue );
                $max_state = $this->IntegerValidator->validate( $maxValueValue );
                if ( ( $min_state == EZ_INPUT_VALIDATOR_STATE_ACCEPTED ) and
                     ( $max_state == EZ_INPUT_VALIDATOR_STATE_ACCEPTED ) )
                {
                    if ($minValueValue <= $maxValueValue)
                        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                    else
                    {
                        $state = EZ_INPUT_VALIDATOR_STATE_INTERMEDIATE;
                        eZDebug::writeNotice( "Integer minimum value great than maximum value." );
                        return $state;
                    }
                }
            }

            if ($defaultValueValue == ""){
                $default_state =  EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else
                $default_state = $this->IntegerValidator->validate( $defaultValueValue );
		}

        return EZ_INPUT_VALIDATOR_STATE_INVALID;
	}

	/*!
	 \reimp
	*/
	function fixupClassAttributeHTTPInput( &$http, $base, &$classAttribute )
	{
		$minValueName = $base . EZ_DATATYPESTRING_MIN_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$maxValueName = $base . EZ_DATATYPESTRING_MAX_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		if ( $http->hasPostVariable( $minValueName ) and $http->hasPostVariable( $maxValueName ) )
		{
			$minValueValue = $http->postVariable( $minValueName );
            $minValueValue = $this->IntegerValidator->fixup( $minValueValue );
            $http->setPostVariable( $minValueName, $minValueValue );

            $maxValueValue = $http->postVariable( $maxValueName );
            $maxValueValue = $this->IntegerValidator->fixup( $maxValueValue );
            $http->setPostVariable( $maxValueName, $maxValueValue );

            if ($minValueValue > $maxValueValue)
			{
                $this->IntegerValidator->setRange( $minValueValue, false );
                $maxValueValue = $this->IntegerValidator->fixup( $maxValueValue );
				$http->setPostVariable( $maxValueName, $maxValueValue );
			}
		}
	}

	/*!
	 \reimp
	*/
	function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
	{
		$minValueName = $base . EZ_DATATYPESTRING_MIN_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$maxValueName = $base . EZ_DATATYPESTRING_MAX_VALUE_VARIABLE . $classAttribute->attribute( "id" );
        $defaultValueName = $base . EZ_DATATYPESTRING_DEFAULT_VALUE_VARIABLE . $classAttribute->attribute( "id" );

        if ( $http->hasPostVariable( $minValueName ) and
             $http->hasPostVariable( $maxValueName ) and
             $http->hasPostVariable( $defaultValueName ) )
		{
            $minValueValue = $http->postVariable( $minValueName );
            $minValueValue = str_replace(" ", "", $minValueValue );
            $maxValueValue = $http->postVariable( $maxValueName );
            $maxValueValue = str_replace(" ", "", $maxValueValue );
            $defaultValueValue = $http->postVariable( $defaultValueName );
            $defaultValueValue = str_replace(" ", "", $defaultValueValue );

            $classAttribute->setAttribute( EZ_DATATYPESTRING_MIN_VALUE_FIELD, $minValueValue );
            $classAttribute->setAttribute( EZ_DATATYPESTRING_MAX_VALUE_FIELD, $maxValueValue );
            $classAttribute->setAttribute( EZ_DATATYPESTRING_DEFAULT_VALUE_FIELD, $defaultValueValue );

            if ( ( $minValueValue == "" ) && ( $maxValueValue == "") ){
                $input_state =  EZ_INTEGER_NO_MIN_MAX_VALUE;
                $classAttribute->setAttribute( EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD, $input_state );
            }
            else if ( ( $minValueValue == "" ) && ( $maxValueValue !== "") )
            {
                $input_state = EZ_INTEGER_HAS_MAX_VALUE;
                $classAttribute->setAttribute( EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD, $input_state );
            }
            else if ( ( $minValueValue !== "" ) && ( $maxValueValue == "") )
            {
                $input_state = EZ_INTEGER_HAS_MIN_VALUE;
                $classAttribute->setAttribute( EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD, $input_state );
            }
            else
            {
                $input_state = EZ_INTEGER_HAS_MIN_MAX_VALUE;
                $classAttribute->setAttribute( EZ_DATATYPESTRING_INTEGER_INPUT_STATE_FIELD, $input_state );
            }
		}
	}

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_int" );
    }


    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_int" );
    }

    /*!
     Returns the integer value.
    */
    function title( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_int" );
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
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'type', 'ezinteger' ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'value', $objectAttribute->attribute( "data_int" ) ) );

        return $node;
    }

    /// \privatesection
    /// The integer value validator
    var $IntegerValidator;
}

eZDataType::register( EZ_DATATYPESTRING_INTEGER, "ezintegertype" );

?>
