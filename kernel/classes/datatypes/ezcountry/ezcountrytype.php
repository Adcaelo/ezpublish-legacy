<?php
//
// Definition of eZCountryType class
//
// Created on: <20-Feb-2006 11:11:19 vs>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
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
  \class eZCountryType ezcountrytype.php
  \ingroup eZDatatype
  \brief A content datatype that contains country.

  The list of countries is fetched from contenet.ini.
  Country is stored as text string.
*/

include_once( 'kernel/classes/ezdatatype.php' );
include_once( 'lib/ezutils/classes/ezintegervalidator.php' );
include_once( 'kernel/common/i18n.php' );

define( 'EZ_DATATYPESTRING_COUNTRY', 'ezcountry' );

class eZCountryType extends eZDataType
{
    function eZCountryType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_COUNTRY, ezi18n( 'kernel/classes/datatypes', 'Country', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'country' ) ) );
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
        else
        {
            $default = '';
            $contentObjectAttribute->setAttribute( "data_text", $default );
        }
    }

    /*!
     \reimp
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( !$contentObjectAttribute->validateIsRequired() )
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;

        if ( $http->hasPostVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) );

            if ( $data )
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        }

        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                             'Input required.' ) );
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }

    /*!
     \reimp
    */
    function validateCollectionAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( !$contentObjectAttribute->validateIsRequired() )
            return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;

        if ( $http->hasPostVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) );

            if ( $data )
                return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
        }

        $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                             'Input required.' ) );
        return EZ_INPUT_VALIDATOR_STATE_INVALID;
    }

    /*!
     Fetches the http post var and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_country_' . $contentObjectAttribute->attribute( 'id' ) );
            $contentObjectAttribute->setAttribute( 'data_text', $data );
            return true;
        }
        return false;
    }

    /*!
     Fetches the http post variables for collected information
    */
    function fetchCollectionAttributeHTTPInput( &$collection, &$collectionAttribute, &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_country_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $dataText = $http->postVariable( $base . "_country_" . $contentObjectAttribute->attribute( "id" ) );
            $collectionAttribute->setAttribute( 'data_text', $dataText );
            return true;
        }
        return false;
    }

    /*!
     \reimp
     Simple string insertion is supported.
    */
    function isSimpleStringInsertionSupported()
    {
        return true;
    }

    /*!
     \reimp
     Inserts the string \a $string in the \c 'data_text' database field.
    */
    function insertSimpleString( &$object, $objectVersion, $objectLanguage,
                                 &$objectAttribute, $string,
                                 &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => true );
        $objectAttribute->setContent( $string );
        $objectAttribute->setAttribute( 'data_text', $string );
        return true;
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
     Returns the country for use as a title
    */
    function title( &$contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
    }

    /*!
     \reimp
    */
    function isIndexable()
    {
        return true;
    }

    /*!
     \reimp
    */
    function isInformationCollector()
    {
        return true;
    }

    /*!
     \reimp
    */
    function sortKey( &$contentObjectAttribute )
    {
        include_once( 'lib/ezi18n/classes/ezchartransform.php' );
        $trans =& eZCharTransform::instance();
        return $trans->transformByGroup( $contentObjectAttribute->attribute( 'data_text' ), 'lowercase' );
    }

    /*!
     \reimp
    */
    function sortKeyType()
    {
        return 'string';
    }
}

eZDataType::register( EZ_DATATYPESTRING_COUNTRY, 'ezcountrytype' );

?>
