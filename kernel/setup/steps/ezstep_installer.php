<?php
//
// Definition of EZStepInstaller class
//
// Created on: <08-Aug-2003 14:46:44 kk>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
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

/*! \file ezstep_class_definition.php
*/

/*!
  \class EZStepInstaller ezstep_class_definition.ph
  \brief The class EZStepInstaller provide a framework for eZStep installer classes

*/

class eZStepInstaller
{
    /*!
     Default constructor for eZ publish installer classes

    \param template
    \param http object
    \param ini settings object
    \param persistencelist, all previous posted data
    */
    function eZStepInstaller( &$tpl, &$http, &$ini, &$persistenceList )
    {
        $this->Tpl =& $tpl;
        $this->Http =& $http;
        $this->Ini =& $ini;
        $this->PersistenceList =& $persistenceList;
    }

    /*!
     \virtual

     Processespost data from this class.
     \return  true if post data accepted, or false if post data is rejected.
    */
    function processPostData()
    {
    }

    /*!
     \virtual

    Performs test needed by this class.

    This class may access class variables to store data needed for viewing if output failed
    \return true if all tests passed and continue with next default step,
            number of next step if all tests passed and next step is "hard coded",
           false if tests failed
    */
    function init()
    {
    }

    /*!
    \virtual

    Display information and forms needed to pass this step.
    \return result to use in template
    */
    function &display()
    {
        return null;
    }

    function findAppropriateCharset( &$primaryLanguage, &$allLanguages, $canUseUnicode )
    {
        include_once( 'lib/ezi18n/classes/ezcharsetinfo.php' );
        $allCharsets = array();
        for ( $i = 0; $i < count( $allLanguages ); ++$i )
        {
            $language =& $allLanguages[$i];
            $charsets = $language->allowedCharsets();
            foreach ( $charsets as $charset )
            {
                $charset = eZCharsetInfo::realCharsetCode( $charset );
                $allCharsets[] = $charset;
            }
        }
        $allCharsets = array_unique( $allCharsets );
//         eZDebug::writeDebug( $allCharsets, 'allCharsets' );
        $commonCharsets = $allCharsets;
        for ( $i = 0; $i < count( $allLanguages ); ++$i )
        {
            $language =& $allLanguages[$i];
            $charsets = $language->allowedCharsets();
            $realCharsets = array();
            foreach ( $charsets as $charset )
            {
                $charset = eZCharsetInfo::realCharsetCode( $charset );
                $realCharsets[] = $charset;
            }
            $realCharsets = array_unique( $realCharsets );
            $commonCharsets = array_intersect( $commonCharsets, $realCharsets );
        }
        $usableCharsets = array_values( $commonCharsets );
//         eZDebug::writeDebug( $usableCharsets, 'usableCharsets' );
        $charset = false;
        if ( count( $usableCharsets ) > 0 )
        {
            if ( in_array( $primaryLanguage->charset(), $usableCharsets ) )
                $charset = $primaryLanguage->charset();
            else // Pick the first charset
                $charset = $usableCharsets[0];
        }
        else
        {
            if ( $canUseUnicode )
            {
                $charset = eZCharsetInfo::realCharsetCode( 'utf-8' );
            }
//             else
//             {
//                 // Pick preferred primary language
//                 $charset = $primaryLanguage->charset();
//             }
        }
//         eZDebug::writeDebug( $charset, 'charset' );
        return $charset;
    }

    var $Tpl;
    var $Http;
    var $Ini;
    var $PersistenceList;
}

?>
