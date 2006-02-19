<?php
//
// Definition of eZCurrencyData class
//
// Created on: <08-Nov-2005 13:06:15 dl>
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

/*! \file ezcurrencydata.php
*/

define( 'EZ_CURRENCYDATA_DEFAULT_AUTO_RATE_VALUE', '0.0000' );
define( 'EZ_CURRENCYDATA_DEFAULT_CUSTOM_RATE_VALUE', '0.0000' );
define( 'EZ_CURRENCYDATA_DEFAULT_RATE_FACTOR_VALUE', '1.0000' );

define( 'EZ_CURRENCYDATA_ERROR_OK', 0 );
define( 'EZ_CURRENCYDATA_ERROR_UNKNOWN', 1 );
define( 'EZ_CURRENCYDATA_ERROR_INVALID_CURRENCY_CODE', 2 );
define( 'EZ_CURRENCYDATA_ERROR_CURRENCY_EXISTS', 3 );

define( 'EZ_CURRENCYDATA_STATUS_ACTIVE', '1' );
define( 'EZ_CURRENCYDATA_STATUS_INACTIVE', '2' );

include_once( "kernel/classes/ezpersistentobject.php" );

class eZCurrencyData extends eZPersistentObject
{
    function eZCurrencyData( $row )
    {
        $this->eZPersistentObject( $row );
        $this->RateValue = false;
    }

    function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'code' => array( 'name' => 'Code',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'symbol' => array( 'name' => 'Symbol',
                                                            'datatype' => 'string',
                                                            'default' => '',
                                                            'required' => false ),
                                         'locale' => array( 'name' => 'Locale',
                                                            'datatype' => 'string',
                                                            'default' => '',
                                                            'required' => false ),
                                         'status' => array( 'name' => 'Status',
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ),
                                         'auto_rate_value' => array( 'name' => 'AutoRateValue',
                                                                'datatype' => 'string',
                                                                'default' => EZ_CURRENCYDATA_DEFAULT_AUTO_RATE_VALUE,
                                                                'required' => false ),
                                         'custom_rate_value' => array( 'name' => 'CustomRateValue',
                                                                  'datatype' => 'string',
                                                                  'default' => EZ_CURRENCYDATA_DEFAULT_CUSTOM_RATE_VALUE,
                                                                  'required' => false ),
                                         'rate_factor' => array( 'name' => 'RateFactor',
                                                                 'datatype' => 'string',
                                                                 'default' => EZ_CURRENCYDATA_DEFAULT_RATE_FACTOR_VALUE,
                                                                 'required' => false ) ),
                      'keys' => array( 'id' ),
                      'increment_key' => 'id',
                      //'sort' => array( 'id' => 'asc' ),
                      //'keys' => array( 'code' ),
                      'function_attributes' => array( 'rate_value' => 'rateValue' ),
                      'class_name' => "eZCurrencyData",
                      'sort' => array( 'code' => 'asc' ),
                      'name' => "ezcurrencydata" );
    }

    /*!
     \static
     \params codeList can be a single code like 'USD' or an array like array( 'USD', 'NOK' )
     or 'false' (means all currencies).
    */
    function fetchList( $conditions = null, $asObjects = true, $offset = false, $limit = false, $asHash = true )
    {
        $currencyList = array();
        $sort = null;
        $limitation = null;
        if ( $offset !== false or $limit !== false )
            $limitation = array( 'offset' => $offset, 'length' => $limit );

        $rows = eZPersistentObject::fetchObjectList( eZCurrencyData::definition(),
                                                     null,
                                                     $conditions,
                                                     $sort,
                                                     $limitation,
                                                     $asObjects );

        if ( count( $rows ) > 0 )
        {
            if ( $asHash )
            {
                $keys = array_keys( $rows );
                foreach ( $keys as $key )
                {
                    if ( $asObjects )
                        $currencyList[$rows[$key]->attribute( 'code' )] =& $rows[$key];
                    else
                        $currencyList[$rows[$key]['code']] =& $rows[$key];
                }
            }
            else
            {
                $currencyList =& $rows;
            }
        }

        return $currencyList;
    }

    /*!
     \static
    */
    function fetchListCount( $conditions = null )
    {
        $rows = eZPersistentObject::fetchObjectList( eZCurrencyData::definition(),
                                                     array(),
                                                     $conditions,
                                                     null,
                                                     null,
                                                     false,
                                                     false,
                                                     array( array( 'operation' => 'count( * )',
                                                                   'name' => 'count' ) ) );
        return $rows[0]['count'];
    }

    /*!
     \static
    */
    function fetch( $currencyCode, $asObject = true )
    {
        if ( $currencyCode )
        {
            $currency = eZCurrencyData::fetchList( array( 'code' => $currencyCode ), $asObject );
            if ( is_array( $currency ) && count( $currency ) > 0 )
                return $currency[$currencyCode];
        }

        return null;
    }

    /*!
     functional attribute
    */
    function rateValue()
    {
        if ( $this->RateValue === false )
        {
            $rateValue = '0.0000';
            if ( $this->attribute( 'custom_rate_value' ) > 0 )
            {
                $rateValue = $this->attribute( 'custom_rate_value' );
            }
            else
            {
                $rateValue = $this->attribute( 'auto_rate_value' );
                $rateValue = $rateValue * $this->attribute( 'rate_factor' );
            }

            $this->RateValue = $rateValue;
        }

        return $this->RateValue;
    }

    function invalidateRateValue()
    {
        $this->RateValue = false;
    }

    /*!
     \static
    */
    function create( $code, $symbol, $locale, $autoRateValue, $customRateValue, $rateFactor, $status = EZ_CURRENCYDATA_STATUS_ACTIVE )
    {
        $code = strtoupper( $code );
        $errCode = eZCurrencyData::canCreate( $code );
        if ( $errCode === EZ_CURRENCYDATA_ERROR_OK )
        {
            $currency = new eZCurrencyData( array( 'code' => $code,
                                                   'symbol' => $symbol,
                                                   'locale' => $locale,
                                                   'status' => $status,
                                                   'auto_rate_value' => $autoRateValue,
                                                   'custom_rate_value' => $customRateValue,
                                                   'rate_factor' => $rateFactor ) );
            $currency->setHasDirtyData( true );
            return $currency;
        }

        return $errCode;
    }

    /*!
     \static
   */
    function canCreate( $code )
    {
        $errCode = eZCurrencyData::validateCurrencyCode( $code );
        if ( $errCode === EZ_CURRENCYDATA_ERROR_OK && eZCurrencyData::currencyExists( $code ) )
            $errCode = EZ_CURRENCYDATA_ERROR_CURRENCY_EXISTS;

        return $errCode;
    }

    /*!
     \static
    */
    function validateCurrencyCode( $code )
    {
        if ( !preg_match( "/^[A-Z]{3}$/", $code ) )
            return EZ_CURRENCYDATA_ERROR_INVALID_CURRENCY_CODE;

        return EZ_CURRENCYDATA_ERROR_OK;
    }

    /*!
     \static
    */
    function currencyExists( $code )
    {
        return ( eZCurrencyData::fetch( $code ) !== null );
    }

    /*!
     \static
    */
    function removeCurrencyList( $currencyCodeList )
    {
        if ( is_array( $currencyCodeList ) && count( $currencyCodeList ) > 0 )
        {
            $db =& eZDB::instance();
            $db->begin();
                eZPersistentObject::removeObject( eZCurrencyData::definition(),
                                                  array( 'code' => array( $currencyCodeList ) ) );
            $db->commit();
        }
    }

    function setStatus( $status )
    {
        $statusNumeric = eZCurrencyData::statusStringToNumeric( $status );
        if ( $statusNumeric !== false )
            $this->setAttribute( 'status', $statusNumeric );
        else
            eZDebug::writeError( "Unknow currency's status '$status'", 'eZCurrencyData::setStatus' );
    }

    function statusStringToNumeric( $statusString )
    {
        $status = false;
        if ( is_numeric( $statusString ) )
        {
            $status = $statusString;
        }
        if ( is_string( $statusString ) )
        {
            $statusString = strtoupper( $statusString );
            if ( defined( "EZ_CURRENCYDATA_STATUS_$statusString" ) )
                $status = constant( "EZ_CURRENCYDATA_STATUS_$statusString" );
        }

        return $status;
    }

    /*!
     \static
    */
    function errorMessage( $errorCode )
    {
        switch ( $errorCode )
        {
            case EZ_CURRENCYDATA_ERROR_INVALID_CURRENCY_CODE:
                return ezi18n( 'kernel/shop/classes/ezcurrencydata', 'Invalid characters in currency code.' );

            case EZ_CURRENCYDATA_ERROR_CURRENCY_EXISTS:
                return ezi18n( 'kernel/shop/classes/ezcurrencydata', 'Currency already exists.' );

            case EZ_CURRENCYDATA_ERROR_UNKNOWN:
            default:
                return ezi18n( 'kernel/shop/classes/ezcurrencydata', 'Unknown error.' );
        }
    }

    function store( $fieldFilters = null )
    {
        // data changed => reset RateValue
        $this->invalidateRateValue();
        eZPersistentObject::store( $fieldFilters );
    }

    var $RateValue;
}

?>