<?php
//
// Definition of eZi18nOperator class
//
// Created on: <18-Apr-2002 12:15:07 amos>
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

//!! eZKernel
//! The class eZi18nOperator does
/*!

*/

include_once( 'kernel/common/i18n.php' );

function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

class eZi18nOperator
{
    /*!
    */
    function eZi18nOperator( $name = 'i18n', $extensionName = 'x18n' )
    {
        $this->Operators = array( $name, $extensionName );
        $this->TranslatorManager =& eZTranslatorManager::instance();
        $this->Name = $name;
        $this->ExtensionName = $extensionName;
    }

    /*!
     Returns the operators in this class.
    */
    function &operatorList()
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array( $this->Name => array( 'context' => array( 'type' => 'string',
                                                                'required' => false,
                                                                'default' => false ),
                                            'comment' => array( 'type' => 'string',
                                                                'required' => false,
                                                                'default' => '' ) ),
                      $this->ExtensionName => array( 'extension' => array( 'type' => 'string',
                                                                           'required' => true,
                                                                           'default' => false ),
                                                     'context' => array( 'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => false ),
                                                     'comment' => array( 'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => '' ) ) );
    }

    /*!
     \reimp
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$value, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case $this->Name:
            {
                $context = $namedParameters['context'];
                $comment = $namedParameters['comment'];
                $value = ezi18n( $context, $value, $comment );
            } break;
            case $this->ExtensionName:
            {
                $extension = $namedParameters['extension'];
                $context = $namedParameters['context'];
                $comment = $namedParameters['comment'];
                $value = ezx18n( $extension, $context, $value, $comment );
            } break;
        }
    }

    /// \privatesection
    var $Operators;
    var $TranslatorManager;
    var $Name;
    var $ExtensionName;
};

?>
