<?php
//
// Definition of eZTemplateArrayOperator class
//
// Created on: <05-Mar-2002 12:52:10 amos>
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

/*!
  \class eZTemplateArrayOperator eztemplatearrayoperator.php
  \ingroup eZTemplateOperators
  \brief Dynamic creation of arrays using operator "array"

  Creates an operator which can create arrays dynamically by
  adding all operator parameters as array elements.

\code
// Example template code
{array(1,"test")}
{array(array(1,2),3)}
\endcode

*/

include_once( "lib/eztemplate/classes/eztemplate.php" );

class eZTemplateArrayOperator
{
    /*!
     Initializes the array operator with the operator name $name.
    */
    function eZTemplateArrayOperator( $arrayName        = 'array',
                                      $hashName         = 'hash',
                                      $arrayPrependName = 'array_prepend', // DEPRECATED/OBSOLETE
                                      $prependName      = 'prepend',       // New, replaces array_prepend.
                                      $arrayAppendName  = 'array_append',  // DEPRECATED/OBSOLETE
                                      $appendName       = 'append',        // New, replaces array_append.
                                      $arrayMergeName   = 'array_merge',   // DEPRECATED/OBSOLETE
                                      $mergeName        = 'merge',         // New, replaces array_merge.
                                      $containsName     = 'contains',
                                      $compareName      = 'compare',
                                      $extractName      = 'extract',
                                      $extractLeftName  = 'extract_left',
                                      $extractRightName = 'extract_right',
                                      $beginsWithName   = 'begins_with',
                                      $endsWithName     = 'ends_with',
                                      $implodeName      = 'implode',
                                      $explodeName      = 'explode',
                                      $repeatName       = 'repeat',
                                      $reverseName      = 'reverse',
                                      $insertName       = 'insert',
                                      $removeName       = 'remove',
                                      $replaceName      = 'replace',
                                      $uniqueName       = 'unique' )
    {
        $this->ArrayName        = $arrayName;
        $this->HashName         = $hashName;
        $this->ArrayPrependName = $arrayPrependName; // DEPRECATED/OBSOLETE
        $this->PrependName      = $prependName;      // New, replaces ArrayPrependName.
        $this->ArrayAppendName  = $arrayAppendName;  // DEPRECATED/OBSOLETE
        $this->AppendName       = $appendName;       // New, replaces ArrayAppendName.
        $this->ArrayMergeName   = $arrayMergeName;   // DEPRECATED/OBSOLETE
        $this->MergeName        = $mergeName;        // New, replaces ArrayMergeName.
        $this->ContainsName     = $containsName;
        $this->CompareName      = $compareName;
        $this->ExtractName      = $extractName;
        $this->ExtractLeftName  = $extractLeftName;
        $this->ExtractRightName = $extractRightName;
        $this->BeginsWithName   = $beginsWithName;
        $this->EndsWithName     = $endsWithName;
        $this->ImplodeName      = $implodeName;
        $this->ExplodeName      = $explodeName;
        $this->RepeatName       = $repeatName;
        $this->ReverseName      = $reverseName;
        $this->InsertName       = $insertName;
        $this->RemoveName       = $removeName;
        $this->ReplaceName      = $replaceName;
        $this->UniqueName       = $uniqueName;

        $this->Operators = array( $arrayName,
                                  $hashName,
                                  $arrayPrependName, // DEPRECATED/OBSOLETE
                                  $prependName,      // New, replaces arrayPrependName.
                                  $arrayAppendName,  // DEPRECATED/OBSOLETE
                                  $appendName,       // New, replaces arrayAppendName.
                                  $arrayMergeName,   // DEPRECATED/OBSOLETE
                                  $mergeName,        // New, replaces arrayMergeName.
                                  $containsName,
                                  $compareName,
                                  $extractName,
                                  $extractLeftName,
                                  $extractRightName,
                                  $beginsWithName,
                                  $endsWithName,
                                  $implodeName,
                                  $explodeName,
                                  $repeatName,
                                  $reverseName,
                                  $insertName,
                                  $removeName,
                                  $replaceName,
                                  $uniqueName );
    }

    /*!
     Returns the operators in this class.
    */
    function &operatorList()
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type.
    */
    function namedParameterPerOperator()
    {
        return true;
    }
    /*!
     See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array( $this->RemoveName  => array( 'offset'            => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ),
                                                   'length'            => array( "type"      => "integer",
                                                                                 "required"  => false,
                                                                                 "default"   => 1 ) ),
                      $this->RepeatName  => array( 'repeat_times'      => array( "type"      => "integer",
                                                                                 "required"  => false,
                                                                                 "default"   => 1 ) ),
                      $this->InsertName  => array( 'insert_position'   => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ),
                                                   'insert_string'     => array( "type"      => "string",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ExplodeName => array( 'explode_first'     => array( "type"      => "mixed",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ExtractName => array( 'extract_start'     => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ),
                                                   'extract_length'    => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ExtractLeftName  => array( 'length'       => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ExtractRightName => array( 'length'       => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ReplaceName      => array( 'offset'       => array( "type"      => "integer",
                                                                                 "required"  => true,
                                                                                 "default"   => false),
                                                        'length'       => array( "type"      => "integer",
                                                                                 "required"  => false,
                                                                                 "default"   => false) ),
                      $this->AppendName     => array( 'append_string'  => array ("type"      => "string",
                                                                                 "required"  => false,
                                                                                 "default"   => false ) ),
                      $this->PrependName    => array( 'prepend_string' => array( "type"      => "string",
                                                                                 "required"  => false,
                                                                                 "default"   => false ) ),
                      $this->ContainsName   => array( 'match'          => array( "type"      => "string",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->BeginsWithName => array( 'match'          => array( "type"      => "string",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->EndsWithName   => array( 'match'          => array( "type"      => "string",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ),
                      $this->ImplodeName    => array( 'separator'      => array( "type"      => "string",
                                                                                 "required"  => true,
                                                                                 "default"   => false) ),
                      $this->CompareName    => array( 'compare'        => array( "type"      => "mixed",
                                                                                 "required"  => true,
                                                                                 "default"   => false ) ) );
    }

    // Takes care of the various operator functions.
    function modify( &$tpl, &$operatorName, &$operatorParameters,
                     &$rootNamespace, &$currentNamespace, &$operatorValue,
                     &$namedParameters )
    {
        switch( $operatorName )
        {
            // Create/build an array:
            case $this->ArrayName:
            {
                $operatorValue = array();
                for ( $i = 0; $i < count( $operatorParameters ); ++$i )
                {
                    $operatorValue[] =& $tpl->elementValue( $operatorParameters[$i],
                                                            $rootNamespace,
                                                            $currentNamespace );
                }
                return;
            }break;

            // __FIX_ME__
            case $this->HashName:
            {
                $operatorValue = array();
                $hashCount = (int)( count( $operatorParameters ) / 2 );
                for ( $i = 0; $i < $hashCount; ++$i )
                {
                    $hashName = $tpl->elementValue( $operatorParameters[$i*2],
                                                    $rootNamespace,
                                                    $currentNamespace );
                    if ( is_string( $hashName ) or
                         is_numeric( $hashName ) )
                        $operatorValue[$hashName] =& $tpl->elementValue( $operatorParameters[($i*2)+1],
                                                                         $rootNamespace,
                                                                         $currentNamespace );
                    else
                        $tpl->error( $operatorName,
                                     "Unknown hash key type '" . gettype( $hashName ) . "', skipping" );
                }
                return;
            }
            break;
        }

        $isArray = false;
        if ( isset( $operatorParameters[0] ) and
             is_array( $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace ) ) )
            $isArray = true;

        if ( is_array( $operatorValue ) )
            $isArray = true;

        if ( $isArray )
        {
            switch( $operatorName )
            {
                // Append or prepend an array (or single elements) to the target array:
                case $this->ArrayPrependName:
                case $this->ArrayAppendName:
                case $this->PrependName:
                case $this->AppendName:
                {
                    $i = 0;
                    if ( is_array( $operatorValue ) )
                    {
                        if ( count( $operatorParameters ) < 1 )
                        {
                            $tpl->error( $operatorName,
                                         "Requires at least one item!" );
                            return;
                        }
                        $mainArray = $operatorValue;
                    }
                    else
                    {
                        if ( count( $operatorParameters ) < 2 )
                        {
                            $tpl->error( $operatorName,
                                         "Requires an array (and at least one item)!" );
                            return;
                        }
                        $mainArray =& $tpl->elementValue( $operatorParameters[$i++],
                                                          $rootNamespace,
                                                          $currentNamespace );
                    }
                    $tmpArray = array();
                    for ( ; $i < count( $operatorParameters ); ++$i )
                    {
                        $tmpArray[] =& $tpl->elementValue( $operatorParameters[$i],
                                                           $rootNamespace,
                                                           $currentNamespace );
                    }
                    if ( $operatorName == $this->ArrayPrependName )
                        $operatorValue = array_merge( $tmpArray, $mainArray );
                    else
                        $operatorValue = array_merge( $mainArray, $tmpArray );

                }
                break;

                // Merge two arrays:
                case $this->ArrayMergeName:
                case $this->MergeName:
                {
                    $tmpArray   = array();
                    $tmpArray[] = $operatorValue;

                    if ( count( $operatorParameters ) < 2 )
                    {
                        $tpl->error( $operatorName, "Requires an array (and at least one item!)" );
                        return;
                    }

                    for ( $i = 0; $i < count( $operatorParameters ); ++$i )
                    {
                        $tmpArray[] =& $tpl->elementValue( $operatorParameters[$i],
                                                           $rootNamespace,
                                                           $currentNamespace );
                    }
                    $operatorValue = call_user_func_array( 'array_merge', $tmpArray );
                }break;

                // Check if the array contains a specified element:
                case $this->ContainsName:
                {
                    if ( count( $operatorParameters ) < 1 )
                    {
                        $tpl->error( $operatorName, "Missing matching value!" );
                        return;
                    }
                    $matchValue =& $tpl->elementValue( $operatorParameters[0],
                                                       $rootNamespace,
                                                       $currentNamespace );

                    $operatorValue = in_array( $matchValue, $operatorValue );
                }
                break;

                // Compare two arrays:
                case $this->CompareName:
                {
                    if ( array_diff( $operatorValue, $namedParameters['compare'] ) )
                    {
                        $operatorValue = false;
                    }
                    else
                    {
                        $operatorValue = true;
                    }
                }
                break;

                // Extract a portion of the array:
                case $this->ExtractName:
                {
                    $operatorValue = array_slice( $operatorValue, $namedParameters['extract_start'], $namedParameters['extract_length'] );
                }
                break;

                // Extract a portion from the start of the array:
                case $this->ExtractLeftName:
                {
                    $operatorValue = array_slice( $operatorValue, 0,  $namedParameters['length'] );
                }break;

                // Extract a portion from the end of the array:
                case $this->ExtractRightName:
                {
                    $index = count( $operatorValue ) - $namedParameters['length'];
                    $operatorValue = array_slice( $operatorValue, $index );
                }break;

                // Check if the array begins with a given sequence:
                case $this->BeginsWithName:
                {
                    for ( $i = 0; $i < count( $operatorParameters ); $i++ )
                    {
                        $test = $tpl->elementValue( $operatorParameters[$i],
                                                    $rootNamespace,
                                                    $currentNamespace );

                        if ( $operatorValue[$i] != $test )
                        {
                            $operatorValue = false;
                            return;
                        }
                    }

                    $operatorValue = true;
                }break;

                // Check if the array ends with a given sequence:
                case $this->EndsWithName:
                {
                    $length = count( $operatorValue );
                    $params = count( $operatorParameters );

                    $start = $length - $params;

                    for ( $i = 0; $i < $params; $i++ )
                    {
                        $test = $tpl->elementValue( $operatorParameters[$i],
                                                    $rootNamespace,
                                                    $currentNamespace );

                        if ( $operatorValue[$start+$i] != $test )
                        {
                            $operatorValue = false;
                            return;
                        }
                    }
                    $operatorValue = true;
                }break;

                // Create a string containing the array elements with the separator string between elements.
                case $this->ImplodeName:
                {
                    $operatorValue = implode( $operatorValue, $namedParameters['separator'] );
                }break;

                // Explode the array by making smaller arrays of it:
                case $this->ExplodeName:
                {
                    $array_one = array();
                    $array_two = array();

                    $array_one = array_slice( $operatorValue, 0, $namedParameters['explode_first'] );
                    $array_two = array_slice( $operatorValue, $namedParameters['explode_first'] );

                    $operatorValue = array( $array_one, $array_two );
                }break;

                // Repeat the contents of an array a specified number of times:
                case $this->RepeatName:
                {
                    for ( $i = 0; $i < $namedParameters['repeat_times']; $i++)
                    {
                        $operatorValue = array_merge( $operatorValue, $operatorValue );
                    }
                }break;

                // Reverse the contents of the array:
                case $this->ReverseName:
                {
                    $operatorValue = array_reverse( $operatorValue );
                }break;

                // Insert an array (or element) into a position in the target array:
                case $this->InsertName:
                {
                    $array_one = array_slice( $operatorValue, 0, $namedParameters['insert_position'] );
                    $array_two = array_slice( $operatorValue, $namedParameters['insert_position'] );


                    $array_to_insert = array();
                    for ( $i = 1; $i < count( $operatorParameters ); ++$i )
                    {
                        $array_to_insert[] =& $tpl->elementValue( $operatorParameters[$i],
                                                                  $rootNamespace,
                                                                  $currentNamespace );
                    }

                    $operatorValue = array_merge( $array_one, $array_to_insert, $array_two );
                }break;

                // Remove a specified element (or portion) from the target array:
                case $this->RemoveName:
                {
                    $array_one = array_slice( $operatorValue, 0, $namedParameters['offset'] );
                    $array_two = array_slice( $operatorValue, $namedParameters['offset'] + $namedParameters['length'] );

                    $operatorValue = array_merge( $array_one, $array_two );
                }break;

                // Replace a portion of the array:
                case $this->ReplaceName:
                {
                }break;

                // Removes duplicate values from array:
                case $this->UniqueName:
                {
                    $operatorValue = array_unique( $operatorValue );
                }break;

                // Default case:
                default:
                {
                    $tpl->warning( $operatorName, "Unknown operatorname: $operatorName" );
                }
                break;
            }
        }
        else if ( is_string( $operatorValue ) )
        {
            switch( $operatorName )
            {
                // Not implemented.
                case $this->ArrayName:
                {
                    $tpl->warning( $operatorName, "$operatorName works only with arrays." );
                }break;

                // Not implemented.
                case $this->HashName:
                {
                    $tpl->warning( $operatorName, "$operatorName works only with arrays." );
                }
                break;

                // Add a string at the beginning of the input/target string:
                case $this->PrependName:
                {
                    $operatorValue = $namedParameters['prepend_string'].$operatorValue;
                }break;

                // Add a string at the end of the input/target string:
                case $this->AppendName:
                {
                    $operatorValue .= $namedParameters['append_string'];
                }break;

                // Not implemented.
                case $this->MergeName:
                {
                    $tpl->warning( $operatorName, "$operatorName works only with arrays." );
                }break;

                // Check if the string contains a specified sequence of chars/string.
                case $this->ContainsName:
                {
                    $operatorValue = strstr( $operatorValue, $namedParameters['match'] );
                }
                break;

                // Compare two strings:
                case $this->CompareName:
                {
                    if ( $operatorValue == $namedParameters['compare'] )
                    {
                        $operatorValue = true;
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }
                break;

                // Extract a portion from/of a string:
                case $this->ExtractName:
                {
                    $operatorValue = substr( $operatorValue, $namedParameters['extract_start'], $namedParameters['extract_length'] );
                }
                break;

                // Extract string/portion from the start of the string.
                case $this->ExtractLeftName:
                {
                    $operatorValue = substr( $operatorValue, 0, $namedParameters['length'] );
                }break;

                // Extract string/portion from the end of the string.
                case $this->ExtractRightName:
                {
                    $offset  = strlen( $operatorValue ) - $namedParameters['length'];
                    $operatorValue = substr( $operatorValue, $offset );
                }break;

                // Check if string begins with specified sequence:
                case $this->BeginsWithName:
                {
                    if ( strpos( $operatorValue, $namedParameters['match'] ) === 0 )
                    {
                        $operatorValue = true;
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }break;

                // Check if string ends with specified sequence:
                case $this->EndsWithName:
                {
                    if (strpos ($operatorValue, $namedParameters['match']) === ( strlen( $operatorValueb ) - strlen ($namedParameters['match'])))
                    {
                        $operatorValue = true;
                    }
                    else
                    {
                        $operatorValue = false;
                    }
                }break;

                // Only works with arrays.
                case $this->ImplodeName:
                {
                    $tpl->warning( $operatorName, "$operatorName only works with arrays" );
                }break;

                // Explode string (split a string by string).
                case $this->ExplodeName:
                {
                    $operatorValue = explode( $namedParameters['explode_first'], $operatorValue );
                }break;

                // Repeat string n times:
                case $this->RepeatName:
                {
                    $operatorValue = str_repeat( $operatorValue, $namedParameters['repeat_times'] );
                }break;

                // Reverse contents of string:
                case $this->ReverseName:
                {
                    $operatorValue = strrev( $operatorValue );
                }break;

                // Insert a given string at a specified position:
                case $this->InsertName:
                {
                    $first  = substr( $operatorValue, 0, $namedParameters['insert_position'] );
                    $second = substr( $operatorValue, $namedParameters['insert_position'] );
                    $operatorValue = $first.$namedParameters['insert_string'].$second;
                }break;

                // Remove a portion from a string:
                case $this->RemoveName:
                {
                    $first  = substr( $operatorValue, 0, $namedParameters['offset'] );
                    $second = substr( $operatorValue, $namedParameters['offset'] + $namedParameters['length'] );
                    $operatorValue = $first.$second;
                }break;

                // Replace a portion of a string:
                case $this->ReplaceName:
                {
                    // __FIX_ME__
                }break;

                // Not implemented.
                case $this->UniqueName:
                {
                    $tpl->warning( $operatorName, "$operatorName works only with arrays." );
                }break;

                // Default case:
                default:
                {
                    $tpl->warning( $operatorName, "Unknown operatorname: $operatorName" );
                }
                break;
            }
        }
        // ..or something else? -> We're building the array:
        else
        {
            $operatorValue = array();
            for ( $i = 0; $i < count( $operatorParameters ); ++$i )
            {
                $operatorValue[] =& $tpl->elementValue( $operatorParameters[$i],
                                                        $rootNamespace,
                                                        $currentNamespace );
            }
        }
    }

    /// \privatesection
    var $Operators;
    var $ArrayName;
    var $HashName;
}

?>
