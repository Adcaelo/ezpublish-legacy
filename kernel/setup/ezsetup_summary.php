<?php
//
// Definition of eZSetupSummary class
//
// Created on: <13-Aug-2003 17:49:28 kk>
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

/*! \file ezsetup_summary.php
*/
include_once( "kernel/setup/ezsetuptests.php" );

/*!
  \class eZSetupSummary ezsetup_summary.php
  \brief The class eZSetupSummary does

*/

class eZSetupSummary
{
    /*!
     Constructor

     Create new object for generating summary

     \param template
     \param persistence list
    */
    function eZSetupSummary( &$tpl, &$persistenceList )
    {
        $this->Tpl =& $tpl;
        $this->PersistenceList =& $persistenceList;
    }

    /*!
    Get summary

    \return Summary
    */
    function &summary()
    {
        $databaseMap = eZSetupDatabaseMap();

        $checkPassed = true;
        foreach ( $this->PersistenceList['tests_run'] as $checkValue )
        {
            if ( $checkValue != 1 )
            {
                $checkPassed = false;
                break;
            }
        }
        if ( $checkPassed === true )
        {
            $this->Tpl->setVariable( 'system_check', 1 );
        }

        $database = $databaseMap[$this->PersistenceList['database_info']['type']]['name'];
        $this->Tpl->setVariable( 'database', $database );

        $languages = $this->PersistenceList['regional_info']['languages'];
        $this->Tpl->setVariable( 'languages', $languages );

        if ( $this->PersistenceList['email_info']['type'] == 1 )
        {
            $this->Tpl->setVariable( 'summary_email_info', 'sendmail' );
        }
        else if ( $this->PersistenceList['email_info']['type'] == 2 )
        {
            $this->Tpl->setVariable( 'summary_email_info', 'SMTP' );
        }

        $siteCount = $this->PersistenceList['site_templates']['count'];
        $sites = array();
        for ( $counter = 0; $counter < $siteCount; $counter++ )
        {
            $sites[$counter]['identifier'] = $this->PersistenceList['site_templates_'.$counter]['identifier'];
            $sites[$counter]['name'] = $this->PersistenceList['site_templates_'.$counter]['name'];
        }
        $this->Tpl->setVariable( 'sites', $sites );

        return $this->Tpl->fetch( 'design:setup/summary.tpl' );
    }

    var $Tpl;
    var $PersistenceList;
}

?>
