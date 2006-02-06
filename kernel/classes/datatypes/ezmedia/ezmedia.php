<?php
//
// Definition of eZBinaryFile class
//
// Created on: <30-Apr-2002 16:47:08 bf>
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

/*!
  \class eZMedia ezmedia.php
  \ingroup eZDatatype
  \brief The class eZMedia handles registered media files

*/

include_once( "lib/ezdb/classes/ezdb.php" );
include_once( "kernel/classes/ezpersistentobject.php" );
include_once( "kernel/classes/ezcontentclassattribute.php" );

class eZMedia extends eZPersistentObject
{
    function eZMedia( $row )
    {
        $this->eZPersistentObject( $row );
    }

    function &definition()
    {
        return array( "fields" => array( "contentobject_attribute_id" => array( 'name' => "ContentObjectAttributeID",
                                                                                'datatype' => 'integer',
                                                                                'default' => 0,
                                                                                'required' => true ),
                                         "version" => array( 'name' => "Version",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "filename" => array( 'name' => "Filename",
                                                              'datatype' => 'string',
                                                              'default' => '',
                                                              'required' => true ),
                                         "original_filename" => array( 'name' => "OriginalFilename",
                                                                       'datatype' => 'string',
                                                                       'default' => '',
                                                                       'required' => true ),
                                         "mime_type" => array( 'name' => "MimeType",
                                                               'datatype' => 'string',
                                                               'default' => '',
                                                               'required' => true ),
                                         "width" => array( 'name' => "Width",
                                                           'datatype' => 'integer',
                                                           'default' => 0,
                                                           'required' => true ),
                                         "height" => array( 'name' => "Height",
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ),
                                         "has_controller" => array( 'name' => "HasController",
                                                                    'datatype' => 'integer',
                                                                    'default' => 0,
                                                                    'required' => true ),
                                         "controls" => array( 'name' => "Controls",
                                                              'datatype' => 'string',
                                                              'default' => '',
                                                              'required' => true ),
                                         "is_autoplay" => array( 'name' => "IsAutoplay",
                                                                 'datatype' => 'integer',
                                                                 'default' => 0,
                                                                 'required' => true ),
                                         "pluginspage" => array( 'name' => "Pluginspage",
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         "quality" => array( 'name' => 'Quality',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         "is_loop" => array( 'name' => "IsLoop",
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ) ),
                      "keys" => array( "contentobject_attribute_id", "version" ),
                      "function_attributes" => array( 'filesize' => 'filesize',
                                                      'filepath' => 'filepath',
                                                      'mime_type_category' => 'mimeTypeCategory',
                                                      'mime_type_part' => 'mimeTypePart' ),
                      "relations" => array( "contentobject_attribute_id" => array( "class" => "ezcontentobjectattribute",
                                                                                   "field" => "id" ),
                                            "version" => array( "class" => "ezcontentobjectattribute",
                                                                "field" => "version" )),
                      "class_name" => "eZMedia",
                      "name" => "ezmedia" );
    }

    function hasAttribute( $attr )
    {
        return ( $attr == 'mime_type_category' or
                 $attr == 'mime_type_part' or
                 $attr == 'filepath' or
                 $attr == 'filesize' or
                 eZPersistentObject::hasAttribute( $attr ) );
    }

    function &attribute( $attr )
    {
        $ini =& eZINI::instance();

        switch( $attr )
        {
            case 'filesize':
            {
                $fileInfo = $this->storedFileInfo();
                if ( file_exists( $fileInfo['filepath'] ) )
                    return filesize( $fileInfo['filepath'] );
                else
                    return 0;
            } break;

            case 'filepath':
            {
                $fileInfo = $this->storedFileInfo();
                return $fileInfo['filepath'];
            } break;

            case 'mime_type_category':
            {
                $types = explode( "/", eZPersistentObject::attribute( "mime_type" ) );
                return $types[0];
            } break;

            case 'mime_type_part':
            {
                $types = explode( "/", eZPersistentObject::attribute( "mime_type" ) );
                return $types[1];
            } break;
            default:
                return eZPersistentObject::attribute( $attr );
        }
        return null;
    }

    function &create( $contentObjectAttributeID, $version )
    {
        $row = array( "contentobject_attribute_id" => $contentObjectAttributeID,
                      "version" => $version,
                      "filename" => "",
                      "original_filename" => "",
                      "mime_type" => "",
                      "width" => "0",
                      "height" => "0",
                      "controller" => true,
                      "autoplay" => true,
                      "pluginspage" => "",
                      "is_loop" => false,
                      "quality" => "",
                      "controls" => ""
                      );
        return new eZMedia( $row );
    }

    function &fetch( $id, $version, $asObject = true )
    {
        if( $version == null )
        {
            return eZPersistentObject::fetchObjectList( eZMedia::definition(),
                                                        null,
                                                        array( "contentobject_attribute_id" => $id ),
                                                        null,
                                                        null,
                                                        $asObject );
        }
        else
        {
            return eZPersistentObject::fetchObject( eZMedia::definition(),
                                                    null,
                                                    array( "contentobject_attribute_id" => $id,
                                                           "version" => $version ),
                                                    $asObject );
        }
    }

    function &remove( $id, $version )
    {
        if( $version == null )
        {
            eZPersistentObject::removeObject( eZMedia::definition(),
                                              array( "contentobject_attribute_id" => $id ) );
        }
        else
        {
            eZPersistentObject::removeObject( eZMedia::definition(),
                                              array( "contentobject_attribute_id" => $id,
                                                     "version" => $version ) );
        }
    }

    function storedFileInfo()
    {
        $fileName = $this->attribute( 'filename' );
        $mimeType = $this->attribute( 'mime_type' );
        $originalFileName = $this->attribute( 'original_filename' );

        $storageDir = eZSys::storageDirectory();

        list( $group, $type ) = explode( '/', $mimeType );

        $filePath = $storageDir . '/original/' . $group . '/' . $fileName;

        return array( 'filename' => $fileName,
                      'original_filename' => $originalFileName,
                      'filepath' => $filePath,
                      'mime_type' => $mimeType );
    }

    var $ContentObjectAttributeID;
    var $Filename;
    var $OriginalFilename;
    var $MimeType;
    var $Width;
    var $Height;
    var $HasController;
    var $Controls;
    var $IsLoop;
    var $IsAutoplay;
    var $Pluginspage;
    var $Quality;
}

?>
