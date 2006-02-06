<?php
//
// Definition of eZMatrixDefinition class
//
// Created on: <03-Jun-2003 18:30:44 sp>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.5.x
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

/*! \file ezmatrixdefinition.php
*/

/*!
  \class eZMatrixDefinition ezmatrixdefinition.php
  \ingroup eZDatatype
  \brief The class eZMatrixDefinition does

*/
include_once( "lib/ezxml/classes/ezxml.php" );

class eZMatrixDefinition
{
    /*!
     Constructor
    */
    function eZMatrixDefinition()
    {
        $this->ColumnNames = array();
    }


    function &decodeClassAttribute( $xmlString )
    {
        $xml = new eZXML();
        $dom =& $xml->domTree( $xmlString );
        if ( strlen ( $xmlString ) != 0 )
        {
            $columns =& $dom->elementsByName( "column-name" );
            $columnList = array();
            foreach ( array_keys( $columns ) as $key )
            {
                $columnElement =& $columns[$key];
                $columnList[] = array( 'name' => $columnElement->textContent(),
                                       'identifier' => $columnElement->attributeValue( 'id' ),
                                       'index' =>  $columnElement->attributeValue( 'idx' ) );
            }
            $this->ColumnNames =& $columnList;
        }
        else
        {
            $this->addColumn( );
            $this->addColumn( );
        }

    }

    function &hasAttribute( $attr )
    {
        if ( $attr == 'columns' )
        {
            return true;
        }
        return false;
    }

    function &attribute( $attr )
    {
        if ( $attr == 'columns' )
        {
            return $this->ColumnNames;
        }
    }

    function &xmlString( )
    {
        $doc = new eZDOMDocument( "Matrix" );
        $root =& $doc->createElementNode( "ezmatrix" );
        $doc->setRoot( $root );

        foreach ( $this->ColumnNames as $columnName )
        {
            $columnNameNode = $doc->createElementNode( 'column-name' );
            $columnNameNode->appendAttribute( $doc->createAttributeNode( 'id', $columnName['identifier'] ) );
            $columnNameNode->appendAttribute( $doc->createAttributeNode( 'idx', $columnName['index'] ) );
            $columnNameNode->appendChild( $doc->createTextNode( $columnName['name'] ) );
            $root->appendChild( $columnNameNode );
            unset( $columnNameNode );
        }

        $xml =& $doc->toString();

        return $xml;
    }

    function addColumn( $name = false , $id = false )
    {
        if ( $name == false )
        {
            $name = 'Col_' . ( count( $this->ColumnNames ) );
        }

        if ( $id == false )
        {
            // Initialize transformation system
            include_once( 'lib/ezi18n/classes/ezchartransform.php' );
            $trans =& eZCharTransform::instance();
            $id = $trans->transformByGroup( $name, 'identifier' );
        }

        $this->ColumnNames[] = array( 'name' => $name,
                                      'identifier' => $id,
                                      'index' => count( $this->ColumnNames ) );
    }

    function removeColumn( $index )
    {
        if ( $index == 0 && count( $this->ColumnNames ) == 1 )
        {
            $this->ColumnNames = array();
        }
        else
        {
            unset( $this->ColumnNames[$index] );
        }
    }

    var $ColumnNames;

}

?>
