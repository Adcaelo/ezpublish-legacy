<?php
//
// Definition of eZXMLText class
//
// Created on: <28-Jan-2003 12:56:49 bf>
//
// Copyright (C) 1999-2002 eZ systems as. All rights reserved.
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

/*! \file ezxmltext.php
*/

/*!
  \class eZXMLText
  \brief The class eZXMLText handles XML text data type instances

*/

class eZXMLText
{
    function eZXMLText( &$xmlData, $contentObjectAttribute )
    {
        $this->XMLData =& $xmlData;
        $this->ContentObjectAttribute = $contentObjectAttribute;
        $this->XMLInputHandler = null;
        $this->XMLOutputHandler = null;
    }

    function hasAttribute( $name )
    {
        if ( $name == 'input' or
             $name == 'output' or
             $name == 'xml_data' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function &attribute( $name )
    {
        switch ( $name )
        {
            case 'input' :
            {
                if ( $this->XMLInputHandler === null )
                {
                    $this->XMLInputHandler =& $this->inputHandler( $this->XMLData );
                }
                return $this->XMLInputHandler;
            }break;

            case 'output' :
            {
                if ( $this->XMLOutputHandler === null )
                {
                    $this->XMLOutputHandler =& $this->outputHandler( $this->XMLData );
                }
                return $this->XMLOutputHandler;
            }break;

            case 'xml_data' :
            {
                return $this->XMLData;
            }break;
        }
    }

    function &inputHandler( &$xmlData, $type = false, $useAlias = true )
    {
        $inputDefinition = array( 'ini-name' => 'ezxml.ini',
                                  'repository-group' => 'HandlerSettings',
                                  'repository-variable' => 'Repositories',
                                  'extension-group' => 'HandlerSettings',
                                  'extension-variable' => 'ExtensionRepositories',
                                  'type-group' => 'InputSettings',
                                  'type-variable' => 'Handler',
                                  'subdir' => 'input',
                                  'type-directory' => false,
                                  'extension-subdir' => 'ezxmltext/handlers/input',
                                  'suffix-name' => 'xmlinput.php' );
        if ( $type !== false )
            $inputDefinition['type'] = $type;
        if ( $useAlias )
        {
            $inputDefinition['alias-group'] = 'InputSettings';
            $inputDefinition['alias-variable'] = 'Alias';
        }
        $inputHandler =& eZXMLText::fetchHandler( $inputDefinition,
                                                  'XMLInput',
                                                  $xmlData );
        if ( $inputHandler === null )
        {
            include_once( 'kernel/classes/datatypes/ezxmltext/handlers/input/ezsimplifiedxmlinput.php' );
            $inputHandler = new eZSimplifiedXMLInput( $this->XMLData, false, $this->ContentObjectAttribute );
        }
        return $inputHandler;
    }

    function &outputHandler( &$xmlData, $type = false, $useAlias = true )
    {
        $outputDefinition = array( 'ini-name' => 'ezxml.ini',
                                   'repository-group' => 'HandlerSettings',
                                   'repository-variable' => 'Repositories',
                                   'extension-group' => 'HandlerSettings',
                                   'extension-variable' => 'ExtensionRepositories',
                                   'type-group' => 'OutputSettings',
                                   'type-variable' => 'Handler',
                                   'subdir' => 'output',
                                   'type-directory' => false,
                                   'extension-subdir' => 'ezxmltext/handlers/output',
                                   'suffix-name' => 'xmloutput.php' );
        if ( $type !== false )
            $outputDefinition['type'] = $type;
        if ( $useAlias )
        {
            $outputDefinition['alias-group'] = 'OutputSettings';
            $outputDefinition['alias-variable'] = 'Alias';
        }
        $outputHandler = eZXMLText::fetchHandler( $outputDefinition,
                                                  'XMLOutput',
                                                  $xmlData );
        if ( $outputHandler === null )
        {
            include_once( 'kernel/classes/datatypes/ezxmltext/handlers/output/ezxhtmlxmloutput.php' );
            $outputHandler = new eZXHTMLXMLOutput( $this->XMLData, false );
        }
        return $outputHandler;
    }

    function &fetchHandler( $definition, $classSuffix, &$xmlData )
    {
        $handler = null;
        if ( eZExtension::findExtensionType( $definition,
                                             $out ) )
        {
            $filePath = $out['found-file-path'];
            include_once( $filePath );
            $class = $out['type'] . $classSuffix;
            $handlerValid = false;
            $aliasedType = false;
            if ( $out['original-type'] != $out['type'] )
                $aliasedType = $out['original-type'];
            if( class_exists( $class ) )
            {
                $handler = new $class( $xmlData, $aliasedType, $this->ContentObjectAttribute );
                if ( $handler->isValid() )
                    $handlerValid = true;
            }
            else
                eZDebug::writeError( "Could not instantiate class '$class', it is not defined",
                                     'eZXMLText::fetchHandler' );
            if ( !$handlerValid and
                 $out['type'] != $out['original-type'] and
                 isset( $definition['alias-group'] ) and
                 isset( $definition['alias-variable'] ) )
            {
                unset( $definition['alias-group'] );
                unset( $definition['alias-variable'] );
                if ( eZExtension::findExtensionType( $definition,
                                                     $out ) )
                {
                    $filePath = $out['found-file-path'];
                    include_once( $filePath );
                    $class = $out['type'] . $classSuffix;
                    $handlerValid = false;
                    if( class_exists( $class ) )
                    {
                        $handler = new $class( $xmlData, false, $this->ContentObjectAttribute );
                        if ( $handler->isValid() )
                            $handlerValid = true;
                    }
                    else
                        eZDebug::writeError( "Could not instantiate class '$class', it is not defined",
                                             'eZXMLText::fetchHandler' );
                    if ( !$handlerValid )
                    {
                        $handler = null;
                    }
                }
            }
        }
        return $handler;
    }

    /// Contains the XML data
    var $XMLData;

    var $XMLInputHandler;
    var $XMLOutputHandler;
    var $XMLAttributeID;
    var $ContentObjectAttribute;
}

?>
