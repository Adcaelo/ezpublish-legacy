<?php
//
// Definition of eZStepSiteAccess class
//
// Created on: <12-Aug-2003 17:35:47 kk>
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

/*! \file ezstep_site_access.php
*/
include_once( 'kernel/setup/steps/ezstep_installer.php');
include_once( "kernel/common/i18n.php" );

/*!
  \class eZStepSiteAccess ezstep_site_access.php
  \brief The class eZStepSiteAccess does

*/

class eZStepSiteAccess extends eZStepInstaller
{
    /*!
     Constructor
    */
    function eZStepSiteAccess( &$tpl, &$http, &$ini, &$persistenceList )
    {
        $this->eZStepInstaller( $tpl, $http, $ini, $persistenceList );
    }

    /*!
     \reimp
    */
    function processPostData()
    {
        $accessType = null;
        if( $this->Http->hasPostVariable( 'eZSetup_site_access' ) )
        {
            $accessType = $this->Http->postVariable( 'eZSetup_site_access' );
        }
        else
        {
            return false; // unknown error
        }

        $templateCount = $this->PersistenceList['site_templates']['count'];

        $portCounter = 8080;
        for ( $counter = 0; $counter < $templateCount; $counter++ )
        {
            $this->PersistenceList['site_templates_'.$counter]['access_type'] = $accessType;
            if ( $accessType == 'url' )
            {
                $this->PersistenceList['site_templates_'.$counter]['access_type_value'] = $this->PersistenceList['site_templates_'.$counter]['identifier'];
                $this->PersistenceList['site_templates_'.$counter]['admin_access_type_value'] = $this->PersistenceList['site_templates_'.$counter]['identifier'] . '_admin';;
            }
            else if ( $accessType == 'port' )
            {
                $this->PersistenceList['site_templates_'.$counter]['access_type_value'] = $portCounter++;
                $this->PersistenceList['site_templates_'.$counter]['admin_access_type_value'] = $portCounter++;
            }
            else if ( $accessType == 'hostname' )
            {
                $this->PersistenceList['site_templates_'.$counter]['access_type_value'] = $this->PersistenceList['site_templates_'.$counter]['identifier'] . '.' . eZSys::hostName();
                $this->PersistenceList['site_templates_'.$counter]['admin_access_type_value'] = $this->PersistenceList['site_templates_'.$counter]['identifier'] . '_admin.' . eZSys::hostName();
            }
            else
            {
                $this->PersistenceList['site_templates_'.$counter]['access_type_value'] = $accessType;
                $this->PersistenceList['site_templates_'.$counter]['admin_access_type_value'] = $accessType . '_admin';
            }
        }
        return true;
    }

    /*!
     \reimp
     */
    function init()
    {
        return false; // Always show site access
    }

    /*!
     \reimp
    */
    function &display()
    {
        $this->Tpl->setVariable( 'site_templates', $this->PersistenceList['site_templates'] );
        $this->Tpl->setVariable( 'error', $this->Error );

        // Return template and data to be shown
        $result = array();
        // Display template
        $result['content'] = $this->Tpl->fetch( 'design:setup/init/site_access.tpl' );
        $result['path'] = array( array( 'text' => ezi18n( 'design/standard/setup/init',
                                                          'Site access' ),
                                        'url' => false ) );
        return $result;
    }

}

?>
