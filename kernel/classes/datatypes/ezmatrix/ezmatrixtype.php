<?php
//
// Definition of eZMatrixType class
//
// Created on: <30-May-2003 14:18:35 sp>
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

/*! \file ezmatrixtype.php
*/

/*!
  \class eZMatrixType ezmatrixtype.php
  \brief The class eZMatrixType does

*/

include_once( "kernel/classes/ezdatatype.php" );
include_once( "kernel/classes/datatypes/ezmatrix/ezmatrix.php" );
include_once( "kernel/classes/datatypes/ezmatrix/ezmatrixdefinition.php" );

define( "EZ_MATRIX_DEFAULT_NAME_VARIABLE", "_ezmatrix_default_name_" );

define( "EZ_MATRIX_NUMCOLUMNS_VARIABLE", "_ezmatrix_default_num_columns_" );
define( "EZ_MATRIX_NUMROWS_VARIABLE", "_ezmatrix_default_num_rows_" );
define( "EZ_MATRIX_CELL_VARIABLE", "_ezmatrix_cell_" );
define( "EZ_DATATYPESTRING_MATRIX", "ezmatrix" );

class eZMatrixType extends eZDataType
{
    /*!
     Constructor
    */
    function eZMatrixType()
    {
        $this->eZDataType( EZ_DATATYPESTRING_MATRIX, "Matrix" );
    }

        /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
/*        if ( $http->hasPostVariable( $base . "_data_option_id_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
                    return EZ_INPUT_VALIDATOR_STATE_INVALID;
        }
*/      return EZ_INPUT_VALIDATOR_STATE_ACCEPTED;
    }

    /*!
     Store content
    */
    function storeObjectAttribute( &$contentObjectAttribute )
    {
        $matrix =& $contentObjectAttribute->content();
        $contentObjectAttribute->setAttribute( "data_text", $matrix->xmlString() );
        $matrix->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );
        $contentObjectAttribute->setContent( $matrix );
    }

    function storeClassAttribute( &$contentClassAttribute, $version )
    {
        $matrixDefinition =& $contentClassAttribute->content();
        $contentClassAttribute->setAttribute( 'data_text4', $matrixDefinition->xmlString() );
        $matrixDefinition->decodeClassAttribute( $contentClassAttribute->attribute( "data_text4" ) );
        $contentClassAttribute->setContent(  $matrixDefinition );
    }

    /*!
     Returns the content.
    */
    function &objectAttributeContent( &$contentObjectAttribute )
    {
        $matrix = new eZMatrix( "" );

        $matrix->decodeXML( $contentObjectAttribute->attribute( "data_text" ) );

        return $matrix;
    }


    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /*!
     Fetches the http post var matrix cells input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( &$http, $base, &$contentObjectAttribute )
    {
        $cellsVarName = $base . EZ_MATRIX_CELL_VARIABLE . $contentObjectAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $cellsVarName ) )
        {
            $cells = array();
            foreach ( $http->postVariable( $cellsVarName ) as $cell )
            {
                $cells[] = $cell;
            }
            $matrix =& $contentObjectAttribute->attribute( 'content' );
            $matrix->Cells =& $cells;
            $contentObjectAttribute->setContent( $matrix );
        }
        return true;
    }

    /*!
    */
    function customObjectAttributeHTTPAction( $http, $action, &$contentObjectAttribute )
    {
        switch ( $action )
        {
            case "new_row" :
            {
                $matrix =& $contentObjectAttribute->content( );

                $postvarname = "ContentObjectAttribute" . "_data_matrix_remove_" . $contentObjectAttribute->attribute( "id" );
                if ( $http->hasPostVariable( $postvarname ) )
                {
                    $selected = $http->postVariable( $postvarname );
                    $matrix->addRow( $selected[0] );
                }
                else
                {
                    $matrix->addRow( );
                }
                $contentObjectAttribute->setAttribute( 'data_text', $matrix->xmlString() );
                $matrix->decodeXML( $contentObjectAttribute->attribute( 'data_text' ) );
                $contentObjectAttribute->setContent( $matrix );
                
            }break;
            case "remove_selected" :
            {
                $matrix =& $contentObjectAttribute->content( );
                $postvarname = "ContentObjectAttribute" . "_data_matrix_remove_" . $contentObjectAttribute->attribute( "id" );
                $arrayRemove = $http->postVariable( $postvarname );

                foreach ( $arrayRemove as $rowNum)
                {
                    $matrix->removeRow( $rowNum );
                }

                $contentObjectAttribute->setAttribute( 'data_text', $matrix->xmlString() );
                $matrix->decodeXML( $contentObjectAttribute->attribute( 'data_text' ) );
                $contentObjectAttribute->setContent( $matrix );
            }break;
            default :
            {
                eZDebug::writeError( "Unknown custom HTTP action: " . $action, "eZMatrixType" );
            }break;
        }
    }

    /*!
     Returns the integer value.
    */
    function title( &$contentObjectAttribute, $name = "name" )
    {
        $matrix =& $contentObjectAttribute->content( );

        $value = $matrix->attribute( $name );

        return $value;
    }


    /*!
     \return a DOM representation of the content object attribute
    */
    function &serializeContentObjectAttribute( $objectAttribute )
    {
/*        include_once( 'lib/ezxml/classes/ezdomdocument.php' );
        include_once( 'lib/ezxml/classes/ezdomnode.php' );

        $node = new eZDOMNode();
        $node->setName( 'attribute' );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'name', $objectAttribute->contentClassAttributeName() ) );
        $node->appendAttribute( eZDOMDocument::createAttributeNode( 'type', 'ezoption' ) );

        return $node;
*/
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( &$contentObjectAttribute, $currentVersion, &$originalContentObjectAttribute )
    {

        if ( $currentVersion != false )
        {
            $matrix =& $contentObjectAttribute->content();
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $matrix->setName( $contentClassAttribute->attribute( 'data_text1' ) );
            $contentObjectAttribute->setAttribute( "data_text", $matrix->xmlString() );
            $contentObjectAttribute->setContent( $matrix );
        }
        else
        {
            $contentClassAttribute =& $contentObjectAttribute->contentClassAttribute();
            $numRows = $contentClassAttribute->attribute( 'data_int1' );
            $matrix = new eZMatrix( "", $numRows, $contentClassAttribute->attribute( 'content' ) );
            $contentObjectAttribute->setAttribute( "data_text", $matrix->xmlString() );
            $contentObjectAttribute->setContent( $matrix );
        }

    }

    /*!
     \reimp
    */
    function fetchClassAttributeHTTPInput( &$http, $base, &$classAttribute )
    {
        $defaultValueName = $base . EZ_MATRIX_DEFAULT_NAME_VARIABLE . $classAttribute->attribute( 'id' );
        $defaultNumColumnsName = $base . EZ_MATRIX_NUMCOLUMNS_VARIABLE . $classAttribute->attribute( 'id' );
        $defaultNumRowsName = $base . EZ_MATRIX_NUMROWS_VARIABLE . $classAttribute->attribute( 'id' );
        $dataFetched = false;
        if ( $http->hasPostVariable( $defaultValueName ) )
        {
            $defaultValueValue = $http->postVariable( $defaultValueName );

            if ($defaultValueValue == "")
            {
                $defaultValueValue = "";
            }
            $classAttribute->setAttribute( 'data_text1', $defaultValueValue );
            $dataFetched = true;
        }

        if ( $http->hasPostVariable( $defaultNumRowsName ) )
        {
            $defaultNumRowsValue = $http->postVariable( $defaultNumRowsName );

            if ( $defaultNumRowsValue == "")
            {
                $defaultNumRowsValue = "1";
            }
            $classAttribute->setAttribute( 'data_int1', $defaultNumRowsValue );
            $dataFetched = true;
        }

        $columnNameVariable = $base . '_data_ezmatrix_column_name_' . $classAttribute->attribute( 'id' );
        $columnIDVariable = $base . '_data_ezmatrix_column_id_' . $classAttribute->attribute( 'id' );


        if ( $http->hasPostVariable( $columnNameVariable ) && $http->hasPostVariable( $columnIDVariable ) )
        {
            $columns = array();
            $i = 0;
            $columnNameList = $http->postVariable( $columnNameVariable );
            $columnIDList = $http->postVariable( $columnIDVariable );

            $matrixDefinition =& $classAttribute->attribute( 'content' );
            $columnNames =& $matrixDefinition->attribute( 'columns' );
            foreach ( $columnNames as $columnName )
            {
                $index = $columnName['index'];
                $columnID = $columnIDList[$index];
                $name = $columnNameList[$index];
                if ( strlen( $columnID ) == 0 )
                {
                    $columnID = $name;
                    $columnID = strtolower( $columnID );
                    $columnID = preg_replace( array( "/[^a-z0-9_ ]/" ,
                                       "/ /",
                                       "/__+/" ),
                                array( "",
                                       "_",
                                       "_" ),
                                $columnID );
                }

                $columns[] = array( 'name' => $name,
                                    'identifier' => $columnID,
                                    'index' => $i );

                $i++;
            }

            $matrixDefinition->ColumnNames =& $columns;
            $classAttribute->setContent( $matrixDefinition );
            $classAttribute->setAttribute( 'data_text4', $matrixDefinition->xmlString() );

            $dataFetched = true;
        }
        if ( $dataFetched )
        {
            return true;
        }
        return false;

    }


    /*!
     Returns the content.
    */
    function &classAttributeContent( &$contentClassAttribute )
    {
        $matrixDefinition =& new eZMatrixDefinition();
        $matrixDefinition->decodeClassAttribute( $contentClassAttribute->attribute( 'data_text4' ) );
        return $matrixDefinition;
    }

    /*!
    */
    function customClassAttributeHTTPAction( &$http, $action, &$contentClassAttribute )
    {
        $id = $contentClassAttribute->attribute( "id" );
        switch ( $action )
        {
            case "new_ezmatrix_column" :
            {
                $matrixDefinition =& $contentClassAttribute->content( );
                $matrixDefinition->addColumn( "" );
                $contentClassAttribute->setContent( $matrixDefinition );
                $contentClassAttribute->store();
            }break;
            case "remove_selected" :
            {
                $matrixDefinition =& $contentClassAttribute->content( );

                $postvarname = "ContentClass" . "_data_ezmatrix_column_remove_" . $contentClassAttribute->attribute( "id" );
                $array_remove = $http->postVariable( $postvarname );
                foreach( $array_remove as $columnIndex )
                {
                    $matrixDefinition->removeColumn( $columnIndex );
                }
                $contentClassAttribute->setContent( $matrixDefinition );
            }break;
            default :
            {
                eZDebug::writeError( "Unknown custom HTTP action: " . $action, "eZEnumType" );
            }break;
        }
    }

}

eZDataType::register( EZ_DATATYPESTRING_MATRIX, "ezmatrixtype" );

?>
