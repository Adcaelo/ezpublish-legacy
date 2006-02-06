<?php
//
// Definition of eZFileDirectHandler class
//
// Created on: <30-Apr-2002 16:47:08 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
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

/*!
  \class eZFileDirectHandler ezfiledirecthandler.php
  \ingroup eZBinaryHandlers
  \brief Handles file downloading by passing an URL directly to the file.

*/
include_once( "kernel/classes/datatypes/ezbinaryfile/ezbinaryfile.php" );
include_once( "kernel/classes/ezbinaryfilehandler.php" );
define( "EZ_FILE_DIRECT_ID", 'ezfiledirect' );

class eZFileDirectHandler extends eZBinaryFileHandler
{
    function eZFileDirectHandler()
    {
        $this->eZBinaryFileHandler( EZ_FILE_DIRECT_ID, "direct download", EZ_BINARY_FILE_HANDLE_DOWNLOAD );
    }

    function handleFileDownload( &$contentObject, &$contentObjectAttribute, $type, $fileInfo )
    {
        return EZ_BINARY_FILE_RESULT_OK;
    }

    /*!
     \reimp
     \return the direct download template suffix
    */
    function &viewTemplate( &$contentobjectAttribute )
    {
        $retValue = 'direct';
        return $retValue;
    }

}

?>
