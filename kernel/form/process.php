<?php
//
// Created on: <30-Jul-2003 14:46:19 bf>
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

include_once( "kernel/common/template.php" );
include_once( "lib/ezutils/classes/ezmail.php" );
include_once( 'lib/ezutils/classes/ezmailtransport.php' );

$Module =& $Params['Module'];

$tpl =& templateInit();

// Parse HTTP POST variables and generate Mail message
$formProcessed = false;
if ( count( $GLOBALS["HTTP_POST_VARS"] ) > 0 )
{
    $ini =& eZINI::instance();
    $mail = new eZMail();
    $receiver = false;
    $mailBody = "";
    $mailSubject = "eZ publish form data";
    $emailSender = "";
    $redirectURL = false;
    foreach ( array_keys( $GLOBALS["HTTP_POST_VARS"] ) as $key )
    {
        $value = $GLOBALS["HTTP_POST_VARS"][$key];

        // Check for special keys
        // Note: the duplicate checks are because of eZ publish 2.2 compatibility
        switch ( $key )
        {
            case "redirectTo":
            case "RedirectTo":
            {
                $redirectURL = trim( $value );
            }break;

            case "mailSendTo":
            case "MailSendTo":
            {
                $receiver = trim( $value );
            }
            break;

            case "mailSendFrom":
            case "MailSendFrom":
            {
                $emailSender = trim( $value );
            }
            break;

            case "mailSubject":
            case "MailSubject":
            {
                $mailSubject = trim( $value );
            }
            break;

            default:
            {
                $mailBody .= "$key:\n$value\n\n";
            }break;
        }
    }

    if ( !$mail->validate( $receiver ) )
    {
        // receiver does not contain a valid email address, get the default one
        $receiver = $ini->variable( "InformationCollectionSettings", "EmailReceiver" );
    }

    if ( !$mail->validate( $emailSender ) )
    {
        // receiver does not contain a valid email address, get the default one
        $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
    }

    $mail->setReceiver( $receiver );
    $mail->setSender( $emailSender );
    $mail->setSubject( $mailSubject );
    $mail->setBody( $mailBody );
    $mailResult = eZMailTransport::send( $mail );

    $formProcessed = true;

    if ( $redirectURL != false )
    {
        $Module->redirectTo( $redirectURL );
    }
}

$tpl->setVariable( 'form_processed', $formProcessed );
$Result = array();
$Result['content'] =& $tpl->fetch( "design:form/process.tpl" );
$Result['path'] = array( array( 'text' => ezi18n( 'kernel/form', 'Form processing' ),
                                'url' => false ) );
?>
