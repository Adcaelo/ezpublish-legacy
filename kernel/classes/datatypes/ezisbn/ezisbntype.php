<?php
//
// Definition of eZISBNType class
//
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.10.x
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
  \class eZISBNType ezisbntype.php
  \brief Handles ISBN type strings
  \ingroup eZDatatype

*/

include_once( "kernel/classes/ezdatatype.php" );

define( "EZ_DATATYPESTRING_ISBN", "ezisbn" );
define( 'EZ_DATATYPESTRING_ISBN_CLASS_IS_ISBN13', 'data_int1' );
define( 'EZ_DATATYPESTRING_ISBN_CONTENT_VALUE', 'data_text' );

class eZISBNType extends eZDataType
{
    function eZISBNType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_ISBN, ezi18n( 'kernel/classes/datatypes', "ISBN", 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE => 'isbn' ) ) );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        $classAttribute =& $contentObjectAttribute->contentClassAttribute();
        $classContent = $classAttribute->content();
        if ( isset( $classContent['ISBN13'] ) and $classContent['ISBN13'] )
        {
            include_once( 'kernel/classes/datatypes/ezisbn/ezisbn13.php' );
            $number13 = $http->hasPostVariable( $base . "_isbn_13_" . $contentObjectAttribute->attribute( "id" ) )
                        ? $http->postVariable( $base . "_isbn_13_" . $contentObjectAttribute->attribute( "id" ) )
                        : false;

            if ( $contentObjectAttribute->validateIsRequired() and ( !$number13 or $number13 == '' ) )
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'Input required.' ) );

                return EZ_INPUT_VALIDATOR_STATE_INVALID;
            }
            else if ( !$contentObjectAttribute->validateIsRequired() and ( !$number13 or $number13 == '' ) )
            {
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }

            // Should also accept ISBN10 values, which should be automatically converted to ISBN13 later.
            $isbn10TestNumber = preg_replace( "/[\s|\-]/", "", trim( $number13 ) );
            if ( strlen( $isbn10TestNumber ) == 10 )
            {
                $status = $this->validateISBNChecksum( $isbn10TestNumber );
                if ( $status === true )
                {
                    return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
                }
                else
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'The ISBN number should be ISBN13, but seems to be ISBN10.' ) );
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
                }
            }

            $isbn13 = new eZISBN13();
            $valid = $isbn13->validate( $number13, $error );

            if ( $valid )
            {
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'The ISBN number is not correct. ' ) . $error );
                return EZ_INPUT_VALIDATOR_STATE_INVALID;
            }

            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }

        $field1 = $http->postVariable( $base . "_isbn_field1_" . $contentObjectAttribute->attribute( "id" ) );
        $field2 = $http->postVariable( $base . "_isbn_field2_" . $contentObjectAttribute->attribute( "id" ) );
        $field3 = $http->postVariable( $base . "_isbn_field3_" . $contentObjectAttribute->attribute( "id" ) );
        $field4 = $http->postVariable( $base . "_isbn_field4_" . $contentObjectAttribute->attribute( "id" ) );
        $isbn = $field1 . '-' . $field2 . '-' . $field3 . '-' . $field4;

        $isbn = strtoupper( $isbn );
        if ( !$contentObjectAttribute->validateIsRequired() and $isbn == "---" )
        {
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        }

        if ( preg_match( "#^[0-9]{1,2}\-[0-9]+\-[0-9]+\-[0-9X]{1}$#", $isbn ) )
        {
            $digits = str_replace( "-", "", $isbn );
            if ( strlen( $digits ) == 10 )
            {
                $valid = $this->validateISBNChecksum ( $digits );
            }
            else
            {
                $valid = false;
            }

            if ( $valid )
            {
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                     'The ISBN number is not correct. Please check the input for mistakes.' ) );
                return EZ_INPUT_VALIDATOR_STATE_INVALID;
            }
        }
        else
        {
            $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                 'The ISBN number is not correct. Please check the input for mistakes.' ) );
            return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }


    /*!
     \private
     Validates the ISBN number \a $isbnNr.
     All characters should be numeric except the last digit that may be the character X,
     which should be calculated as 10.
     \param $isbnNr A string containing the number without any dashes.
     \return \c true if it is valid.
    */
    function validateISBNChecksum ( $isbnNr )
    {
        $result = 0;
        $isbnNr = strtoupper( $isbnNr );
        for ( $i = 10; $i > 0; $i-- )
        {
            if ( is_numeric( $isbnNr{$i-1} ) or ( $i == 10  and $isbnNr{$i-1} == 'X' ) )
            {
                if ( ( $i == 1 ) and ( $isbnNr{9} == 'X' ) )
                {
                    $result += 10 * $i;
                }
                else
                {
                    $result += $isbnNr{10-$i} * $i;
                }
            }
            else
            {
                return false;
            }
        }
        return ( $result % 11 == 0 );
    }

    /*!
     \private
     \depricated, should use the class eZISBN13 instead.
     Validates the ISBN-13 number \a $isbnNr.
     \param $isbnNr A string containing the number without any dashes.
     \return \c true if it is valid.
    */
    function validateISBN13Checksum ( $isbnNr, &$error )
    {
        $isbn13 = new eZISBN13();
        $status = $isbn13->validateISBN13Checksum( $isbnNr, $error );

        return $status;
    }

    /*!
      Calculate the ISBN13 checkdigit and return a valid ISBN13 number
      based on a ISBN10 number as input.
      \return a valid ISBN13 number.
    */
    function convertISBN10toISBN13( $isbnNr )
    {
        $isbnNr = 978 . substr( $isbnNr, 0, 9 );

        $weight13 = 1;
        $checksum13 = 0;
        $val = 0;

        for ( $i = 0; $i < 12; $i++ )
        {
            $val = $isbnNr{$i};
            $checksum13 = $checksum13 + $weight13 * $val;
            $weight13 = ( $weight13 + 2 ) % 4;
        }

        $checkDigit = ( 10 - ( $checksum13 % 10 ) ) % 10;
        $isbnNr .= $checkDigit;

        return $isbnNr;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        $classAttribute =& $contentObjectAttribute->contentClassAttribute();
        $classContent = $classAttribute->content();
        if ( isset( $classContent['ISBN13'] ) and $classContent['ISBN13'] )
        {
            $number13 = $http->hasPostVariable( $base . "_isbn_13_" . $contentObjectAttribute->attribute( "id" ) )
                        ? $http->postVariable( $base . "_isbn_13_" . $contentObjectAttribute->attribute( "id" ) )
                        : false;
            if ( $number13 === false )
                return true;

            if ( !$contentObjectAttribute->validateIsRequired() and ( !$number13 or $number13 == '' ) )
            {
                return true;
            }

            // Test if we have a ISBN10 number. This should be automatically converted to ISBN13 if found.
            $isbn10TestNumber = preg_replace( "/[\s|\-]/", "", trim( $number13 ) );
            if ( strlen( $isbn10TestNumber ) == 10 )
            {
                if ( $contentObjectAttribute->IsValid == EZ_INPUT_VALIDATOR_STATE_ACCEPTED )
                {
                    // Convert the isbn-10 number to isbn-13.
                    $number13 = $this->convertISBN10toISBN13( $isbn10TestNumber );
                }
                else
                {
                    // Add the value so the added value will be shown back to the user with an error message.
                    $contentObjectAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE, $number13 );
                    return true;
                }
            }

            // Extract the different parts and set the hyphens correctly.
            $isbn13 = new eZISBN13();
            $isbn13Value = $isbn13->formatedISBNValue( $number13, $error );
            $contentObjectAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE, $isbn13Value );
            return true;
        }

        $field1 = $http->postVariable( $base . "_isbn_field1_" . $contentObjectAttribute->attribute( "id" ) );
        $field2 = $http->postVariable( $base . "_isbn_field2_" . $contentObjectAttribute->attribute( "id" ) );
        $field3 = $http->postVariable( $base . "_isbn_field3_" . $contentObjectAttribute->attribute( "id" ) );
        $field4 = $http->postVariable( $base . "_isbn_field4_" . $contentObjectAttribute->attribute( "id" ) );
        // If $fields are empty if should not store empty content to db.
        if ( !$field1 and !$field2 and !$field3 and !$field4 )
            return true;

        $isbn = $field1 . '-' . $field2 . '-' . $field3 . '-' . $field4;
        $isbn = strtoupper( $isbn );
        $contentObjectAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE, $isbn );
        return true;
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $classAttributeID = $classAttribute->attribute( 'id' );
        $content = $classAttribute->content();

        if ( $http->hasPostVariable( $base . '_ezisbn_13_value_' . $classAttributeID . '_exists' ) )
        {
             $content['ISBN13'] = $http->hasPostVariable( $base . '_ezisbn_13_value_' . $classAttributeID ) ? 1 : 0;
        }
        $classAttribute->setContent( $content );
        $classAttribute->store();
        return true;
    }

    /*!
     Store the content.
    */
    function storeObjectAttribute( &$attribute )
    {
    }

    /*!
     \reimp
    */
    function preStoreClassAttribute( &$classAttribute, $version )
    {
        $content = $classAttribute->content();
        return eZISBNType::storeClassAttributeContent( $classAttribute, $content );
    }

    function storeClassAttributeContent( &$classAttribute, $content )
    {
        if ( is_array( $content ) )
        {
            $ISBN_13 = $content['ISBN13'];
            $classAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CLASS_IS_ISBN13, $ISBN_13 );
        }
        return false;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        $data = $contentObjectAttribute->attribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE );
        $classAttribute =& $contentObjectAttribute->contentClassAttribute();
        $classContent = $classAttribute->content();
        if ( isset( $classContent['ISBN13'] ) and $classContent['ISBN13'] )
        {
            list ( $prefix, $field1, $field2, $field3, $field4 ) = array_merge( preg_split( '#-#', $data ),
                                                                       array( 0 => '', 1 => '', 2 => '', 3 => '', 4 => '' ) );
            $dataArray = array(  'prefix' => $prefix,
                                 'field1' => $field1, 'field2' => $field2,
                                 'field3' => $field3, 'field4' => $field4,
                                 'value' => $data,
                                 'value_without_hyphens' => str_replace( "-", "", $data ),
                                 'value_with_spaces' => str_replace( "-", " ", $data ) );
            return $dataArray;
        }

        // The array_merge makes sure missing elements gets an empty string instead of NULL
        list ( $field1, $field2, $field3, $field4 ) = array_merge( preg_split( '#-#', $data ),
                                                                   array( 0 => '', 1 => '', 2 => '', 3 => '' ) );
        $isbn = array( 'field1' => $field1, 'field2' => $field2,
                       'field3' => $field3, 'field4' => $field4,
                       'value' => $data,
                       'value_without_hyphens' => str_replace( "-", "", $data ),
                       'value_with_spaces' => str_replace( "-", " ", $data ) );
        return $isbn;
    }

    /*!
     \reimp
    */
    function &classAttributeContent( &$classAttribute )
    {
        $ISBN_13 = $classAttribute->attribute( EZ_DATATYPESTRING_ISBN_CLASS_IS_ISBN13 );
        $content = array( 'ISBN13' => $ISBN_13 );
        return $content;
    }


    /*!
     \reimp
     ISBN numbers are indexable, returns \c true.
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE );
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE );
    }

    function fromString( &$contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE, $string );
    }

    /*!
     Returns the text.
    */
    function title( $data_instance, $name = null )
    {
        return $data_instance->attribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE );
    }

    /*!
     Initializes the class attribute.
     data_int will be se default to 1, as this is ISBN-13.
    */
    function initializeClassAttribute( &$classAttribute )
    {
        if ( $classAttribute->attribute( EZ_DATATYPESTRING_ISBN_CLASS_IS_ISBN13 ) === null )
        {
            $classAttribute->setAttribute( EZ_DATATYPESTRING_ISBN_CLASS_IS_ISBN13, 1 );
        }
    }

    /*!
      Check if a ISBN value exist in the datatype.
    */
    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( EZ_DATATYPESTRING_ISBN_CONTENT_VALUE ) ) != '';
    }
}

eZDataType::register( EZ_DATATYPESTRING_ISBN, "ezisbntype" );

?>
