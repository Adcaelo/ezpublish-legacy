<?php
//
// Definition of eZMailTransport class
//
// Created on: <10-Dec-2002 14:31:35 amos>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.x
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

/*! \file ezmailtransport.php
*/

/*!
  \class eZMailTransport ezmailtransport.php
  \brief Interface for mail transport handling

*/

class eZMailTransport
{
    /*!
     Constructor
    */
    function eZMailTransport()
    {
    }

    /*!
     \virtual
     Tries to send the contents of the email object \a $mail and
     returns \c true if succesful.
    */
    function sendMail( &$mail )
    {
        if ( get_class( $mail ) != 'ezmail' )
        {
            eZDebug::writeError( 'Can only handle objects of type eZMail.', 'eZMailTransport::sendMail' );
            return false;
        }
        return false;
    }

    /*!
     \static
     Sends the contents of the email object \a $mail using the default transport.
    */
    function send( &$mail )
    {
        if ( get_class( $mail ) != 'ezmail' )
        {
            eZDebug::writeError( 'Can only handle objects of type eZMail.', 'eZMailTransport::send' );
            return false;
        }
        $ini =& eZINI::instance();

        $transportType = trim( $ini->variable( 'MailSettings', 'Transport' ) );
        $transportObject =& $GLOBALS['eZMailTransportHandler_' . strtolower( $transportType )];
        if ( !isset( $transportObject ) or
             !is_object( $transportObject ) )
        {
            $transportClassFile = 'ez' . strtolower( $transportType ) . 'transport.php';
            $transportClassPath = 'lib/ezutils/classes/' . $transportClassFile;
            $transportClass = 'eZ' . $transportType . 'Transport';
            if ( !file_exists( $transportClassPath ) )
            {
                eZDebug::writeError( "Unknown mail transport type '$transportType', cannot send mail",
                                     'eZMailTransport::send' );
                return false;
            }
            include_once( $transportClassPath );
            if ( !class_exists( $transportClass ) )
            {
                eZDebug::writeError( "No class available for mail transport type '$transportType', cannot send mail",
                                     'eZMailTransport::send' );
                return false;
            }
            $transportObject = new $transportClass();
        }
        return $transportObject->sendMail( $mail );
    }
}

?>
