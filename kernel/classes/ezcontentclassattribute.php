<?php
//
// Definition of eZContentClassAttribute class
//
// Created on: <16-Apr-2002 11:08:14 amos>
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
  \class eZContentClassAttribute ezcontentclassattribute.php
  \ingroup eZKernel
  \brief Encapsulates data for a class attribute

*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( 'kernel/classes/ezcontentclassattributenamelist.php' );

class eZContentClassAttribute extends eZPersistentObject
{
    function eZContentClassAttribute( $row )
    {
        $this->eZPersistentObject( $row );

        $this->Content = null;
        $this->DisplayInfo = null;
        $this->Module = null;

        $this->NameList = new eZContentClassNameList();
        $this->NameList->initFromSerializedList( $row['serialized_name_list'] );
    }

    function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'serialized_name_list' => array( 'name' => 'SerializedNameList',
                                                                          'datatype' => 'string',
                                                                          'default' => '',
                                                                          'required' => true ),
                                         'version' => array( 'name' => 'Version',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'contentclass_id' => array( 'name' => 'ContentClassID',
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true,
                                                                     'foreign_class' => 'eZContentClass',
                                                                     'foreign_attribute' => 'id',
                                                                     'multiplicity' => '1..*' ),
                                         'identifier' => array( 'name' => 'Identifier',
                                                                'datatype' => 'string',
                                                                'default' => '',
                                                                'required' => true ),
                                         'placement' => array( 'name' => 'Position',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'is_searchable' => array( 'name' => 'IsSearchable',
                                                                   'datatype' => 'integer',
                                                                   'default' => 0
                                                                   ),
                                         'is_required' => array( 'name' => 'IsRequired',
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'can_translate' => array( 'name' => 'CanTranslate',
                                                                   'datatype' => 'integer',
                                                                   'default' => 0
                                                                   ),
                                         'is_information_collector' => array( 'name' => 'IsInformationCollector',
                                                                              'datatype' => 'integer',
                                                                              'default' => 0,
                                                                              'required' => true ),
                                         'data_type_string' => array( 'name' => 'DataTypeString',
                                                                      'datatype' => 'string',
                                                                      'default' => '',
                                                                      'required' => true ),
                                         'data_int1' => array( 'name' => 'DataInt1',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_int2' => array( 'name' => 'DataInt2',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_int3' => array( 'name' => 'DataInt3',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_int4' => array( 'name' => 'DataInt4',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'data_float1' => array( 'name' => 'DataFloat1',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_float2' => array( 'name' => 'DataFloat2',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_float3' => array( 'name' => 'DataFloat3',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_float4' => array( 'name' => 'DataFloat4',
                                                                 'datatype' => 'float',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         'data_text1' => array( 'name' => 'DataText1',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text2' => array( 'name' => 'DataText2',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text3' => array( 'name' => 'DataText3',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text4' => array( 'name' => 'DataText4',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text5' => array( 'name' => 'DataText5',
                                                                'datatype' => 'text',
                                                                'default' => '',
                                                                'required' => true ) ),
                      'keys' => array( 'id', 'version' ),
                      "function_attributes" => array( "content" => "content",
                                                      'temporary_object_attribute' => 'instantiateTemporary',
                                                      'data_type' => 'dataType',
                                                      'display_info' => 'displayInfo',
                                                      'name' => 'name',
                                                      'nameList' => 'nameList' ),
                      'increment_key' => 'id',
                      'sort' => array( 'placement' => 'asc' ),
                      'class_name' => 'eZContentClassAttribute',
                      'name' => 'ezcontentclass_attribute' );
    }

    function clone()
    {
        $row = array(
            'id' => null,
            'version' => $this->attribute( 'version' ),
            'contentclass_id' => $this->attribute( 'contentclass_id' ),
            'identifier' => $this->attribute( 'identifier' ),
            'serialized_name_list' => $this->attribute( 'serialized_name_list' ),
            'is_searchable' => $this->attribute( 'is_searchable' ),
            'is_required' => $this->attribute( 'is_required' ),
            'can_translate' => $this->attribute( 'can_translate' ),
            'is_information_collector' => $this->attribute( 'is_information_collector' ),
            'data_type_string' => $this->attribute( 'data_type_string' ),
            'placement' => $this->attribute( 'placement' ),
            'data_int1' => $this->attribute( 'data_int1' ),
            'data_int2' => $this->attribute( 'data_int2' ),
            'data_int3' => $this->attribute( 'data_int3' ),
            'data_int4' => $this->attribute( 'data_int4' ),
            'data_float1' => $this->attribute( 'data_float1' ),
            'data_float2' => $this->attribute( 'data_float2' ),
            'data_float3' => $this->attribute( 'data_float3' ),
            'data_float4' => $this->attribute( 'data_float4' ),
            'data_text1' => $this->attribute( 'data_text1' ),
            'data_text2' => $this->attribute( 'data_text1' ),
            'data_text3' => $this->attribute( 'data_text3' ),
            'data_text4' => $this->attribute( 'data_text4' ),
            'data_text5' => $this->attribute( 'data_text5' ) );
        return new eZContentClassAttribute( $row );
    }

    function create( $class_id, $data_type_string, $optionalValues = array(), $languageLocale = false )
    {
        if ( $languageLocale == false )
        {
            $languageLocale = eZContentObject::defaultLanguage();
        }

        $row = array(
            'id' => null,
            'version' => EZ_CLASS_VERSION_STATUS_TEMPORARY,
            'contentclass_id' => $class_id,
            'identifier' => '',
            'serialized_name_list' => '',
            'is_searchable' => 1,
            'is_required' => 0,
            'can_translate' => 1,
            'is_information_collector' => 0,
            'data_type_string' => $data_type_string,
            'placement' => eZPersistentObject::newObjectOrder( eZContentClassAttribute::definition(),
                                                               'placement',
                                                               array( 'version' => 1,
                                                                      'contentclass_id' => $class_id ) ) );
        $row = array_merge( $row, $optionalValues );
        $attribute = new eZContentClassAttribute( $row );

        $attribute->NameList->setNameByLanguageLocale( '', $languageLocale );
        $attribute->NameList->setAlwaysAvailableLanguage( $languageLocale );

        return $attribute;
    }

    function instantiate( $contentobjectID, $languageCode = false, $version = 1 )
    {
        $attribute = eZContentObjectAttribute::create( $this->attribute( 'id' ), $contentobjectID, $version, $languageCode );
        $attribute->initialize();
        $attribute->store();
        $attribute->postInitialize();
    }

    /*!
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &instantiateTemporary( $contentobjectID = false )
    {
        $attribute = eZContentObjectAttribute::create( $this->attribute( 'id' ), $contentobjectID );
        return $attribute;
    }

    function store()
    {
        $dataType = $this->dataType();
        $dataType->preStoreClassAttribute( $this, $this->attribute( 'version' ) );

        $this->setAttribute( 'serialized_name_list', $this->NameList->serializeNames() );

        $stored = eZPersistentObject::store();

        // store the content data for this attribute
        $info = $dataType->attribute( "information" );
        $dataType->storeClassAttribute( $this, $this->attribute( 'version' ) );

        return $stored;
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function storeDefined()
    {
        $dataType = $this->dataType();

        $db =& eZDB::instance();
        $db->begin();
        $dataType->preStoreDefinedClassAttribute( $this );

        $this->setAttribute( 'serialized_name_list', $this->NameList->serializeNames() );

        $stored = eZPersistentObject::store();

        // store the content data for this attribute
        $info = $dataType->attribute( "information" );
        $dataType->storeDefinedClassAttribute( $this );
        $db->commit();

        return $stored;
    }

    /*!
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
     */
    function remove()
    {
        $dataType = $this->dataType();
        $version = $this->Version;
        if ( $dataType->isClassAttributeRemovable( $this ) )
        {
            $db =& eZDB::instance();
            $db->begin();
            $dataType->deleteStoredClassAttribute( $this, $version );
            eZPersistentObject::remove();
            $db->commit();
        }
        else
        {
            eZDebug::writeError( 'Datatype [' . $dataType->attribute( 'name' ) . '] can not be deleted to avoid system crash' );
        }
    }

    function &fetch( $id, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $field_filters = null )
    {
        $object = null;
        if ( $field_filters === null and $asObject and
             isset( $GLOBALS['eZContentClassAttributeCache'][$id][$version] ) )
        {
            $object =& $GLOBALS['eZContentClassAttributeCache'][$id][$version];
        }
        if ( $object === null )
        {
            $object = eZPersistentObject::fetchObject( eZContentClassAttribute::definition(),
                                                       $field_filters,
                                                       array( 'id' => $id,
                                                              'version' => $version ),
                                                       $asObject );
        }
        return $object;
    }

    function &fetchList( $asObject = true, $parameters = array() )
    {
        $parameters = array_merge( array( 'data_type' => false,
                                          'version' => false ),
                                   $parameters );
        $dataType = $parameters['data_type'];
        $version = $parameters['version'];
        $objects = null;
        if ( $asObject and $dataType === false and $version === false )
        {
            $objects =& $GLOBALS['eZContentClassAttributeCacheListFull'];
        }
        if ( !isset( $objects ) or
             $objects === null )
        {
            $conditions = null;
            if ( $dataType !== false or
                 $version !== false )
            {
                $conditions = array();
                if ( $dataType !== false )
                    $conditions['data_type_string'] = $dataType;
                if ( $version !== false )
                    $conditions['version'] = $version;
            }
            $objectList = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(),
                                                                null, $conditions, null, null,
                                                                $asObject );
            foreach ( array_keys( $objectList ) as $objectKey )
            {
                $objectItem =& $objectList[$objectKey];
                $objectID = $objectItem->ID;
                $objectVersion = $objectItem->Version;
                $GLOBALS['eZContentClassAttributeCache'][$objectID][$objectVersion] =& $objectItem;
            }
            $objects = $objectList;
        }
        return $objects;
    }

    function &fetchListByClassID( $classID, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $asObject = true )
    {
        $objects = null;
        if ( $asObject )
        {
            $objects =& $GLOBALS['eZContentClassAttributeCacheList'][$classID][$version];
        }
        if ( !isset( $objects ) or
             $objects === null )
        {
            $cond = array( 'contentclass_id' => $classID,
                           'version' => $version );
            $objectList = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(),
                                                                null, $cond, null, null,
                                                                $asObject );
            foreach ( array_keys( $objectList ) as $objectKey )
            {
                $objectItem =& $objectList[$objectKey];
                $objectID = $objectItem->ID;
                $objectVersion = $objectItem->Version;
                if ( !isset( $GLOBALS['eZContentClassAttributeCache'][$objectID][$objectVersion] ) )
                    $GLOBALS['eZContentClassAttributeCache'][$objectID][$objectVersion] =& $objectItem;
            }
            $objects = $objectList;
        }
        return $objects;
    }

    function &fetchFilteredList( $cond, $asObject = true )
    {
        $objectList = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(),
                                                           null, $cond, null, null,
                                                           $asObject );
        foreach ( array_keys( $objectList ) as $objectKey )
        {
            $objectItem =& $objectList[$objectKey];
            $objectID = $objectItem->ID;
            $objectVersion = $objectItem->Version;
            if ( !isset( $GLOBALS['eZContentClassAttributeCache'][$objectID][$objectVersion] ) )
                $GLOBALS['eZContentClassAttributeCache'][$objectID][$objectVersion] =& $objectItem;
        }
        return $objectList;
    }

    /*!
     Moves the object down if $down is true, otherwise up.
     If object is at either top or bottom it is wrapped around.
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function move( $down, $params = null )
    {
        if ( is_array( $params ) )
        {
            $pos = $params['placement'];
            $cid = $params['contentclass_id'];
            $version = $params['version'];
        }
        else
        {
            $pos = $this->Position;
            $cid = $this->ContentClassID;
            $version = $this->Version;
        }
        eZPersistentObject::reorderObject( eZContentClassAttribute::definition(),
                                           array( 'placement' => $pos ),
                                           array( 'contentclass_id' => $cid,
                                                  'version' => $version ),
                                           $down );
    }

    function &dataType()
    {
        include_once( 'kernel/classes/ezdatatype.php' );
        $datatype = eZDataType::create( $this->DataTypeString );
        return $datatype;
    }

    /*!
     \return The content for this attribute.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &content()
    {
        if ( $this->Content === null )
        {
            $dataType = $this->dataType();
            $this->Content =& $dataType->classAttributeContent( $this );
        }

        return $this->Content;
    }

    /*!
     Sets the content for the current attribute
    */
    function setContent( $content )
    {
        $this->Content =& $content;
    }

    /*!
     \return Information on how to display the class attribute.
             See eZDataType::classDisplayInformation() for more information on what is returned.
    */
    function &displayInfo()
    {
        if ( !$this->DisplayInfo )
        {
            $dataType = $this->dataType();
            if ( is_object( $dataType ) )
            {
                $this->DisplayInfo =& $dataType->classDisplayInformation( $this, false );
            }
        }
        return $this->DisplayInfo;
    }

    /*!
     Executes the custom HTTP action
    */
    function customHTTPAction( &$module, &$http, $action )
    {
        $dataType = $this->dataType();
        $this->Module =& $module;
        $dataType->customClassAttributeHTTPAction( $http, $action, $this );
        unset( $this->Module );
        $this->Module = null;
    }

    /*!
     \return the module which uses this attribute or \c null if no module set.
     \note Currently only customHTTPAction sets this.
    */
    function &currentModule()
    {
        return $this->Module;
    }

    function cachedInfo()
    {
        include_once( 'lib/ezutils/classes/ezphpcreator.php' );
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );

        $info = array();
        $db =& eZDB::instance();
        $dbName = $db->DB;

        $cacheDir = eZSys::cacheDirectory();
        $phpCache = new eZPHPCreator( "$cacheDir", "sortkey_$dbName.php", '', array( 'clustering' => 'sortkey' ) );
        $handler =& eZExpiryHandler::instance();
        $expiryTime = 0;

        if ( $handler->hasTimestamp( 'content-view-cache' ) )
        {
            $expiryTime = $handler->timestamp( 'content-view-cache' );
        }

        if ( $phpCache->canRestore( $expiryTime ) )
        {
            $info = $phpCache->restore( array( 'sortkey_type_array' => 'sortKeyTypeArray',
                                               'attribute_type_array' => 'attributeTypeArray' ) );
        }
        else
        {
            // Fetch all datatypes and id's used
            $query = "SELECT id, data_type_string FROM ezcontentclass_attribute";
            $attributeArray = $db->arrayQuery( $query );

            $attributeTypeArray = array();
            $sortKeyTypeArray = array();
            foreach ( $attributeArray as $attribute )
            {
                $attributeTypeArray[$attribute['id']] = $attribute['data_type_string'];
                $sortKeyTypeArray[$attribute['data_type_string']] = 0;
            }

            include_once( 'kernel/classes/ezdatatype.php' );

            // Fetch datatype for every unique datatype
            foreach ( array_keys( $sortKeyTypeArray ) as $key )
            {
                unset( $dataType );
                $dataType = eZDataType::create( $key );
                if( is_object( $dataType ) )
                    $sortKeyTypeArray[$key] = $dataType->sortKeyType();
            }
            unset( $dataType );

            // Store identifier list to cache file
            $phpCache->addVariable( 'sortKeyTypeArray', $sortKeyTypeArray );
            $phpCache->addVariable( 'attributeTypeArray', $attributeTypeArray );
            $phpCache->store();

            $info['sortkey_type_array'] =& $sortKeyTypeArray;
            $info['attribute_type_array'] =& $attributeTypeArray;
        }

        return $info;
    }

    /*!
     \static
    */
    function sortKeyTypeByID( $classAttributeID )
    {
        $sortKeyType = false;

        $info = eZContentClassAttribute::cachedInfo();
        if ( isset( $info['attribute_type_array'][$classAttributeID] ) )
        {
            $classAttributeType = $info['attribute_type_array'][$classAttributeID];
            $sortKeyType = $info['sortkey_type_array'][$classAttributeType];
        }

        return $sortKeyType;
    }

    /*!
     \static
    */
    function dataTypeByID( $classAttributeID )
    {
        $dataTypeString = false;
        $info = eZContentClassAttribute::cachedInfo();

        if ( isset( $info['attribute_type_array'][$classAttributeID] ) )
            $dataTypeString = $info['attribute_type_array'][$classAttributeID];

        return $dataTypeString;
    }

    /*!
      This methods relay calls to the diff method inside the datatype.
    */
    function diff( $old, $new )
    {
        $datatype = $this->dataType();
        $result = $datatype->diff( $old, $new );
        return $result;
    }

    /*!
     \static
    */
    function nameFromSerializedString( $serailizedNameList, $languageLocale = false )
    {
        return eZContentClassAttributeNameList::nameFromSerializedString( $serailizedNameList, $languageLocale );
    }

    function &name( $languageLocale = false )
    {
        $name = $this->NameList->name( $languageLocale );
        return $name;
    }

    function setName( $name, $languageLocale )
    {
        $this->NameList->setNameByLanguageLocale( $name, $languageLocale );
    }

    function &nameList()
    {
        $nameList = $this->NameList->nameList();
        return $nameList;
    }

    function setAlwaysAvailableLanguageID( $languageID )
    {
        $languageLocale = false;
        if ( $languageID )
        {
            $language = eZContentLanguage::fetch( $languageID );
            $languageLocale = $language->attribute( 'locale' );
        }

        $this->setAlwaysAvailableLanguage( $languageLocale );
    }

    function setAlwaysAvailableLanguage( $languageLocale )
    {
        if ( $languageLocale )
        {
            $this->NameList->setAlwaysAvailableLanguage( $languageLocale );
        }
        else
        {
            $this->NameList->setAlwaysAvailableLanguage( false );
        }
    }

    function removeTranslation( $languageLocale )
    {
        $this->NameList->removeName( $languageLocale );
    }


    /// \privatesection
    /// Contains the content for this attribute
    var $Content;
    /// Contains information on how to display the current attribute in various viewmodes
    var $DisplayInfo;
    var $ID;
    var $Version;
    var $ContentClassID;
    var $Identifier;
    // serialized array of translated names
    var $SerializedNameList;
    // unserialized attribute names
    var $NameList;
    var $DataTypeString;
    var $Position;
    var $IsSearchable;
    var $IsRequired;
    var $IsInformationCollector;
    var $Module;
}

?>
