<?php
//
// Definition of eZKeywordType class
//
// Created on: <29-Apr-2003 14:59:12 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.5.x
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
  \class eZKeywordType ezkeywordtype.php
  \ingroup eZDatatype
  \brief A content datatype which handles keyword indexes

*/

include_once( 'kernel/classes/ezdatatype.php' );
include_once( 'kernel/common/i18n.php' );

include_once( 'kernel/classes/datatypes/ezkeyword/ezkeyword.php' );

define( 'EZ_DATATYPESTRING_KEYWORD', 'ezkeyword' );

class eZKeywordType extends eZDataType
{
    /*!
     Initializes with a keyword id and a description.
    */
    function eZKeywordType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_KEYWORD, ezi18n( 'kernel/classes/datatypes', 'Keyword', 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $originalContentObjectAttributeID = $originalContentObjectAttribute->attribute( 'id' );
            $contentObjectAttributeID = $contentObjectAttribute->attribute( 'id' );

            // if translating or copying an object
            if ( $originalContentObjectAttributeID != $contentObjectAttributeID )
            {
                // copy keywords links as well
                $keyword =& $originalContentObjectAttribute->content();
                if ( is_object( $keyword ) )
                {
                    $keyword->store( $contentObjectAttribute );
                }
            }
        }
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezkeyword_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_ezkeyword_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute =& $contentObjectAttribute->contentClassAttribute();

            if ( $data == "" )
            {
                if ( !$classAttribute->attribute( 'is_information_collector' ) and
                     $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Input required.' ) );
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
                }
            }
        }
        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var keyword input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_ezkeyword_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data =& $http->postVariable( $base . '_ezkeyword_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $keyword = new eZKeyword();
            $keyword->initializeKeyword( $data );
            $contentObjectAttribute->setContent( $keyword );
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
        // create keyword index
        $keyword =& $attribute->content();
        if ( is_object( $keyword ) )
        {
            $keyword->store( $attribute );
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
    function validateClassAttributeHTTPInput( &$http, $base, &$attribute )
    {
        return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    /*!
     \reimp
    */
    function fixupClassAttributeHTTPInput( &$http, $base, &$attribute )
    {
    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( &$http, $base, &$attribute )
    {
        return true;
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$attribute )
    {
        $keyword = new eZKeyword();
        $keyword->fetch( $attribute );

        return $keyword;
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( &$attribute )
    {
        $keyword = new eZKeyword();
        $keyword->fetch( $attribute );
        $return =& $keyword->keywordString();

        return $return;
    }

    /*!
     \reuturn the collect information action if enabled
    */
    function contentActionList( &$classAttribute )
    {
        return array();
    }

    /*!
     Delete stored object attribute
    */
    function deleteStoredObjectAttribute( &$contentObjectAttribute, $version = null )
    {
        if ( $version != null ) // Do not delete if discarding draft
        {
            return;
        }

        $contentObjectAttributeID = $contentObjectAttribute->attribute( "id" );

        $db =& eZDB::instance();

        /* First we retrieve all the keyword ID related to this object attribute */
        $res = $db->arrayQuery( "SELECT keyword_id
                                 FROM ezkeyword_attribute_link
                                 WHERE objectattribute_id='$contentObjectAttributeID'" );
        if ( !count ( $res ) )
        {
            /* If there are no keywords at all, we abort the function as there
             * is nothing more to do */
            return;
        }
        $keywordIDs = array();
        foreach ( $res as $record )
            $keywordIDs[] = $record['keyword_id'];
        $keywordIDString = implode( ', ', $keywordIDs );

        /* Then we see which ones only have a count of 1 */
        $res = $db->arrayQuery( "SELECT keyword_id
                                 FROM ezkeyword, ezkeyword_attribute_link
                                 WHERE ezkeyword.id = ezkeyword_attribute_link.keyword_id
                                     AND ezkeyword.id IN ($keywordIDString)
                                 GROUP BY keyword_id
                                 HAVING COUNT(*) = 1" );
        $unusedKeywordIDs = array();
        foreach ( $res as $record )
            $unusedKeywordIDs[] = $record['keyword_id'];
        $unusedKeywordIDString = implode( ', ', $unusedKeywordIDs );

        /* Then we delete those unused keywords */
        if ( $unusedKeywordIDString )
            $db->query( "DELETE FROM ezkeyword WHERE id IN ($unusedKeywordIDString)" );

        /* And as last we remove the link between the keyword and the object
         * attribute to be removed */
        $db->query( "DELETE FROM ezkeyword_attribute_link
                     WHERE objectattribute_id='$contentObjectAttributeID'" );
    }

    /*!
     Returns the content of the keyword for use as a title
    */
    function title( &$attribute )
    {
        $keyword = new eZKeyword();
        $keyword->fetch( $attribute );
        $return =& $keyword->keywordString();

        return $return;
    }

    function hasObjectAttributeContent( &$contentObjectAttribute )
    {
        $keyword = new eZKeyword();
        $keyword->fetch( $contentObjectAttribute );
        $array =& $keyword->keywordArray();

        return count( $array ) > 0;
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
     \param package
     \param content attribute

     \return a DOM representation of the content object attribute
    */
    function &serializeContentObjectAttribute( &$package, &$objectAttribute )
    {
        $node = new eZDOMNode();

        $node->setPrefix( 'ezobject' );
        $node->setName( 'attribute' );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'id', $objectAttribute->attribute( 'id' ), 'ezremote' ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'identifier', $objectAttribute->contentClassAttributeIdentifier(), 'ezremote' ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'name', $objectAttribute->contentClassAttributeName() ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'type', $this->isA() ) );

        $keyword = new eZKeyword();
        $keyword->fetch( $objectAttribute );
        $keyWordString =& $keyword->keywordString();
        $node->appendChild( eZDOMDocument::createElementTextNode( 'keyword-string', $keyWordString ) );

        return $node;
    }

    /*!
     \reimp
     Unserailize contentobject attribute

     \param package
     \param contentobject attribute object
     \param ezdomnode object
    */
    function unserializeContentObjectAttribute( &$package, &$objectAttribute, $attributeNode )
    {
        $keyWordString = $attributeNode->elementTextContentByName( 'keyword-string' );
        $keyword = new eZKeyword();
        $keyword->initializeKeyword( $keyWordString );
        $objectAttribute->setContent( $keyword );
    }
}

eZDataType::register( EZ_DATATYPESTRING_KEYWORD, 'ezkeywordtype' );

?>
