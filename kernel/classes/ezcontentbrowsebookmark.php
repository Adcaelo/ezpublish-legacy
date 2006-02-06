<?php
//
// Definition of eZContentBrowseBookmark class
//
// Created on: <29-Apr-2003 15:32:53 sp>
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

/*! \file ezcontentbrowsebookmark.php
*/

/*!
  \class eZContentBrowseBookmark ezcontentbrowsebookmark.php
  \brief Handles bookmarking nodes for users

  Allows the creation and fetching of bookmark lists for users.
  The bookmark list is used in the browse page to allow quick navigation and selection.

  Creating a new bookmark item is done with
\code
$userID = eZUser::currentUserID();
$nodeID = 2;
$nodeName = 'Node';
eZContentBrowseBookmark::createNew( $userID, $nodeID, $nodeName )
\endcode

  Fetching the list is done with
\code
$userID = eZUser::currentUserID();
eZContentBrowseBookmark::fetchListForUser( $userID )
\endcode

*/

include_once( 'kernel/classes/ezcontentobjecttreenode.php' );

class eZContentBrowseBookmark extends eZPersistentObject
{
    /*!
     \reimp
    */
    function eZContentBrowseBookmark( $row )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    function &definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "user_id" => array( 'name' => 'UserID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "node_id" => array( 'name' => "NodeID",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "name" => array( 'name' => "Name",
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( 'node' => 'fetchNode',
                                                      'contentobject_id' => 'contentObjectID' ),
                      "increment_key" => "id",
                      "sort" => array( "id" => "asc" ),
                      "class_name" => "eZContentBrowseBookmark",
                      "name" => "ezcontentbrowsebookmark" );

    }

    /*!
     \reimp
    */
    function attributes()
    {
        return eZPersistentObject::attributes();
    }

    /*!
     \reimp
    */
    function hasAttribute( $attributeName )
    {
        if ( $attributeName == 'node' or
             $attributeName == 'contentobject_id' )
        {
            return true;
        }
        else if ( $attributeName == 'contentobject_id' )
        {
            return $this->contentObjectID();
        }
        else
            return eZPersistentObject::hasAttribute( $attributeName );
    }

    /*!
     \reimp
    */
    function &attribute( $attributeName )
    {
        if ( $attributeName == 'node' )
        {
            return $this->fetchNode();
        }
        else
            return eZPersistentObject::attribute( $attributeName );
    }


    /*!
     \static
     \return the bookmark item \a $bookmarkID.
    */
    function fetch( $bookmarkID )
    {
        return eZPersistentObject::fetchObject( eZContentBrowseBookmark::definition(),
                                                null, array( 'id' => $bookmarkID ), true );
    }

    /*!
     \static
     \return the bookmark list for user \a $userID.
    */
    function fetchListForUser( $userID, $offset = false, $limit = false )
    {
        return eZPersistentObject::fetchObjectList( eZContentBrowseBookmark::definition(),
                                                    null,
                                                    array( 'user_id' => $userID ),
                                                    array( 'id' => 'desc' ),
                                                    array( 'offset' => $offset, 'length' => $limit ),
                                                    true );
    }

    /*!
     \static
     Creates a new bookmark item for user \a $userID with node id \a $nodeID and name \a $nodeName.
     The new item is returned.
    */
    function &createNew( $userID, $nodeID, $nodeName )
    {
        $db =& eZDB::instance();
        $userID =(int) $userID;
        $nodeID =(int) $nodeID;
        $nodeName = $db->escapeString( $nodeName );
        $db->query( "DELETE FROM ezcontentbrowsebookmark WHERE node_id=$nodeID and user_id=$userID" );
        $bookmark = new eZContentBrowseBookmark( array( 'user_id' => $userID,
                                                        'node_id' => $nodeID,
                                                        'name' => $nodeName ) );
        $bookmark->store();
        return $bookmark;
    }

    /*!
     \return the tree node which this item refers to.
    */
    function &fetchNode()
    {
        return eZContentObjectTreeNode::fetch( $this->attribute( 'node_id' ) );
    }

    /*!
     \return the content object ID of the tree node which this item refers to.
    */
    function &contentObjectID()
    {
        $node =& $this->fetchNode();
        if ( $node )
            return $node->attribute( 'contentobject_id' );
        return null;
    }

    /*!
     \static
     Removes all bookmark entries for all users.
    */
    function cleanup()
    {
        $db =& eZDB::instance();
        $db->query( "DELETE FROM ezcontentbrowsebookmark" );
    }

    /*!
     \static
     Removes all bookmark entries for node.
    */
    function removeByNodeID( $nodeID )
    {
        $db =& eZDB::instance();
        $nodeID =(int) $nodeID;
        $db->query( "DELETE FROM ezcontentbrowsebookmark WHERE node_id=$nodeID" );
    }
}

?>
