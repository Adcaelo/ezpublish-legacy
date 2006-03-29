<?php
//
// Created on: <17-Apr-2002 10:34:48 bf>
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

include_once( 'kernel/classes/eztrigger.php' );
include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "lib/ezutils/classes/ezini.php" );
$Module =& $Params["Module"];
include_once( 'kernel/content/node_edit.php' );
initializeNodeEdit( $Module );
include_once( 'kernel/content/relation_edit.php' );
initializeRelationEdit( $Module );
$obj =& eZContentObject::fetch( $ObjectID );

if ( !$obj )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

// If the object has status Archived (trash) we redirect to content/restore
// which can handle this status properly.
if ( $obj->attribute( 'status' ) == EZ_CONTENT_OBJECT_STATUS_ARCHIVED )
{
    return $Module->redirectToView( 'restore', array( $ObjectID ) );
}

// Check if we should switch access mode (http/https) for this object.
include_once( 'kernel/classes/ezsslzone.php' );
eZSSLZone::checkObject( 'content', 'edit', $obj );

// Check permission for object and version.
if ( !$obj->checkAccess( 'edit', false, false, false, $EditLanguage ) )
{
    // Check if it is a first created version of an object.
    // If so, then edit is allowed if we have an access to the 'create' function.

    if ( $obj->attribute( 'current_version' ) == 1 && !$obj->attribute( 'status' ) )
    {
        $mainNode = eZNodeAssignment::fetchForObject( $obj->attribute( 'id' ), 1 );
        $parentObj = $mainNode[0]->attribute( 'parent_contentobject' );
        $allowEdit = $parentObj->checkAccess( 'create', $obj->attribute( 'contentclass_id' ), $parentObj->attribute( 'contentclass_id' ), false, $EditLanguage );
    }
    else
        $allowEdit = false;

    if ( !$allowEdit )
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel', array( 'AccessList' => $obj->accessList( 'edit' ) ) );
}

$classID = $obj->attribute( 'contentclass_id' );
$class = eZContentClass::fetch( $classID );
$http =& eZHTTPTool::instance();

// Action for the edit_draft.tpl/edit_languages.tpl page.
// CancelDraftButton is set for the Cancel button.
// Note: This code is safe to place before permission checking.
if( $http->hasPostVariable( 'CancelDraftButton' ) )
{
   $mainNode = eZNodeAssignment::fetchForObject( $obj->attribute( 'id' ), $obj->attribute( 'current_version' ), true );
   if ( count( $mainNode ) == 1 )
   {
       $node = $mainNode[0]->attribute( 'node' );
       return $Module->redirectToView( 'view', array( 'full', $node->attribute( 'node_id' ) ) );
   }
   else
   {
       $nodes = $obj->assignedNodes();
       $chosenNode = null;
       foreach ( $nodes as $node )
       {
           if ( $node->attribute( 'is_main' ) )
           {
               $chosenNode = $node;
           }
           else if ( $chosenNode === null )
           {
               $chosenNode = $node;
           }
       }
       if ( $chosenNode )
       {
           return $Module->redirectToView( 'view', array( 'full', $chosenNode->attribute( 'node_id' ) ) );
       }
       else
       {
           $contentINI =& eZINI::instance( 'content.ini' );
           $rootNodeID = $contentINI->variable( 'NodeSettings', 'RootNode' );
           return $Module->redirectToView( 'view', array( 'full', $rootNodeID ) );
       }
   }
}

// Remember redirection URI in session for later use.
// Note: This code is safe to place before permission checking.
if ( $http->hasPostVariable( 'RedirectURIAfterPublish' ) )
{
    $http->setSessionVariable( 'RedirectURIAfterPublish', $http->postVariable( 'RedirectURIAfterPublish' ) );
}

// Action for edit_draft.tpl page,
// EditButton is the button for editing the selected version.
// Note: This code is safe to place before permission checking.
if ( $http->hasPostVariable( 'EditButton' ) )
{
    if ( $http->hasPostVariable( 'SelectedVersion' ) )
    {
        $selectedVersion = $http->postVariable( 'SelectedVersion' );
        // Kept for backwards compatability, EditLanguage may also be set in URL
        if ( $http->hasPostVariable( 'ContentObjectLanguageCode' ) )
        {
            $EditLanguage = $http->postVariable( 'ContentObjectLanguageCode' );
        }

        return $Module->redirectToView( "edit", array( $ObjectID, $selectedVersion, $EditLanguage ) );
    }
}
// Action for edit_draft.tpl page,
// This will create a new draft of the object which the user can edit.
if ( $http->hasPostVariable( 'NewDraftButton' ) )
{
    $contentINI =& eZINI::instance( 'content.ini' );
    $versionlimit = $contentINI->variable( 'VersionManagement', 'DefaultVersionHistoryLimit' );
    // Kept for backwards compatability
    if ( $http->hasPostVariable( 'ContentObjectLanguageCode' ) )
    {
        $EditLanguage = $http->postVariable( 'ContentObjectLanguageCode' );
    }

    $limitList = $contentINI->variable( 'VersionManagement', 'VersionHistoryClass' );
    foreach ( array_keys ( $limitList ) as $key )
    {
        if ( $classID == $key )
            $versionlimit =& $limitList[$key];
    }
    if ( $versionlimit < 2 )
        $versionlimit = 2;
    $versionCount = $obj->getVersionCount();
    if ( $versionCount < $versionlimit )
    {
        $db =& eZDB::instance();
        $db->begin();
        $version = $obj->createNewVersionIn( $EditLanguage, $FromLanguage );
        $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
        $version->store();
        $db->commit();
        if ( !$http->hasPostVariable( 'DoNotEditAfterNewDraft' ) )
        {
            return $Module->redirectToView( 'edit', array( $ObjectID, $version->attribute( 'version' ), $EditLanguage ) );
        }
        else
        {
            return $Module->redirectToView( 'edit', array( $ObjectID, 'f', $EditLanguage ) );
        }
    }
    else
    {
        $params = array( 'conditions'=> array( 'status' => 3 ) );
        $versions =& $obj->versions( true, $params );
        if ( count( $versions ) > 0 )
        {
            $modified = $versions[0]->attribute( 'modified' );
            $removeVersion =& $versions[0];
            foreach ( array_keys( $versions ) as $versionKey )
            {
                $version =& $versions[$versionKey];
                $currentModified = $version->attribute( 'modified' );
                if ( $currentModified < $modified )
                {
                    $modified = $currentModified;
                    $removeVersion = $version;
                }
            }

            $db =& eZDB::instance();
            $db->begin();
            $removeVersion->remove();
            $version = $obj->createNewVersionIn( $EditLanguage );
            $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
            $version->store();
            $db->commit();

            if( !$http->hasPostVariable( 'DoNotEditAfterNewDraft' ) )
            {
                return $Module->redirectToView( 'edit', array( $ObjectID, $version->attribute( 'version' ), $EditLanguage ) );
            }
            else
            {
                return $Module->redirectToView( 'edit', array( $ObjectID, 'f', $EditLanguage ) );
            }

            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
        else
        {
            $http->setSessionVariable( 'ExcessVersionHistoryLimit', true );
            $currentVersion = $obj->attribute( 'current_version' );
            $Module->redirectToView( 'versions', array( $ObjectID, $currentVersion, $EditLanguage ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
    }
}

// Action for the edit_language.tpl page.
// LanguageSelection is used to choose a language to edit the object in.
if ( $http->hasPostVariable( 'LanguageSelection' ) )
{
    $selectedEditLanguage = $http->postVariable( 'EditLanguage' );
    $selectedFromLanguage = $http->postVariable( 'FromLanguage' );
    if ( in_array( $selectedEditLanguage, $obj->availableLanguages() ) )
    {
        $selectedFromLanguage = false;
    }
    $user =& eZUser::currentUser();
    $parameters = array( 'conditions' =>
                         array( 'status' => array( array( EZ_VERSION_STATUS_DRAFT,
                                                          EZ_VERSION_STATUS_INTERNAL_DRAFT ) ),
                                'creator_id' => $user->attribute( 'contentobject_id' ) ) );
    $chosenVersion = null;
    foreach ( $obj->versions( true, $parameters ) as $possibleVersion )
    {
        if ( $possibleVersion->initialLanguageCode() == $selectedEditLanguage )
        {
            if ( !$chosenVersion ||
                 $chosenVersion->attribute( 'modified' ) < $possibleVersion->attribute( 'modified' ) )
            {
                $chosenVersion = $possibleVersion;
            }
        }
    }
    // We already found a draft by the current user,
    // immediately redirect to edit page for that version.
    if ( $chosenVersion )
    {
        return $Module->redirectToView( 'edit', array( $ObjectID, 'f', $selectedEditLanguage, $selectedFromLanguage ) );
    }

    $version = $obj->createNewVersionIn( $selectedEditLanguage, $selectedFromLanguage );
    $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );

    $version->store();
    return $Module->redirectToView( 'edit', array( $ObjectID, $version->attribute( 'version' ), $selectedEditLanguage, $selectedFromLanguage ) );
}

// If we have a version number we check if it exists.
if ( is_numeric( $EditVersion ) )
{
    $version =& $obj->version( $EditVersion );
    if ( !$version )
    {
        return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }
}

// No language was specified in the URL, we need to figure out
// the language to use.
if ( $EditLanguage == false )
{
    // We check the $version variable which might be set above
    if ( isset( $version ) && $version )
    {
        // We have a version so we then know the language directly.

        // JB start
        $obj->cleanupInternalDrafts();
        // JB end
        $translationList = $version->translationList( false, false );
        if ( $translationList )
        {
            $EditLanguage = $translationList[0];
        }
    }
    else
    {
        // No version so we investigage further.
        $obj->cleanupInternalDrafts();

        // Check number of languages
        include_once( 'kernel/classes/ezcontentlanguage.php' );
        $languages = eZContentLanguage::fetchList();
        // If there is only one language we choose it for the user and goes to version choice screen.
        if ( count( $languages ) == 1 )
        {
            $firstLanguage = array_shift( $languages );
            return $Module->redirectToView( 'edit', array( $ObjectID, 'f', $firstLanguage->attribute( 'locale' ) ) );
        }

        // No version found, ask the user.
        include_once( 'kernel/common/template.php' );

        $tpl =& templateInit();

        $res =& eZTemplateDesignResource::instance();
        $res->setKeys( array( array( 'object', $obj->attribute( 'id' ) ) ) );

        $tpl->setVariable( 'object', $obj );
        $tpl->setVariable( 'show_existing_languages', ( $EditVersion == 'a' )? false: true );

        $Result = array();
        $Result['content'] =& $tpl->fetch( 'design:content/edit_languages.tpl' );
        $Result['path'] = array( array( 'text' => ezi18n( 'kernel/content', 'Content' ),
                                 'url' => false ),
                          array( 'text' => ezi18n( 'kernel/content', 'Edit' ),
                                 'url' => false ) );

        return $Result;
    }
}

$ini =& eZINI::instance();

// There version is not set but we do have a language.
// This means we need to create a new draft for the user, or reuse
// an existing one.
if ( !is_numeric( $EditVersion ) )
{
    if ( $ini->variable( 'ContentSettings', 'EditDirtyObjectAction' ) == 'usecurrent' )
    {
        // JB start
        $obj->cleanupInternalDrafts();
        // JB end
        $version = $obj->createNewVersionIn( $EditLanguage );
        $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
        $version->store();
    }
    else
    {
        // JB start
        $obj->cleanupInternalDrafts();
        // JB end
        $draftVersions =& $obj->versions( true, array( 'conditions' => array( 'status' => array( array( EZ_VERSION_STATUS_DRAFT, EZ_VERSION_STATUS_INTERNAL_DRAFT ) ),
                                                                              'language_code' => $EditLanguage ) ) );
        if ( count( $draftVersions ) > 1 )
        {
            // There are already drafts for the specified language so we need to ask the user what to do.
            $mostRecentDraft =& $draftVersions[0];
            foreach( $draftVersions as $currentDraft )
            {
                if( $currentDraft->attribute( 'modified' ) > $mostRecentDraft->attribute( 'modified' ) )
                {
                    $mostRecentDraft =& $currentDraft;
                }
            }

            include_once( 'kernel/common/template.php' );
            $tpl =& templateInit();

            $res =& eZTemplateDesignResource::instance();
            $res->setKeys( array( array( 'object', $obj->attribute( 'id' ) ),
                                array( 'class', $class->attribute( 'id' ) ),
                                array( 'class_identifier', $class->attribute( 'identifier' ) ),
                                array( 'class_group', $class->attribute( 'match_ingroup_id_list' ) ) ) );

            $tpl->setVariable( 'edit_language', $EditLanguage );
            $tpl->setVariable( 'from_language', $FromLanguage );
            $tpl->setVariable( 'object', $obj );
            $tpl->setVariable( 'class', $class );
            $tpl->setVariable( 'draft_versions', $draftVersions );
            $tpl->setVariable( 'most_recent_draft', $mostRecentDraft );

            $Result = array();
            $Result['content'] =& $tpl->fetch( 'design:content/edit_draft.tpl' );
            return $Result;
        }
        elseif ( count( $draftVersions ) == 1 )
        {
            // If there is only one draft by you, edit it immediately.
            $parameters = array( $ObjectID, $draftVersions[0]->attribute( 'version' ), $EditLanguage );
            if ( strlen( $FromLanguage ) != 0 )
            {
                $parameters[] = $FromLanguage;
            }
            return $Module->redirectToView( 'edit', $parameters );
        }
        else
        {
            $version = $obj->createNewVersionIn( $EditLanguage );
            $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
            $version->store();
            return $Module->redirectToView( "edit", array( $ObjectID, $version->attribute( "version" ), $EditLanguage ) );
        }
    }
}

// JB start
// This method performs duplicate checking of what is done above it,
// it should not be required anymore.
// Disabling it while testing, remove it when 100% sure.
/*
if ( !function_exists( 'checkForExistingVersion'  ) )
{
    function checkForExistingVersion( &$module, $objectID, &$editVersion, &$editLanguage )
    {
        $requireNewVersion = false;
        $object =& eZContentObject::fetch( $objectID );
        if ( $object === null )
            return;

        $user =& eZUser::currentUser();
        $version = null;
        if ( is_numeric( $editVersion ) )
        {
            $version =& $object->version( $editVersion );
            if ( !$version )
            {
                $module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
        }
        else
        {
            $userID = $user->id();
            $version = eZContentObjectVersion::fetchUserDraft( $objectID, $userID );
        }

        if ( $version )
        {
            $currentVersion = $object->currentVersion();
            if ( ( $version->attribute( 'status' ) != EZ_VERSION_STATUS_DRAFT and
                   $version->attribute( 'status' ) != EZ_VERSION_STATUS_INTERNAL_DRAFT and
                   $version->attribute( 'status' ) != EZ_VERSION_STATUS_PENDING ) or
                   $version->attribute( 'creator_id' ) != $user->id() )
            {
                $module->redirectToView( 'versions', array( $objectID, $version->attribute( "version" ), $editLanguage ) );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
            if ( $version->attribute( 'version' ) != $editVersion )
            {
                $module->redirectToView( "edit", array( $objectID, $version->attribute( "version" ), $editLanguage ) );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
        }
        else
            $requireNewVersion = true;
        if ( $requireNewVersion )
        {
            // Fetch and create new version
            if ( !$object->checkAccess( 'edit', false, false, false, $editLanguage ) )
            {
                $module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }

            $contentINI =& eZINI::instance( 'content.ini' );
            $versionlimit = $contentINI->variable( 'VersionManagement', 'DefaultVersionHistoryLimit' );

            $limitList = $contentINI->variable( 'VersionManagement', 'VersionHistoryClass' );

            $classID = $object->attribute( 'contentclass_id' );
            foreach ( array_keys ( $limitList ) as $key )
            {
                if ( $classID == $key )
                    $versionlimit =& $limitList[$key];
            }
            if ( $versionlimit < 2 )
                $versionlimit = 2;

            $versionCount = $object->getVersionCount();
            if ( $versionCount < $versionlimit )
            {
                $db =& eZDB::instance();
                $db->begin();
                $version = $object->createNewVersionIn( $editLanguage );
                $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
                $version->store();
                $db->commit();
                $module->redirectToView( "edit", array( $objectID, $version->attribute( "version" ), $editLanguage ) );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
            else
            {
                // Remove oldest archived version first
                $params = array( 'conditions'=> array( 'status' => 3 ) );
                $versions =& $object->versions( true, $params );
                if ( count( $versions ) > 0 )
                {
                    $modified = $versions[0]->attribute( 'modified' );
                    $removeVersion =& $versions[0];
                    foreach ( array_keys( $versions ) as $versionKey )
                    {
                        $version =& $versions[$versionKey];
                        $currentModified = $version->attribute( 'modified' );
                        if ( $currentModified < $modified )
                        {
                            $modified = $currentModified;
                            $removeVersion = $version;
                        }
                    }
                    $db =& eZDB::instance();
                    $db->begin();
                    $removeVersion->remove();
                    $version = $object->createNewVersionIn( $editLanguage );
                    $version->setAttribute( 'status', EZ_VERSION_STATUS_INTERNAL_DRAFT );
                    $version->store();
                    $db->commit();
                    $db->commit();
                    $module->redirectToView( "edit", array( $objectID, $version->attribute( "version" ), $editLanguage ) );
                    return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
                }
                else
                {
                    $http =& eZHTTPTool::instance();
                    $http->setSessionVariable( 'ExcessVersionHistoryLimit', true );
                    $currentVersion = $object->attribute( 'current_version' );
                    $module->redirectToView( 'versions', array( $objectID, $currentVersion, $editLanguage ) );
                    return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
                }
            }
        }
    }
}
$Module->addHook( 'pre_fetch', 'checkForExistingVersion' );
*/
// JB end

if ( !function_exists( 'checkContentActions' ) )
{
    function checkContentActions( &$module, &$class, &$object, &$version, &$contentObjectAttributes, $EditVersion, $EditLanguage, $FromLanguage, &$Result )
    {
        if ( $module->isCurrentAction( 'Preview' ) )
        {
            $module->redirectToView( 'versionview', array( $object->attribute('id'), $EditVersion, $EditLanguage, $FromLanguage ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }

        if ( $module->isCurrentAction( 'Translate' ) )
        {
            $module->redirectToView( 'translate', array( $object->attribute( 'id' ), $EditVersion, $EditLanguage, $FromLanguage ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }

        if ( $module->isCurrentAction( 'VersionEdit' ) )
        {
            if ( isset( $GLOBALS['eZRequestedURI'] ) and is_object( $GLOBALS['eZRequestedURI'] ) )
            {
                $uri = $GLOBALS['eZRequestedURI'];
                $uri = $uri->originalURIString();
                $http =& eZHTTPTool::instance();
                $http->setSessionVariable( 'LastAccessesVersionURI', $uri );
            }
            $module->redirectToView( 'versions', array( $object->attribute( 'id' ), $EditVersion, $EditLanguage ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }

        if ( $module->isCurrentAction( 'EditLanguage' ) )
        {
            if ( $module->hasActionParameter( 'SelectedLanguage' ) )
            {
                $EditLanguage = $module->actionParameter( 'SelectedLanguage' );
                // We reset the from language to disable the translation look
                $FromLanguage = false;
                $module->redirectToView( 'edit', array( $object->attribute('id'), $EditVersion, $EditLanguage, $FromLanguage ) );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
        }

        if ( $module->isCurrentAction( 'TranslateLanguage' ) )
        {
            if ( $module->hasActionParameter( 'SelectedLanguage' ) )
            {
                $FromLanguage = $EditLanguage;
                $EditLanguage = $module->actionParameter( 'SelectedLanguage' );
                $module->redirectToView( 'edit', array( $object->attribute('id'), $EditVersion, $EditLanguage, $FromLanguage ) );
                return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }
        }

        if ( $module->isCurrentAction( 'FromLanguage' ) )
        {
            $FromLanguage = $module->actionParameter( 'FromLanguage' );
            $module->redirectToView( 'edit', array( $object->attribute('id'), $EditVersion, $EditLanguage, $FromLanguage ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
        
        if ( $module->isCurrentAction( 'Discard' ) )
        {
            $http =& eZHTTPTool::instance();
            $objectID = $object->attribute( 'id' );
            $discardConfirm = true;
            if ( $http->hasPostVariable( 'DiscardConfirm' ) )
                $discardConfirm = $http->postVariable( 'DiscardConfirm' );
            if ( $http->hasPostVariable( 'RedirectIfDiscarded' ) )
                $http->setSessionVariable( 'RedirectIfDiscarded', $http->postVariable( 'RedirectIfDiscarded' ) );
            $http->setSessionVariable( 'DiscardObjectID', $objectID );
            $http->setSessionVariable( 'DiscardObjectVersion', $EditVersion );
            $http->setSessionVariable( 'DiscardObjectLanguage', $EditLanguage );
            $http->setSessionVariable( 'DiscardConfirm', $discardConfirm );
            $module->redirectTo( $module->functionURI( 'removeeditversion' ) . '/' );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }

        // helper function which computes the redirect after
        // publishing and final store of a draft.
        function computeRedirect( &$module, &$object, &$version )
        {
            $http =& eZHTTPTool::instance();

            $node = $object->mainNode();

            $hasRedirected = false;
            if ( $http->hasSessionVariable( 'ParentObject' ) && $http->sessionVariable( 'NewObjectID' ) == $object->attribute( 'id' ) )
            {
                $parentArray = $http->sessionVariable( 'ParentObject' );
                $parentURL = $module->redirectionURI( 'content', 'edit', $parentArray );
                $parentObject = eZContentObject::fetch( $parentArray[0] );
                $db =& eZDB::instance();
                $db->begin();
                $parentObject->addContentObjectRelation( $object->attribute( 'id' ), $parentArray[1] );
                $db->commit();
                $http->removeSessionVariable( 'ParentObject' );
                $http->removeSessionVariable( 'NewObjectID' );
                $module->redirectTo( $parentURL );
                $hasRedirected = true;
            }
            if ( $http->hasSessionVariable( 'RedirectURIAfterPublish' ) && !$hasRedirected )
            {
                $uri =& $http->sessionVariable( 'RedirectURIAfterPublish' );
                $http->removeSessionVariable( 'RedirectURIAfterPublish' );
                $module->redirectTo( $uri );
                $hasRedirected = true;
            }
            if ( $http->hasPostVariable( 'RedirectURIAfterPublish' )  && !$hasRedirected )
            {
                $uri = $http->postVariable( 'RedirectURIAfterPublish' );
                $module->redirectTo( $uri );
                $hasRedirected = true;
            }
            if ( !$hasRedirected )
            {
                if ( $http->hasPostVariable( 'RedirectURI' ) )
                {
                    $uri = $http->postVariable( 'RedirectURI' );
                    $module->redirectTo( $uri );
                }
                else if ( $node !== null )
                {
                    $parentNode = $node->attribute( 'parent_node_id' );
                    if ( $parentNode == 1 )
                    {
                        $parentNode = $node->attribute( 'node_id' );
                    }
                    $module->redirectToView( 'view', array( 'full', $parentNode ) );
                }
                else
                {
                    $module->redirectToView( 'view', array( 'full', $version->attribute( 'main_parent_node_id' ) ) );
                }
            }

        }

        if( $module->isCurrentAction( 'StoreExit' ) )
        {
            computeRedirect( $module, $object, $version );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }

        if ( $module->isCurrentAction( 'Publish' ) )
        {
            $user =& eZUser::currentUser();
            include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
            eZDebug::accumulatorStart( 'publish', '', 'publish' );
            $oldObjectName = $object->name();
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $object->attribute( 'id' ),
                                                                                         'version' => $version->attribute( 'version' ) ) );
            eZDebug::accumulatorStop( 'publish' );

            if ( ( array_key_exists( 'status', $operationResult ) && $operationResult['status'] != EZ_MODULE_OPERATION_CONTINUE ) )
            {
                switch( $operationResult['status'] )
                {
                    case EZ_MODULE_OPERATION_HALTED:
                    {
                        if ( isset( $operationResult['redirect_url'] ) )
                        {
                            $module->redirectTo( $operationResult['redirect_url'] );
                            return;
                        }
                        else if ( isset( $operationResult['result'] ) )
                        {
                            $result =& $operationResult['result'];
                            $resultContent = false;
                            if ( is_array( $result ) )
                            {
                                if ( isset( $result['content'] ) )
                                    $resultContent = $result['content'];
                                if ( isset( $result['path'] ) )
                                    $Result['path'] = $result['path'];
                            }
                            else
                                $resultContent =& $result;
                            // Temporary fix to make approval workflow work with edit.
                            if ( strpos( $resultContent, 'Deffered to cron' ) === 0 )
                                $Result = null;
                            else
                                $Result['content'] =& $resultContent;
                        }
                    }break;
                    case EZ_MODULE_OPERATION_CANCELED:
                    {
                        $Result = array();
                        $Result['content'] = "Content publish cancelled<br/>";
                    }
                }

                /* If we already have a correct module result
                 * we don't need to continue module execution.
                 */
                if ( is_array( $Result ) )
                    return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
            }

            // update content object attributes array by refetching them from database
            $object = eZContentObject::fetch( $object->attribute( 'id' ) );
            $contentObjectAttributes = $object->attribute( 'contentobject_attributes' );

            // set chosen hidden/invisible attributes for object nodes
            $http          =& eZHTTPTool::instance();
            $assignedNodes =& $object->assignedNodes( true );
            foreach ( $assignedNodes as $node )
            {
                $nodeID               =& $node->attribute( 'node_id' );
                $parentNodeID         =& $node->attribute( 'parent_node_id' );
                $updateNodeVisibility =  false;
                $postVarName          = "FutureNodeHiddenState_$parentNodeID";

                if ( !$http->hasPostVariable( $postVarName ) )
                    $updateNodeVisibility = true;
                else
                {
                    $futureNodeHiddenState = $http->postVariable( $postVarName );
                    $db =& eZDB::instance();
                    $db->begin();
                    if ( $futureNodeHiddenState == 'hidden' )
                        eZContentObjectTreeNode::hideSubTree( $node );
                    else if ( $futureNodeHiddenState == 'visible' )
                        eZContentObjectTreeNode::unhideSubTree( $node );
                    else if ( $futureNodeHiddenState == 'unchanged' )
                        $updateNodeVisibility = true;
                    else
                        eZDebug::writeWarning( "Unknown value for the future node hidden state: '$futureNodeHiddenState'" );
                    $db->commit();
                }

                if ( $updateNodeVisibility )
                {
                    // this might be redundant
                    $db =& eZDB::instance();
                    $db->begin();
                    $parentNode = eZContentObjectTreeNode::fetch( $parentNodeID );
                    eZContentObjectTreeNode::updateNodeVisibility( $node, $parentNode, /* $recursive = */ false );
                    $db->commit();
                    unset( $node, $parentNode );
                }
            }
            unset( $assignedNodes );

            $object = eZContentObject::fetch( $object->attribute( 'id' ) );

            $newObjectName = $object->name();

            $http =& eZHttpTool::instance();

            computeRedirect( $module, $object, $version );
            // we have set redirection URI for module so we don't need to continue module execution
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
    }
}
$Module->addHook( 'action_check', 'checkContentActions' );

$includeResult = include( 'kernel/content/attribute_edit.php' );

if ( $includeResult != 1 )
    return $includeResult;

?>
