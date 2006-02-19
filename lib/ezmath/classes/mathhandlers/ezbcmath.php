<?php
//
// Definition of eZBCMath class
//
// Created on: <04-Nov-2005 12:26:52 dl>
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

/*! \file ezbcmath.php
*/

/*!
  \class eZBCMath ezbcmath.php
  \brief Handles calculation using bcmath library.
*/

include_once( 'lib/ezmath/classes/mathhandlers/ezphpmath.php' );

define( 'EZ_BCMATH_DEFAULT_SCALE', 10 );

class eZBCMath extends eZPHPMath
{
    function eZBCMath( $params = array () )
    {
        if( isset( $params['scale'] ) && is_numeric( $params['scale'] ) )
            $this->setScale( $params['scale'] );
        else
            $this->setScale( EZ_BCMATH_DEFAULT_SCALE );
    }

    function scale()
    {
        return $this->Scale;
    }

    function setScale( $scale )
    {
        $this->Scale = $scale;
    }

    function add( $a, $b )
    {
        return ( bcadd( $a, $b, $this->Scale ) );
    }

    function sub( $a, $b )
    {
        return ( bcsub( $a, $b, $this->Scale ) );
    }

    function mul( $a, $b )
    {
        return ( bcmul( $a, $b, $this->Scale ) );
    }

    function div( $a, $b )
    {
        return ( bcdiv( $a, $b, $this->Scale ) );
    }

    function pow( $base, $exp )
    {
        return ( bcpow( $base, $exp, $this->Scale ) );
    }

    function round( $value, $precision, $target )
    {
        $result = $value;
        $fractPart = $this->fractval( $value, $precision + 1 );
        if ( strlen( $fractPart ) > $precision )
        {
            $lastDigit = (int)substr( $fractPart, -1, 1 );
            $fractPart = substr( $fractPart, 0, $precision );
            if ( $lastDigit >= 5 )
                $fractPart = $this->add( $fractPart, 1 );

            $fractPart = $this->div( $fractPart, $this->pow( 10, $precision ) );

            $result = $this->add( $this->intval( $value ), $fractPart );
            $result = $this->adjustFractPart( $result, $precision, $target );
        }

        return $result;
    }


    /// \privatesection
    var $Scale;
};

?>