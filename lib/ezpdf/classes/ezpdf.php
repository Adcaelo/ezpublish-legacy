<?php
//
// Created on: <26-Aug-2003 15:15:32 kk>
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

/*! \file eztemplateautoload.php
*/

include_once( 'lib/ezpdf/classes/class.ezpdftable.php' );
include_once( 'lib/ezpdf/classes/class.pdf.php' );

//include_once( 'lib/ezutils/classes/eztexttool.php' );

/*!
  \class eZPDF ezpdf.php
  \brief The class eZPDF does
*/

class eZPDF
{

    /*!
     Initializes the object with the name $name, default is "attribute".
    */
    function eZPDF( $name = "pdf" )
    {
        $this->Operators = array( $name );
        $this->Config =& eZINI::instance( 'pdf.ini' );
    }

    /*!
     Returns the template operators.
    */
    function &operatorList()
    {
        return $this->Operators;
    }

    /*!
     See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array( 'operation' => array( 'type' => 'string',
                                            'required' => true,
                                            'default' => '' ) );
    }

    /*!
     Display the variable.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        if ( ! isset( $this->PDF ) )
        {
            $this->createPDF();
        }

        switch ( $namedParameters['operation'] )
        {
            case 'toc':
            {
                $specification = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = '<C:callTOC>';
                eZDebug::writeNotice( 'PDF: Generating TOC', 'eZPDF::modify' );
            } break;

            case 'setfont':
            {
                $font = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = '<ezCall:callFont';

                if ( isset( $font['name'] ) )
                {
                    $operatorValue .= ':name:'. $font['name'];
                }
                if ( isset( $font['size'] ) )
                {
                    $operatorValue .= ':size:'. $font['size'];
                }

                $operatorValue .= '>';

                eZDebug::writeNotice( 'PDF: Changed font, name: '. $font['name'] .', size: '. $font['size'], 'eZPDF::modify' );
            } break;

            case 'table':
            {
                $table = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = str_replace( array( ' ', "\t", "\r\n", "\n" ),
                                              '',
                                              '<ezGroup:callTable>'. $table . '</ezGroup:callTable><C:callNewLine>' );

                eZDebug::writeNotice( 'PDF: Added table to PDF',
                                      'eZPDF::modify' );
            } break;

            case 'header':
            {
                $header = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                if ( !isset( $header['align'] ) )
                    $header['align'] = 'left';

                $operatorValue = '<ezCall:callHeader:level:'. $header['level'] .':size:'. $header['size'] .':justification:'. $header['align'] .':label:'.
                     rawurlencode( $header['text'] ) .'>'. $header['text'] .'</ezCall:callHeader><C:callSpace><C:callNewLine>';

                eZDebug::writeNotice( 'PDF: Added header: '. $header['text'] .', size: '. $header['size'] .
                                      ', align: '. $header['align'] .', level: '. $header['level'],
                                      'eZPDF::modify' );
            } break;

            case 'create':
            {
                $this->createPDF();
            } break;

            case 'newline':
            {
                $operatorValue = '<C:callNewLine>';
            } break;

            case 'newpage':
            {
                $operatorValue = '<C:callNewPage>';

                eZDebug::writeNotice( 'PDF: New page', 'eZPDF::modify' );
            } break;

            case 'image': //TODO
            {
                $image = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $width = isset( $image['width'] ) ? $image['width']: 100;
                $height = isset( $image['height'] ) ? $image['height']: 100;

                $operatorValue = '<C:callImage:src:'. rawurlencode( $image['src'] ) .':width:'. $width .':height:'. $height;

                if ( isset( $image['xOffset'] ) )
                {
                    $operatorValue .= ':xOffset:'. $image['xOffset'];
                }

                $operatorValue .= '>';

                eZDebug::writeNotice( 'PDF: Added Image '.$image['src'].' to PDF file', 'eZPDF::modify' );
            } break;

            case 'anchor':
            {
                $name = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = '<C:callAnchor:'. $name['name'] .':FitH:>';
                eZDebug::writeNotice( 'PDF: Added anchor: '.$name['name'], 'eZPDF::modify' );
            } break;

            case 'link': // external link
            {
                $link = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = '<c:alink:'. $link['url'] .'>'. $link['text'] .'</c:alink>';
                eZDebug::writeNotice( 'PDF: Added link, url: '.$link['url'], 'eZPDF::modify' );
            } break;

            case 'stream':
            {
                $this->PDF->ezStream();
            }

            case 'close':
            {
//                include_once( 'lib/ezutils/classes/eztexttool.php' );
                include_once( 'lib/ezfile/classes/ezfile.php' );
                $filename = 'tmp.pdf';
                eZFile::create( $filename, eZSys::storageDirectory() .'/pdf', $this->PDF->ezOutput() );
                eZDebug::writeNotice( 'PDF file closed and saved to '. eZSys::storageDirectory() .'/pdf/'. $filename, 'eZPDF::modify' );
            } break;

            case 'strike':
            {
                $text = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );
                $operatorValue = '<c:strike>'. $text .'</c:strike>';
                eZDebug::writeNotice( 'Striked text added to PDF: "'. $text .'"', 'eZPDF::modify' );
            } break;

            /* usage : execute/add text to pdf file, pdf(execute,<text>) */
            case 'execute':
            {
                $text = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $text = str_replace( array( ' ', "\n", "\t" ), '', $text );

                $this->PDF->ezText( $text );
                eZDebug::writeNotice( 'Execute text in PDF: "'. $text .'"', 'eZPDF::modify' );
            } break;

            case 'pageNumber':
            {
                $numberDesc = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                if ( isset( $numberDesc['identifier'] ) )
                {
                    $identifier = $numberDesc['identifier'];
                }
                else
                {
                    $identifier = 'main';
                }

                if ( isset( $numberDesc['start'] ) )
                {
                    $operatorValue = '<C:callStartPageCounter:action:start:identifier:'. $identifier .'>';
                }
                else if ( isset( $numberDesc['stop'] ) )
                {
                    $operatorValue = '<C:callStartPageCounter:action:stop:identifier:'. $identifier .'>';
                }
            } break;

            case 'footer':
            case 'header':
            {
                $frameDesc = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue  = '<ezGroup:callFrame';
                $operatorValue .= ':location:'. $namedParameters['operation'];

                if ( $namedParameters['operation'] == 'footer' )
                {
                    $frameType = 'Footer';
                }
                else if( $namedParameters['operation'] == 'header' )
                {
                    $frameType = 'Header';
                }

                if ( isset( $frameDesc['align'] ) )
                {
                    $operatorValue .= ':justification:'. $frameDesc['align'];
                }

                if ( isset( $frameDesc['page'] ) )
                {
                    $operatorValue .= ':page:'. $frameDesc['page'];
                }
                else
                {
                    $operatorValue .= ':page:all';
                }

                $operatorValue .= ':pageOffset:';
                if ( isset( $frameDesc['pageOffset'] ) )
                {
                    $operatorValue .= $frameDesc['pageOffset'];
                }
                else
                {
                    $operatorValue .= $this->Config->variable( $frameType, 'PageOffset' );
                }

                if ( isset( $frameDesc['size'] ) )
                {
                    $operatorValue .= ':size:'. $frameDesc['size'];
                }

                if ( isset( $frameDesc['font'] ) )
                {
                    $operatorValue .= ':font:'. $frameDesc['font'];
                }

                $operatorValue .= '>';

                if ( isset( $frameDesc['text'] ) )
                {
                    $operatorValue .= $frameDesc['text'];
                }

                $operatorValue .= '</ezGroup:callFrame>';

                if ( isset( $frameDesc['margin'] ) )
                {
                    $operatorValue .= '<C:callFrameMargins';

                    $operatorValue .= 'identifier:'. $namedParameters['operation'];

                    $operatorValue .= ':topMargin:';
                    if ( isset( $frameDesc['margin']['top'] ) )
                    {
                        $operatorValue .= $frameDesc['margin']['top'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'TopMargin' );
                    }

                    $operatorValue .= ':bottomMargin:';
                    if ( isset( $frameDesc['margin']['bottom'] ) )
                    {
                        $operatorValue .= $frameDesc['margin']['bottom'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'BottomMargin' );
                    }

                    $operatorValue .= ':leftMargin:';
                    if ( isset( $frameDesc['margin']['left'] ) )
                    {
                        $operatorValue .= $frameDesc['margin']['left'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'LeftMargin' );
                    }

                    $operatorValue .= ':rightMargin:';
                    if ( isset( $frameDesc['margin']['right'] ) )
                    {
                        $operatorValue .= $frameDesc['margin']['right'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'RightMargin' );
                    }

                    $operatorValue .= ':height:';
                    if ( isset( $frameDesc['margin']['height'] ) )
                    {
                        $operatorValue .= $frameDesc['margin']['height'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'Height' );
                    }

                    $operatorValue .= '>';
                }

                if ( isset( $frameDesc['line'] ) )
                {
                    $operatorValue .= '<C:callFrameLine';
                    $operatorValue .= ':location:'. $namedParameters['operation'];

                    $operatorValue .= ':margin:';
                    if( isset( $frameDesc['line']['margin'] ) )
                    {
                        $operatorValue .= $frameDesc['line']['margin'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'LineMargin' );
                    }

                    if ( isset( $frameDesc['line']['leftMargin'] ) )
                    {
                        $operatorValue .= ':leftMargin:'. $frameDesc['line']['leftMargin'];
                    }
                    if ( isset( $frameDesc['line']['rightMargin'] ) )
                    {
                        $operatorValue .= ':rightMargin:'. $frameDesc['line']['rightMargin'];
                    }

                    $operatorValue .= ':pageOffset:';
                    if ( isset( $frameDesc['line']['pageOffset'] ) )
                    {
                        $operatorValue .= $frameDesc['line']['pageOffset'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'PageOffset' );
                    }

                    $operatorValue .= ':page:';
                    if ( isset( $frameDesc['line']['page'] ) )
                    {
                        $operatorValue .= $frameDesc['line']['page'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'Page' );
                    }

                    $operatorValue .= ':thicknes:';
                    if ( isset( $frameDesc['line']['thicknes'] ) )
                    {
                        $operatorValue .= $frameDesc['line']['thicknes'];
                    }
                    else
                    {
                        $operatorValue .= $this->Config->variable( $frameType, 'LineThicknes' );
                    }

                    $operatorValue .= '>';
                }

            } break;

            /* add keyword to pdf document */
            case 'keyword':
            {
                $text = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $text = str_replace( array( ' ', "\n", "\t" ), '', $text );

                $operatorValue = '<C:callKeyword:'. rawurlencode( $text ) .'>';
            } break;

            /* add Keyword index to pdf document */
            case 'createIndex':
            {
                $operatorValue = '<C:callIndex>';

                eZDebug::writeNotice( 'Adding Keyword index to PDF', 'eZPDF::modify' );
            } break;

            /* usage : pdf(text, <text>, array( 'font' => <fontname>, 'size' => <fontsize> )) */
            case 'text':
            {
                $text = $tpl->elementValue( $operatorParameters[1], $rootNamespace, $currentNamespace );

                $operatorValue = '';
                $changeFont = false;

                if ( count( $operatorParameters ) >= 3)
                {
                    $textSettings = $tpl->elementValue( $operatorParameters[2], $rootNamespace, $currentNamespace );

                    if ( isset( $textSettings ) )
                    {
                        $operatorValue .= '<ezCall:callText';
                        $changeFont = true;

                        if ( isset( $textSettings['font'] ) )
                        {
                            $operatorValue .= ':font:'. $textSettings['font'];
                        }

                        if ( isset( $textSettings['size'] ) )
                        {
                            $operatorValue .= ':size:'. $textSettings['size'];
                        }

                        if ( isset( $textSettings['align'] ) )
                        {
                            $operatorValue .= ':justification:'. $textSettings['align'];
                        }

                        $operatorValue .= '>';
                    }
                }

                $operatorValue .= $text;
                if ( $changeFont )
                {
                    $operatorValue .= '</ezCall:callText>';
                }

                eZDebug::writeNotice( 'Text added to PDF: "'. $text .'"', 'eZPDF::modify' );
            } break;

            default:
            {
                eZDebug::writeNotice( 'No operation defined, nothing done', 'eZPDF::modify' );
            }

        }

    }

    /*
     \private
     Create PDF object
    */
    function createPDF()
    {
        $this->PDF = new eZPDFTable();
        $this->PDF->selectFont( 'lib/ezpdf/classes/fonts/Helvetica' );
        eZDebug::writeNotice( 'PDF: File created' );
    }

    /// The array of operators, used for registering operators
    var $Operators;
    var $PDF;
    var $Config;
}


?>
