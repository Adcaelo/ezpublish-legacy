<?php
//
// Definition of eZXMLText class
//
// Created on: <28-Jan-2003 12:56:49 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.7.x
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

/*! \file ezxmltext.php
*/

/*!
  \class eZXMLText ezxmltext.php
  \ingroup eZDatatype
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

    function attributes()
    {
        return array( 'input',
                      'output',
                      'pdf_output',
                      'xml_data',
                      'is_empty' );
    }

    function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
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

            case 'pdf_output' :
            {
                if ( $this->XMLOutputHandler === null )
                {
                    $this->XMLOutputHandler =& $this->outputHandler( $this->XMLData, 'ezpdf' );
                }
                return $this->XMLOutputHandler;
            }break;

            case 'xml_data' :
            {
                return $this->XMLData;
            }break;

            case 'is_empty' :
            {
                $isEmpty = true;
                $xml = new eZXML();
                $dom =& $xml->domTree( $this->XMLData, array(), true );
                if ( $dom )
                {
                    $node = $dom->get_elements_by_tagname( "section" );

                    $sectionNode = $node[0];
                    if ( ( get_class( $sectionNode ) == "ezdomnode" ) or
                         ( get_class( $sectionNode ) == "domelement" ) )
                    {
                        $children = $sectionNode->children();
                        if ( count( $children ) > 0 )
                            $isEmpty = false;
                    }
                }
                return $isEmpty;
            }break;

            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", 'eZXMLText::attribute' );
                $retValue = null;
                return $retValue;
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
