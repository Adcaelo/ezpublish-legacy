<?php
//
// Definition of eZTemplateMultiPassParser class
//
// Created on: <26-Nov-2002 17:25:44 amos>
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

/*! \file eztemplatemultipassparser.php
*/

/*!
  \class eZTemplateMultiPassParser eztemplatemultipassparser.php
  \brief The class eZTemplateMultiPassParser does

*/

include_once( 'lib/eztemplate/classes/eztemplateparser.php' );
include_once( 'lib/eztemplate/classes/eztemplateelementparser.php' );
include_once( 'lib/eztemplate/classes/eztemplate.php' );

class eZTemplateMultiPassParser extends eZTemplateParser
{
    /*!
     Constructor
    */
    function eZTemplateMultiPassParser()
    {
        $this->ElementParser = eZTemplateElementParser::instance();
    }


    /*!
     Parses the template file $sourceText. See the description of this class
     for more information on the parsing process.

     \todo Use indexes in pass 1 and 2 instead of substrings, this means that strings are not extracted
     until they are needed.
    */
    function parse( &$tpl, &$sourceText, &$rootElement, $rootNamespace, $relation )
    {
        $relatedResource = $relation['resource'];
        $relatedTemplateName = $relation['template-name'];

//         $tpl->setRelation( $rootElement, $relatedResource, $relatedTemplateName );
//         $tpl->CurrentRelatedResource = $relatedResource;
//         $tpl->CurrentRelatedTemplateName = $relatedTemplateName;
        $currentRoot =& $rootElement;
        $leftDelimiter = $tpl->LDelim;
        $rightDelimiter = $tpl->RDelim;
        $sourceLength = strlen( $sourceText );
        $sourcePosition = 0;

        eZDebug::accumulatorStart( 'template_multi_parser_1', 'template_total', 'Template parser: create text elements' );
        $textElements =& $this->parseIntoTextElements( $tpl, $sourceText, $sourcePosition,
                                                       $leftDelimiter, $rightDelimiter, $sourceLength,
                                                       $relatedTemplateName );
        eZDebug::accumulatorStop( 'template_multi_parser_1' );

        eZDebug::accumulatorStart( 'template_multi_parser_2', 'template_total', 'Template parser: remove whitespace' );
        $textElements =& $this->parseWhitespaceRemoval( $tpl, $textElements );
        eZDebug::accumulatorStop( 'template_multi_parser_2' );

        eZDebug::accumulatorStart( 'template_multi_parser_3', 'template_total', 'Template parser: construct tree' );
        $this->parseIntoTree( $tpl, $textElements, $currentRoot,
                              $rootNamespace, $relatedResource, $relatedTemplateName );
        eZDebug::accumulatorStop( 'template_multi_parser_3' );
    }

    function gotoEndPosition( $text, $line, $column, &$endLine, &$endColumn )
    {
        $lines = preg_split( "#\r\n|\r|\n#", $text );
        if ( count( $lines ) > 0 )
        {
            $endLine = $line + count( $lines ) - 1;
            $lastLine = $lines[count($lines)-1];
            if ( count( $lines ) > 1 )
                $endColumn = strlen( $lastLine );
            else
                $endColumn = $column + strlen( $lastLine );
        }
        else
        {
            $endLine = $line;
            $endColumn = $column;
        }
    }

    function &parseIntoTextElements( &$tpl, $sourceText, $sourcePosition,
                                     $leftDelimiter, $rightDelimiter, $sourceLength,
                                     $relatedTemplateName )
    {
        if ( $tpl->ShowDetails )
            eZDebug::addTimingPoint( "Parse pass 1 (simple tag parsing)" );
        $currentLine = 1;
        $currentColumn = 0;
        $textElements = array();
        while( $sourcePosition < $sourceLength )
        {
            $tagPos = strpos( $sourceText, $leftDelimiter, $sourcePosition );
            if ( $tagPos === false )
            {
                // No more tags
                unset( $data );
                $data =& substr( $sourceText, $sourcePosition );
                $this->gotoEndPosition( $data, $currentLine, $currentColumn, $endLine, $endColumn );
                $textElements[] = array( "text" => $data,
                                         "type" => EZ_ELEMENT_TEXT,
                                         'placement' => array( 'templatefile' => $relatedTemplateName,
                                                               'start' => array( 'line' => $currentLine,
                                                                                 'column' => $currentColumn ),
                                                               'stop' => array( 'line' => $endLine,
                                                                                'column' => $endColumn ) ) );
                $sourcePosition = $sourceLength;
                $currentLine = $endLine;
                $currentColumn = $endColumn;
            }
            else
            {
                $blockStart = $tagPos;
                $tagPos++;
                if ( $tagPos < $sourceLength and
                     $sourceText[$tagPos] == "*" ) // Comment
                {
                    $endPos = strpos( $sourceText, "*$rightDelimiter", $tagPos + 1 );
                    $len = $endPos - $tagPos;
                    if ( $sourcePosition < $blockStart )
                    {
                        // Add text before tag.
                        unset( $data );
                        $data =& substr( $sourceText, $sourcePosition, $blockStart - $sourcePosition );
                        $this->gotoEndPosition( $data, $currentLine, $currentColumn, $endLine, $endColumn );
                        $textElements[] = array( "text" => $data,
                                                 "type" => EZ_ELEMENT_TEXT,
                                                 'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                       'start' => array( 'line' => $currentLine,
                                                                                         'column' => $currentColumn ),
                                                                       'stop' => array( 'line' => $endLine,
                                                                                        'column' => $endColumn ) ) );
                        $currentLine = $endLine;
                        $currentColumn = $endColumn;
                    }
                    if ( $endPos === false )
                    {
                        $endPos = $sourceLength;
                        $blockEnd = $sourceLength;
                    }
                    else
                    {
                        $blockEnd = $endPos + 2;
                    }
                    $comment_text = substr( $sourceText, $tagPos + 1, $endPos - $tagPos - 1 );
                    $this->gotoEndPosition( $comment_text, $currentLine, $currentColumn, $endLine, $endColumn );
                    $textElements[] = array( "text" => $comment_text,
                                             "type" => EZ_ELEMENT_COMMENT,
                                             'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                   'start' => array( 'line' => $currentLine,
                                                                                     'column' => $currentColumn ),
                                                                   'stop' => array( 'line' => $endLine,
                                                                                    'column' => $endColumn ) ) );
                    if ( $sourcePosition < $blockEnd )
                        $sourcePosition = $blockEnd;
                    $currentLine = $endLine;
                    $currentColumn = $endColumn;
//                     eZDebug::writeDebug( "eZTemplate: Comment: $comment" );
                }
                else
                {
                    $tmp_pos = $tagPos;
                    while( ( $endPos = strpos( $sourceText, $rightDelimiter, $tmp_pos ) ) !== false )
                    {
                        if ( $sourceText[$endPos-1] != "\\" )
                            break;
                        $tmp_pos = $endPos + 1;
                    }
                    if ( $endPos === false )
                    {
                        // Unterminated tag
                        $tpl->warning( "parse()", "Unterminated tag at pos $tagPos" );
                        unset( $data );
                        $data =& substr( $sourceText, $sourcePosition );
                        $this->gotoEndPosition( $data, $currentLine, $currentColumn, $endLine, $endColumn );
                        $textElements[] = array( "text" => $data,
                                                 "type" => EZ_ELEMENT_TEXT,
                                                 'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                       'start' => array( 'line' => $currentLine,
                                                                                         'column' => $currentColumn ),
                                                                       'stop' => array( 'line' => $endLine,
                                                                                        'column' => $endColumn ) ) );
                        $sourcePosition = $sourceLength;
                        $currentLine = $endLine;
                        $currentColumn = $endColumn;
                    }
                    else
                    {
                        $blockEnd = $endPos + 1;
                        $len = $endPos - $tagPos;
                        if ( $sourcePosition < $blockStart )
                        {
                            // Add text before tag.
                            unset( $data );
                            $data =& substr( $sourceText, $sourcePosition, $blockStart - $sourcePosition );
                            $this->gotoEndPosition( $data, $currentLine, $currentColumn, $endLine, $endColumn );
                            $textElements[] = array( "text" => $data,
                                                     "type" => EZ_ELEMENT_TEXT,
                                                     'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                           'start' => array( 'line' => $currentLine,
                                                                                             'column' => $currentColumn ),
                                                                           'stop' => array( 'line' => $endLine,
                                                                                            'column' => $endColumn ) ) );
                            $currentLine = $endLine;
                            $currentColumn = $endColumn;
                        }

                        unset( $tag );
                        $tag = substr( $sourceText, $tagPos, $len );
                        $tag = preg_replace( "/\\\\[}]/", "}", $tag );
                        $isEndTag = false;
                        $isSingleTag = false;

                        if ( $tag[0] == "/" )
                        {
                            $isEndTag = true;
                            $tag = substr( $tag, 1 );
                        }
                        else if ( $tag[strlen($tag) - 1] == "/" )
                        {
                            $isSingleTag = true;
                            $tag = substr( $tag, 0, strlen( $tag ) - 1 );
                        }

                        $this->gotoEndPosition( $tag, $currentLine, $currentColumn, $endLine, $endColumn );
                        if ( $tag[0] == "$" or
                             $tag[0] == "\"" or
                             $tag[0] == "'" or
                             is_numeric( $tag[0] ) or
                             ( $tag[0] == '-' and
                               isset( $tag[1] ) and
                               is_numeric( $tag[1] ) ) or
                             preg_match( "/^[a-z0-9]+\(/", $tag ) )
                        {
                            $textElements[] = array( "text" => $tag,
                                                     "type" => EZ_ELEMENT_VARIABLE,
                                                     'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                           'start' => array( 'line' => $currentLine,
                                                                                             'column' => $currentColumn ),
                                                                           'stop' => array( 'line' => $endLine,
                                                                                            'column' => $endColumn ) ) );
                        }
                        else
                        {
                            $type = EZ_ELEMENT_NORMAL_TAG;
                            if ( $isEndTag )
                                $type = EZ_ELEMENT_END_TAG;
                            else if ( $isSingleTag )
                                $type = EZ_ELEMENT_SINGLE_TAG;
                            $spacepos = strpos( $tag, " " );
                            if ( $spacepos === false )
                                $name = $tag;
                            else
                                $name = substr( $tag, 0, $spacepos );
                            $textElements[] = array( "text" => $tag,
                                                     "name" => $name,
                                                     "type" => $type,
                                                     'placement' => array( 'templatefile' => $relatedTemplateName,
                                                                           'start' => array( 'line' => $currentLine,
                                                                                             'column' => $currentColumn ),
                                                                           'stop' => array( 'line' => $endLine,
                                                                                            'column' => $endColumn ) ) );
                        }

                        if ( $sourcePosition < $blockEnd )
                            $sourcePosition = $blockEnd;
                        $currentLine = $endLine;
                        $currentColumn = $endColumn;
                    }
                }
            }
        }
        return $textElements;
    }

    function &parseWhitespaceRemoval( &$tpl, &$textElements )
    {
        if ( $tpl->ShowDetails )
            eZDebug::addTimingPoint( "Parse pass 2 (whitespace removal)" );
        $tempTextElements = array();
        reset( $textElements );
        while ( ( $key = key( $textElements ) ) !== null )
        {
            unset( $element );
            $element =& $textElements[$key];
            next( $textElements );
            $next_key = key( $textElements );
            unset( $next_element );
            $next_element = null;
            if ( $next_key !== null )
                $next_element =& $textElements[$next_key];
            switch ( $element["type"] )
            {
                case EZ_ELEMENT_COMMENT:
                {
                    // Ignore comments
                } break;
                case EZ_ELEMENT_TEXT:
                case EZ_ELEMENT_VARIABLE:
                {
                    if ( $next_element !== null )
                    {
                        switch ( $next_element["type"] )
                        {
                            case EZ_ELEMENT_END_TAG:
                            case EZ_ELEMENT_SINGLE_TAG:
                            case EZ_ELEMENT_NORMAL_TAG:
                            {
                                unset( $text );
                                $text =& $element["text"];
                                $text_cnt = strlen( $text );
                                if ( $text_cnt > 0 )
                                {
                                    $char = $text[$text_cnt - 1];
                                    if ( $char == "\n" )
                                    {
                                        $text = substr( $text, 0, $text_cnt - 1 );
                                    }
                                }
                            } break;
                        }
                    }
                    if ( !empty( $element["text"] ) )
                        $tempTextElements[] =& $element;
                } break;
                case EZ_ELEMENT_END_TAG:
                case EZ_ELEMENT_SINGLE_TAG:
                case EZ_ELEMENT_NORMAL_TAG:
                {
                    unset( $name );
                    $name =& $element["name"];
                    $startLine = false;
                    $startColumn = false;
                    $stopLine = false;
                    $stopColumn = false;
                    $templateFile = false;
                    $hasStartPlacement = false;
                    if ( isset( $tpl->Literals[$name] ) )
                    {
                        unset( $text );
                        $text = "";
                        $key = key( $textElements );
                        while ( $key !== null )
                        {
                            unset( $element );
                            $element =& $textElements[$key];
                            $elementPlacement = $element['placement'];
                            if ( !$hasStartPlacement )
                            {
                                $startLine = $elementPlacement['start']['line'];
                                $startColumn = $elementPlacement['start']['column'];
                                $stopLine = $elementPlacement['stop']['line'];
                                $stopColumn = $elementPlacement['stop']['column'];
                                $templateFile = $elementPlacement['templatefile'];
                                $hasStartPlacement = true;
                            }
                            else
                            {
                                $stopLine = $elementPlacement['stop']['line'];
                                $stopColumn = $elementPlacement['stop']['column'];
                            }
                            switch ( $element["type"] )
                            {
                                case EZ_ELEMENT_END_TAG:
                                {
                                    if ( $element["name"] == $name )
                                    {
                                        next( $textElements );
                                        $key = null;
                                        $tempTextElements[] = array( "text" => $text,
                                                                     "type" => EZ_ELEMENT_TEXT,
                                                                     'placement' => array( 'templatefile' => $templateFile,
                                                                                           'start' => array( 'line' => $currentLine,
                                                                                                             'column' => $currentColumn ),
                                                                                           'stop' => array( 'line' => $stopLine,
                                                                                                            'column' => $stopColumn ) ) );
                                        $startLine = false;
                                        $startColumn = false;
                                        $stopLine = false;
                                        $stopColumn = false;
                                        $templateFile = false;
                                        $hasStartPlacement = false;
                                    }
                                    else
                                    {
                                        $text .= $leftDelimiter . "/" . $element["text"] . $rightDelimiter;
                                        next( $textElements );
                                        $key = key( $textElements );
                                    }
                                } break;
                                case EZ_ELEMENT_NORMAL_TAG:
                                {
                                    $text .= $leftDelimiter . $element["text"] . $rightDelimiter;
                                    next( $textElements );
                                    $key = key( $textElements );
                                } break;
                                case EZ_ELEMENT_SINGLE_TAG:
                                {
                                    $text .= $leftDelimiter . $element["text"] . "/" . $rightDelimiter;
                                    next( $textElements );
                                    $key = key( $textElements );
                                } break;
                                case EZ_ELEMENT_COMMENT:
                                {
                                    $text .= $leftDelimiter . "*" . $element["text"] . "*$rightDelimiter";
                                    next( $textElements );
                                    $key = key( $textElements );
                                } break;
                                default:
                                {
                                    $text .= $element["text"];
                                    next( $textElements );
                                    $key = key( $textElements );
                                } break;
                            }
                        }
                    }
                    else
                    {
                        if ( $next_element !== null )
                        {
                            switch ( $next_element["type"] )
                            {
                                case EZ_ELEMENT_TEXT:
                                case EZ_ELEMENT_VARIABLE:
                                {
                                    unset( $text );
                                    $text =& $next_element["text"];
                                    $text_cnt = strlen( $text );
                                    if ( $text_cnt > 0 )
                                    {
                                        $char = $text[0];
                                        if ( $char == "\n" )
                                        {
                                            $text = substr( $text, 1 );
                                        }
                                    }
                                } break;
                            }
                        }
                        $tempTextElements[] =& $element;
                    }
                } break;
            }
        }
        return $tempTextElements;
    }

    function appendChild( &$root, &$node )
    {
        if ( !is_array( $root[1] ) )
            $root[1] = array();
        $root[1][] =& $node;
    }

    function parseIntoTree( &$tpl, &$textElements, &$treeRoot,
                            $rootNamespace, $relatedResource, $relatedTemplateName )
    {
        $currentRoot =& $treeRoot;
        if ( $tpl->ShowDetails )
            eZDebug::addTimingPoint( "Parse pass 3 (build tree)" );

        $tagStack = array();

        reset( $textElements );
        while ( ( $key = key( $textElements ) ) !== null )
        {
            unset( $element );
            $element =& $textElements[$key];
            $elementPlacement = $element['placement'];
            $startLine = $elementPlacement['start']['line'];
            $startColumn = $elementPlacement['start']['column'];
            $stopLine = $elementPlacement['stop']['line'];
            $stopColumn = $elementPlacement['stop']['column'];
            $templateFile = $elementPlacement['templatefile'];
            $placement = array( array( $startLine,
                                       $startColumn ),
                                array( $stopLine,
                                       $stopColumn ),
                                $templateFile );
            switch ( $element["type"] )
            {
                case EZ_ELEMENT_TEXT:
                {
                    unset( $node );
                    $node = array( EZ_TEMPLATE_NODE_TEXT,
                                   false,
                                   $element['text'],
                                   $placement );
//                     $node = new eZTemplateTextElement( $element["text"] );
//                     $tpl->setRelation( $node, $relatedResource, $relatedTemplateName );
//                     $currentRoot->appendChild( $node );
                    $this->appendChild( $currentRoot, $node );
                } break;
                case EZ_ELEMENT_VARIABLE:
                {
                    $text =& $element["text"];
                    $text_len = strlen( $text );
                    $var_data =& $this->ElementParser->parseVariableTag( $tpl, $text, 0, $var_end, $text_len, $rootNamespace );

                    unset( $node );
                    $node = array( EZ_TEMPLATE_NODE_VARIABLE,
                                   false,
                                   $var_data,
                                   $placement );
//                     $node =& new eZTemplateVariableElement( $var_data );
//                     $tpl->setRelation( $node, $relatedResource, $relatedTemplateName );
//                     $currentRoot->appendChild( $node );
                    $this->appendChild( $currentRoot, $node );
                    if ( $var_end < $text_len )
                    {
                        $tpl->warning( "", "Junk at variable end: '" . substr( $text, $var_end, $text_len - $var_end ) . "' (" . substr( $text, 0, $var_end ) . ")" );
                    }
                } break;
                case EZ_ELEMENT_SINGLE_TAG:
                case EZ_ELEMENT_NORMAL_TAG:
                case EZ_ELEMENT_END_TAG:
                {
                    unset( $text );
                    unset( $type );
                    $text =& $element["text"];
                    $text_len = strlen( $text );
                    $type =& $element["type"];

                    $ident_pos = $this->ElementParser->identifierEndPosition( $tpl, $text, 0, $text_len );
                    $tag = substr( $text, 0, $ident_pos - 0 );
                    $attr_pos = $ident_pos;
                    unset( $args );
                    $args = array();
                    $lastPosition = false;
                    while ( $attr_pos < $text_len )
                    {
                        if ( $lastPosition !== false and
                             $lastPosition == $attr_pos )
                        {
                            break;
                        }
                        $lastPosition = $attr_pos;
                        $attr_pos_start = $this->ElementParser->whitespaceEndPos( $tpl, $text, $attr_pos, $text_len );
                        if ( $attr_pos_start == $attr_pos and
                             $attr_pos_start < $text_len )
                        {
                            $tpl->error( "", "Expected whitespace, got: '" . substr( $text, $attr_pos ) . "'" );
                            break;
                        }
                        $attr_pos = $attr_pos_start;
                        $attr_name_pos = $this->ElementParser->identifierEndPosition( $tpl, $text, $attr_pos, $text_len );
                        $attr_name = substr( $text, $attr_pos, $attr_name_pos - $attr_pos );
                        if ( $attr_name_pos >= $text_len or
                             ( $text[$attr_name_pos] != '=' and
                               preg_match( "/[ \t\r\n]/", $text[$attr_name_pos] ) ) )
                        {
                            unset( $var_data );
                            $var_data = array();
                            $var_data[] = array( EZ_TEMPLATE_TYPE_NUMERIC, // type
                                                 true, // content
                                                 false // debug
                                                 );
                            $args[$attr_name] = $var_data;
                            $attr_pos = $attr_name_pos;
                            continue;
//                             $tpl->error( "", "Unterminated parameter in function '$tag' ($text)" );
//                             break;
                        }
                        if ( $text[$attr_name_pos] != "=" )
                        {
                            $placement = $element['placement'];
                            $startLine = $placement['start']['line'];
                            $startColumn = $placement['start']['column'];
                            $subText = substr( $text, 0, $attr_name_pos );
                            $this->gotoEndPosition( $subText, $startLine, $startColumn, $currentLine, $currentColumn );
                            $tpl->error( "parser error @ $relatedTemplateName:$currentLine" . "[$currentColumn]", "Invalid parameter characters in function '$tag': '" .
                                          substr( $text, $attr_name_pos )  . "'" );
                            break;
                        }
                        ++$attr_name_pos;
                        unset( $var_data );
                        $var_data =& $this->ElementParser->parseVariableTag( $tpl, $text, $attr_name_pos, $var_end, $text_len, $rootNamespace );
                        $args[$attr_name] = $var_data;
                        $attr_pos = $var_end;
                    }

                    if ( $type == EZ_ELEMENT_END_TAG and count( $args ) > 0 )
                    {
                        $tpl->warning( "", "End tag \"$tag\" cannot have attributes" );
                        $args = array();
                    }

                    if ( $type == EZ_ELEMENT_NORMAL_TAG )
                    {
                        unset( $node );
                        $node = array( EZ_TEMPLATE_NODE_FUNCTION,
                                       false,
                                       $tag,
                                       $args,
                                       $placement );
//                         $node =& new eZTemplateFunctionElement( $tag, $args );
//                         $tpl->setRelation( $node, $relatedResource, $relatedTemplateName );
//                         $currentRoot->appendChild( $node );
                        $this->appendChild( $currentRoot, $node );
                        $has_children = true;
                        if ( isset( $tpl->FunctionAttributes[$tag] ) )
                        {
//                             eZDebug::writeDebug( $tpl->FunctionAttributes[$tag], "\$tpl->FunctionAttributes[$tag] #1" );
                            if ( is_array( $tpl->FunctionAttributes[$tag] ) )
                                $tpl->loadAndRegisterFunctions( $tpl->FunctionAttributes[$tag] );
                            $has_children = $tpl->FunctionAttributes[$tag];
                        }
                        else if ( isset( $tpl->Functions[$tag] ) )
                        {
//                             eZDebug::writeDebug( $tpl->Functions[$tag], "\$tpl->Functions[$tag] #1" );
                            if ( is_array( $tpl->Functions[$tag] ) )
                                $tpl->loadAndRegisterFunctions( $tpl->Functions[$tag] );
                            $has_children = $tpl->hasChildren( $tpl->Functions[$tag], $tag );
                        }
                        if ( $has_children )
                        {
                            $tagStack[] = array( "Root" => &$currentRoot,
                                                 "Tag" => $tag );
                            unset( $currentRoot );
                            $currentRoot =& $node;
                        }
                    }
                    else if ( $type == EZ_ELEMENT_END_TAG )
                    {
                        $has_children = true;
                        if ( isset( $tpl->FunctionAttributes[$tag] ) )
                        {
//                             eZDebug::writeDebug( $tpl->FunctionAttributes[$tag], "\$tpl->FunctionAttributes[$tag] #2" );
                            if ( is_array( $tpl->FunctionAttributes[$tag] ) )
                                $tpl->loadAndRegisterFunctions( $tpl->FunctionAttributes[$tag] );
                            $has_children = $tpl->FunctionAttributes[$tag];
                        }
                        else if ( isset( $tpl->Functions[$tag] ) )
                        {
//                             eZDebug::writeDebug( $tpl->Functions[$tag], "\$tpl->Functions[$tag] #2" );
                            if ( is_array( $tpl->Functions[$tag] ) )
                                $tpl->loadAndRegisterFunctions( $tpl->Functions[$tag] );
                            $has_children = $tpl->hasChildren( $tpl->Functions[$tag], $tag );
                        }
                        if ( !$has_children )
                        {
                            $tpl->warning( "", "End tag \"$tag\" for function which does not accept children, ignoring tag" );
                        }
                        else
                        {
                            unset( $oldTag );
                            unset( $oldTagName );
                            include_once( "lib/ezutils/classes/ezphpcreator.php" );
//                             eZDebug::writeDebug( eZPHPCreator::variableText( $treeRoot, 0 ), '$treeRoot' );
//                             eZDebug::writeDebug( eZPHPCreator::variableText( $currentRoot, 0 ), '$currentRoot' );
                            $oldTag =& array_pop( $tagStack );
                            $oldTagName = $oldTag["Tag"];
                            unset( $currentRoot );
                            $currentRoot =& $oldTag["Root"];

                            if ( $oldTagName != $tag )
                                $tpl->warning( "", "Unterminated tag \"$oldTagName\" does not match tag \"$tag\" at $blockStart" );
                        }
                    }
                    else // EZ_ELEMENT_SINGLE_TAG
                    {
                        unset( $node );
                        $node = array( EZ_TEMPLATE_NODE_FUNCTION,
                                       false,
                                       $tag,
                                       $args,
                                       $placement );
//                         $node =& new eZTemplateFunctionElement( $tag, $args );
//                         $tpl->setRelation( $node, $relatedResource, $relatedTemplateName );
//                         $currentRoot->appendChild( $node );
//                         eZDebug::writeDebug( $currentRoot, '$currentRoot' );
                        $this->appendChild( $currentRoot, $node );
                    }
                    unset( $tag );

                } break;
            }
            next( $textElements );
        }
        unset( $textElements );
        if ( $tpl->ShowDetails )
            eZDebug::addTimingPoint( "Parse pass 3 done" );
    }

    function &instance()
    {
        $instance =& $GLOBALS['eZTemplateMultiPassParserInstance'];
        if ( get_class( $instance ) != 'eztemplatemultipassparser' )
        {
            $instance = new eZTemplateMultiPassParser();
        }
        return $instance;
    }

    /// \privatesection
    var $ElementParser;
}

?>
