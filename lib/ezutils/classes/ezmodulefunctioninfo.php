<?php
//
// Definition of eZModuleFunctionInfo class
//
// Created on: <06-Oct-2002 16:27:36 amos>
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

/*! \file ezmodulefunctioninfo.php
*/

/*!
  \class eZModuleFunctionInfo ezmodulefunctioninfo.php
  \brief The class eZModuleFunctionInfo does

*/

include_once( 'lib/ezutils/classes/ezmodule.php' );
include_once( 'lib/ezutils/classes/ezdebug.php' );

define( 'EZ_MODULE_FUNCTION_ERROR_NO_CLASS', 5 );
define( 'EZ_MODULE_FUNCTION_ERROR_NO_CLASS_METHOD', 6 );
define( 'EZ_MODULE_FUNCTION_ERROR_CLASS_INSTANTIATE_FAILED', 7 );
define( 'EZ_MODULE_FUNCTION_ERROR_MISSING_PARAMETER', 8 );

class eZModuleFunctionInfo
{
    /*!
     Constructor
    */
    function eZModuleFunctionInfo( $moduleName )
    {
        $this->ModuleName = $moduleName;
        $this->IsValid = false;
        $this->FunctionList = array();
        $this->UseOldCall = false;
    }

    function isValid()
    {
        return $this->IsValid;
    }

    function loadDefinition()
    {
        $pathList = eZModule::globalPathList();
        foreach ( $pathList as $path )
        {
            $definitionFile = $path . '/' . $this->ModuleName . '/function_definition.php';
            if ( file_exists( $definitionFile ) )
                break;
            $definitionFile = null;
        }
        if ( $definitionFile === null )
        {
            eZDebug::writeError( 'Missing function definition file for module: ' . $this->ModuleName,
                                 'eZModuleFunctionInfo::loadDefinition' );
            return false;
        }
        unset( $FunctionList );
        include( $definitionFile );
        if ( !isset( $FunctionList ) )
        {
            eZDebug::writeError( 'Missing function definition list for module: ' . $this->ModuleName,
                                 'eZModuleFunctionInfo::loadDefinition' );
            return false;
        }
        $this->FunctionList = $FunctionList;
        $this->IsValid = true;
        return true;
    }

    function execute( $functionName, $functionParameters )
    {
        $moduleName = $this->ModuleName;
        if ( !isset( $this->FunctionList[$functionName] ) )
        {
            eZDebug::writeError( "No such function '$functionName' in module '$moduleName'",
                                 'eZModuleFunctionInfo::execute' );
            return null;
        }
        $functionDefinition =& $this->FunctionList[$functionName];
        if ( !isset( $functionName['call_method'] ) )
        {
            eZDebug::writeError( "No call method defined for function '$functionName' in module '$moduleName'",
                                 'eZModuleFunctionInfo::execute' );
            return null;
        }
        if ( !isset( $functionName['parameters'] ) )
        {
            eZDebug::writeError( "No parameters defined for function '$functionName' in module '$moduleName'",
                                 'eZModuleFunctionInfo::execute' );
            return null;
        }
        $callMethod =& $functionDefinition['call_method'];
        if ( isset( $callMethod['include_file'] ) and
             isset( $callMethod['class'] ) and
             isset( $callMethod['method'] ) )
        {
            $resultArray =& $this->executeClassMethod( $callMethod['include_file'], $callMethod['class'], $callMethod['method'],
                                                       $functionDefinition['parameters'], $functionParameters );
        }
        else
        {
            eZDebug::writeError( "No valid call methods found for function '$functionName' in module '$moduleName'",
                                 'eZModuleFunctionInfo::execute' );
            return null;
        }
        if ( !is_array( $resultArray ) )
        {
            eZDebug::writeError( "Function '$functionName' in module '$moduleName' did not return a result array",
                                 'eZFunctionHandler::execute' );
            return null;
        }
        if ( isset( $resultArray['internal_error'] ) )
        {
            switch ( $resultArray['internal_error'] )
            {
                case EZ_MODULE_FUNCTION_ERROR_NO_CLASS:
                {
                    $className = $resultArray['internal_error_class_name'];
                    eZDebug::writeError( "No class '$className' available for function '$functionName' in module '$moduleName'",
                                         'eZModuleFunctionInfo::execute' );
                    return null;
                } break;
                case EZ_MODULE_FUNCTION_ERROR_NO_CLASS_METHOD:
                {
                    $className = $resultArray['internal_error_class_name'];
                    $classMethodName = $resultArray['internal_error_class_method_name'];
                    eZDebug::writeError( "No method '$classMethodName' in class '$className' available for function '$functionName' in module '$moduleName'",
                                         'eZModuleFunctionInfo::execute' );
                    return null;
                } break;
                case EZ_MODULE_FUNCTION_ERROR_CLASS_INSTANTIATE_FAILED:
                {
                    $className = $resultArray['internal_error_class_name'];
                    eZDebug::writeError( "Failed instantiating class '$className' which is needed for function '$functionName' in module '$moduleName'",
                                         'eZModuleFunctionInfo::execute' );
                    return null;
                } break;
                case EZ_MODULE_FUNCTION_ERROR_MISSING_PARAMETER:
                {
                    $parameterName = $resultArray['internal_error_parameter_name'];
                    eZDebug::writeError( "Missing parameter '$parameterName' for function '$functionName' in module '$moduleName'",
                                         'eZModuleFunctionInfo::execute' );
                    return null;
                } break;
                default:
                {
                    $internalError = $resultArray['internal_error'];
                    eZDebug::writeError( "Unknown internal error '$internalError' for function '$functionName' in module '$moduleName'",
                                         'eZModuleFunctionInfo::execute' );
                    return null;
                } break;
            }
            return null;
        }
        else if ( isset( $resultArray['error'] ) )
        {
        }
        else if ( isset( $resultArray['result'] ) )
        {
            return $resultArray['result'];
        }
        else
        {
            eZDebug::writeError( "Function '$functionName' in module '$moduleName' did not return a result value",
                                 'eZFunctionHandler::execute' );
        }
        return null;
    }

    function &objectForClass( $className )
    {
        $classObjectList =& $GLOBALS['eZModuleFunctionClassObjectList'];
        if ( !isset( $classObjectList ) )
            $classObjectList = array();
        if ( isset( $classObjectList[$className] ) )
            return $classObjectList[$className];
        $classObject = new $className();
        $classObjectList[$className] =& $classObject;
        return $classObject;
    }

    function executeClassMethod( $includeFile, $className, $methodName,
                                 $functionParameterDefinitions, $functionParameters )
    {
        include_once( $includeFile );
        if ( !class_exists( $className ) )
        {
            return array( 'internal_error' => EZ_MODULE_FUNCTION_ERROR_NO_CLASS,
                          'internal_error_class_name' => $className );
        }
        $classObject =& $this->objectForClass( $className );
        if ( $classObject === null )
        {
            return array( 'internal_error' => EZ_MODULE_FUNCTION_ERROR_CLASS_INSTANTIATE_FAILED,
                          'internal_error_class_name' => $className );
        }
        if ( !method_exists( $classObject, $methodName ) )
        {
            return array( 'internal_error' => EZ_MODULE_FUNCTION_ERROR_NO_CLASS_METHOD,
                          'internal_error_class_name' => $className,
                          'internal_error_class_method_name' => $methodName );
        }
        $parameterArray = array();
        foreach ( $functionParameterDefinitions as $functionParameterDefinition )
        {
            $parameterName = $functionParameterDefinition['name'];
            if ( isset( $functionParameters[$parameterName] ) )
            {
                // Do type checking
                $parameterArray[] = $functionParameters[$parameterName];
            }
            else
            {
                if ( $functionParameterDefinition['required'] )
                {
                    return array( 'internal_error' => EZ_MODULE_FUNCTION_ERROR_MISSING_PARAMETER,
                                  'internal_error_parameter_name' => $parameterName );
                }
                else if ( isset( $functionParameterDefinition['default'] ) )
                {
                    $parameterArray[] = $functionParameterDefinition['default'];
                }
                else
                {
                    $parameterArray[] = null;
                }
            }
        }
        return $this->callClassMethod( $methodName, $classObject, $parameterArray );
    }

    function callClassMethod( $methodName, &$classObject, $parameterArray )
    {
        if ( $this->UseOldCall )
            return call_user_method_array( $methodName, $classObject, $parameterArray );
        else
            return call_user_func_array( array( $classObject, $methodName ), $parameterArray );
    }


    /// \privatesection
    var $ModuleName;
    var $FunctionList;
    var $IsValid;
    var $UseOldCall;
}

?>
