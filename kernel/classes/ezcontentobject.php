<?php
//
// Definition of eZContentObject class
//
// Created on: <17-Apr-2002 09:15:27 bf>
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
  \class eZContentObject ezcontentobject.php
  \ingroup eZKernel
  \brief Handles eZ publish content objects

  It encapsulates the date for an object and provides lots of functions
  for dealing with versions, translations and attributes.

  \sa eZContentClass
*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( 'lib/ezlocale/classes/ezlocale.php' );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezcontentobjectversion.php" );
include_once( "kernel/classes/ezcontentobjectattribute.php" );
include_once( "kernel/classes/ezcontentclass.php" );
include_once( "kernel/classes/ezcontentobjecttreenode.php" );
include_once( "kernel/classes/eznodeassignment.php" );
include_once( "kernel/classes/ezcontenttranslation.php" );
include_once( "kernel/classes/ezsearch.php" );
include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );

define( "EZ_CONTENT_OBJECT_STATUS_DRAFT", 0 );
define( "EZ_CONTENT_OBJECT_STATUS_PUBLISHED", 1 );
define( "EZ_CONTENT_OBJECT_STATUS_ARCHIVED", 2 );


class eZContentObject extends eZPersistentObject
{
    function eZContentObject( $row )
    {
        $this->eZPersistentObject( $row );
        $this->CurrentLanguage = eZContentObject::defaultLanguage();
    }

    function &definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "section_id" => array( 'name' => "SectionID",
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         "owner_id" => array( 'name' => "OwnerID",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "contentclass_id" => array( 'name' => "ClassID",
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         "name" => array( 'name' => "Name",
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         "is_published" => array( 'name' => "IsPublished",
                                                                  'datatype' => 'integer',
                                                                  'default' => 0,
                                                                  'required' => true ),
                                         "published" => array( 'name' => "Published",
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         "modified" => array( 'name' => "Modified",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "current_version" => array( 'name' => "CurrentVersion",
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         "status" => array( 'name' => "Status",
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ),
                                         "remote_id" => array( 'name' => "RemoteID",
                                                               'datatype' => 'string',
                                                               'default' => '',
                                                               'required' => true ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( "current" => "currentVersion",
                                                      'versions' => 'versions',
                                                      'author_array' => 'authorArray',
                                                      "class_name" => "className",
                                                      "content_class" => "contentClass",
                                                      "contentobject_attributes" => "contentObjectAttributes",
                                                      "owner" => "owner",
                                                      "related_contentobject_array" => "relatedContentObjectArray",
                                                      "related_contentobject_count" => "relatedContentObjectCount",
                                                      "can_read" => "canRead",
                                                      "can_create" => "canCreate",
                                                      "can_create_class_list" => "canCreateClassList",
                                                      "can_edit" => "canEdit",
                                                      "can_remove" => "canRemove",
                                                      "data_map" => "dataMap",
                                                      "main_parent_node_id" => "mainParentNodeID",
                                                      "assigned_nodes" => "assignedNodes",
                                                      "parent_nodes" => "parentNodes",
                                                      "main_node_id" => "mainNodeID",
                                                      "main_node" => "mainNode",
                                                      "content_action_list" => "contentActionList",
                                                      "name" => "Name" ),
                      "increment_key" => "id",
                      "class_name" => "eZContentObject",
                      "sort" => array( "id" => "asc" ),
                      "name" => "ezcontentobject" );
    }

    function &attribute( $attr )
    {
        if ( $attr == "current" or
             $attr == 'versions' or
             $attr == 'author_array' or
             $attr == "class_name" or
             $attr == "content_class" or
             $attr == "owner" or
             $attr == "contentobject_attributes" or
             $attr == "related_contentobject_array" or
             $attr == "related_contentobject_count" or
             $attr == "can_read" or
             $attr == "can_create" or
             $attr == "can_create_class_list" or
             $attr == "can_edit" or
             $attr == "can_remove" or
             $attr == "data_map" or
             $attr == "content_action_list"
             )
        {
            if ( $attr == "current" )
                return $this->currentVersion();
            else if ( $attr == 'versions' )
                return $this->versions();
            else if ( $attr == 'author_array' )
                return $this->authorArray();
            else if ( $attr == "class_name" )
                return $this->className();
            else if ( $attr == 'content_class' )
                return $this->contentClass();
            else if ( $attr == "owner" )
                return $this->owner();
            else if ( $attr == "can_read" )
                return $this->canRead();
            else if ( $attr == "can_create" )
                return $this->canCreate();
            else if ( $attr == "can_create_class_list" )
                return $this->canCreateClassList();
            else if ( $attr == "can_edit" )
                return $this->canEdit();
            else if ( $attr == "can_remove" )
                return $this->canRemove();
            else if ( $attr == "contentobject_attributes" )
                return $this->contentObjectAttributes();
            else if ( $attr == "related_contentobject_array" )
                return $this->relatedContentObjectArray();
            else if ( $attr == 'related_contentobject_count' )
                return $this->relatedContentObjectCount();
            else if ( $attr == "content_action_list" )
                return $this->contentActionList();
            else if ( $attr == "data_map" )
            {
                return $this->dataMap();
            }
        }
        elseif ( $attr == "main_parent_node_id" )
        {
            return  $this->mainParentNodeID() ;
        }
        elseif ( $attr == 'assigned_nodes' )
        {
            return $this->assignedNodes( true );
        }
        elseif ( $attr == 'parent_nodes' )
        {
            return $this->parentNodes( true, false );
        }
        elseif ( $attr == 'main_node_id' )
        {
            return $this->mainNodeID();
        }
        elseif ( $attr == 'main_node' )
        {
            return $this->mainNode();
        }
        elseif ( $attr == 'name' )
        {
            return $this->name();
        }
        else
            return eZPersistentObject::attribute( $attr );
    }

    function &name( $version = false , $lang = false )
    {
        if ( isset( $this->Name ) && !$version && !$lang )
        {
            return $this->Name;
        }
        if ( !$version )
        {
            $version = $this->attribute( 'current_version' );
        }
        $objectID = $this->attribute( 'id' );
        return $this->versionLanguageName( $objectID, $version, $lang );
    }

    function &versionLanguageName( $contentObjectID, $version, $lang = false )
    {
        if ( !$lang )
        {
            $lang = eZContentObject::defaultLanguage();
        }
        $db =& eZDb::instance();
        $query= "select name,real_translation from ezcontentobject_name where contentobject_id = '$contentObjectID' and content_version = '$version'  and content_translation = '$lang'";
        $result =& $db->arrayQuery( $query );
        if ( count( $result ) < 1 )
        {
            eZDebug::writeNotice( "There is no object name for version($version) of the content object ($contentObjectID) in language($lang)", 'eZContentObject::versionLanguageName' );
            $name = false;
            return $name;
        }
        return $result[0]['name'];
    }

    /*!
     Sets the name of the object, in memory only. Use setName() to change it.
    */
    function setCachedName( $name )
    {
        $this->Name = $name;
    }

    function setName( $objectName, $versionNum = false, $translation = false )
    {
        $db =& eZDB::instance();
        $objectName = $db->escapeString( $objectName );
        if ( !$versionNum )
        {
            $versionNum = $this->attribute( 'current_version' );
        }

        if ( !$translation )
        {
            $translation = $this->defaultLanguage();
        }

        $ini =& eZINI::instance();
//        $needTranslations = $ini->variableArray( "ContentSettings", "TranslationList" );
        $needTranslations =& eZContentTranslation::fetchLocaleList();
        $default = false;
        if ( $translation == $this->defaultLanguage() )
        {
            $default = true;
        }

        $objectID = $this->attribute( 'id' );
        if ( !$default || count( $needTranslations ) == 1 )
        {
            $query = "DELETE FROM ezcontentobject_name WHERE contentobject_id = $objectID and content_version = $versionNum and content_translation ='$translation' ";
            $db->query( $query );
            $query = "INSERT INTO ezcontentobject_name( contentobject_id,
                                                        name,
                                                        content_version,
                                                        content_translation,
                                                        real_translation )
                              VALUES( '$objectID',
                                      '$objectName',
                                      '$versionNum',
                                      '$translation',
                                      '$translation' )";
            $db->query( $query );
            return;
        }
        else
        {
            $existingTranslationNamesResult = $db->arrayQuery( "select * from ezcontentobject_name where contentobject_id = $objectID and content_version = $versionNum" );
            $existingTranslationList = array();
            foreach ( $existingTranslationNamesResult as $existingTranslation )
            {
                $existingTranslationList[] = $existingTranslation['content_translation'];
            }
            $realTranslation =  $translation;
            foreach ( $needTranslations as $needTranslation )
            {
                if ( $translation == $needTranslation )
                {
                    $query = "delete from ezcontentobject_name where contentobject_id = $objectID and content_version = $versionNum and content_translation ='$translation' ";
                    $db->query( $query );
                    $query = "insert into ezcontentobject_name( contentobject_id,name,content_version,content_translation,real_translation )
                              values( $objectID,
                                      '$objectName',
                                      $versionNum,
                                      '$translation',
                                      '$translation' )";
                    $db->query( $query );
                }else if ( ! in_array( $needTranslation, $existingTranslationList ) )
                {
                    $query = "insert into ezcontentobject_name( contentobject_id,name,content_version,content_translation,real_translation )
                              values( $objectID,
                                      '$objectName',
                                      $versionNum,
                                      '$needTranslation',
                                      '$translation' )";
                    $db->query( $query );
                }
            }


        }
    }

    /*!
	 \return a map with all the content object attributes where the keys are the
             attribute identifiers.
    */
    function &dataMap()
    {
        return $this->fetchDataMap();
    }

    /*!
	 \return a map with all the content object attributes where the keys are the
             attribute identifiers.
     \sa eZContentObjectTreeNode::dataMap
    */
    function &fetchDataMap( $version = false, $language = false )
    {
        // Global variable to cache datamaps
        global $eZContentObjectDataMapCache;

        if ( $version == false )
            $version = $this->attribute( 'current_version' );
        if ( $language == false )
        {
            if ( $this->CurrentLanguage != false )
            {
                $language = $this->CurrentLanguage;
            }
            else
            {
                $language = $this->defaultLanguage();
            }
        }

        if ( !isset( $eZContentObjectDataMapCache[$this->ID][$version][$language] ) )
        {
            $data =& $this->contentObjectAttributes( true, $version, $language );
            // Store the attributes for later use
            $this->ContentObjectAttributeArray[$version][$language] =& $data;

            $eZContentObjectDataMapCache[$this->ID][$version][$language] =& $data;
        }
        else
        {
            $data =& $eZContentObjectDataMapCache[$this->ID][$version][$language];
        }

        if ( !isset( $this->DataMap[$version][$language] ) )
        {
            $ret = array();
            reset( $data );
            while( ( $key = key( $data ) ) !== null )
            {
                $item =& $data[$key];

                $identifier = $item->contentClassAttributeIdentifier();

                $ret[$identifier] =& $item;

                next( $data );
            }
            $this->DataMap[$version][$language] =& $ret;
        }
        else
        {
            $ret =& $this->DataMap[$version][$language];
        }
        return $ret;
    }

    function resetDataMap()
    {
        $this->ContentObjectAttributeArray = array();
        $this->DataMap = array();
        return $this->DataMap;
    }

    /*!
     Returns the owner of the object as a content object.
    */
    function &owner()
    {
        return eZContentObject::fetch( $this->OwnerID );
    }

    /*!
     \return the content class identifier for the current content object
    */
    function &contentClassIdentifier()
    {
        $contentClass =& $this->contentClass();
        return $contentClass->attribute( 'identifier' );
    }

    /*!
     \return the content class for the current content object
    */
    function &contentClass()
    {
        return eZContentClass::fetch( $this->ClassID );
    }

    function &mainParentNodeID()
    {
        $temp = eZContentObjectTreeNode::getParentNodeId( $this->attribute( 'main_node_id' ) );

        return $temp;
    }

    function &fetch( $id, $asObject = true )
    {
        global $eZContentObjectContentObjectCache;

        if ( !isset( $eZContentObjectContentObjectCache[$id] ) and $asObject )
        {
            $language = eZContentObject::defaultLanguage();

            $useVersionName = true;
            if ( $useVersionName )
            {
                $versionNameTables = ', ezcontentobject_name ';
                $versionNameTargets = ', ezcontentobject_name.name as name,  ezcontentobject_name.real_translation ';

                $versionNameJoins = " and  ezcontentobject.id = ezcontentobject_name.contentobject_id and
                                  ezcontentobject.current_version = ezcontentobject_name.content_version and
                                  ezcontentobject_name.content_translation = '$language' ";
            }

            $db =& eZDB::instance();

            $query = "SELECT ezcontentobject.* $versionNameTargets
                      FROM
                         ezcontentobject
                         $versionNameTables
                      WHERE
                         ezcontentobject.id='$id'
                         $versionNameJoins";

            $resArray =& $db->arrayQuery( $query );


            if ( count( $resArray ) == 1 )
            {
                $objectArray =& $resArray[0];
            }
            else
                eZDebug::writeError( 'Object not found', 'eZContentObject::fetch()' );

            if ( $asObject )
            {
                $obj = new eZContentObject( $objectArray );
                $obj->CurrentLanguage = $objectArray['real_translation'];
                $eZContentObjectContentObjectCache[$id] =& $obj;
            }
            else
            {
                return $objectArray;
            }

            return $obj;
        }
        else
        {
            return $eZContentObjectContentObjectCache[$id];
        }
    }

    function &fetchList( $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                    array( 'id',
                                                           'parent_id',
                                                           'section_id',
                                                           'owner_id',
                                                           'contentclass_id',
                                                           'is_published',
                                                           'published',
                                                           'modified',
                                                           'current_version',
                                                           'remote_id'
                                                           ),
                                                    null, null, null,
                                                    $asObject );
    }

    function &fetchSameClassList( $contentClassID, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                    null,
                                                    array( "contentclass_id" => $contentClassID ),
                                                    null,
                                                    null,
                                                    $asObject );
    }
    /*!
      Returns the current version of this document.
    */
    function &currentVersion( $asObject = true )
    {
        return eZContentObjectVersion::fetchVersion( $this->attribute( "current_version" ), $this->ID, $asObject );
    }

    /*!
      Returns the given object version. False is returned if the versions does not exist.
    */
    function version( $version, $asObject = true )
    {
        return eZContentObjectVersion::fetchVersion( $version, $this->ID, $asObject );
    }

    /*!
      \return an array of versions for the current object.
    */
    function versions( $asObject = true, $parameters = array() )
    {
        $conditions = array( "contentobject_id" => $this->ID );
        if ( isset( $parameters['conditions'] ) )
        {
            if ( isset( $parameters['conditions']['status'] ) )
                $conditions['status'] = $parameters['conditions']['status'];
            if ( isset( $parameters['conditions']['creator_id'] ) )
                $conditions['creator_id'] = $parameters['conditions']['creator_id'];
        }
        return eZPersistentObject::fetchObjectList( eZContentObjectVersion::definition(),
                                                    null, $conditions,
                                                    null, null,
                                                    $asObject );
    }

    function &createInitialVersion( $userID )
    {
        return eZContentObjectVersion::create( $this->attribute( "id" ), $userID, 1 );
    }

    /*!
     Creates a new version and returns it as an eZContentObjectVersion object.
     If version number is given as argument that version is used to create a copy.
    */
    function &createNewVersion( $copyFromVersion = false )
    {
        // get the next available version number
        $nextVersionNumber = $this->nextVersion();

        if ( $copyFromVersion == false )
            $version =& $this->currentVersion();
        else
            $version =& $this->version( $copyFromVersion );

       return $this->copyVersion( $this, $version, $nextVersionNumber );
    }

    /*!
     Creates a new version and returns it as an eZContentObjectVersion object.
     If version number is given as argument that version is used to create a copy.
    */
    function &copyVersion( &$object, &$version, $newVersionNumber, $contentObjectID = false, $status = EZ_VERSION_STATUS_DRAFT )
    {
        $user =& eZUser::currentUser();
        $userID =& $user->attribute( 'contentobject_id' );

        $nodeAssignmentList =& $version->attribute( 'node_assignments' );
        foreach ( array_keys( $nodeAssignmentList ) as $key )
        {
            $nodeAssignment =& $nodeAssignmentList[$key];
            $clonedAssignment =& $nodeAssignment->clone( $newVersionNumber, $contentObjectID );
            $clonedAssignment->store();
            eZDebugSetting::writeDebug( 'kernel-content-object-copy', $clonedAssignment, 'copyVersion:Copied assignment' );
        }

        $currentVersionNumber = $version->attribute( "version" );
        $contentObjectTranslations =& $version->translations();

        $clonedVersion = $version->clone( $newVersionNumber, $userID, $contentObjectID, $status );
        if ( $contentObjectID !== false )
        {
            if ( $clonedVersion->attribute( 'status' ) == EZ_VERSION_STATUS_PUBLISHED )
                $clonedVersion->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
        }
        eZDebugSetting::writeDebug( 'kernel-content-object-copy', $clonedVersion, 'copyVersion:cloned version' );

        $clonedVersion->store();

        foreach ( array_keys( $contentObjectTranslations ) as $contentObjectTranslationKey )
        {
            $contentObjectTranslation =& $contentObjectTranslations[$contentObjectTranslationKey];
            $contentObjectAttributes =& $contentObjectTranslation->objectAttributes();
            foreach ( array_keys( $contentObjectAttributes ) as $attributeKey )
            {
                $attribute =& $contentObjectAttributes[$attributeKey];
                $clonedAttribute =& $attribute->clone( $newVersionNumber, $currentVersionNumber, $contentObjectID );
                $clonedAttribute->sync();
                eZDebugSetting::writeDebug( 'kernel-content-object-copy', $clonedAttribute, 'copyVersion:cloned attribute' );
            }
        }

        $relatedObjects =& $object->relatedContentObjectArray( $currentVersionNumber );
        foreach ( array_keys( $relatedObjects ) as $key )
        {
            $relatedObject =& $relatedObjects[$key];
            $objectID = $relatedObject->attribute( 'id' );
            $object->addContentObjectRelation( $objectID, $newVersionNumber );
            eZDebugSetting::writeDebug( 'kernel-content-object-copy', 'Add object relation', 'copyVersion' );
        }

        return $version;
//        return $clonedVersion;

    }

    /*!
     Creates a new content object instance and stores it.
    */
    function &create( $name, $contentclassID, $userID, $sectionID = 1, $version = 1 )
    {
        $row = array(
            "name" => $name,
            "current_version" => $version,
            "contentclass_id" => $contentclassID,
            "permission_id" => 1,
            "parent_id" => 0,
            "main_node_id" => 0,
            "owner_id" => $userID,
            "section_id" => $sectionID );
        return new eZContentObject( $row );
    }

    /*!
     \return a new clone of the current object which has is
             ready to be stored with a new ID.
    */
    function &clone()
    {
        $contentObject = $this;
        $contentObject->setAttribute( 'id', null );
        return $contentObject;
    }

    /*!
     Makes a copy of the object which is stored and then returns it.
    */
    function &copy( $allVersions = true )
    {
        eZDebugSetting::writeDebug( 'kernel-content-object-copy', 'Copy start, all versions=' . $allVersions ? 'true' : 'false', 'copy' );
        $contentObject =& $this->clone();
        $contentObject->setAttribute( 'current_version', 0 );
        $contentObject->store();
        eZDebugSetting::writeDebug( 'kernel-content-object-copy', $contentObject, 'contentObject' );

        $user =& eZUser::currentUser();
        $userID =& $user->attribute( 'contentobject_id' );

        $versionList = array();
        if ( $allVersions )
        {
            $versions =& $this->versions();
            for ( $i = 0; $i < count( $versions ); ++$i )
            {
                $versionID = $versions[$i]->attribute( 'version' );
                $versionList[$versionID] =& $versions[$i];
            }
        }
        else
        {
            $versionList[1] =& $this->currentVersion();
        }

        $versionKeys = array_keys( $versionList );
        foreach ( $versionKeys as $versionNumber )
        {
            $currentContentObjectVersion =& $versionList[$versionNumber];
            $contentObjectVersion =& $contentObject->copyVersion( $contentObject, $currentContentObjectVersion,
                                                                  $versionNumber, $contentObject->attribute( 'id' ),
                                                                  false );
            eZDebugSetting::writeDebug( 'kernel-content-object-copy', $contentObjectVersion, 'Copied version' );
        }
        eZDebugSetting::writeDebug( 'kernel-content-object-copy', 'Copy done', 'copy' );
        return $contentObject;
    }

    /*!
      Reverts the object to the given version. All versions newer then the given version will
      be deleted.
    */
    function revertTo( $version )
    {
        $db =& eZDB::instance();

        // Delete stored attribute from other tables
        $contentobjectAttributes =& $this->allContentObjectAttributes( $this->ID );
        foreach (  $contentobjectAttributes as $contentobjectAttribute )
        {
            $contentobjectAttributeVersion = $contentobjectAttribute->attribute("version");
            if( $contentobjectAttributeVersion > $version )
            {
                $classAttribute =& $contentobjectAttribute->contentClassAttribute();
                $dataType =& $classAttribute->dataType();
                $dataType->deleteStoredObjectAttribute( $contentobjectAttribute, $contentobjectAttributeVersion );
            }
        }

        $db->query( "DELETE FROM ezcontentobject_attribute
					      WHERE contentobject_id='$this->ID' AND version>'$version'" );

        $db->query( "DELETE FROM ezcontentobject_version
					      WHERE contentobject_id='$this->ID' AND version>'$version'" );

        $db->query( "DELETE FROM eznode_assignment
					      WHERE contentobject_id='$this->ID' AND contentobject_version > '$version'" );

        $this->CurrentVersion = $version;
        $this->store();
    }

    /*!
     Copies the given version of the object and creates a new current version.
    */
    function copyRevertTo( $version )
    {
        $versionObject =& $this->createNewVersion( $version );

//         $this->CurrentVersion = $versionObject->attribute( 'version' );
//         $this->store();
        return $versionObject->attribute( 'version' );
    }

    /*!
      If nodeID is not given, this function will remove object from database. All versions and translations of this object will be lost.
      Otherwise, it will check node assignment and only delete the object from this node if it was assigned to other nodes as well.
    */
    function purge( $id = false )
    {
        $delID = $this->ID;
        if ( is_numeric( $id ) )
        {
            $delID = $id;
            $contentobject =& eZContentObject::fetch( $delID );
        }
        else
        {
            $contentobject =& $this;
        }
        $db =& eZDB::instance();

//        $contentobjectAttributes =& $contentobject->allContentObjectAttributes( $delID );

        $contentobjectAttributes =& $contentobject->attribute( 'contentobject_attributes' );
        foreach (  $contentobjectAttributes as $contentobjectAttribute )
        {
            $classAttribute =& $contentobjectAttribute->contentClassAttribute();
            if ( !$classAttribute )
                continue;
            $dataType =& $classAttribute->dataType();
            if ( !$dataType )
                continue;
            $dataType->deleteStoredObjectAttribute( $contentobjectAttribute );
        }

        $db->query( "DELETE FROM ezcontentobject_attribute
		     WHERE contentobject_id='$delID'" );

        $db->query( "DELETE FROM ezcontentobject_version
		     WHERE contentobject_id='$delID'" );

        $db->query( "DELETE FROM ezcontentobject_name
		     WHERE contentobject_id='$delID'" );

        $db->query( "DELETE FROM ezcontentobject
		     WHERE id='$delID'" );

        $db->query( "DELETE FROM eznode_assignment
             WHERE contentobject_id = '$delID'" );

        $db->query( "DELETE FROM ezcontentobject_link
             WHERE from_contentobject_id = '$delID' OR to_contentobject_id = '$delID'" );
    }

    function remove( $id = false, $nodeID = null )
    {
        $delID = $this->ID;
        if ( is_numeric( $id ) )
        {
            $delID = $id;
            $contentobject =& eZContentObject::fetch( $delID );
        }
        else
        {
            $contentobject =& $this;
        }

        $nodes = $contentobject->attribute( 'assigned_nodes' );

        if ( $nodeID === null  or count( $nodes ) <= 1 )
        {
            foreach ( $nodes as $node )
            {
                $node->remove();
            }
//            $db =& eZDB::instance();

            $contentobject->setAttribute( 'status', EZ_CONTENT_OBJECT_STATUS_ARCHIVED );
            eZSearch::removeObject( $contentobject );
            $contentobject->store();
            // Delete stored attribute from other tables

        }
        else if ( $nodeID !== null )
        {
            $node =& eZContentObjectTreeNode::fetch( $nodeID );
            if ( $node->attribute( 'main_node_id' )  == $nodeID )
            {
                foreach ( array_keys( $nodes ) as $key )
                {
                    $node =& $nodes[$key];
                    $node->remove();
                }
                $contentobject->setAttribute( 'status', EZ_CONTENT_OBJECT_STATUS_ARCHIVED );
                eZSearch::removeObject( $contentobject );
                $contentobject->store();
            }
            else
            {
                eZContentObjectTreeNode::remove( $nodeID );
            }
        }
        else
        {
            eZContentObjectTreeNode::remove( $nodeID );
        }
    }

    /*
     Fetch all attributes of all versions belongs to a contentObject.
    */
    function &allContentObjectAttributes( $contentObjectID, $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(),
                                                    null,
                                                    array("contentobject_id" => $contentObjectID ),
                                                    null,
                                                    null,
                                                    $asObject );
    }

    /*!
      Fetches the attributes for the current published version of the object.
    */
    function &contentObjectAttributes( $asObject = true, $version = false, $language = false )
    {
        $db =& eZDB::instance();
        if ( $version === false )
            $version = $this->CurrentVersion;
        if ( $language === false )
            $language = eZContentObject::defaultLanguage();

//         print( "Attributes fetch $this->ID, $version" );

        if ( !isset( $this->ContentObjectAttributes[$version][$language] ) )
        {
//             print( "uncached<br>" );
            $query = "SELECT ezcontentobject_attribute.*, ezcontentclass_attribute.identifier as identifier FROM
                    ezcontentobject_attribute, ezcontentclass_attribute
                  WHERE
                    ezcontentclass_attribute.version = '0' AND
                    ezcontentclass_attribute.id = ezcontentobject_attribute.contentclassattribute_id AND
                    ezcontentobject_attribute.version = '$version' AND
                    ezcontentobject_attribute.contentobject_id = '$this->ID' AND
                    ezcontentobject_attribute.language_code = '$language'
                  ORDER by
                    ezcontentclass_attribute.placement ASC";

            $attributeArray =& $db->arrayQuery( $query );

            $returnAttributeArray = array();
            foreach ( $attributeArray as $attribute )
            {
                $attr = new eZContentObjectAttribute( $attribute );
                $attr->setContentClassAttributeIdentifier( $attribute['identifier'] );
                $returnAttributeArray[] = $attr;
            }

            $this->ContentObjectAttributes[$version][$language] =& $returnAttributeArray;
        }
        else
        {
//             print( "Cached<br>" );
            $returnAttributeArray =& $this->ContentObjectAttributes[$version][$language];
        }

        return $returnAttributeArray;
    }

    /*!
     Initializes the cached copy of the content object attributes for the given version and language
    */
    function setContentObjectAttributes( &$attributes, $version, $language )
    {
        $this->ContentObjectAttributes[$version][$language] =& $attributes;
    }

    /*!
      \static
      Fetches the attributes for an array of objects. The objectList parameter
      contains an array of object id's , versions and language to fetch attributes from.
    */
    function &fillNodeListAttributes( &$nodeList, $asObject = true )
    {
        $db =& eZDB::instance();

        if ( count( $nodeList ) > 0 )
        {
            $keys = array_keys( $nodeList );
            $objectArray = array();
            $whereSQL = '';
            $count = count( $nodeList );
            $i = 0;
            foreach ( $keys as $key )
            {
                $object =& $nodeList[$key]->attribute( 'object' );

                $objectArray = array( 'id' => $object->attribute( 'id' ),
                                      'language' => eZContentObject::defaultLanguage(),
                                      'version' => $nodeList[$key]->attribute( 'contentobject_version' ) );

                $whereSQL .= "( ezcontentobject_attribute.version = '" . $nodeList[$key]->attribute( 'contentobject_version' ) . "' AND
                    ezcontentobject_attribute.contentobject_id = '" . $object->attribute( 'id' ) . "' AND
                    ezcontentobject_attribute.language_code = '" . eZContentObject::defaultLanguage() . "' ) ";

                $i++;
                if ( $i < $count )
                    $whereSQL .= ' OR ';
            }

            $query = "SELECT ezcontentobject_attribute.*, ezcontentclass_attribute.identifier as identifier FROM
                    ezcontentobject_attribute, ezcontentclass_attribute
                  WHERE
                    ezcontentclass_attribute.version = '0' AND
                    ezcontentclass_attribute.id = ezcontentobject_attribute.contentclassattribute_id AND
                    ( $whereSQL )
                  ORDER BY
                    ezcontentobject_attribute.contentobject_id, ezcontentclass_attribute.placement ASC";

            $attributeArray =& $db->arrayQuery( $query );

            $tmpAttributeObjectList = array();
            $returnAttributeArray = array();
            foreach ( $attributeArray as $attribute )
            {
                unset( $attr );
                $attr = new eZContentObjectAttribute( $attribute );
                $attr->setContentClassAttributeIdentifier( $attribute['identifier'] );

                $tmpAttributeObjectList[$attr->attribute( 'contentobject_id' )][] = $attr;
            }

            $keys = array_keys( $nodeList );
            foreach ( $keys as $key )
            {
                unset( $node );
                $node = $nodeList[$key];

                unset( $object );
                $object = $node->attribute( 'object' );
                $attributes =& $tmpAttributeObjectList[$object->attribute( 'id' )];
                $object->setContentObjectAttributes( $attributes, $node->attribute( 'contentobject_version' ), eZContentObject::defaultLanguage() );
                $node->setContentObject( $object );

                $nodeList[$key] =& $node;
            }
        }
    }

    /*!
	  Returns the parent objects.
    */
    function &parents( )
    {
        $objectID = $this->ID;

        $parents = array();

        $parentID = $this->ParentID;

        $parent =& eZContentObject::fetch( $parentID );

        if ( $parentID > 0 )
            while ( ( $parentID > 0 ) )
            {
                $parents = array_merge( array( $parent ), $parents );
                $parentID = $parent->attribute( "parent_id" );
                $parent =& eZContentObject::fetch( $parentID );
            }
        return $parents;
    }

    function &fetchTree( $objectID=0, $level=0 )
    {
        $objectList =& eZContentObject::children( $objectID, true );

        $tree = array( );
        if ( $level == 0 )
            $tree[] = array( "Level" => $level, "Object" => eZContentObject::fetch( $objectID ) );

        $level++;
        foreach ( $objectList as $childObject )
        {
            $tree[] = array( "Level" => $level, "Object" => $childObject );

            $tree = array_merge( $tree, eZContentObject::fetchTree( $childObject->attribute( "id" ), $level ) );
        }
        return $tree;
    }

    /*!
	 Returns the next available version number for this object.
    */
    function nextVersion()
    {
        $db =& eZDB::instance();
        $versions =& $db->arrayQuery( "SELECT ( MAX( version ) + 1 ) AS next_id FROM ezcontentobject_version
				       WHERE contentobject_id='$this->ID'" );
        return $versions[0]["next_id"];

    }

    /*!
	 Returns number of exist versions.
    */
    function getVersionCount()
    {
        $db =& eZDB::instance();
        $versionCount =& $db->arrayQuery( "SELECT ( COUNT( version ) ) AS version_count FROM ezcontentobject_version
				       WHERE contentobject_id='$this->ID'" );
        return $versionCount[0]["version_count"];

    }
    function setCurrentLanguage( $lang )
    {
        $this->CurrentLanguage = $lang;
    }

    /*!
     Adds a link to the given content object id.
    */
    function addContentObjectRelation( $objectID, $version )
    {
        $db =& eZDB::instance();
        $db->query( "INSERT INTO ezcontentobject_link ( from_contentobject_id, from_contentobject_version, to_contentobject_id )
		     VALUES ( '$this->ID', '$version', '$objectID' )" );
    }

    /*!
     Removes a link to the given content object id.
    */
    function removeContentObjectRelation( $objectID, $version = null )
    {
        $db =& eZDB::instance();
        $db->query( "DELETE FROM ezcontentobject_link WHERE from_contentobject_id='$this->ID' AND  from_contentobject_version='$version' AND to_contentobject_id='$objectID'" );
    }

    /*!
     \return the number of related objects
    */
    function &relatedContentObjectCount( $version = false, $objectID = false )
    {
        eZDebugSetting::writeDebug( 'kernel-content-object-related-objects', $objectID, "relatedContentObjectArray::objectID" );
        if ( $version == false )
            $version = $this->CurrentVersion;
        if( !$objectID )
            $objectID = $this->ID;
        $db =& eZDB::instance();
        $relatedObjectArray =& $db->arrayQuery( "SELECT
					       count( ezcontentobject_link.from_contentobject_id ) as count
					     FROM
					       ezcontentobject_link
					     WHERE
					       ezcontentobject_link.from_contentobject_id='$objectID' AND
					       ezcontentobject_link.from_contentobject_version='$version'" );

        return $relatedObjectArray[0]['count'];
    }

    /*!
     Returns the related objects.
    */
    function &relatedContentObjectArray( $version = false, $objectID = false )
    {
        eZDebugSetting::writeDebug( 'kernel-content-object-related-objects', $objectID, "objectID" );
        if ( $version == false )
            $version = $this->CurrentVersion;
        if( ! $objectID )
        {
            $objectID = $this->ID;
        }
        $db =& eZDB::instance();

        $language = eZContentObject::defaultLanguage();

        $useVersionName = true;
        if ( $useVersionName )
        {
            $versionNameTables = ', ezcontentobject_name ';
            $versionNameTargets = ', ezcontentobject_name.name as name,  ezcontentobject_name.real_translation ';

            $ini =& eZINI::instance();
            if ( $language == false )
            {
                $language = $ini->variable( 'RegionalSettings', 'ContentObjectLocale' );
            }

            $versionNameJoins = " and  ezcontentobject.id = ezcontentobject_name.contentobject_id and
                                  ezcontentobject.current_version = ezcontentobject_name.content_version and
                                  ezcontentobject_name.content_translation = '$language' ";
        }

        $relatedObjects =& $db->arrayQuery( "SELECT
					       ezcontentobject.* $versionNameTargets
					     FROM
					       ezcontentobject,
                           ezcontentobject_link
                           $versionNameTables
					     WHERE
					       ezcontentobject.id=ezcontentobject_link.to_contentobject_id AND
					       ezcontentobject_link.from_contentobject_id='$objectID' AND
					       ezcontentobject_link.from_contentobject_version='$version'
                           $versionNameJoins;;" );

        $return = array();
        foreach ( $relatedObjects as $object )
        {
            $obj = new eZContentObject( $object );
            $obj->CurrentLanguage = $object['real_translation'];

            $return[] = $obj;
        }
        return $return;
    }

    /*!
     Returns objects to which this object is related
    */
    function &reverseRelatedObjectList( $version = false, $objectID = false )
    {
        if ( $version == false )
            $version = $this->CurrentVersion;
        if( ! $objectID )
        {
            $objectID = $this->ID;
        }
        $db =& eZDB::instance();
        $relatedObjects =& $db->arrayQuery( "SELECT distinct
					       ezcontentobject.*
					     FROM
					       ezcontentobject, ezcontentobject_link
					     WHERE
					       ezcontentobject.id=ezcontentobject_link.from_contentobject_id AND
					       ezcontentobject_link.to_contentobject_id='$objectID'" );

        $return = array();
        foreach ( $relatedObjects as $object )
        {
            $return[] = new eZContentObject( $object );
        }
        return $return;
    }

    /*!
     Returns the related objects.
    */
    function &contentObjectListRelatingThis( $version = false, $objectID = false )
    {
        eZDebugSetting::writeDebug( 'kernel-content-object-related-objects', $objectID, "objectID" );
        if ( $version == false )
            $version = $this->CurrentVersion;
        if( ! $objectID )
        {
            $objectID = $this->ID;
        }
        $db =& eZDB::instance();
        $relatedObjects =& $db->arrayQuery( "SELECT
					       ezcontentobject.*
					     FROM
					       ezcontentobject, ezcontentobject_link
					     WHERE
					       ezcontentobject.id=ezcontentobject_link.from_contentobject_id AND
					       ezcontentobject_link.to_contentobject_id='$objectID'" );

        $return = array();
        foreach ( $relatedObjects as $object )
        {
            $return[] = new eZContentObject( $object );
        }
        return $return;
    }

    /*!
     \return the parnet nodes for the current object.
    */
    function &parentNodes( $version = false, $asObject = true )
    {
        $retNodes = array();
        if ( $version )
        {
            if( is_numeric( $version ) )
            {
                $nodeAssignmentList =& eZNodeAssignment::fetchForObject( $this->attribute( 'id' ), $version );
            }
            else
            {
                $nodeAssignmentList =& eZNodeAssignment::fetchForObject( $this->attribute( 'id' ), $this->attribute( 'current_version' ) );
            }
            foreach ( array_keys( $nodeAssignmentList ) as $key )
            {
                $nodeAssignment =& $nodeAssignmentList[$key];
                if ( $asObject )
                {
                    $retNodes[] =& $nodeAssignment->attribute( 'parent_node_obj' );
                }
                else
                {
                    $retNodes[] =& $nodeAssignment->attribute( 'parent_node' );
                }
            }
            return $retNodes;
        }
        /*
        $nodes = $this->attribute( 'assigned_nodes' );
        //  $retNodes = array();
        if ( $asObject )
        {
            foreach ( $nodes as $node )
            {
                if ( $node->attribute( 'parent_node_id' ) != 1 )
                {
                    $retNodes[] =& eZContentObjectTreeNode::fetch( $node->attribute( 'parent_node_id' ) );
                }
            }
        }
        else
        {
            foreach ( $nodes as $node )
            {
                $retNodes[] = $node->attribute( 'parent_node_id' );
            }
        }
//        var_dump($retNodes);
        return $retNodes;
        */
    }

    /*!
     Returns the node assignments for the current object.
    */
    function &assignedNodes( $asObject = true)
    {
        $contentobjectID = $this->attribute( 'id' );
        $query = "SELECT ezcontentobject.*,
			 ezcontentobject_tree.*,
			 ezcontentclass.name as class_name
		  FROM   ezcontentobject_tree,
			 ezcontentobject,
			 ezcontentclass
		  WHERE  contentobject_id=$contentobjectID AND
			 ezcontentobject_tree.contentobject_id=ezcontentobject.id  AND
			 ezcontentclass.version=0 AND
			 ezcontentclass.id = ezcontentobject.contentclass_id
		  ORDER BY path_string";
        $db =& eZDB::instance();
        $nodesListArray =& $db->arrayQuery( $query );
        if ( $asObject == true )
        {
            $nodes =& eZContentObjectTreeNode::makeObjectsArray( $nodesListArray );
            return $nodes;
        }
        else
            return $nodesListArray;
    }

    function mainNodeID()
    {
        return eZContentObjectTreeNode::findMainNode( $this->attribute( 'id' ) );
    }

    function mainNode()
    {
        return eZContentObjectTreeNode::findMainNode( $this->attribute( 'id' ), true );
    }

    /*!
     Sets the permissions for this object.
    */
    function setPermissions( $permissionArray )
    {
        $this->Permissions =& $permissionArray;
    }

    /*!
     Returns the permission for the current object.
    */
    function permissions( )
    {
        return $this->Permissions;
    }

    function checkAccess( $functionName, $classID = false, $parentClassID = false )
    {
        $user =& eZUser::currentUser();
        $userID = $user->attribute( 'contentobject_id' );
        $accessResult =  $user->hasAccessTo( 'content' , $functionName );
        $accessWord = $accessResult['accessWord'];
        if ( ! $classID )
        {
            $classID = $this->attribute( 'contentclass_id' );
        }
        if ( $accessWord == 'yes' )
        {
            return 1;
        }
        elseif ( $accessWord == 'no' )
        {
            return 0;
        }
        else
        {
            $policies  =& $accessResult['policies'];
            foreach ( array_keys( $policies ) as $key  )
            {
                $policy =& $policies[$key];
                $limitationList[] =& $policy->attribute( 'limitations' );
            }
            if ( count( $limitationList ) > 0 )
            {
                $access = 'denied';
                foreach ( array_keys( $limitationList ) as $key  )
                {
                    $limitationArray =& $limitationList[ $key ];
                    if ( $access == 'allowed' )
                    {
                        break;
                    }
                    foreach ( array_keys( $limitationArray ) as $key  )
//                    foreach ( $limitationArray as $limitation )
                    {
                        $limitation =& $limitationArray[$key];
//                        if ( $functionName == 'remove' )
//                        {
//                            eZDebugSetting::writeDebug( 'kernel-content-object-limitation', $limitation, 'limitation in check access' );
//                        }

                        if ( $limitation->attribute( 'identifier' ) == 'Class' )
                        {
                            if ( $functionName == 'create' )
                            {
                                $access = 'allowed';
                            }
                            elseif ( in_array( $this->attribute( 'contentclass_id' ), $limitation->attribute( 'values_as_array' )  )  )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                break;
                            }
                        }
                        elseif ( $limitation->attribute( 'identifier' ) == 'ParentClass' )
                        {

                            if (  in_array( $this->attribute( 'contentclass_id' ), $limitation->attribute( 'values_as_array' )  ) )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                break;
                            }
                        }
                        elseif ( $limitation->attribute( 'identifier' ) == 'Section' )
                        {
                            if (  in_array( $this->attribute( 'section_id' ), $limitation->attribute( 'values_as_array' )  ) )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                break;
                            }
                        }
                        elseif ( $limitation->attribute( 'identifier' ) == 'Owner' )
                        {
                            if ( $this->attribute( 'owner_id' ) == $userID )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                break;
                            }
                        }
                        elseif ( $limitation->attribute( 'identifier' ) == 'Node' )
                        {
                            $mainNodeID = $this->attribute( 'main_node_id' );
                            foreach (  $limitation->attribute( 'values_as_array' ) as $nodeID )
                            {
                                $node = eZContentObjectTreeNode::fetch( $nodeID );
                                $limitationNodeID = $node->attribute( 'main_node_id' );
                                if ( $mainNodeID == $limitationNodeID )
                                {
                                    $access = 'allowed';
                                }
                                if ( $access == 'allowed' )
                                break;
                            }
                        }
                        elseif ( $limitation->attribute( 'identifier' ) == 'Subtree' )
                        {
                            $assignedNodes = $this->attribute( 'assigned_nodes' );
                            foreach (  $assignedNodes as  $assignedNode )
                            {
                                $path =  $assignedNode->attribute( 'path_string' );
                                $subtreeArray = $limitation->attribute( 'values_as_array' );
                                foreach ( $subtreeArray as $subtreeString )
                                {
                                    if (  strstr( $path, $subtreeString ) )
                                    {
                                        $access = 'allowed';
                                    }
                                }
                            }
                            if ( $access == 'allowed' )
                            {
                                // do nothing
                            }
                            else
                            {
                                $access = 'denied';
                                break;
                            }
                        }
                    }
                }
                if ( $access == 'denied' )
                {
                    return 0;
                }
                else
                {
                    return 1;
                }
            }
        }
    }

    function classListFromLimitation( $limitationList )
    {
        $canCreateClassIDListPart = array();
        $hasClassIDLimitation = false;
        foreach ( $limitationList as $limitation )
        {
            if ( $limitation->attribute( 'identifier' ) == 'Class' )
            {
                $canCreateClassIDListPart =& $limitation->attribute( 'values_as_array' );
                $hasClassIDLimitation = true;
            }
            elseif ( $limitation->attribute( 'identifier' ) == 'Section' )
            {
                if ( !in_array( $this->attribute( 'section_id' ), $limitation->attribute( 'values_as_array' )  ) )
                {
                    return array();
                }
            }
            elseif ( $limitation->attribute( 'identifier' ) == 'ParentClass' )
            {
                if ( !in_array( $this->attribute( 'contentclass_id' ), $limitation->attribute( 'values_as_array' )  ) )
                {
                    return array();
                }
            }

            elseif ( $limitation->attribute( 'name' ) == 'Assigned' )
            {
                if ( $this->attribute( 'owner_id' ) != $user->attribute( 'contentobject_id' )  )
                {
                    return array();
                }
            }
        }
        if ( $hasClassIDLimitation )
        {
            return $canCreateClassIDListPart;
        }
        return '*';
    }

    function &canCreateClassList()
    {

//        eZDebugSetting::writeDebug( 'kernel-content-object-limitation', $this, "object in canCreateClass" );
        $user =& eZUser::currentUser();
        $accessResult =  $user->hasAccessTo( 'content' , 'create' );
        $accessWord = $accessResult['accessWord'];

        if ( $accessWord == 'yes' )
        {
            $classList =& eZContentClass::fetchList( 0, false,false, null, array( 'id', 'name' ) );
//            eZDebugSetting::writeDebug( 'kernel-content-object-limitation', $classList, 'can create everithing' );
            return $classList;
        }
        elseif ( $accessWord == 'no' )
        {
//            eZDebugSetting::writeDebug( 'kernel-content-object-limitation', array(), 'can create nothing' );
            return array();
        }
        else
        {
            $policies  =& $accessResult['policies'];
            $classIDArray = array();
            foreach ( $policies as $policy )
            {
//                $classIDArrayPart = array();
                $limitationArray =& $policy->attribute( 'limitations' );
                $classIDArrayPart = $this->classListFromLimitation( $limitationArray );
                if ( $classIDArrayPart == '*' )
                {
                    $classList =& eZContentClass::fetchList( 0, false,false, null, array( 'id', 'name' ) );
//                    eZDebugSetting::writeDebug( 'kernel-content-object-limitation', $classList, 'can create everything' );
                    return $classList;
                }else
                {
                    $classIDArray = array_merge( $classIDArray, array_diff( $classIDArrayPart, $classIDArray ) );
                    unset( $classIDArrayPart );
                }
            }
        }
        if( count( $classIDArray ) == 0  )
        {
//            eZDebugSetting::writeDebug( 'kernel-content-object-limitation', array(), 'can create nothing' );
            return array();
        }
        $classList = array();
        // needs to be optimized
        $db = eZDb::instance();
        $classString = implode( ',', $classIDArray );
        $classList =& $db->arrayQuery( "select id, name from ezcontentclass where id in ( $classString  )  and version = 0" );
//        eZDebugSetting::writeDebug( 'kernel-content-object-limitation', $classList, 'can create some classes' );
        return $classList;
    }

    /*!
     Returns true if the current
    */
    function canRead( )
    {
        if ( !isset( $this->Permissions["can_read"] ) )
        {
            $this->Permissions["can_read"] = $this->checkAccess( 'read' );
        }
        $p = ( $this->Permissions["can_read"] == 1 );
        return $p;
    }

    function canCreate( )
    {
        if ( !isset( $this->Permissions["can_create"] ) )
        {
            $this->Permissions["can_create"] = $this->checkAccess( 'create' );
        }
        $p = ( $this->Permissions["can_create"] == 1 );
        return $p;
    }


    function canEdit( )
    {
        if ( !isset( $this->Permissions["can_edit"] ) )
        {
            $this->Permissions["can_edit"] = $this->checkAccess( 'edit' );
            if ( $this->Permissions["can_edit"] != 1 )
            {
                 $user =& eZUser::currentUser();
                 if ( $user->id() == $this->attribute( 'id' ) )
                 {
                     $access = $user->hasAccessTo( 'user', 'selfedit' );
                     if ( $access['accessWord'] == 'yes' )
                     {
                         $this->Permissions["can_edit"] = 1;
                     }
                 }
            }
        }
        $p = ( $this->Permissions["can_edit"] == 1 );
        return $p;
    }

    function canRemove( )
    {

        if ( !isset( $this->Permissions["can_remove"] ) )
        {
            $this->Permissions["can_remove"] = $this->checkAccess( 'remove' );
        }
        $p = ( $this->Permissions["can_remove"] == 1 );
        return $p;
    }

    function &className()
    {
        return $this->ClassName;
    }

    /*!
     Returns an array of the content actions which can be performed on
     the current object.
    */
    function &contentActionList()
    {
        $version = $this->attribute( 'current_version' );
        $language = $this->defaultLanguage();
        if ( !isset( $this->ContentObjectAttributeArray[$version][$language] ) )
        {
            $this->ContentObjectAttributeArray[$version][$language] =& $this->contentObjectAttributes();
        }

        // Fetch content actions if not already fetched
        if ( $this->ContentActionList === false )
        {

            $contentActionList = array();
            foreach ( $this->ContentObjectAttributeArray[$version][$language] as $attribute )
            {
                $contentActions =& $attribute->contentActionList();
                if ( count( $contentActions ) > 0 )
                {
                    $contentActionList =& $attribute->contentActionList();


                    foreach ( $contentActionList as $action )
                    {
                        if ( !$this->hasContentAction( $action['action'] ) )
                        {
                            $this->ContentActionList[] = $action;
                        }
                    }
                }
            }
        }
        return $this->ContentActionList;
    }

    /*!
     \return true if the content action is in the content action list
    */
    function hasContentAction( $name )
    {
        $return = false;
        foreach ( $this->ContentActionList as $action )
        {
            if ( $action['action'] == $name )
            {
                $return = true;
            }
        }
        return $return;
    }

    function defaultLanguage()
    {
        $ini =& eZINI::instance();
        return $ini->variable( 'RegionalSettings', 'ContentObjectLocale' );
//         return eZLocale::currentLocaleCode();
    }

    /*!

    */
    function setClassName( $name )
    {
        $this->ClassName = $name;
    }

    /*!
     \returns an array with locale strings, these strings represents the languages which content objects are allowed to be translated into.
     \note the setting ContentSettings/TranslationList in site.ini determines the array.
     \sa translationList
    */
    function translationStringList()
    {
        $translationList =& $GLOBALS['eZContentTranslationStringList'];
        if ( isset( $translationList ) )
            return $translationList;
        $translationList = eZContentTranslation::fetchLocaleList();
/*
        $ini =& eZINI::instance();
        $translationList = $ini->variableArray( 'ContentSettings', 'TranslationList' );
*/
        return $translationList;
    }

    /*!
     \returns an array with locale objects, these objects represents the languages the content objects are allowed to be translated into.
     \note the setting ContentSettings/TranslationList in site.ini determines the array.
     \sa translationStringList
    */
    function &translationList()
    {
        $translationList =& $GLOBALS['eZContentTranslationList'];
        if ( isset( $translationList ) )
            return $translationList;
        $translationList = array();
        $translationStringList = eZContentObject::translationStringList();
        foreach ( $translationStringList as $translationString )
        {
            $translationList[] =& eZLocale::instance( $translationString );
        }
        return $translationList;
    }

    /*!
     Returns the attributes for the content object version \a $version and content object \a $contentObjectID.
     \a $language defines the language to fetch.
     \static
     \sa attributes
    */
    function &fetchClassAttributes( $version = 0, $asObject = true )
    {
        return eZContentClassAttribute::fetchListByClassID( $this->attribute( 'contentclass_id' ), $version, $asObject );
    }

    /*!
     \return a DOM structure of the content object and it's attributes.
    */
    function &serialize( $specificVersion = false )
    {
        include_once( 'lib/ezxml/classes/ezdomdocument.php' );
        include_once( 'lib/ezxml/classes/ezdomnode.php' );
        $objectNode = new eZDOMNode();

        $objectNode->setName( 'object' );
        $objectNode->setPrefix( 'ez' );
        $objectNode->appendAttribute( eZDOMDocument::createAttributeNode( 'id', $this->ID, 'ezremote' ) );
        $objectNode->appendAttribute( eZDOMDocument::createAttributeNode( 'section_id', $this->SectionID, 'ezremote' ) );
        $objectNode->appendAttribute( eZDOMDocument::createAttributeNode( 'owner_id', $this->OwnerID, 'ezremote' ) );

        $versions = array();
        if ( $specificVersion === false )
        {
            $versions =& $this->versions();
        }
        else if ( $specificVersion === true )
        {
            $versions[] = $this->currentVersion();
        }
        else
        {
            $versions[] = $this->version( $specificVersion );
        }

        $this->fetchClassAttributes();

        $versionsNode = new eZDOMNode();
        $versionsNode->setPrefix( 'ez' );
        $versionsNode->setName( 'version-list' );
        $versionsNode->appendAttribute( eZDOMDocument::createAttributeNode( 'active_version', $this->CurrentVersion ) );
        $versionsNode->appendAttribute( eZDOMDocument::createAttributeNamespaceDefNode( "ezobject", "http://ez.no/object/" ) );
        foreach ( array_keys( $versions ) as $versionKey )
        {
            $version =& $versions[$versionKey];
            $versionNode =& $version->serialize();
//             $attributes =& $this->contentObjectAttributes( true, $version );

//             foreach ( $attributes as $attribute )
//             {
//                 $objectNode->appendChild( $attribute->serialize() );
//             }
            $versionsNode->appendChild( $versionNode );
        }
        $objectNode->appendChild( $versionsNode );
        return $objectNode;
    }

    /*!
     \return a structure with information required for caching.
    */
    function cacheInfo( $Params )
    {
        $contentCacheInfo =& $GLOBALS['eZContentCacheInfo'];
        if ( !isset( $contentCacheInfo ) )
        {
            include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
            include_once( 'kernel/classes/ezuserdiscountrule.php' );
            $user =& eZUser::currentUser();
            $languageCode = $Params['Language'];
            $language = $languageCode;
            if ( $language == '' )
                $language = eZContentObject::defaultLanguage();
            $roleList = $user->roleIDList();
            $discountList =& eZUserDiscountRule::fetchIDListByUserID( $user->attribute( 'contentobject_id' ) );
            $contentCacheInfo = array( 'language' => $language,
                                       'role_list' => $roleList,
                                       'discount_list' => $discountList );
        }
        return $contentCacheInfo;
    }

    /*!
     Sets all content cache files to be expired.
    */
    function expireAllCache()
    {
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $handler->setTimestamp( 'content-cache', mktime() );
        $handler->store();
    }

    /*!
     Sets all complex viewmode content cache files to be expired.
    */
    function expireComplexViewModeCache()
    {
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $handler->setTimestamp( 'content-complex-viewmode-cache', mktime() );
        $handler->store();
    }

    /*!
     \return if the content cache timestamp \a $timestamp is expired.
    */
    function isCacheExpired( $timestamp )
    {
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        if ( !$handler->hasTimestamp( 'content-cache' ) )
            return false;
        $expiryTime = $handler->timestamp( 'content-cache' );
        if ( $expiryTime > $timestamp )
            return true;
        return false;
    }

    /*!
     \return true if the viewmode is a complex viewmode.
    */
    function isComplexViewMode( $viewMode )
    {
        $ini =& eZINI::instance();
        $viewModes = $ini->variableArray( 'ContentSettings', 'ComplexDisplayViewModes' );
        return in_array( $viewMode, $viewModes );
    }

    /*!
     \return true if the viewmode is a complex viewmode and the viewmode timestamp is expired.
    */
    function isComplexViewModeCacheExpired( $viewMode, $timestamp )
    {
        if ( !eZContentObject::isComplexViewMode( $viewMode ) )
            return false;
        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        if ( !$handler->hasTimestamp( 'content-complex-viewmode-cache' ) )
            return false;
        $expiryTime = $handler->timestamp( 'content-complex-viewmode-cache' );
        if ( $expiryTime > $timestamp )
            return true;
        return false;
    }

    /*!
     Returns a list of all the authors for this object. The returned value is an
     array of eZ user objects.
    */
    function &authorArray()
    {
        $db =& eZDB::instance();

        $userArray = $db->arrayQuery( "SELECT DISTINCT ezuser.contentobject_id, ezuser.login, ezuser.email, ezuser.password_hash, ezuser.password_hash_type
                                       FROM ezcontentobject_version, ezuser where ezcontentobject_version.contentobject_id='$this->ID'
                                       AND ezcontentobject_version.creator_id=ezuser.contentobject_id" );

        $return = array();

        foreach ( $userArray as $userRow )
        {
            $return[] = new eZUser( $userRow );
        }
        return $return;
    }

    var $ID;
    var $Name;

    /// Stores the current language
    var $CurrentLanguage;

    /// Stores the current permissions
    var $ClassName;

    /// Contains the datamap for content object attributes
    var $DataMap = array();

    /// Contains an array of the content object actions for the current object
    var $ContentActionList = false;

    /// Contains a cached version of the content object attributes for the given version and language
    var $ContentObjectAttributes = array();
}

?>
