<?php
//
// Definition of eZURLType class
//
// Created on: <08-Oct-2002 19:12:43 bf>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
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

/*!
  \class eZURLType ezurltype.php
  \ingroup eZKernel
  \brief A content datatype which handles urls

*/

include_once( 'kernel/classes/ezdatatype.php' );
include_once( 'lib/ezutils/classes/ezintegervalidator.php' );
include_once( 'kernel/common/i18n.php' );
include_once( 'kernel/classes/datatypes/ezurl/ezurl.php' );
include_once( 'kernel/classes/datatypes/ezurl/ezurlobjectlink.php' );

define( 'EZ_DATATYPEURL_URL', 'ezurl' );

class eZURLType extends eZDataType
{
    /*!
     Initializes with a url id and a description.
    */
    function eZURLType()
    {
        $this->eZDataType( EZ_DATATYPEURL_URL, ezi18n( 'kernel/classes/datatypes', 'URL', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
        $this->MaxLenValidator = new eZIntegerValidator();
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
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
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
        return EZ_INPUT_VALIDATOR_STATE_VALID;
    }

    /*!
     \reimp
    */
    function deleteStoredObjectAttribute( &$contentObjectAttribute, $version = null )
    {
        $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );
        $urls = array();
        if ( $version == null )
        {
            $urls =& eZURLObjectLink::fetchLinkList( $contentObjectAttributeID, false, false );
            eZURLObjectLink::removeURLlinkList( $contentObjectAttributeID, false );
        }
        else
        {
            $urls =& eZURLObjectLink::fetchLinkList( $contentObjectAttributeID, $version, false );
            eZURLObjectLink::removeURLlinkList( $contentObjectAttributeID, $version );
        }
        $urls = array_unique( $urls );
        foreach ( $urls as $urlID )
        {
            if ( !eZURLObjectLink::hasObjectLinkList( $urlID ) )
            {
                eZURL::removeByID( $urlID );
            }
        }
    }

    /*!
     Fetches the http post var url input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezurl_url_' . $contentObjectAttribute->attribute( 'id' ) ) and
             $http->hasPostVariable( $base . '_ezurl_text_' . $contentObjectAttribute->attribute( 'id' ) )
             )
        {
            $url =& $http->postVariable( $base . '_ezurl_url_' . $contentObjectAttribute->attribute( 'id' ) );
            $text =& $http->postVariable( $base . '_ezurl_text_' . $contentObjectAttribute->attribute( 'id' ) );

            $contentObjectAttribute->setAttribute( 'data_text', $text );

            $contentObjectAttribute->setContent( $url );
            return true;
        }
        return false;
    }

    /*!
      Store the URL in the URL database and store the reference to it.
    */
    function storeObjectAttribute( &$attribute )
    {
        $urlValue = $attribute->content();
        if ( trim( $urlValue ) != '' )
        {
            $oldURLID = $attribute->attribute( 'data_int' );
            $urlID = eZURL::registerURL( $urlValue );
            $attribute->setAttribute( 'data_int', $urlID );

            // Update url-object link
            $contentObjectAttributeID = $attribute->attribute( 'id' );
            $contentObjectAttributeVersion = $attribute->attribute( 'version' );
            eZURLObjectLink::removeURLlinkList( $contentObjectAttributeID, $contentObjectAttributeVersion );
            $linkObjectLink =& eZURLObjectLink::create( $urlID, $contentObjectAttributeID, $contentObjectAttributeVersion );
            $linkObjectLink->store();

            if ( $oldURLID )
            {
                if ( !eZURLObjectLink::hasObjectLinkList( $oldURLID ) )
                {
                    eZURL::removeByID( $oldURLID );
                }
            }
        }
    }

    function storeClassAttribute( &$attribute, $version )
    {
    }

    function storeDefinedClassAttribute( &$attribute )
    {
    }

    /*!
     \reimp
    */
    function validateClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        return EZ_INPUT_VALIDATOR_STATE_VALID;
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        if ( !$contentObjectAttribute->attribute( 'data_int' ) )
            return false;

        return eZURL::url( $contentObjectAttribute->attribute( 'data_int' ) );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the content of the url for use as a title
    */
    function title( &$contentObjectAttribute )
    {
        return  $contentObjectAttribute->attribute( 'data_text' );
    }
}

eZDataType::register( EZ_DATATYPEURL_URL, 'ezurltype' );

?>
