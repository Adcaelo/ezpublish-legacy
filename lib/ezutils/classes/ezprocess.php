<?php
//
// Definition of eZProcess class
//
// Created on: <16-Apr-2002 10:53:33 amos>
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
  \class eZProcess ezprocess.php
  \ingroup eZUtils
  \brief Executes php scripts with parameters safely

*/

include_once( "lib/ezutils/classes/ezdebug.php" );

class eZProcess
{
    function eZProcess()
    {
    }

    function &run( $file, $Params = array(), $params_as_var = false )
    {
        if ( !isset( $this ) or
             get_class( $this ) != "ezprocess" )
            $this =& eZProcess::instance();
        return $this->runFile( $Params, $file, $params_as_var );
    }

    /*!
     Helper function, executes the file.
     */
    function runFile( &$Params, $file, $params_as_var )
    {
        $Result = null;
        if ( $params_as_var )
        {
            reset( $Params );
            while( ( $key = key( $Params ) ) !== null )
            {
                if ( $key != "Params" and
                     $key != "this" and
                     $key != "file" and
                     !is_numeric( $key ) )
                    ${$key} =& $Params[$key];
                next( $Params );
            }
        }

        if ( file_exists( $file ) )
        {
            include( $file );
        }
        else
            eZDebug::writeWarning( "PHP script $file does not exist, cannot run.",
                                   "eZProcess" );
        return $Result;
    }

    function &instance()
    {
        $instance =& $GLOBALS["eZProcessInstance"];
        if ( get_class( $instance ) != "ezprocess" )
        {
            $instance = new eZProcess();
        }
        return $instance;
    }
}

?>
