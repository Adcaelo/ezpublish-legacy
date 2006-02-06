<?php
//
// Definition of eZTestXMLArraySerialization class
//
// Created on: <22-Nov-2004 13:48:38 jb>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.6.x
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

/*! \file eztestxmlarrayserialization.php
*/

/*!
  \class eZTestXMLArraySerialization eztestxmlarrayserialization.php
  \brief The class eZTestXMLArraySerialization does

*/

class eZTestXMLArraySerialization extends eZTestCase
{
    /*!
     Constructor
    */
    function eZTestXMLArraySerialization( $name = false )
    {
        $this->eZTestCase( $name );

        $this->addTest( 'testSerialize', 'Serialization of array to XML' );
//         $this->addTest( 'testUnserialize', 'Unserialization of XML to array' );
        $this->addTest( 'testTwoWaySerialize', 'Serialize array to XML and then back again' );
    }

    /*!
     \return The test array.
    */
    function generateArray()
    {
        $array = array( 'key1' => array( 'file' => 'file1.txt',
                                         'path' => 'a/small/path' ),
                        'key2' => array( 'file' => 'file2.txt',
                                         'path' => 'another/path' ) );
        return $array;
    }

    /*!
     \return The test node structure as array.
    */
    function generateNodeMatchArray()
    {
        $key1Attributes = array( 0 => array( 'name' => 'file',
                                             'type' => 2,
                                             'children' => array(),
                                             'attributes' => array(),
                                             'content' => 'file1.txt' ),
                                 1 => array( 'name' => 'path',
                                             'type' => 2,
                                             'children' => array(),
                                             'attributes' => array(),
                                             'content' => 'a/small/path' ) );
        $key1 = array( 'name' => 'key1',
                       'type' => 1,
                       'children' => array(),
                       'attributes' => $key1Attributes,
                       'content' => '' );
        $key2Attributes = array( 0 => array( 'name' => 'file',
                                             'type' => 2,
                                             'children' => array(),
                                             'attributes' => array(),
                                             'content' => 'file2.txt' ),
                                 1 => array( 'name' => 'path',
                                             'type' => 2,
                                             'children' => array(),
                                             'attributes' => array(),
                                             'content' => 'another/path' ) );
        $key2 = array( 'name' => 'key2',
                       'type' => 1,
                       'children' => array(),
                       'attributes' => $key2Attributes,
                       'content' => '' );
        return array( 'name' => 'test-array',
                      'type' => 1,
                      'children' => array( 0 => $key1,
                                           1 => $key2 ),
                      'attributes' => array(),
                      'content' => '' );
    }

    function nodeToArray( $node )
    {
        $array = array( 'name' => $node->name(),
                        'type' => $node->type(),
                        'children' => array(),
                        'attributes' => array(),
                        'content' => $node->content() );
        $children =& $node->children();
        foreach ( $children as $child )
        {
            $array['children'][] = $this->nodeToArray( $child );
        }
        $attributes =& $node->attributes();
        foreach ( $attributes as $attribute )
        {
            $array['attributes'][] = $this->nodeToArray( $attribute );
        }
        return $array;
    }

    function matchNodeArray( $node, $nodeMatchArray )
    {
        $nodeArray = $this->nodeToArray( $node );
        return $this->arrayCompare( $nodeArray, $nodeMatchArray );
    }

    /*!
     \return \c true if \a $array1 is similar to \a $array2.

     It is considered similar if they have the same keys and with
     the same values (using == for compare).
    */
    function arrayCompare( $array1, $array2 )
    {
        foreach ( $array1 as $key => $value )
        {
            if ( is_array( $value ) )
            {
                if ( !isset( $array2[$key] ) )
                    return false;
                if ( !$this->arrayCompare( $value, $array2[$key] ) )
                    return false;
            }
            else
            {
                if ( !isset( $array2[$key] ) )
                    return false;
                if ( $value != $array2[$key] )
                    return false;
            }
        }

        foreach ( $array2 as $key => $value )
        {
            if ( is_array( $value ) )
            {
                if ( !isset( $array1[$key] ) )
                    return false;
                if ( !$this->arrayCompare( $value, $array1[$key] ) )
                    return false;
            }
            else
            {
                if ( !isset( $array1[$key] ) )
                    return false;
                if ( $value != $array1[$key] )
                    return false;
            }
        }
        return true;
    }

    /*!
     Serializes an array into a node structure and validates that against a given structure.
    */
    function testSerialize( &$tr )
    {
        include_once( 'lib/ezxml/classes/ezxml.php' );
        $doc = new eZDomDocument();
        $array = $this->generateArray();
        $node =& $doc->createElementNodeFromArray( 'test-array', $array );
        $nodeMatchArray = $this->generateNodeMatchArray();
        $tr->assert( $this->matchNodeArray( $node, $nodeMatchArray ) );
    }

    /*!
     Serializes an array into a node structure and then back again to an array.
     These two arrays are then compared against each other.
    */
    function testTwoWaySerialize( &$tr )
    {
        include_once( 'lib/ezxml/classes/ezxml.php' );
        $doc = new eZDomDocument();
        $array1 = $this->generateArray();
        $node =& $doc->createElementNodeFromArray( 'test-array', $array1 );
        $array2 = $doc->createArrayFromDOMNode( $node );
        $tr->assert( $this->arrayCompare( $array1, $array2 ) );
    }

}

?>
