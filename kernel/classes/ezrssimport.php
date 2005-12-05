<?php
//
// Definition of eZRSSImport class
//
// Created on: <24-Sep-2003 12:53:56 kk>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file ezrssimport.php
*/

/*!
  \class eZRSSImport ezrssimport.php
  \brief Handles RSS Import in eZ publish

  RSSImport is used to create RSS feeds from published content. See kernel/rss for more files.
*/

include_once( 'kernel/classes/ezpersistentobject.php' );

define( "EZ_RSSIMPORT_STATUS_VALID", 1 );
define( "EZ_RSSIMPORT_STATUS_DRAFT", 0 );

class eZRSSImport extends eZPersistentObject
{
    /*!
     Initializes a new RSSImport.
    */
    function eZRSSImport( $row )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'modified' => array( 'name' => 'Modified',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         'modifier_id' => array( 'name' => 'ModifierID',
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'created' => array( 'name' => 'Created',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'creator_id' => array( 'name' => 'CreatorID',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'object_owner_id' => array( 'name' => 'ObjectOwnerID',
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         'status' => array( 'name' => 'Status',
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ),
                                         'name' => array( 'name' => 'Name',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'url' => array( 'name' => 'URL',
                                                         'datatype' => 'string',
                                                         'default' => '',
                                                         'required' => true ),
                                         'destination_node_id' => array( 'name' => 'DestinationNodeID',
                                                                         'datatype' => 'int',
                                                                         'default' => '',
                                                                         'required' => true ),
                                         'class_id' => array( 'name' => 'ClassID',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         'class_title' => array( 'name' => 'ClassTitle', // depricated
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => false ),
                                         'class_url' => array( 'name' => 'ClassURL', // depricated
                                                               'datatype' => 'string',
                                                               'default' => '',
                                                               'required' => false ),
                                         'class_description' => array( 'name' => 'ClassDescription', // depricated
                                                                       'datatype' => 'string',
                                                                       'default' => '',
                                                                       'required' => false ),
                                         'active' => array( 'name' => 'Active',
                                                            'datatype' => 'integer',
                                                            'default' => 1,
                                                            'required' => true ),
                                         'import_description' => array( 'name' => 'ImportDescriptionValue',
                                                                        'datatype' => 'string',
                                                                        'default' => '',
                                                                        'required' => true ) ),
                      "keys" => array( "id", 'status' ),
                      'function_attributes' => array( 'class_attributes' => 'classAttributes',
                                                      'destination_path' => 'destinationPath',
                                                      'modifier' => 'modifier',
                                                      'object_owner' => 'objectOwner',
                                                      'import_description_array' => 'importDescription',
                                                      'field_map' => 'fieldMap',
                                                      'object_attribute_list' => 'objectAttributeList' ),
                      "increment_key" => "id",
                      "class_name" => "eZRSSImport",
                      "name" => "ezrss_import" );
    }

    /*!
     \static
     Creates a new RSS Import
     \param User ID

     \return the new RSS Import object
    */
    function create( $userID = false )
    {
        if ( $userID === false )
        {
            include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
            $user = eZUser::currentUser();
            $userID = $user->attribute( "contentobject_id" );
        }

        $dateTime = time();
        $row = array( 'id' => null,
                      'name' => ezi18n( 'kernel/rss', 'New RSS Import' ),
                      'modifier_id' => $userID,
                      'modified' => $dateTime,
                      'creator_id' => $userID,
                      'created' => $dateTime,
                      'object_owner_id' => $userID,
                      'url' => '',
                      'status' => 0,
                      'destination_node_id' => 0,
                      'class_id' => 0,
                      'class_title' => '',
                      'class_url' => '',
                      'class_description' => '',
                      'active' => 1 );

        return new eZRSSImport( $row );
    }

    /*!
     Store Object to database
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function store()
    {
        include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
        $dateTime = time();
        $user =& eZUser::currentUser();

        $this->setAttribute( 'modifier_id', $user->attribute( 'contentobject_id' ) );
        $this->setAttribute( 'modified', $dateTime );
        eZPersistentObject::store();
    }

    /*!
     \static
      Fetches the RSS Import by ID.

     \param RSS Import ID
    */
    function fetch( $id, $asObject = true, $status = EZ_RSSIMPORT_STATUS_VALID )
    {
        return eZPersistentObject::fetchObject( eZRSSImport::definition(),
                                                null,
                                                array( "id" => $id,
                                                       'status' => $status ),
                                                $asObject );
    }

    /*!
     \static
      Fetches complete list of RSS Imports.
    */
    function fetchList( $asObject = true, $status = EZ_RSSIMPORT_STATUS_VALID )
    {
        $cond = null;
        if ( $status !== false )
        {
            $cond = array( 'status' => $status );
        }
        return eZPersistentObject::fetchObjectList( eZRSSImport::definition(),
                                                    null, $cond, null, null,
                                                    $asObject );
    }

    /*!
     \static
      Fetches complete list of active RSS Imports.
    */
    function fetchActiveList( $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZRSSImport::definition(),
                                                    null,
                                                    array( 'status' => 1,
                                                           'active' => 1 ),
                                                    null,
                                                    null,
                                                    $asObject );
    }


    function &objectOwner()
    {
        if ( isset( $this->ObjectOwnerID ) and $this->ObjectOwnerID )
        {
            include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
            $user = eZUser::fetch( $this->ObjectOwnerID );
        }
        else
            $user = null;
        return $user;
    }

    function &modifier()
    {
        if ( isset( $this->ModifierID ) and $this->ModifierID )
        {
            include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
            $user = eZUser::fetch( $this->ModifierID );
        }
        else
            $user = null;
        return $user;
    }

    function &classAttributes()
    {
        if ( isset( $this->ClassID ) and $this->ClassID )
        {
            include_once( 'kernel/classes/ezcontentclass.php' );
            $contentClass = eZContentClass::fetch( $this->ClassID );
            if ( $contentClass )
                $attributes =& $contentClass->fetchAttributes();
            else
                $attributes = null;
        }
        else
            $attributes = null;
        return $attributes;
    }

    function &destinationPath()
    {
        if ( isset( $this->DestinationNodeID ) and $this->DestinationNodeID )
        {
            include_once( "kernel/classes/ezcontentobjecttreenode.php" );
            $objectNode = eZContentObjectTreeNode::fetch( $this->DestinationNodeID );
            if ( isset( $objectNode ) )
            {
                $path_array =& $objectNode->attribute( 'path_array' );
                for ( $i = 0; $i < count( $path_array ); $i++ )
                {
                    $treenode = eZContentObjectTreeNode::fetch( $path_array[$i] );
                    if( $i == 0 )
                        $retValue = $treenode->attribute( 'name' );
                    else
                        $retValue .= '/'.$treenode->attribute( 'name' );
                }
            }
            else
                $retValue = null;
        }
        else
            $retValue = null;
        return $retValue;
    }

    /*!
     \static
     Analize RSS import, and get RSS version number

     \param URL

     \return RSS version number, false if invalid URL
    */
    function getRSSVersion( $url )
    {
        $fid = @fopen( $url, 'r' );
        if ( $fid === false )
        {
            return false;
        }

        $xmlData = "";
        do {
            $data = fread($fid, 8192);
            if (strlen($data) == 0) {
                break;
            }
            $xmlData .= $data;
        } while(true);

        fclose( $fid );

        include_once( 'lib/ezxml/classes/ezxml.php' );
        // Create DomDocumnt from http data
        $xmlObject = new eZXML();
        $domDocument = $xmlObject->domTree( $xmlData );

        if ( $domDocument == null or $domDocument === false )
        {
            return false;
        }

        $root = $domDocument->root();

        if ( $root == null )
        {
            return false;
        }

        switch( $root->attributeValue( 'version' ) )
        {
            default:
            case '1.0':
            {
                return '1.0';
            } break;

            case '0.91':
            case '0.92':
            case '2.0':
            {
                return $root->attributeValue( 'version' );
            } break;
        }
    }

    /*!
     \static
     Object attribute list
    */
    function &objectAttributeList()
    {
        $objectAttributeList = array( 'published' => 'Published',
                                      'modified' => 'Modified' );
        return $objectAttributeList;
    }

    /*!
     \static

     Return default RSS field definition

     \param RSS version

     \return RSS field definition array.
    */
    function rssFieldDefinition( $version = '2.0' )
    {
        switch ( $version )
        {
            case '1.0':
            {
                return array( 'item' => array( 'attributes' => array( 'about' ),
                                               'elements' => array( 'title',
                                                                    'link',
                                                                    'description' ) ),
                              'channel' => array( 'attributes' => array( 'about' ),
                                                  'elements' => array( 'title',
                                                                       'link',
                                                                       'description'.
                                                                       'image' => array( 'attributes' => array( 'resource' ) ) ) ) );
            } break;

            case '2.0':
            case '0.91':
            case '0.92':
            {
                return array( 'item' => array( 'elements' => array( 'title',
                                                                    'link',
                                                                    'description',
                                                                    'author',
                                                                    'category',
                                                                    'comments',
                                                                    'guid',
                                                                    'pubDate' ) ),
                              'channel' => array( 'elements' => array( 'title',
                                                                       'link',
                                                                       'description',
                                                                       'copyright',
                                                                       'managingEditor',
                                                                       'webMaster',
                                                                       'pubDate',
                                                                       'lastBuildDate',
                                                                       'category',
                                                                       'generator',
                                                                       'docs',
                                                                       'cloud',
                                                                       'ttl' ) ) );
            }
        }
    }

    /*!
     \static

     \param RSS version

     \return Ordered array of field definitions
    */
    function &fieldMap( $version = '2.0' )
    {
        $fieldDefinition = eZRSSImport::rssFieldDefinition();

        $ini = eZINI::instance();
        foreach( $ini->variable( 'RSSSettings', 'ActiveExtensions' ) as $activeExtension )
        {
            if ( file_exists( eZExtension::baseDirectory() . '/' . $activeExtension . '/rss/ezrssimport.php' ) )
            {
                include_once( eZExtension::baseDirectory() . '/' . $activeExtension . '/rss/ez' . $activeExtension . 'rssimport.php' );
                $fieldDefinition = eZRSSImport::arrayMergeRecursive( $fieldDefinition, call_user_func( array( 'ez' . $activeExtension . 'rssimport', 'rssFieldDefinition' ), array() ) );
            }
        }

        $returnArray = array();
        eZRSSImport::recursiveFieldMap( $fieldDefinition, '', '', $returnArray, 0 );

        return $returnArray;
    }

    /*!
     \static

     Recursivly build field map

     \param array
    */
    function recursiveFieldMap( $definitionArray, $globalKey, $value, &$returnArray, $count )
    {
        foreach( $definitionArray as $key => $definition )
        {
            if ( is_string( $definition ) )
            {
                $returnArray[$globalKey . ' - ' . $definition ] = $value . ' - ' . ucfirst( $definition );
            }
            else
            {
                eZRSSImport::recursiveFieldMap( $definition,
                                                $globalKey . ( strlen( $globalKey ) ? ' - ' : '' ) . $key ,
                                                $value . ( strlen( $value ) && ( $count % 2 == 0 ) ? ' - ' : '' ) . ( $count % 2 == 0 ? ucfirst( $key ) : '' ),
                                                $returnArray, $count + 1 );
            }
        }
    }

    /*!
     Set import description

     Import definition must be set as an multidimentional array.

     Example : array( 'rss_version' => <version>,
                      'object_attributes' => array( ... ),
                      'class_attributes' => array( <content class attribute id> => <RSS import field>,  ... ) )
    */
    function setImportDescription( $definition = array() )
    {
        $this->setAttribute( 'import_description', serialize( $definition ) );
    }

    /*!
     Get import description

     \return import description
    */
    function &importDescription()
    {
        $description = @unserialize( $this->attribute( 'import_description' ) );
        if ( !$description )
        {
            $description = array();
        }
        return $description;
    }

    function arrayMergeRecursive( $arr1, $arr2 )
    {
        if ( !is_array( $arr1 ) ||
             !is_array( $arr2 ) )
        {
            return $arr2;
        }
        foreach ($arr2 AS $key => $value )
        {
            $arr1[$key] = eZRSSImport::arrayMergeRecursive( @$arr1[$key], $value);
        }

        return $arr1;
    }
}

?>
