<?php
//
// Definition of eZCodePage class
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
  \class eZCodePage ezcodepage.php
  \ingroup eZI18N
  \brief Handles codepage files for charset mapping

*/

include_once( "lib/ezutils/classes/ezdebug.php" );
include_once( "lib/ezi18n/classes/ezutf8codec.php" );
include_once( "lib/ezi18n/classes/ezcharsetinfo.php" );

define( "EZ_CODEPAGE_CACHE_CODE_DATE", 1028204478 );

class eZCodePage
{
    /*!
     Initializes the codepage with the charset code $charset_code, and then loads it.
    */
    function eZCodePage( $charset_code, $use_cache = true )
    {
        $this->RequestedCharsetCode = $charset_code;
        $this->CharsetCode = eZCharsetInfo::realCharsetCode( $charset_code );
        $this->CharsetEncodingScheme = eZCharsetInfo::characterEncodingScheme( $charset_code );
        $this->Valid = false;
        $this->SubstituteChar = 63; // the ? character
        $this->MinCharValue = 0;
        $this->MaxCharValue = 0;

        $this->load( $use_cache );
    }

    function &convertString( &$str )
    {
        $len = strlen( $str );
        $chars = array();
        $utf8_codec =& eZUTF8Codec::instance();
        for ( $i = 0; $i < $len; )
        {
            $charLen = 1;
            $char = $this->charToUTF8( $str, $i, $charLen );
            if ( $char !== null )
                $chars[] = $char;
            else
                $chars[] = $utf8_codec->toUtf8( $this->SubstituteChar );
            $i += $charLen;
        }
        return implode( '', $chars );
    }

    function &convertStringFromUTF8( &$str )
    {
        $len = strlen( $str );
        $chars = array();
        $utf8_codec =& eZUTF8Codec::instance();
        for ( $i = 0; $i < $len; )
        {
            $charLen = 1;
            $ucode = $utf8_codec->fromUtf8( $str, $i, $charLen );
            $chars[] = $this->unicodeToChar( $ucode );
            $i += $charLen;
        }
        return implode( '', $chars );
    }

    function strlen( &$str )
    {
        if ( $this->CharsetEncodingScheme == "doublebyte" )
        {
            $len = strlen( $str );
            $strlen = 0;
            for ( $i = 0; $i < $len; )
            {
                $charLen = 1;
                $code = ord( $str[$i] );
                if ( isset( $this->ReadExtraMap[$code] ) )
                    $charLen = 2;
                ++$strlen;
                $i += $charLen;
            }
            return $strlen;
        }
        else
            return strlen( $str );
    }

    function strlenFromUTF8( &$str )
    {
        $utf8_codec =& eZUTF8Codec::instance();
        return $utf8_codec->strlen( $str );
    }

    function &charToUtf8( &$str, $pos, &$charLen )
    {
        $code = ord( $str[$pos] );
        $charLen = 1;
        if ( isset( $this->ReadExtraMap[$code] ) )
        {
            $code = ( $code << 8 ) | ord( $str[$pos+1] );
            $charLen = 2;
        }
        if ( isset( $this->UTF8Map[$code] ) )
            return $this->UTF8Map[$code];
        return null;
    }

    function &charToUnicode( &$char )
    {
        $code = ord( $str[$pos] );
        $charLen = 1;
        if ( isset( $this->ReadExtraMap[$code] ) )
        {
            $code = ( $code << 8 ) | ord( $str[$pos+1] );
            $charLen = 2;
        }
        if ( isset( $this->UnicodeMap[$code] ) )
            return $this->UnicodeMap[$code];
        return null;
    }

    function &codeToUtf8( &$code )
    {
        return $this->UTF8Map[$code];
    }

    function &codeToUnicode( &$code )
    {
        return $this->UnicodeMap[$code];
    }

    function &utf8ToChar( &$ucode )
    {
        if ( isset( $this->UTF8CodeMap[$ucode] ) )
        {
            $code = $this->UTF8CodeMap[$ucode];
            if ( $code <= 0xff )
                return chr( $code );
            else
                return chr( ( $code >> 8 ) & 0xff ) . chr( $code & 0xff );
        }
        else
            return chr( $this->SubstituteChar );
    }

    function &unicodeToChar( &$ucode )
    {
        if ( isset( $this->CodeMap[$ucode] ) )
        {
            $code = $this->CodeMap[$ucode];
            if ( $code <= 0xff )
                return chr( $code );
            else
                return chr( ( $code >> 8 ) & 0xff ) . chr( $code & 0xff );
        }
        else
            return chr( $this->SubstituteChar );
    }

    function &utf8ToCode( &$ucode )
    {
        if ( isset( $this->UTF8CodeMap[$ucode] ) )
            return $this->UTF8CodeMap[$ucode];
        return null;
    }

    function &unicodeToCode( &$ucode )
    {
        if ( isset( $this->CodeMap[$ucode] ) )
            return $this->CodeMap[$ucode];
        return null;
    }

    function substituteChar()
    {
        return $this->SubstituteChar;
    }

    function setSubstituteChar( $char )
    {
        $this->SubstituteChar = $char;
    }

    /*!
     \static
     Returns true if the codepage $charset_code exists.
    */
    function exists( $charset_code )
    {
        $file = eZCodePage::fileName( $charset_code );
        return file_exists( $file );
    }

    /*!
     \static
     Returns the filename of the charset code \a $charset_code.
    */
    function fileName( $charset_code )
    {
        $charset_code = eZCharsetInfo::realCharsetCode( $charset_code );
        $file = "share/codepages/" . $charset_code;
        return $file;
    }

    function cacheFileName( $charset_code )
    {
        $charset_code = eZCharsetInfo::realCharsetCode( $charset_code );
        $cache_dir = "var/cache/codepages/";
        $cache_filename = md5( $charset_code );
        $cache = $cache_dir . $cache_filename . ".php";
        return $cache;
    }

    function fileModification( $charset_code )
    {
        $file = eZCodePage::fileName( $charset_code );
        if ( !file_exists( $file ) )
            return false;
        return filemtime( $file );
    }

    function codepageList()
    {
        $list = array();
        $dir = "share/codepages/";
        $dh = opendir( $dir );
        while ( ( $file = readdir( $dh ) ) !== false )
        {
            if ( $file == "." or
                 $file == ".." or
                 preg_match( "/^\./", $file ) or
                 preg_match( "/~$/", $file ) )
                continue;
            $list[] = $file;
        }
        closedir( $dh );
        sort( $list );
        return $list;
    }

    /*!
     Loads the codepage from disk.
     If $use_cache is true and a cached version is found it is used instead.
     If $use_cache is true and no cache was found a new cache is created.
    */
    function load( $use_cache = true )
    {
        $file = "share/codepages/" . $this->CharsetCode;
        $cache_dir = "var/cache/codepages/";
        $cache_filename = md5( $this->CharsetCode );
        $cache = $cache_dir . $cache_filename . ".php";

        if ( !file_exists( $file ) )
        {
            eZDebug::writeWarning( "Couldn't load codepage file $file", "eZCodePage" );
            return;
        }
        $file_m = filemtime( $file );
        $this->Valid = false;
        if ( file_exists( $cache ) and $use_cache )
        {
            $cache_m = filemtime( $cache );
            if ( $file_m <= $cache_m )
            {
                unset( $eZCodePageCacheCodeDate );
                $umap =& $this->UnicodeMap;
                $utf8map =& $this->UTF8Map;
                $cmap =& $this->CodeMap;
                $utf8cmap =& $this->UTF8CodeMap;
                $min_char =& $this->MinCharValue;
                $max_char =& $this->MaxCharValue;
                $read_extra =& $this->ReadExtraMap;
                include( $cache );
                unset( $umap );
                unset( $utf8map );
                unset( $cmap );
                unset( $utf8map );
                unset( $min_char );
                unset( $max_char );
                unset( $read_extra );
                if ( isset( $eZCodePageCacheCodeDate ) and
                     $eZCodePageCacheCodeDate == EZ_CODEPAGE_CACHE_CODE_DATE )
                {
                    $this->Valid = true;
                    return;
                }
            }
        }

        $utf8_codec =& eZUTF8Codec::instance();

        $this->UnicodeMap = array();
        $this->UTF8Map = array();
        $this->CodeMap = array();
        $this->UTF8CodeMap = array();
        $this->ReadExtraMap = array();
        for ( $i = 0; $i < 32; ++$i )
        {
            $code = $i;
            $ucode = $i;
            $utf8_code = $utf8_codec->toUtf8( $ucode );
            $this->UnicodeMap[$code] = $ucode;
            $this->UTF8Map[$code] = $utf8_code;
            $this->CodeMap[$ucode] = $code;
            $this->UTF8CodeMap[$utf8_code] = $code;
        }
        $this->MinCharValue = 0;
        $this->MaxCharValue = 31;

        $lines =& file( $file );
        reset( $lines );
        while ( ( $key = key( $lines ) ) !== null )
        {
            if ( preg_match( "/^#/", $lines[$key] ) )
            {
                next( $lines );
                continue;
            }
            $line = trim( $lines[$key] );
            $items = explode( "\t", $line );
            if ( count( $items ) == 3 )
            {
                $code = false;
                $ucode = false;
                $desc = $items[2];
                if ( preg_match( "/(=|0x)([0-9a-fA-F]{4})/", $items[0], $args ) )
                {
                    $code = hexdec( $args[2] );
                    eZDebug::writeNotice( $args, "doublebyte" );
                }
                else if ( preg_match( "/(=|0x)([0-9a-fA-F]{2})/", $items[0], $args ) )
                {
                    $code = hexdec( $args[2] );
                    eZDebug::writeNotice( $args, "singlebyte" );
                }
                if ( preg_match( "/(U\+|0x)([0-9a-fA-F]{4})/", $items[1], $args ) )
                {
                    $ucode = hexdec( $args[2] );
                }
                if ( $code !== false and
                     $ucode !== false )
                {
                    $utf8_code = $utf8_codec->toUtf8( $ucode );
                    $this->UnicodeMap[$code] = $ucode;
                    $this->UTF8Map[$code] = $utf8_code;
                    $this->CodeMap[$ucode] = $code;
                    $this->UTF8CodeMap[$utf8_code] = $code;
                    $this->MinCharValue = min( $this->MinCharValue, $code );
                    $this->MaxCharValue = max( $this->MaxCharValue, $code );
                }
                else if ( $code !== false )
                {
                    $this->ReadExtraMap[$code] = true;
                }
            }
            next( $lines );
        }
        $this->Valid = true;

        if ( $use_cache )
        {
            $str = "\$umap = array();\n\$utf8map = array();\n\$cmap = array();\n\$utf8cmap = array();\n";
            reset( $this->UnicodeMap );
            while ( ( $key = key( $this->UnicodeMap ) ) !== null )
            {
                $item =& $this->UnicodeMap[$key];
                $str .= "\$umap[$key] = $item;\n";
                next( $this->UnicodeMap );
            }
            reset( $this->UTF8Map );
            while ( ( $key = key( $this->UTF8Map ) ) !== null )
            {
                $item =& $this->UTF8Map[$key];
                $val = str_replace( array( "\\", "'" ),
                                    array( "\\\\", "\\'" ),
                                    $item );
                $str .= "\$utf8map[$key] = '$val';\n";
                next( $this->UTF8Map );
            }
            reset( $this->CodeMap );
            while ( ( $key = key( $this->CodeMap ) ) !== null )
            {
                $item =& $this->CodeMap[$key];
                $str .= "\$cmap[$key] = $item;\n";
                next( $this->CodeMap );
            }
            reset( $this->UTF8CodeMap );
            while ( ( $key = key( $this->UTF8CodeMap ) ) !== null )
            {
                $item =& $this->UTF8CodeMap[$key];
                if ( $item == 0 )
                {
                    $str .= "\$utf8cmap[chr(0)] = 0;\n";
                }
                else
                {
                    $val = str_replace( array( "\\", "'" ),
                                        array( "\\\\", "\\'" ),
                                        $key );
                    $str .= "\$utf8cmap['$val'] = $item;\n";
                }
                next( $this->UTF8CodeMap );
            }
            reset( $this->ReadExtraMap );
            while ( ( $key = key( $this->ReadExtraMap ) ) !== null )
            {
                $item =& $this->ReadExtraMap[$key];
                $str .= "\$read_extra[$key] = $item;\n";
                next( $this->ReadExtraMap );
            }
            $this->MinCharValue = min( $this->MinCharValue, $code );
            $this->MaxCharValue = max( $this->MaxCharValue, $code );
            $str = "<?" . "php
$str
\$eZCodePageCacheCodeDate = " . EZ_CODEPAGE_CACHE_CODE_DATE . ";
\$min_char = " . $this->MinCharValue . ";
\$max_char = " . $this->MaxCharValue . ";
?" . ">";
            if ( !file_exists( $cache_dir ) )
			{
                if ( ! @mkdir( $cache_dir, 0777 ) )
					eZDebug::writeError( "Couldn't create cache directory $cache_dir, perhaps wrong permissions", "eZCodepage" );					
			}
            $fd = @fopen( $cache, "w+" );
			if ( ! $fd )
			{
				eZDebug::writeError( "Couldn't write cache file $cache, perhaps wrong permissions or leading directories not created", "eZCodepage" );
			}
			else
			{
            	fwrite( $fd, $str );
            	fclose( $fd );
			}
        }
    }

    /*!
     \return the charset code which is in use. This may not be the charset that was
     requested due to aliases.
     \sa requestedCharsetCode
    */
    function charsetCode()
    {
        return $this->CharsetCode;
    }

    /*!
     \return the charset code which was requested, may differ from charsetCode()
    */
    function requestedCharsetCode()
    {
        return $this->RequestedCharsetCode;
    }

    /*!
     \return the lowest character value used in the mapping table.
    */
    function minCharValue()
    {
        return $this->MinCharValue;
    }

    /*!
     \return the largest character value used in the mapping table.
    */
    function maxCharValue()
    {
        return $this->MaxCharValue;
    }

    /*!
     Returns true if the codepage is valid for use.
    */
    function isValid()
    {
        return $this->Valid;
    }

    /*!
     Returns the only instance of the codepage for $charset_code.
    */
    function &instance( $charset_code, $use_cache = true )
    {
        $cp =& $GLOBALS["eZCodePage-$charset_code"];
        if ( get_class( $cp ) != "ezcodepage" )
        {
            $cp = new eZCodePage( $charset_code, $use_cache );
        }
        return $cp;
    }

    /// \privatesection
    /// The charset code which was requested, may differ from $CharsetCode
    var $RequestedCharsetCode;
    /// The read charset code, may differ from $RequestedCharsetCode
    var $CharsetCode;
    /// Encoding scheme for current charset, for instance utf-8, singlebyte, multibyte
    var $CharsetEncodingScheme;
    /// Maps normal codes to unicode
    var $UnicodeMap;
    /// Maps normal codes to utf8
    var $UTF8Map;
    /// Maps unicode to normal codes
    var $CodeMap;
    /// Maps utf8 to normal codes
    var $UTF8CodeMap;
    /// The minimum key value for the mapping tables
    var $MinCharValue;
    /// The maximum key value for the mapping tables
    var $MaxCharValue;
    /// Whether the codepage is valid or not
    var $Valid;
    /// The character to use when an alternative doesn't exist
    var $SubstituteChar;
}

?>
