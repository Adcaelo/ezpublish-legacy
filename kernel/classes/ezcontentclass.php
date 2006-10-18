<?php
//
// Definition of eZContentClass class
//
// Created on: <16-Apr-2002 11:08:14 amos>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.x
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
  \class eZContentClass ezcontentclass.php
  \ingroup eZKernel
  \brief Handles eZ publish content classes

  \sa eZContentObject
*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezcontentobject.php" );
include_once( "kernel/classes/ezcontentclassattribute.php" );
include_once( "kernel/classes/ezcontentclassclassgroup.php" );
include_once( "kernel/classes/ezcontentclassnamelist.php" );
include_once( "kernel/common/i18n.php" );

define( "EZ_CLASS_VERSION_STATUS_DEFINED", 0 );
define( "EZ_CLASS_VERSION_STATUS_TEMPORARY", 1 );
define( "EZ_CLASS_VERSION_STATUS_MODIFED", 2 );

class eZContentClass extends eZPersistentObject
{
    function eZContentClass( $row )
    {
        if ( is_array( $row ) )
        {
            $this->eZPersistentObject( $row );
            $this->VersionCount = false;
            $this->InGroups = null;
            $this->AllGroups = null;
            if ( isset( $row["version_count"] ) )
                $this->VersionCount = $row["version_count"];

            $this->NameList = new eZContentClassNameList();
            if ( isset( $row['serialized_name_list'] ) )
            {
                $this->NameList->initFromSerializedList( $row['serialized_name_list'] );
            }
            else if ( isset( $row['name'] ) ) // depricated
            {
                $this->NameList->initFromString( $row['name'] );
            }
            else
            {
                $this->NameList->initDefault();
            }

        }
        $this->DataMap = false;
    }

    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "version" => array( 'name' => 'Version',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "serialized_name_list" => array( 'name' => 'SerializedNameList',
                                                                          'datatype' => 'string',
                                                                          'default' => '',
                                                                          'required' => true ),
                                         "identifier" => array( 'name' => "Identifier",
                                                                'datatype' => 'string',
                                                                'default' => '',
                                                                'required' => true ),
                                         "contentobject_name" => array( 'name' => "ContentObjectName",
                                                                        'datatype' => 'string',
                                                                        'default' => '',
                                                                        'required' => true ),
                                         "creator_id" => array( 'name' => "CreatorID",
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true,
                                                                'foreign_class' => 'eZUser',
                                                                'foreign_attribute' => 'contentobject_id',
                                                                'multiplicity' => '1..*' ),
                                         "modifier_id" => array( 'name' => "ModifierID",
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true,
                                                                 'foreign_class' => 'eZUser',
                                                                 'foreign_attribute' => 'contentobject_id',
                                                                 'multiplicity' => '1..*' ),
                                         "created" => array( 'name' => "Created",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "remote_id" => array( 'name' => "RemoteID",
                                                               'datatype' => 'string',
                                                               'default' => '',
                                                               'required' => true ),
                                         "modified" => array( 'name' => "Modified",
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "is_container" => array( 'name' => "IsContainer",
                                                                  'datatype' => 'integer',
                                                                  'default' => 0,
                                                                  'required' => true ),
                                         'always_available' => array( 'name' => "AlwaysAvailable",
                                                                      'datatype' => 'integer',
                                                                      'default' => 0,
                                                                      'required' => true ),
                                         'language_mask' => array( 'name' => "LanguageMask",
                                                                   'datatype' => 'integer',
                                                                   'default' => 0,
                                                                   'required' => true ),
                                         'initial_language_id' => array( 'name' => "InitialLanguageID",
                                                                         'datatype' => 'integer',
                                                                         'default' => 0,
                                                                         'required' => true,
                                                                         'foreign_class' => 'eZContentLanguage',
                                                                         'foreign_attribute' => 'id',
                                                                         'multiplicity' => '1..*' ),
                                         'sort_field' => array( 'name' => 'SortField',
                                                                'datatype' => 'integer',
                                                                'default' => 1,
                                                                'required' => true ),
                                         'sort_order' => array( 'name' => 'SortOrder',
                                                                'datatype' => 'integer',
                                                                'default' => 1,
                                                                'required' => true ) ),
                      "keys" => array( "id", "version" ),
                      "function_attributes" => array( "data_map" => "dataMap",
                                                      'object_count' => 'objectCount',
                                                      'version_count' => 'versionCount',
                                                      'version_status' => 'versionStatus',
                                                      'remote_id' => 'remoteID', // Note: This overrides remote_id field
                                                      'ingroup_list' => 'fetchGroupList',
                                                      'ingroup_id_list' => 'fetchGroupIDList',
                                                      'match_ingroup_id_list' => 'fetchMatchGroupIDList',
                                                      'group_list' => 'fetchAllGroups',
                                                      'creator' => 'creator',
                                                      'modifier' => 'modifier',
                                                      'can_instantiate_languages' => 'canInstantiateLanguages',
                                                      'name' => 'name',
                                                      'nameList' => 'nameList',
                                                      'languages' => 'languages',
                                                      'can_create_languages' => 'canCreateLanguages',
                                                      'top_priority_language' => 'topPriorityLanguage',
                                                      'default_language' => 'defaultLanguage' ),
                      "increment_key" => "id",
                      "class_name" => "eZContentClass",
                      "sort" => array( "id" => "asc" ),
                      "name" => "ezcontentclass" );
    }

    function clone()
    {
        $row = array(
            "id" => null,
            "version" => $this->attribute( 'version' ),
            "serialized_name_list" => $this->attribute( 'serialized_name_list' ),
            "identifier" => $this->attribute( 'identifier' ),
            "contentobject_name" => $this->attribute( 'contentobject_name' ),
            "creator_id" => $this->attribute( 'creator_id' ),
            "modifier_id" => $this->attribute( 'modifier_id' ),
            "created" => $this->attribute( 'created' ),
            "modified" => $this->attribute( 'modified' ),
            "is_container" => $this->attribute( 'is_container' ),
            "always_available" => $this->attribute( 'always_available' ),
            'language_mask' => $this->attribute( 'language_mask' ),
            'initital_language_id' => $this->attribute( 'initial_language_id' ),
            "sort_field" => $this->attribute( 'sort_field' ),
            "sort_order" => $this->attribute( 'sort_order' ) );

        $tmpClass = new eZContentClass( $row );
        return $tmpClass;
    }

    function create( $userID = false, $optionalValues = array(), $languageLocale = false )
    {
        $dateTime = time();
        if ( !$userID )
            $userID = eZUser::currentUserID();

        if ( $languageLocale == false )
        {
            $languageLocale = eZContentObject::defaultLanguage();
        }

        $languageID = eZContentLanguage::idByLocale( $languageLocale );

        $contentClassDefinition = eZContentClass::definition();
        $row = array(
            "id" => null,
            "version" => 1,
            "serialized_name_list" => "",
            "identifier" => "",
            "contentobject_name" => "",
            "creator_id" => $userID,
            "modifier_id" => $userID,
            "created" => $dateTime,
            'remote_id' => md5( (string)mt_rand() . (string)mktime() ),
            "modified" => $dateTime,
            "is_container" => $contentClassDefinition[ 'fields' ][ 'is_container' ][ 'default' ],
            "always_available" => $contentClassDefinition[ 'fields' ][ 'always_available' ][ 'default' ],
            'language_mask' => $languageID,
            'initial_language_id' => $languageID,
            "sort_field" => $contentClassDefinition[ 'fields' ][ 'sort_field' ][ 'default' ],
            "sort_order" => $contentClassDefinition[ 'fields' ][ 'sort_order' ][ 'default' ] );

        $row = array_merge( $row, $optionalValues );

        $contentClass = new eZContentClass( $row );

        return $contentClass;
    }

    function instantiateIn( $lang, $userID = false, $sectionID = 0, $versionNumber = false, $versionStatus = false )
    {
        return eZContentClass::instantiate( $userID, $sectionID, $versionNumber, $lang, $versionStatus );
    }

    /*!
     Creates a new content object instance and stores it.

     \param user ID (optional), current user if not set
     \param section ID (optional), 0 if not set
     \param version number, create initial version if not set
     \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     the calls within a db transaction; thus within db->begin and db->commit.
    */
    function instantiate( $userID = false, $sectionID = 0, $versionNumber = false, $languageCode = false, $versionStatus = false )
    {
        $attributes = $this->fetchAttributes();

        $user =& eZUser::currentUser();
        if ( $userID === false )
        {
            $userID =& $user->attribute( 'contentobject_id' );
        }

        if ( $languageCode == false )
        {
            $languageCode = eZContentObject::defaultLanguage();
        }

        $object = eZContentObject::create( ezi18n( "kernel/contentclass", "New %1", null, array( $this->name( $languageCode ) ) ),
                                           $this->attribute( "id" ),
                                           $userID,
                                           $sectionID,
                                           1,
                                           $languageCode );

        if ( $this->attribute( 'always_available' ) )
        {
            $object->setAttribute( 'language_mask', (int)$object->attribute( 'language_mask') | 1 );
        }

        $db =& eZDB::instance();
        $db->begin();

        $object->store();
        $object->setName( ezi18n( "kernel/contentclass", "New %1", null, array( $this->name( $languageCode ) ) ), false, $languageCode );

        if ( !$versionNumber )
        {
            $version = $object->createInitialVersion( $userID, $languageCode );
        }
        else
        {
            $version = eZContentObjectVersion::create( $object->attribute( "id" ), $userID, $versionNumber, $languageCode );
        }
        if ( $versionStatus !== false )
        {
            $version->setAttribute( 'status', $versionStatus );
        }

        $version->store();

        foreach ( array_keys( $attributes ) as $attributeKey )
        {
            $attribute =& $attributes[$attributeKey];
            $attribute->instantiate( $object->attribute( 'id' ), $languageCode );
        }

        if ( $user->isAnonymous() )
        {
            include_once( 'kernel/classes/ezpreferences.php' );
            $createdObjectIDList = eZPreferences::value( 'ObjectCreationIDList' );
            if ( !$createdObjectIDList )
            {
                $createdObjectIDList = array( $object->attribute( 'id' ) );
            }
            else
            {
                $createdObjectIDList = unserialize( $createdObjectIDList );
                $createdObjectIDList[] = $object->attribute( 'id' );
            }
            eZPreferences::setValue( 'ObjectCreationIDList', serialize( $createdObjectIDList ) );
        }

        $db->commit();
        return $object;
    }

    function canInstantiateClasses()
    {
        $ini =& eZINI::instance();
        $enableCaching = $ini->variable( 'RoleSettings', 'EnableCaching' );

        if ( $enableCaching == 'true' )
        {
            $http =& eZHTTPTool::instance();

            include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
            $handler =& eZExpiryHandler::instance();
            $expiredTimeStamp = 0;
            if ( $handler->hasTimestamp( 'user-class-cache' ) )
                $expiredTimeStamp = $handler->timestamp( 'user-class-cache' );

            $classesCachedForUser = $http->sessionVariable( 'CanInstantiateClassesCachedForUser' );
            $classesCachedTimestamp = $http->sessionVariable( 'ClassesCachedTimestamp' );
            $user =& eZUser::currentUser();
            $userID = $user->id();

            if ( ( $classesCachedTimestamp >= $expiredTimeStamp ) && $classesCachedForUser == $userID )
            {
                if ( $http->hasSessionVariable( 'CanInstantiateClasses' ) )
                {
                    return $http->sessionVariable( 'CanInstantiateClasses' );
                }
            }
            else
            {
                // store cache
                $http->setSessionVariable( 'CanInstantiateClassesCachedForUser', $userID );
            }
        }
        $user =& eZUser::currentUser();
        $accessResult = $user->hasAccessTo( 'content' , 'create' );
        $accessWord = $accessResult['accessWord'];
        $canInstantiateClasses = 1;
        if ( $accessWord == 'no' )
        {
            $canInstantiateClasses = 0;
        }

        if ( $enableCaching == 'true' )
        {
            $http->setSessionVariable( 'CanInstantiateClasses', $canInstantiateClasses );
        }
        return $canInstantiateClasses;
    }

    // code-template::create-block: can-instantiate-class-list, group-filter, role-caching, class-policy-list, name-instantiate, object-creation
    // code-template::auto-generated:START can-instantiate-class-list
    // This code is automatically generated from templates/classcreatelist.ctpl
    // DO NOT EDIT THIS CODE DIRECTLY, CHANGE THE TEMPLATE FILE INSTEAD

    /*!
     \static
     Finds all classes that the current user can create objects from and returns.
     It is also possible to filter the list event more with \a $includeFilter and \a $groupList.

     \param $asObject If \c true then it return eZContentClass objects, if not it will
                      be an associative array with \c name and \c id keys.
     \param $includeFilter If \c true then it will include only from class groups defined in
                           \a $groupList, if not it will exclude those groups.
     \param $groupList An array with class group IDs that should be used in filtering, use
                       \c false if you do not wish to filter at all.
     \param $id A unique name for the current fetch, this must be supplied when filtering is
                used if you want caching to work.
    */
    function &canInstantiateClassList( $asObject = false, $includeFilter = true, $groupList = false, $fetchID = false )
    {
        $ini =& eZINI::instance();
        $groupArray = array();

        $enableCaching = ( $ini->variable( 'RoleSettings', 'EnableCaching' ) == 'true' );
        if ( is_array( $groupList ) )
        {
            if ( $fetchID == false )
                $enableCaching = false;
        }

        if ( $enableCaching )
        {
            $http =& eZHTTPTool::instance();
            include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
            $handler =& eZExpiryHandler::instance();
            $expiredTimeStamp = 0;
            if ( $handler->hasTimestamp( 'user-class-cache' ) )
                $expiredTimeStamp = $handler->timestamp( 'user-class-cache' );

            $classesCachedForUser = $http->sessionVariable( 'ClassesCachedForUser' );
            $classesCachedTimestamp = $http->sessionVariable( 'ClassesCachedTimestamp' );

            $cacheVar = 'CanInstantiateClassList';
            if ( is_array( $groupList ) and $fetchID !== false )
            {
                $cacheVar = 'CanInstantiateClassListGroup';
            }

            $user =& eZUser::currentUser();
            $userID = $user->id();
            if ( ( $classesCachedTimestamp >= $expiredTimeStamp ) && $classesCachedForUser == $userID )
            {
                if ( $http->hasSessionVariable( $cacheVar ) )
                {
                    if ( $fetchID !== false )
                    {
                        // Check if the group contains our ID, if not we need to fetch from DB
                        $groupArray = $http->sessionVariable( $cacheVar );
                        if ( isset( $groupArray[$fetchID] ) )
                        {
                            return $groupArray[$fetchID];
                        }
                    }
                    else
                    {
                        return $http->sessionVariable( $cacheVar );
                    }
                }
            }
            else
            {
                $http->setSessionVariable( 'ClassesCachedForUser' , $userID );
                $http->setSessionVariable( 'ClassesCachedTimestamp', mktime() );
            }
        }

        $languageCodeList = eZContentLanguage::fetchLocaleList();
        $allowedLanguages = array( '*' => array() );

        $user =& eZUser::currentUser();
        $accessResult = $user->hasAccessTo( 'content' , 'create' );
        $accessWord = $accessResult['accessWord'];

        $classIDArray = array();
        $classList = array();
        $fetchAll = false;
        if ( $accessWord == 'yes' )
        {
            $fetchAll = true;
            $allowedLanguages['*'] = $languageCodeList;
        }
        else if ( $accessWord == 'no' )
        {
            // Cannnot create any objects, return empty list.
            return $classList;
        }
        else
        {
            $policies = $accessResult['policies'];
            foreach ( $policies as $policyKey => $policy )
            {
                $classIDArrayPart = '*';
                if ( isset( $policy['Class'] ) )
                {
                    $classIDArrayPart = $policy['Class'];
                }
                $languageCodeArrayPart = $languageCodeList;
                if ( isset( $policy['Language'] ) )
                {
                    $languageCodeArrayPart = array_intersect( $policy['Language'], $languageCodeList );
                }

                if ( $classIDArrayPart == '*' )
                {
                    $fetchAll = true;
                    $allowedLanguages['*'] = array_unique( array_merge( $allowedLanguages['*'], $languageCodeArrayPart ) );
                }
                else
                {
                    foreach( $classIDArrayPart as $class )
                    {
                        if ( isset( $allowedLanguages[$class] ) )
                        {
                            $allowedLanguages[$class] = array_unique( array_merge( $allowedLanguages[$class], $languageCodeArrayPart ) );
                        }
                        else
                        {
                            $allowedLanguages[$class] = $languageCodeArrayPart;
                        }
                    }
                    $classIDArray = array_merge( $classIDArray, array_diff( $classIDArrayPart, $classIDArray ) );
                }
            }
        }

        $filterTableSQL = '';
        $filterSQL = '';
        // Create extra SQL statements for the class group filters.
        if ( is_array( $groupList ) )
        {
            $filterTableSQL = ', ezcontentclass_classgroup ccg';
            $filterSQL = ( " AND\n" .
                           "      cc.id = ccg.contentclass_id AND\n" .
                           "      ccg.group_id " );
            $groupText = implode( ', ', $groupList );
            if ( $includeFilter )
                $filterSQL .= "IN ( $groupText )";
            else
                $filterSQL .= "NOT IN ( $groupText )";
        }

        $classNameFilter = eZContentClassName::sqlFilter( 'cc' );

        if ( $fetchAll )
        {
            $classList = array();
            $db =& eZDb::instance();
            $classString = implode( ',', $classIDArray );
            // If $asObject is true we fetch all fields in class
            $fields = $asObject ? "cc.*" : "cc.id, $classNameFilter[nameField]";
            $rows = $db->arrayQuery( "SELECT DISTINCT $fields\n" .
                                     "FROM ezcontentclass cc$filterTableSQL, $classNameFilter[from]\n" .
                                     "WHERE cc.version = " . EZ_CLASS_VERSION_STATUS_DEFINED . "$filterSQL\n" .
                                     "ORDER BY $classNameFilter[nameField] ASC" );
            $classList = eZPersistentObject::handleRows( $rows, 'ezcontentclass', $asObject );
        }
        else
        {
            // If the constrained class list is empty we are not allowed to create any class
            if ( count( $classIDArray ) == 0 )
            {
                $classList = array();
                return $classList;
            }

            $classList = array();
            $db =& eZDb::instance();
            $classString = implode( ',', $classIDArray );
            // If $asObject is true we fetch all fields in class
            $fields = $asObject ? "cc.*" : "cc.id, $classNameFilter[nameField]";
            $rows = $db->arrayQuery( "SELECT DISTINCT $fields\n" .
                                     "FROM ezcontentclass cc$filterTableSQL, $classNameFilter[from]\n" .
                                     "WHERE cc.id IN ( $classString  ) AND\n" .
                                     "      cc.version = " . EZ_CLASS_VERSION_STATUS_DEFINED . "$filterSQL\n",
                                     "ORDER BY $classNameFilter[nameField] ASC" );
            $classList = eZPersistentObject::handleRows( $rows, 'ezcontentclass', $asObject );
        }

        if ( $asObject )
        {
            foreach ( $classList as $key => $class )
            {
                $id = $class->attribute( 'id' );
                if ( isset( $allowedLanguages[$id] ) )
                {
                    $languageCodes = array_unique( array_merge( $allowedLanguages['*'], $allowedLanguages[$id] ) );
                }
                else
                {
                    $languageCodes = $allowedLanguages['*'];
                }
                $classList[$key]->setCanInstantiateLanguages( $languageCodes );
            }
        }

        eZDebugSetting::writeDebug( 'kernel-content-class', $classList, "class list fetched from db" );
        if ( $enableCaching )
        {
            if ( $fetchID !== false )
            {
                $groupArray[$fetchID] = $classList;
                $http->setSessionVariable( $cacheVar, $groupArray );
            }
            else
            {
                $http->setSessionVariable( $cacheVar, $classList );
            }
        }

        return $classList;
    }

    // This code is automatically generated from templates/classcreatelist.ctpl
    // code-template::auto-generated:END can-instantiate-class-list

    /*!
     \return The creator of the class as an eZUser object by using the $CreatorID as user ID.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &creator()
    {
        if ( isset( $this->CreatorID ) and $this->CreatorID )
        {
            include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
            $user = eZUser::fetch( $this->CreatorID );
        }
        else
            $user = null;
        return $user;
    }

    /*!
     \return The modifier of the class as an eZUser object by using the $ModifierID as user ID.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
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

    /*!
     Find all groups the current class is placed in and returns a list of group objects.
     \return An array with eZContentClassGroup objects.
     \sa fetchGroupIDList()
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &fetchGroupList()
    {
        $this->InGroups = eZContentClassClassGroup::fetchGroupList( $this->attribute( "id" ),
                                                                     $this->attribute( "version" ),
                                                                     true );
        return $this->InGroups;
    }

    /*!
     Find all groups the current class is placed in and returns a list of group IDs.
     \return An array with integers (ids).
     \sa fetchGroupList()
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &fetchGroupIDList()
    {
        $list = eZContentClassClassGroup::fetchGroupList( $this->attribute( "id" ),
                                                          $this->attribute( "version" ),
                                                          false );
        $this->InGroupIDs = array();
        foreach ( $list as $item )
        {
            $this->InGroupIDs[] = $item['group_id'];
        }
        return $this->InGroupIDs;
    }

    /*!
     Returns the result from fetchGroupIDList() if class group overrides is
     enabled in content.ini.
     \return An array with eZContentClassGroup objects or \c false if disabled.
     \note \c EnableClassGroupOverride in group \c ContentOverrideSettings from INI file content.ini
           controls this behaviour.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &fetchMatchGroupIDList()
    {
        include_once( 'lib/ezutils/classes/ezini.php' );
        $contentINI =& eZINI::instance( 'content.ini' );
        if( $contentINI->variable( 'ContentOverrideSettings', 'EnableClassGroupOverride' ) == 'true' )
        {
            $retValue =& $this->attribute( 'ingroup_id_list' );
        }
        else
        {
            $retValue = false;
        }
        return $retValue;
    }

    /*!
     Finds all Classes in the system and returns them.
     \return An array with eZContentClass objects.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &fetchAllClasses( $asObject = true, $includeFilter = true, $groupList = false )
    {
        if ( is_array( $groupList ) )
        {
            $filterTableSQL = ', ezcontentclass_classgroup ccg';
            $filterSQL = ( " AND\n" .
                           "      cc.id = ccg.contentclass_id AND\n" .
                           "      ccg.group_id " );
            $groupText = implode( ', ', $groupList );
            if ( $includeFilter )
                $filterSQL .= "IN ( $groupText )";
            else
                $filterSQL .= "NOT IN ( $groupText )";
        }

        $classNameFilter = eZContentClassName::sqlFilter( 'cc' );

        $classList = array();
        $db =& eZDb::instance();
        // If $asObject is true we fetch all fields in class
        $fields = $asObject ? "cc.*" : "cc.id, $classNameFilter[nameField]";
        $rows = $db->arrayQuery( "SELECT DISTINCT $fields\n" .
                                 "FROM ezcontentclass cc$filterTableSQL, $classNameFilter[from]\n" .
                                 "WHERE cc.version = " . EZ_CLASS_VERSION_STATUS_DEFINED . "$filterSQL AND $classNameFilter[where]\n" .
                                 "ORDER BY $classNameFilter[nameField] ASC" );

        $classList = eZPersistentObject::handleRows( $rows, 'ezcontentclass', $asObject );
        return $classList;
    }

    /*!
     Finds all Class groups in the system and returns them.
     \return An array with eZContentClassGroup objects.
     \sa fetchGroupList(), fetchGroupIDList()
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &fetchAllGroups()
    {
        $this->AllGroups = eZContentClassGroup::fetchList();
        return $this->AllGroups;
    }

    /*!
     \return true if the class is part of the group \a $groupID
    */
    function inGroup( $groupID )
    {
        return eZContentClassClassGroup::classInGroup( $this->attribute( 'id' ),
                                                       $this->attribute( 'version' ),
                                                       $groupID );
    }

    /*!
     \static
     Will remove all temporary classes from the database.
    */
    function removeTemporary()
    {
        $version = EZ_CLASS_VERSION_STATUS_TEMPORARY;
        $temporaryClasses = eZContentClass::fetchList( $version, true );
        $db =& eZDb::instance();
        $db->begin();
        foreach ( $temporaryClasses as $class )
        {
            $class->remove( true, $version );
        }
        eZPersistentObject::removeObject( eZContentClassAttribute::definition(),
                                          array( 'version' => $version ) );

        $db->commit();
    }

    /*!
     Get remote id of content node
    */
    function &remoteID()
    {
        $remoteID = eZPersistentObject::attribute( 'remote_id', true );
        if ( !$remoteID &&
             $this->Version == EZ_CLASS_VERSION_STATUS_DEFINED )
        {
            $this->setAttribute( 'remote_id', md5( (string)mt_rand() . (string)mktime() ) );
            $this->sync( array( 'remote_id' ) );
            $remoteID = eZPersistentObject::attribute( 'remote_id', true );
        }

        return $remoteID;
    }

    /*!
     \note If you want to remove a class with all data associated with it (objects/classMembers)
           you should use eZContentClassOperations::remove()
    */
    function remove( $remove_childs = false, $version = EZ_CLASS_VERSION_STATUS_DEFINED )
    {
        // If we are not allowed to remove just return false
        if ( $this->Version != EZ_CLASS_VERSION_STATUS_TEMPORARY && !$this->isRemovable() )
            return false;

        if ( is_array( $remove_childs ) or $remove_childs )
        {
            if ( is_array( $remove_childs ) )
            {
                $db =& eZDb::instance();
                $db->begin();

                $attributes =& $remove_childs;
                for ( $i = 0; $i < count( $attributes ); ++$i )
                {
                    $attribute =& $attributes[$i];
                    $attribute->remove();
                }
                $db->commit();
            }
            else
            {
                if ( $version == EZ_CLASS_VERSION_STATUS_DEFINED )
                {
                    $contentClassID = $this->ID;
                    $version = $this->Version;
                    $classAttributes =& $this->fetchAttributes( );

                    foreach ( $classAttributes as $classAttribute )
                    {
                        $dataType = $classAttribute->dataType();
                        $dataType->deleteStoredClassAttribute( $classAttribute, $version );
                    }
                    eZPersistentObject::removeObject( eZContentClassAttribute::definition(),
                                                      array( "contentclass_id" => $contentClassID,
                                                             "version" => $version ) );
                }
                else
                {
                    $contentClassID = $this->ID;
                    $version = $this->Version;
                    $classAttributes =& $this->fetchAttributes( );

                    foreach ( $classAttributes as $classAttribute )
                    {
                        $dataType = $classAttribute->dataType();
                        $dataType->deleteStoredClassAttribute( $classAttribute, $version );
                    }
                    eZPersistentObject::removeObject( eZContentClassAttribute::definition(),
                                                      array( "contentclass_id" => $contentClassID,
                                                             "version" => $version ) );
                }
            }
        }

        $this->NameList->remove( $this );

        eZPersistentObject::remove();
    }

    /*!
     Checks if the class can be removed and returns \c true if it can, \c false otherwise.
     \sa removableInformation()
    */
    function isRemovable()
    {
        $info = $this->removableInformation( false );
        return count( $info['list'] ) == 0;
    }

    /*!
     Returns information on why the class cannot be removed,
     it does the same checks as in isRemovable() but generates
     some text in the return array.
     \return An array which contains:
             - text - Plain text description why this cannot be removed
             - list - An array with reasons why this failed, each entry contains:
                      - text - Plain text description of the reason.
                      - list - A sublist of reason (e.g from an attribute), is optional.
     \param $includeAll Controls whether the returned information will contain all
                        sources for not being to remove or just the first that it finds.
    */
    function removableInformation( $includeAll = true )
    {
        $result  = array( 'text' => ezi18n( 'kernel/contentclass', "Cannot remove class '%class_name':",
                                         null, array( '%class_name' => $this->attribute( 'name' ) ) ),
                       'list' => array() );
        $reasons =& $result['list'];
        $db      =& eZDB::instance();

        // Check top-level nodes
        $rows = $db->arrayQuery( "SELECT ezcot.node_id
FROM ezcontentobject_tree ezcot, ezcontentobject ezco
WHERE ezcot.depth = 1 AND
      ezco.contentclass_id = $this->ID AND
      ezco.id=ezcot.contentobject_id" );
        if ( count( $rows ) > 0 )
        {
            $reasons[] = array( 'text' => ezi18n( 'kernel/contentclass', 'The class is used by a top-level node and cannot be removed.
You will need to change the class of the node by using the swap functionality.' ) );
            if ( !$includeAll )
                return $result;
        }

        // Check class attributes
        $attributes =& $this->fetchAttributes();
        foreach ( $attributes as $key => $attribute )
        {
            $dataType = $attribute->dataType();
            if ( !$dataType->isClassAttributeRemovable( $attribute ) )
            {
                $info = $dataType->classAttributeRemovableInformation( $attribute, $includeAll );
                $reasons[] = $info;
                if ( !$includeAll )
                    return $result;
            }
        }

        return $result;
    }

    function removeAttributes( $attributes = false, $id = false, $version = false )
    {
        if ( is_array( $attributes ) )
        {
            $db =& eZDB::instance();
            $db->begin();
            for ( $i = 0; $i < count( $attributes ); ++$i )
            {
                $attribute =& $attributes[$i];
                $attribute->remove();
                $contentObject->purge();
            }
            $db->commit();
        }
        else
        {
            if ( $version === false )
                $version = $this->Version;
            if ( $id === false )
                $id = $this->ID;
            eZPersistentObject::removeObject( eZContentClassAttribute::definition(),
                                              array( "contentclass_id" => $id,
                                                     "version" => $version ) );
        }
    }

    function compareAttributes( $attr1, $attr2 )
    {
        return  ( $attr1->attribute( "placement" ) > $attr2->attribute( "placement" )  ) ? 1 : -1;
    }

    function adjustAttributePlacements( &$attributes )
    {
        if ( !is_array( $attributes ) )
            return;
        usort( $attributes, array( $this, "compareAttributes" ) );
        for ( $i = 0; $i < count( $attributes ); ++$i )
        {
            $attribute =& $attributes[$i];
            $attribute->setAttribute( "placement", $i + 1 );
        }
    }

    /*!
     \reimp
    */
    function store( $store_childs = false, $fieldFilters = null )
    {
        $db =& eZDB::instance();
        $db->begin();

        if ( is_array( $store_childs ) or $store_childs )
        {
            if ( is_array( $store_childs ) )
                $attributes =& $store_childs;
            else
                $attributes =& $this->fetchAttributes();

           for ( $i = 0; $i < count( $attributes ); ++$i )
            {
                $attribute =& $attributes[$i];
                if ( is_object ( $attribute ) )
                    $attribute->store();
            }
        }

        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $handler->setTimestamp( 'user-class-cache', mktime() );
        $handler->store();

        $this->setAttribute( 'serialized_name_list', $this->NameList->serializeNames() );

        eZPersistentObject::store( $fieldFilters );

        $this->NameList->store( $this );

        $db->commit();
    }

    /*!
     \reimp
    */
    function sync( $fieldFilters = null )
    {
        if ( $this->hasDirtyData() )
            $this->store( false, $fieldFilters );
    }

    /*!
     Initializes this class as a copy of \a $originalClass by
     creating new a new name and identifier.
     It will check if there are other classes already with this name
     in which case it will append a unique number to the name and identifier.
    */
    function initializeCopy( &$originalClass )
    {
        $name = ezi18n( 'kernel/class', 'Copy of %class_name', null,
                        array( '%class_name' => $originalClass->attribute( 'name' ) ) );
        $identifier = 'copy_of_' . $originalClass->attribute( 'identifier' );
        $db =& eZDB::instance();
        $sql = "SELECT count( ezcontentclass_name.name ) AS count FROM ezcontentclass, ezcontentclass_name WHERE ezcontentclass.id = ezcontentclass_name.contentclass_id AND ezcontentclass_name.name like '" . $db->escapeString( $name ) . "%'";
        $rows = $db->arrayQuery( $sql );
        $count = $rows[0]['count'];
        if ( $count > 0 )
        {
            ++$count;
            $name .= $count;
            $identifier .= $count;
        }
        $this->setName( $name );
        $this->setAttribute( 'identifier', $identifier );
        $this->setAttribute( 'created', time() );
        include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
        $user =& eZUser::currentUser();
        $userID = $user->attribute( "contentobject_id" );
        $this->setAttribute( 'creator_id', $userID );
    }

    /*!
     Stores the current class as a defined version, updates the contentobject_name
     attribute and recreates the class group entries.
     \note It will remove any existing temporary or defined classes before storing.
    */
    function storeDefined( &$attributes )
    {
        $db =& eZDB::instance();
        $db->begin();

        eZContentClass::removeAttributes( false, $this->attribute( "id" ), EZ_CLASS_VERSION_STATUS_DEFINED );
        eZContentClass::removeAttributes( false, $this->attribute( "id" ), EZ_CLASS_VERSION_STATUS_TEMPORARY );
        $this->remove( false );
        $this->setVersion( EZ_CLASS_VERSION_STATUS_DEFINED, $attributes );
        include_once( "kernel/classes/datatypes/ezuser/ezuser.php" );
        $user =& eZUser::currentUser();
        $user_id = $user->attribute( "contentobject_id" );
        $this->setAttribute( "modifier_id", $user_id );
        $this->setAttribute( "modified", time() );
        $this->adjustAttributePlacements( $attributes );

        for ( $i = 0; $i < count( $attributes ); ++$i )
        {
            $attribute =& $attributes[$i];
            $attribute->storeDefined();
        }

        // Set contentobject_name to something sensible if it is missing
        if ( count( $attributes ) > 0 )
        {
            $identifier = $attributes[0]->attribute( 'identifier' );
            $identifier = '<' . $identifier . '>';
            if ( trim( $this->attribute( 'contentobject_name' ) ) == '' )
            {
                $this->setAttribute( 'contentobject_name', $identifier );
            }
        }

        // Recreate class member entries
        eZContentClassClassGroup::removeClassMembers( $this->ID, EZ_CLASS_VERSION_STATUS_DEFINED );
        $classgroups = eZContentClassClassGroup::fetchGroupList( $this->ID, EZ_CLASS_VERSION_STATUS_TEMPORARY );
        for ( $i = 0; $i < count( $classgroups ); $i++ )
        {
            $classgroup =& $classgroups[$i];
            $classgroup->setAttribute( 'contentclass_version', EZ_CLASS_VERSION_STATUS_DEFINED );
            $classgroup->store();
        }
        eZContentClassClassGroup::removeClassMembers( $this->ID, EZ_CLASS_VERSION_STATUS_TEMPORARY );

        include_once( 'lib/ezutils/classes/ezexpiryhandler.php' );
        $handler =& eZExpiryHandler::instance();
        $handler->setTimestamp( 'user-class-cache', mktime() );
        $handler->store();

        include_once( 'kernel/classes/ezcontentcachemanager.php' );
        eZContentCacheManager::clearAllContentCache();

        $this->setAttribute( 'serialized_name_list', $this->NameList->serializeNames() );
        eZPersistentObject::store();
        $this->NameList->store( $this );

        $db->commit();
    }

    function setVersion( $version, $set_childs = false )
    {
        if ( is_array( $set_childs ) or $set_childs )
        {
            if ( is_array( $set_childs ) )
                $attributes =& $set_childs;
            else
                $attributes =& $this->fetchAttributes();
            for ( $i = 0; $i < count( $attributes ); ++$i )
            {
                $attribute =& $attributes[$i];
                $attribute->setAttribute( "version", $version );
            }
        }

        if ( $this->Version != $version )
            $this->NameList->setHasDirtyData();

        eZPersistentObject::setAttribute( "version", $version );
    }

    function exists( $id, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $userID = false, $useIdentifier = false )
    {
        $conds = array( "version" => $version );
        if ( $useIdentifier )
            $conds["identifier"] = $id;
        else
            $conds["id"] = $id;
        if ( $userID !== false and is_numeric( $userID ) )
            $conds["creator_id"] = $userID;
        $version_sort = "desc";
        if ( $version == EZ_CLASS_VERSION_STATUS_DEFINED )
            $conds['version'] = $version;
        $rows = eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                      null,
                                                      $conds,
                                                      null,
                                                      array( "offset" => 0,
                                                             "length" => 1 ),
                                                      false );
        if ( count( $rows ) > 0 )
            return $rows[0]['id'];
        return false;
    }

    function fetch( $id, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $user_id = false ,$parent_id = null )
    {
        $conds = array( "id" => $id,
                        "version" => $version );

        if ( $user_id !== false and is_numeric( $user_id ) )
            $conds["creator_id"] = $user_id;

        $version_sort = "desc";
        if ( $version == EZ_CLASS_VERSION_STATUS_DEFINED )
            $version_sort = "asc";
        $rows = eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                      null,
                                                      $conds,
                                                      array( "version" => $version_sort ),
                                                      array( "offset" => 0,
                                                             "length" => 2 ),
                                                      false );

        if ( count( $rows ) == 0 )
        {
            $contentClass = null;
            return $contentClass;
        }

        $row =& $rows[0];
        $row["version_count"] = count( $rows );

        if ( $asObject )
            $contentClass = new eZContentClass( $row );
        else
            $contentClass = $row;

        return $contentClass;
    }

    function fetchByRemoteID( $remoteID, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $user_id = false ,$parent_id = null )
    {
        $conds = array( "remote_id" => $remoteID,
                        "version" => $version );
        if ( $user_id !== false and is_numeric( $user_id ) )
            $conds["creator_id"] = $user_id;
        $version_sort = "desc";
        if ( $version == EZ_CLASS_VERSION_STATUS_DEFINED )
            $version_sort = "asc";
        $rows = eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                      null,
                                                      $conds,
                                                      array( "version" => $version_sort ),
                                                      array( "offset" => 0,
                                                             "length" => 2 ),
                                                      false );
        if ( count( $rows ) == 0 )
        {
            $contentClass = null;
            return $contentClass;
        }

        $row =& $rows[0];
        $row["version_count"] = count( $rows );

        if ( $asObject )
            $contentClass = new eZContentClass( $row );
        else
            $contentClass = $row;

        return $contentClass;

    }

    function fetchByIdentifier( $identifier, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED, $user_id = false ,$parent_id = null )
    {
        $conds = array( "identifier" => $identifier,
                        "version" => $version );
        if ( $user_id !== false and is_numeric( $user_id ) )
            $conds["creator_id"] = $user_id;
        $version_sort = "desc";
        if ( $version == EZ_CLASS_VERSION_STATUS_DEFINED )
            $version_sort = "asc";
        $rows = eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                      null,
                                                      $conds,
                                                      array( "version" => $version_sort ),
                                                      array( "offset" => 0,
                                                             "length" => 2 ),
                                                      false );
        if ( count( $rows ) == 0 )
        {
            $contentClass = null;
            return $contentClass;
        }

        $row =& $rows[0];
        $row["version_count"] = count( $rows );

        if ( $asObject )
            $contentClass = new eZContentClass( $row );
        else
            $contentClass = $row;

        return $contentClass;
    }

    /*!
     \static
    */
    function fetchList( $version = EZ_CLASS_VERSION_STATUS_DEFINED, $asObject = true, $user_id = false,
                         $sorts = null, $fields = null, $classFilter = false, $limit = null )
    {
        $conds = array();
        $custom_fields = null;
        $custom_tables = null;
        $custom_conds = null;

        if ( is_numeric( $version ) )
            $conds["version"] = $version;
        if ( $user_id !== false and is_numeric( $user_id ) )
            $conds["creator_id"] = $user_id;
        if ( $classFilter )
        {
            $classIDCount = 0;
            $classIdentifierCount = 0;

            $classIDFilter = array();
            $classIdentifierFilter = array();
            foreach ( $classFilter as $classType )
            {
                if ( is_numeric( $classType ) )
                {
                    $classIDFilter[] = $classType;
                    $classIDCount++;
                }
                else
                {
                    $classIdentifierFilter[] = $classType;
                    $classIdentifierCount++;
                }
            }

            if ( $classIDCount > 1 )
                $conds['id'] = array( $classIDFilter );
            else if ( $classIDCount == 1 )
                $conds['id'] = $classIDFilter[0];
            if ( $classIdentifierCount > 1 )
                $conds['identifier'] = array( $classIdentifierFilter );
            else if ( $classIdentifierCount == 1 )
                $conds['identifier'] = $classIdentifierFilter[0];
        }

        if ( $sorts && isset( $sorts['name'] ) )
        {
            $nameFiler = eZContentClassName::sqlFilter( 'ezcontentclass' );
            $custom_tables = array( $nameFiler['from'] );
            $custom_conds = "AND " . $nameFiler['where'];
            $custom_fields = array( $nameFiler['nameField'] );

            $sorts[$nameFiler['orderBy']] = $sorts['name'];
            unset( $sorts['name'] );
        }

        return eZPersistentObject::fetchObjectList( eZContentClass::definition(),
                                                            $fields,
                                                            $conds,
                                                            $sorts,
                                                            $limit,
                                                            $asObject,
                                                            false,
                                                            $custom_fields,
                                                            $custom_tables,
                                                            $custom_conds );
    }

    /*!
     Returns all attributes as an associative array with the key taken from the attribute identifier.
    */
    function &dataMap()
    {
        $map =& $this->DataMap[$this->Version];
        if ( !isset( $map ) )
        {
            $map = array();
            $attributes = $this->fetchAttributes( false, true, $this->Version );
            foreach ( array_keys( $attributes ) as $attributeKey )
            {
                $attribute =& $attributes[$attributeKey];
                $map[$attribute->attribute( 'identifier' )] =& $attribute;
            }
        }
        return $map;
    }

    function &fetchAttributes( $id = false, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED )
    {
        if ( $id === false )
        {
            if ( isset( $this ) and
                 get_class( $this ) == "ezcontentclass" )
            {
                $id = $this->ID;
                $version = $this->Version;
            }
            else
            {
                $attributes = null;
                return $attributes;
            }
        }

        $filteredList =& eZContentClassAttribute::fetchFilteredList( array( "contentclass_id" => $id,
                                                                            "version" => $version ),
                                                                     $asObject );
        return $filteredList;
    }

    /*!
     Fetch class attribute by identifier, return null if none exist.

     \param attribute identifier.

     \return Class Attribute, null if none matched
    */
    function &fetchAttributeByIdentifier( $identifier, $asObject = true )
    {
        $attributeArray =& eZContentClassAttribute::fetchFilteredList( array( 'contentclass_id' => $this->ID,
                                                                              'version' => $this->Version,
                                                                              'identifier' => $identifier ), $asObject );
        if ( count( $attributeArray ) > 0 )
            return $attributeArray[0];
        $retValue = null;
        return $retValue;
    }

    function fetchSearchableAttributes( $id = false, $asObject = true, $version = EZ_CLASS_VERSION_STATUS_DEFINED )
    {
        if ( $id === false )
        {
            if ( isset( $this ) and
                 get_class( $this ) == "ezcontentclass" )
            {
                $id = $this->ID;
                $version = $this->Version;
            }
            else
                return null;
        }

        return eZContentClassAttribute::fetchFilteredList( array( "contentclass_id" => $id,
                                                                  "is_searchable" => 1,
                                                                  "version" => $version ), $asObject );
    }

    /*!
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &versionStatus()
    {

        if ( $this->VersionCount == 1 )
        {
            if ( $this->Version == EZ_CLASS_VERSION_STATUS_TEMPORARY )
                $status = EZ_CLASS_VERSION_STATUS_TEMPORARY;
            else
                $status = EZ_CLASS_VERSION_STATUS_DEFINED;
        }
        else
            $status = EZ_CLASS_VERSION_STATUS_MODIFED;
        return $status;
    }

    /*!
     \deprecated
     \return The version count for the class if has been determined.
     \note The reference for the return value is required to workaround
           a bug with PHP references.
    */
    function &versionCount()
    {
        return $this->VersionCount;
    }

    /*!
     Will generate a name for the content object based on the class
     settings for content object.
    */
    function contentObjectName( &$contentObject, $version = false, $translation = false )
    {

        $contentObjectName = $this->ContentObjectName;
        $dataMap =& $contentObject->fetchDataMap( $version, $translation );

        eZDebugSetting::writeDebug( 'kernel-content-class', $dataMap, "data map" );
        preg_match_all( "/[<|\|](\(.+\))[\||>]/U",
                        $contentObjectName,
                        $subTagMatchArray );

        $i = 0;
        $tmpTagResultArray = array();
        foreach ( $subTagMatchArray[1]  as $subTag )
        {
            $tmpTag = 'tmptag' . $i;

            $contentObjectName = str_replace( $subTag, $tmpTag, $contentObjectName );

            $subTag = substr( $subTag, 1,strlen($subTag) - 2 );
            $tmpTagResultArray[$tmpTag] = eZContentClass::buildContentObjectName( $subTag, $dataMap );
            $i++;
        }
        $contentObjectName = eZContentClass::buildContentObjectName( $contentObjectName, $dataMap, $tmpTagResultArray );
        return $contentObjectName;
    }

    /*!
     Generates a name for the content object based on the content object name pattern
     and data map of an object.
    */
    function buildContentObjectName( &$contentObjectName, $dataMap, $tmpTags = false )
    {

        preg_match_all( "|<[^>]+>|U",
                        $contentObjectName,
                        $tagMatchArray );

        foreach ( $tagMatchArray[0] as $tag )
        {
            $tagName = str_replace( "<", "", $tag );
            $tagName = str_replace( ">", "", $tagName );

            $tagParts = explode( '|', $tagName );

            $namePart = "";
            foreach ( $tagParts as $name )
            {
                // get the value of the attribute to use in name
                if ( isset( $dataMap[$name] ) )
                {
                    $namePart = $dataMap[$name]->title();
                    if ( $namePart != "" )
                        break;
                }
                elseif ( $tmpTags && isset( $tmpTags[$name] ) && $tmpTags[$name] != '' )
                {
                    $namePart = $tmpTags[$name];
                    break;
                }

            }

            // replace tag with object name part
            $contentObjectName = str_replace( $tag, $namePart, $contentObjectName );
        }
        return $contentObjectName;
    }

    /*!
     \return will return the number of objects published by this class.
    */
    function &objectCount()
    {
        $db =& eZDB::instance();

        $countRow = $db->arrayQuery( 'SELECT count(*) AS count FROM ezcontentobject '.
                                     'WHERE contentclass_id='.$this->ID ." and status = " . EZ_CONTENT_OBJECT_STATUS_PUBLISHED );

        return $countRow[0]['count'];
    }

    /*!
     \return Sets the languages which are allowed to be instantiated for the class.
     Used only for the content/ fetch function.
    */
    function setCanInstantiateLanguages( $languageCodes )
    {
        $this->CanInstantiateLanguages = $languageCodes;
    }

    function &canInstantiateLanguages()
    {
        if ( is_array( $this->CanInstantiateLanguages ) )
            $languageCodes = array_intersect( eZContentLanguage::prioritizedLanguageCodes(), $this->CanInstantiateLanguages );
        else
            $languageCodes = array();
        return $languageCodes;
    }

    /*!
     \static
    */
    function nameFromSerializedString( $serailizedNameList )
    {
        return eZContentClassNameList::nameFromSerializedString( $serailizedNameList );
    }

    function &name( $languageLocale = false )
    {
        $name = $this->NameList->name( $languageLocale );
        return $name;
    }

    function setName( $name, $languageLocale = false )
    {
        if ( !$languageLocale )
            $languageLocale = $this->topPriorityLanguage();
        $this->NameList->setNameByLanguageLocale( $name, $languageLocale );

        $languageID = eZContentLanguage::idByLocale( $languageLocale );
        $languageMask = $this->attribute( 'language_mask' );
        $this->setAttribute( 'language_mask', $languageMask | $languageID );
    }

    function setAlwaysAvailableLanguageID( $languageID, $updateChilds = true )
    {
        $db =& eZDB::instance();
        $db->begin();

        $languageLocale = false;
        if ( $languageID )
        {
            $language = eZContentLanguage::fetch( $languageID );
            $languageLocale = $language->attribute( 'locale' );
        }

        if ( $languageID )
        {
            $this->setAttribute( 'language_mask', (int)$this->attribute( 'language_mask' ) | 1 );
            $this->NameList->setAlwaysAvailableLanguage( $languageLocale );
        }
        else
        {
            $this->setAttribute( 'language_mask', (int)$this->attribute( 'language_mask' ) & ~1 );
            $this->NameList->setAlwaysAvailableLanguage( false );
        }
        $this->store();

        $classID = $this->attribute( 'id' );
        $version = $this->attribute( 'version' );

        $attributes =& $this->fetchAttributes();
        foreach( array_keys( $attributes ) as $attrKey )
        {
            $attribute =& $attributes[$attrKey];
            $attribute->setAlwaysAvailableLanguage( $languageLocale );
            $attribute->store();
        }

        // reset 'always available' flag
        $sql = "UPDATE ezcontentclass_name SET language_id=";
        if ( $db->databaseName() == 'oracle' )
        {
            $sql .= "bitand( language_id, ~1 )";
        }
        else
        {
            $sql .= "language_id & ~1";
        }
        $sql .= " WHERE contentclass_id = '$classID' AND contentclass_version = '$version'";
        $db->query( $sql );

        if ( $languageID != false )
        {
            $newLanguageID = $languageID | 1;

            $sql = "UPDATE ezcontentclass_name
                    SET language_id='$newLanguageID'
                WHERE language_id='$languageID' AND contentclass_id = '$classID' AND contentclass_version = '$version'";
            $db->query( $sql );
        }

        $db->commit();
    }

    function clearAlwaysAvailableLanguageID()
    {
        $this->setAlwaysAvailableLanguageID( false );
    }

    function &languages()
    {
        $languages = eZContentLanguage::prioritizedLanguagesByMask( $this->LanguageMask );

        return $languages;
    }

    function &canCreateLanguages()
    {
        $availableLanguages = $this->languages();
        $availableLanguagesCodes = array_keys( $availableLanguages );

        $languages = array();
        foreach ( eZContentLanguage::prioritizedLanguages() as $language )
        {
            $languageCode = $language->attribute( 'locale' );
            if ( !in_array( $languageCode, $availableLanguagesCodes ) )
            {
                $languages[$languageCode] = $language;
            }
        }

        return $languages;
    }

    function &topPriorityLanguage()
    {
        $language = eZContentLanguage::topPriorityLanguageByMask( $this->attribute( 'language_mask' ) );
        if ( !$language )
            $language = eZContentLanguage::fetch( $this->attribute( 'initial_language_id' ) );

        $languageLocale = $language ? $language->attribute( 'locale' ) : false;

        return $languageLocale;
    }

    function &defaultLanguage()
    {
        $defaultLanguage = false;

        $language = eZContentLanguage::topPriorityLanguage();
        if ( $language )
        {
            $defaultLanguage = $language->attribute( 'locale' );
        }

        return $defaultLanguage;
    }

    function &nameList()
    {
        $nameList = $this->NameList->nameList();
        return $nameList;
    }

    function removeTranslation( $languageID )
    {
        $language = eZContentLanguage::fetch( $languageID );

        if ( !$language )
        {
            return false;
        }

        // check if it is not the initial language
        $classInitialLanguageID = $this->attribute( 'initial_language_id' );
        if ( $classInitialLanguageID == $languageID )
        {
            return false;
        }

        $db =& eZDB::instance();
        $db->begin();

        $classID = $this->attribute( 'id' );
        $languageID = $language->attribute( 'id' );
        $altLanguageID = $languageID++;

        // change language_mask of the object
        $languageMask = (int) $this->attribute( 'language_mask' );
        $languageMask = (int) $languageMask & ~ (int) $languageID;
        $this->setAttribute( 'language_mask', $languageMask );

        // Remove all names in the language
        $db->query( "DELETE FROM ezcontentclass_name
                     WHERE contentclass_id='$classID'
                       AND ( language_id='$languageID' OR language_id='$altLanguageID' )" );

        $languageLocale = $language->attribute( 'locale' );
        $this->NameList->removeName( $languageLocale );

        $this->store();

        // Remove names for attributes in the language
        $attributes = $this->fetchAttributes();
        foreach ( array_keys( $attributes ) as $attr_key )
        {
            $attribute =& $attributes[$attr_key];
            $attribute->removeTranslation( $languageLocale );
            $attribute->store();
            unset( $attribute );
        }

        $db->commit();

        return true;
    }

    /// \privatesection
    var $ID;
    // serialized array of translated class names
    var $SerializedNameList;
    // unserialized class names
    var $NameList;
    var $Identifier;
    var $ContentObjectName;
    var $Version;
    var $VersionCount;
    var $CreatorID;
    var $ModifierID;
    var $Created;
    var $Modified;
    var $InGroups;
    var $AllGroups;
    var $IsContainer;
    var $CanInstantiateLanguages;
    var $LanguageMask;
}

?>
