<?php
//
// Definition of eZBenchmarkCase class
//
// Created on: <18-Feb-2004 11:55:40 >
//
// Copyright (C) 1999-2006 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file ezbenchmarkcase.php
*/

/*!
  \class eZBenchmarkCase ezbenchmarkcase.php
  \ingroup eZTest
  \brief eZBenchmarkCase provides a base class for doing automated benchmarks

  This class provides basic functionality and interface
  for creating benchmarks. It keeps a list of marks and
  a name which are accessible with markList() and name().

  To add new tests use addMark() or addFunctionMark()
  with the appropriate mark data.

  The methods prime() and cleanup() will be called before
  and after the mark itself is handled. This allows for priming
  certain values for the mark and cleaning up afterwards.

  To create a mark case inherit this class and create some mark methods
  that takes one parameter \a $tr which is the current test runner and
  a $parameter which is optional parameters added to the mark entry.
  The constructor must call the eZBenchmarkCase constructor with a useful
  name and setup some test methods with addMark() and addFunctionMark().

  For running the marks you must pass the case to an eZBenchmarkRunner instance.

  \code
include_once( 'benchmarks/classes/ezbenchmarkcase.php' );
class MyTest extends eZBenchmarkCase
{
    function MyTest()
    {
        $this->eZBenchmarkCase( 'My test case' );
        $this->addmark( 'markFunctionA', 'Addition mark' );
        $this->addFunctionTest( 'MyFunctionMark', 'Addition mark 2' );
    }

    function markFunctionA( &$tr, $parameter )
    {
        $a = 1 + 2;
    }
}

function MyFunctionMark( &$tr, $parameter )
{
    $a = 1 + 2;
}

$case = new MyTest();
$runner = new eZBenchmarkCLIRunner();
$runner->run( $case );
  \endcode

*/

include_once( 'lib/ezutils/classes/ezdebug.php' );
include_once( 'benchmarks/classes/ezbenchmarkunit.php' );

class eZBenchmarkCase extends eZBenchmarkUnit
{
    /*!
     Constructor
    */
    function eZBenchmarkCase( $name = false )
    {
        $this->eZBenchmarkUnit( $name );
    }

    function addMark( $method, $name, $parameter = false )
    {
        if ( !method_exists( $this, $method ) )
        {
            eZDebug::writeWarning( "Mark method $method in mark " . $this->Name . " does not exist, cannot add",
                                   'eZBenchmarkCase::addMark' );
        }
        if ( !$name )
            $name = $method;
        $this->addEntry( array( 'name' => $name,
                                'object' => &$this,
                                'method' => $method,
                                'parameter' => $parameter ) );
    }

    function addFunctionMark( $function, $name, $parameter = false )
    {
        if ( !function_exists( $function ) )
        {
            eZDebug::writeWarning( "Mark function $method does not exist, cannot add to mark " . $this->Name,
                                   'eZBenchmarkCase::addFunctionMark' );
        }
        if ( !$name )
            $name = $function;
        $this->addEntry( array( 'name' => $name,
                                'function' => $function,
                                'parameter' => $parameter ) );
    }
}

?>
