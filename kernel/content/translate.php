<?php
//
// Created on: <03-May-2002 15:17:01 bf>
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

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentobjectversion.php' );
include_once( 'kernel/classes/ezcontentobjectattribute.php' );

include_once( 'lib/ezutils/classes/ezhttptool.php' );
include_once( 'lib/ezlocale/classes/ezlocale.php' );

include_once( 'kernel/common/template.php' );

$ObjectID = $Params['ObjectID'];
$EditVersion = $Params['EditVersion'];

$http =& eZHTTPTool::instance();

$redirection = false;
if ( $Module->isCurrentAction( 'EditObject' ) )
{
    $redirection = array( 'view' => 'edit',
                          'parameters' => array( $ObjectID, $EditVersion ),
                          'unordered_parameters' => null );
}

$translateToLanguage = false;
$activeTranslation = false;
$activeTranslationLocale = false;

if ( $http->hasPostVariable( 'TranslationLanguageEdit' ) )
{
    $translateToLanguage = $http->postVariable( 'TranslationLanguageEdit' );
    $activeTranslation = $translateToLanguage;
    $activeTranslationLocale =& eZLocale::instance( $activeTranslation );
}

if ( $Module->isCurrentAction( 'EditLanguage' ) and
     $Module->hasActionParameter( 'SelectedLanguage' ) )
{
    $translateToLanguage = $Module->actionParameter( 'SelectedLanguage' );
}

$createLanguage = false;
if ( $Module->isCurrentAction( 'AddLanguage' ) and
     $Module->hasActionParameter( 'SelectedLanguage' ) )
{
    $createLanguage = $Module->actionParameter( 'SelectedLanguage' );
}

$tpl =& templateInit();

$object =& eZContentObject::fetch( $ObjectID );

if ( $object === null  )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

if ( !$object->attribute( 'can_edit' ) )
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );

$version =& $object->version( $EditVersion );

if ( $version === null  )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

$isRemoveActive = false;
$removeLanguageArray = false;
if ( ( $Module->isCurrentAction( 'RemoveLanguage' ) or
       $Module->isCurrentAction( 'RemoveLanguageConfirmation' ) ) and
     $Module->hasActionParameter( 'SelectedLanguageList' ) )
{
    $isRemoveActive = true;
    $removeLanguageList = $Module->actionParameter( 'SelectedLanguageList' );
    if ( $Module->isCurrentAction( 'RemoveLanguageConfirmation' ) )
    {
        foreach ( $removeLanguageList as $removeLanguage )
        {
            $version->removeTranslation( $removeLanguage );
        }
        $isRemoveActive = false;
    }
    else
    {
        $removeLanguageArray = array();
        foreach ( $removeLanguageList as $removeLanguage )
        {
            $removeLanguageArray[] = new eZContentObjectTranslation( $ObjectID, $EditVersion, $removeLanguage );
        }
    }
}

$classID = $object->attribute( 'contentclass_id' );
$class =& eZContentClass::fetch( $classID );
$originalContentAttributes =& $version->contentObjectAttributes();
$originalLocale =& eZLocale::instance( eZContentObject::defaultLanguage() );

$translateContentAttributes = false;
$translateContentMap = false;
$translateLocale = false;

if ( $createLanguage !== false )
{
    // Create a new language
    unset( $translateContentAttributes );
    $translateContentAttributes = $originalContentAttributes;
    foreach ( array_keys( $translateContentAttributes ) as $contentAttributeKey )
    {
        $contentAttribute =& $translateContentAttributes[$contentAttributeKey];
        $contentAttribute->setAttribute( 'id', null );
        $contentAttribute->setAttribute( 'language_code', $createLanguage );
        $contentAttribute->store();
    }
//    $translateContentAttributes =& $version->contentObjectAttributes( $translateToLanguage );
}

if ( $translateToLanguage !== false )
{
    $translateContentAttributes =& $version->contentObjectAttributes( $translateToLanguage );
    if ( $translateContentAttributes === null or
         count( $translateContentAttributes ) == 0 )
        $translateToLanguage = false;
}

$redirectionAllowed = true;
$unvalidatedAttributes = false;
if ( $translateToLanguage !== false )
{
    $translateLocale =& eZLocale::instance( $translateToLanguage );

    $translateContentMap = array();
    foreach ( array_keys( $translateContentAttributes ) as $contentAttributeKey )
    {
        $contentAttribute =& $translateContentAttributes[$contentAttributeKey];
        $translateContentMap[$contentAttribute->attribute( 'contentclassattribute_id' )] =& $contentAttribute;
    }

    foreach ( array_keys( $originalContentAttributes ) as $originalContentAttributeKey )
    {
        $originalContentAttribute =& $originalContentAttributes[$originalContentAttributeKey];
        $originalContentAttributeID = $originalContentAttribute->attribute( 'contentclassattribute_id' );
        if ( !isset( $translateContentMap[$originalContentAttributeID] ) )
            $translateContentMap[$originalContentAttributeID] = false;
    }
}

if ( $activeTranslation )
{
    $storeActions = array( 'Store', 'EditObject', 'AddLanguage', 'RemoveLanguage', 'EditLanguage' );

    $inputValidated = true;
    $storeRequired = in_array( $Module->currentAction(), $storeActions );

    if ( $storeRequired )
    {
        $unvalidatedAttributes = array();
        foreach ( array_keys( $translateContentAttributes ) as $translateContentAttributeKey )
        {
            $contentObjectAttribute =& $translateContentAttributes[$translateContentAttributeKey];
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $dataType =& $contentClassAttribute->dataType();
            $dataProperties = $dataType->attribute( 'properties' );
            if ( $dataProperties['translation_allowed'] )
            {
                if ( $contentObjectAttribute->validateInput( $http, 'ContentObjectAttribute' ) == false )
                {
                    eZDebug::writeDebug( 'Validating ' . $contentObjectAttribute->attribute( 'id' ) . ' failed' );
                    $inputValidated = false;
                    $unvalidatedAttributes[] = array( 'identifier' => $contentClassAttribute->attribute( 'identifier' ),
                                                      'name' => $contentClassAttribute->attribute( 'name' ),
                                                      'description' => $contentObjectAttribute->attribute( 'validation_log' ),
                                                      'id' => $contentObjectAttribute->attribute( 'id' ) );
                }
                else
                {
                    eZDebug::writeDebug( 'Validating ' . $contentObjectAttribute->attribute( 'id' ) . ' success' );
                }
                $contentObjectAttribute->fetchInput( $http, 'ContentObjectAttribute' );
            }
        }

        if ( $inputValidated )
        {
            foreach( array_keys( $translateContentAttributes ) as $translateContentAttributeKey )
            {
                $contentObjectAttribute =& $translateContentAttributes[$translateContentAttributeKey];
                $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
                $dataType =& $contentClassAttribute->dataType();
                $dataProperties = $dataType->attribute( 'properties' );
                if ( $dataProperties['translation_allowed'] )
                {
                    $contentObjectAttribute->store();
                }
            }
            $object->store();
        }

        if ( !$inputValidated )
        {
            $isRemoveActive = false;
            $redirectionAllowed = false;
        }
    }
}

if ( $redirectionAllowed and
     $redirection !== false )
{
    if ( is_string( $redirection ) )
        return $Module->redirectTo( $redirection );
    else if ( is_array( $redirection ) )
        return $Module->redirectToView( $redirection['view'], $redirection['parameters'], $redirection['unordered_parameters'] );
}

$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'edit_version', $EditVersion );
$tpl->setVariable( 'content_version', $version );
$tpl->setVariable( 'translation_language', $translateToLanguage );
$tpl->setVariable( 'translation_locale', $translateLocale );
$tpl->setVariable( 'original_locale', $originalLocale );

$tpl->setVariableRef( 'content_attributes', $originalContentAttributes );
$tpl->setVariableRef( 'content_attributes_language', $translateContentAttributes );
$tpl->setVariableRef( 'content_attribute_map', $translateContentMap );

$tpl->setVariable( 'validation', array( 'attributes' => $unvalidatedAttributes,
                                        'processed' => is_array( $unvalidatedAttributes ),
                                        'language_code' => $activeTranslation,
                                        'locale' => $activeTranslationLocale ) );

$tpl->setVariable( 'is_remove_active', $isRemoveActive );
$tpl->setVariable( 'remove_language_list', $removeLanguageArray );

$Result = array();
$Result['content'] =& $tpl->fetch( 'design:content/translate.tpl' );
$Result['path'] = array( array( 'text' => ezi18n( 'kernel/content', 'Translate' ),
                                'url' => false ),
                         array( 'text' => $object->attribute( 'name' ),
                                'url' => false ) );

?>
