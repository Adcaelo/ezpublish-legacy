<?php
//
// Created on: <12-Jun-2002 16:25:40 bf>
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

/*!
  \class eZContentObjectTranslation ezcontentobjecttranslation.php
  \brief eZContentObjectTranslation handles translation a translation of content objects
  \ingroup eZKernel

  \sa eZContentObject eZContentObjectVersion eZContentObjectTranslation
*/

class eZContentObjectTranslation
{
    function eZContentObjectTranslation( $contentObjectID, $version, $languageCode )
    {
        $this->ContentObjectID = $contentObjectID;
        $this->Version = $version;
        $this->LanguageCode = $languageCode;
    }

    function languageCode()
    {
        return $this->LanguageCode;
    }

    /*!
     Returns the attributes for the current content object translation.
    */
    function attributes( $as_object = true )
    {
        return eZContentObjectVersion::fetchAttributes( $this->Version, $this->ContentObjectID, $this->LanguageCode, $as_object );
    }

    /// The content object identifier
    var $ContentObjectID;
    /// Contains the content object
    var $Version;

    /// Contains the language code for the current translation
    var $LanguageCode;

}
?>
