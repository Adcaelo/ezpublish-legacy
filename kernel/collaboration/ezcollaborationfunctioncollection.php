<?php
//
// Definition of eZCollaborationFunctionCollection class
//
// Created on: <06-Oct-2002 16:19:31 amos>
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

/*! \file ezcontentfunctioncollection.php
*/

/*!
  \class eZCollaborationFunctionCollection ezcontentfunctioncollection.php
  \brief The class eZCollaborationFunctionCollection does

*/

include_once( 'kernel/error/errors.php' );

class eZCollaborationFunctionCollection
{
    /*!
     Constructor
    */
    function eZCollaborationFunctionCollection()
    {
    }

    function &fetchParticipantList( $itemID, $sortBy, $offset, $limit )
    {
        include_once( 'kernel/classes/ezcollaborationitemparticipantlink.php' );
        $itemParameters = array( 'item_id' => $itemID,
                                 'offset' => $offset,
                                 'limit' => $limit,
                                 'sort_by' => $sortBy );
        $children =& eZCollaborationItemParticipantLink::fetchParticipantList( $itemParameters );
        if ( $children === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => &$children );
    }

    function &fetchMessageList( $itemID, $sortBy, $offset, $limit )
    {
        include_once( 'kernel/classes/ezcollaborationitemmessagelink.php' );
        $itemParameters = array( 'item_id' => $itemID,
                                 'offset' => $offset,
                                 'limit' => $limit,
                                 'sort_by' => $sortBy );
        $children =& eZCollaborationItemMessageLink::fetchItemList( $itemParameters );
        if ( $children === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => &$children );
    }

    function &fetchItemList( $sortBy, $offset, $limit )
    {
        include_once( 'kernel/classes/ezcollaborationitem.php' );
        $itemParameters = array( 'offset' => $offset,
                                 'limit' => $limit,
                                 'sort_by' => $sortBy );
        $children =& eZCollaborationItem::fetchList( $itemParameters );
        if ( $children === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => &$children );
    }

    function &fetchItemCount()
    {
        include_once( 'kernel/classes/ezcollaborationitem.php' );
        $itemParameters = array();
        $count =& eZCollaborationItem::fetchListCount( $itemParameters );
        return array( 'result' => $count );
    }

    function &fetchGroupTree( $parentGroupID, $sortBy, $offset, $limit, $depth )
    {
        include_once( 'kernel/classes/ezcollaborationgroup.php' );
        $treeParameters = array( 'parent_group_id' => $parentGroupID,
                                 'offset' => $offset,
                                 'limit' => $limit,
                                 'sort_by' => $sortBy,
                                 'depth' => $depth );
        $children =& eZCollaborationGroup::subTree( $treeParameters );
        if ( $children === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => &$children );
    }

    function &fetchObjectTreeCount( $parentNodeID, $class_filter_type, $class_filter_array, $depth )
    {
        include_once( 'kernel/classes/ezcontentobjecttreenode.php' );
        $node =& eZCollaborationObjectTreeNode::fetch( $parentNodeID );
        $childrenCount =& $node->subTreeCount( array( 'Limitation' => null,
                                                      'ClassFilterType' => $class_filter_type,
                                                      'ClassFilterArray' => $class_filter_array,
                                                      'Depth' => $depth ) );
        if ( $childrenCount === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => EZ_ERROR_KERNEL_NOT_FOUND ) );
        return array( 'result' => &$childrenCount );
    }

}

?>
