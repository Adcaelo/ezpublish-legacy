<?php
//
// Definition of eZSOAPServer class
//
// Created on: <14-May-2002 10:45:38 bf>
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
  \class eZSOAPServer ezsoapserver.php
  \ingroup eZSOAP
  \brief The class eZSOAPServer handles SOAP server requensts

  Sample code for a SOAP server with one function, addNumbers.
  \code
include_once( "lib/ezsoap/classes/ezsoapserver.php" );

$server = new eZSOAPServer( );
$server->registerFunction( "addNumbers", array( "valueA" => "integer", "valueB" => "integer" ) );
$server->processRequest();

function addNumbers( $valueA, $valueB )
{
    $return = $valueA + $valueB;
    settype( $return, "integer" );
    return $return;
}
  \endcode
  \sa eZSOAPClient eZSOAPRequest eZSOAPResponse

  \todo Add better fault handling
*/

include_once( "lib/ezsoap/classes/ezsoaprequest.php" );
include_once( "lib/ezsoap/classes/ezsoapresponse.php" );
include_once( "lib/ezxml/classes/ezxml.php" );

class eZSOAPServer
{
    /*!
      Creates a new eZSOAPServer object.
    */
    function eZSOAPServer()
    {
        global $HTTP_RAW_POST_DATA;
        $this->RawPostData = $HTTP_RAW_POST_DATA;
    }

    /*!
      Processes the SOAP request and prints out the
      propper response.
    */
    function processRequest()
    {
        global $HTTP_SERVER_VARS;

        if ( $HTTP_SERVER_VARS["REQUEST_METHOD"] != "POST" )
        {
            print( "Error: this web page does only understand POST methods" );
            exit();
        }

        $xmlData =& $this->stripHTTPHeader( $this->RawPostData );

        $xml = new eZXML();

        $dom =& $xml->domTree( $xmlData );

        // add namespace fetching on body
        // get the SOAP body
        $body =& $dom->elementsByName( "Body" );

        $children =& $body[0]->children();

        if ( count( $children ) == 1 )
        {
            $requestNode =& $children[0];
            // get target namespace for request
            $functionName =& $requestNode->name();
            $namespaceURI =& $requestNode->namespaceURI();

            $params = array();
            // check parameters
            foreach ( $requestNode->children() as $parameterNode )
            {
                $params[] = $parameterNode->textContent();
            }

            if ( function_exists( $functionName ) )
            {
                $value =& call_user_func_array( $functionName, $params );

                // Convert input data to XML
                $response = new eZSOAPResponse( $functionName, $namespaceURI );
                $response->setValue( $value );

                $payload = $response->payload();

                header( "SOAPServer: eZ soap" );
                header( "Content-Type: text/xml; charset=utf8" );
                Header( "Content-Length: " . strlen( $payload ) );

                ob_end_clean();

                print( $payload );
            }
        }
        else
        {
            // error
        }
    }

    /*!
      Registers a new function on the server.

      Returns false if the function could not be registered.
    */
    function registerFunction( $name, $params=array() )
    {
        foreach ( $params as $param )
        {

        }

        $this->FunctionList[] = $func;
    }


    /*!
      \static
      \private
      Strips the header information from the HTTP raw response.
    */
    function &stripHTTPHeader( $data )
    {
        $start = strpos( $data, "<?xml version=\"1.0\"?>" );
        $data =& substr( $data, $start, strlen( $data ) - $start );

        return $data;
    }

    /// Contains a list over registered functions
    var $FunctionList;
    /// Contains the RAW HTTP post data information
    var $RawPostData;
}

?>
