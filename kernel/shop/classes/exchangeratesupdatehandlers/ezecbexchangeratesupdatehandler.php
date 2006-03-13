<?php
//
// Definition of eZECBExchangeRatesUpdateHandler class
//
// Created on: <12-Mar-2006 13:06:15 dl>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
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

/*! \file ezecbexchangeratesupdatehandler.php
*/

include_once( 'kernel/shop/classes/exchangeratesupdatehandlers/ezexchangeratesupdatehandler.php' );

class eZECBExchangeRatesUpdateHandler extends eZExchangeRatesUpdateHandler
{
    function eZECBExchangeRatesUpdateHandler()
    {
        $this->ServerName = false;
        $this->ServerPort = false;
        $this->RatesURI = false;

        eZExchangeRatesUpdateHandler::eZExchangeRatesUpdateHandler();
    }

    function initialize( $params = array() )
    {
        eZExchangeRatesUpdateHandler::initialize();

        $shopINI =& eZINI::instance( 'shop.ini' );
        if ( !isset( $params['ServerName'] ) )
        {
            $params['ServerName'] = '';
            if ( $shopINI->hasVariable( 'ECBExchangeRatesSettings', 'ServerName' ) )
                $params['ServerName'] = $shopINI->variable( 'ECBExchangeRatesSettings', 'ServerName' );
        }

        if ( !isset( $params['ServerPort'] ) )
        {
            $params['ServerPort'] = '';
            if ( $shopINI->hasVariable( 'ECBExchangeRatesSettings', 'ServerPort' ) )
                $params['ServerPort'] = $shopINI->variable( 'ECBExchangeRatesSettings', 'ServerPort' );
        }

        if ( !isset( $params['RatesURI'] ) )
        {
            $params['RatesURI'] = '';
            if ( $shopINI->hasVariable( 'ECBExchangeRatesSettings', 'RatesURI' ) )
                $params['RatesURI'] = $shopINI->variable( 'ECBExchangeRatesSettings', 'RatesURI' );
        }

        if ( !isset( $params['BaseCurrency'] ) )
        {
            $params['BaseCurrency'] = '';
            if ( $shopINI->hasVariable( 'ECBExchangeRatesSettings', 'BaseCurrency' ) )
                $params['BaseCurrency'] = $shopINI->variable( 'ECBExchangeRatesSettings', 'BaseCurrency' );
        }


        $this->setServerName( $params['ServerName'] );
        $this->setServerPort( $params['ServerPort'] );
        $this->setRatesURI( $params['RatesURI'] );
        $this->setBaseCurrency( $params['BaseCurrency'] );
    }

    function requestRates( &$error )
    {
        $serverName = $this->serverName();
        $serverPort = $this->serverPort();
        $ratesURI = $this->ratesURI();

        $ratesList = array();

        include_once( 'lib/ezutils/classes/ezhttptool.php' );
        $buf =& eZHTTPTool::sendHTTPRequest( "{$serverName}/{$ratesURI}", $serverPort,  false, 'eZ publish', false );
        if ( $buf )
        {
            $header = false;
            $body = false;

            if ( eZHTTPTool::parseHTTPResponse( $buf, $header, $body ) )
            {
                if ( $header['content-type'] === 'text/xml' )
                {
                    // parse xml
                    include_once( 'lib/ezxml/classes/ezxml.php' );
                    $xml = new eZXML();
                    $domDocument =& $xml->domTree( $body );

                    $rootNode =& $domDocument->root();
                    $cubeNode = $rootNode->elementFirstChildByName( 'Cube' );
                    if ( $cubeNode )
                    {
                        $currencyNodes = $cubeNode->children();
                        foreach ( $currencyNodes as $currencyNode )
                        {
                            $currencyCode = $currencyNode->attributeValue( 'currency' );
                            $rateValue = $currencyNode->attributeValue( 'rate' );
                            $ratesList[$currencyCode] = $rateValue;
                        }
                    }
                }
                else
                {
                    $error = "Unknown body format in HTTP response. Expected 'text/xml'";
                }
            }
            else
            {
                $error = "Invalid HTTP response";
            }
        }
        else
        {
            $error = "Unable to send http request: {$serverName}:{$serverPort}/{$ratesURI}";
        }

        $this->setRateList( $ratesList );
        return ( count( $ratesList ) > 0 );
    }

    function setServerName( $serverName )
    {
        $this->ServerName = $serverName;
    }

    function serverName()
    {
        return $this->ServerName;
    }

    function setServerPort( $serverPort )
    {
        $this->ServerPort = $serverPort;
    }

    function setRatesURI( $ratesURI )
    {
        $this->RatesURI = $ratesURI;
    }

    function serverPort()
    {
        return $this->ServerPort;
    }

    function ratesURI()
    {
        return $this->RatesURI;
    }

    var $ServerName;
    var $ServerPort;
    var $RatesURI;
}

?>
