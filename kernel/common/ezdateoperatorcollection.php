<?php
//
// Definition of eZDateOperatorCollection class
//
// Created on: <07-Feb-2003 09:39:55 bf>
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

class eZDateOperatorCollection
{
    /*!
     */
    function eZDateOperatorCollection( $monthName = 'month_overview' )
    {
        $this->MonthOverviewName = $monthName;
        $this->Operators = array( $monthName );
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
        return array( 'month_overview' => array( 'field' => array( 'type' => 'string',
                                                                   'required' => true,
                                                                   'default' => false ),
                                                 'date' => array( 'type' => 'integer',
                                                                  'required' => true,
                                                                  'default' => false ),
                                                 'optional' => array( 'type' => 'array',
                                                                      'required' => false,
                                                                      'default' => false ) ) );
    }

    /*!
     \reimp
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        $locale =& eZLocale::instance();
        if ( $operatorName == $this->MonthOverviewName )
        {
            $field = $namedParameters['field'];
            $date = $namedParameters['date'];
            if ( !$field )
                return $tpl->missingParameter( $operatorName, 'field' );
            if ( !$date )
                return $tpl->missingParameter( $operatorName, 'date' );
            $optional = $namedParameters['optional'];
            $dateInfo = getdate( $date );
            if ( is_array( $operatorValue ) )
            {
                $month = array();
                $month['year'] = $dateInfo['year'];
                $month['month'] = $locale->longMonthName( $dateInfo['mon'] );
                $weekDays = $locale->weekDays();
                $weekDaysMap = array();
                $i = 0;
                foreach ( $weekDays as $weekDay )
                {
                    $weekDaysMap[$weekDay] = $i;
                    $weekDayName = $locale->shortDayName( $weekDay );
                    $weekDayClass = 'weekday';
                    if ( $weekDay == 6 or $weekDay == 0 )
                        $weekDayClass = 'holiday';
                    $month['weekdays'][] = array( 'day' => $weekDayName,
                                                  'class' => $weekDayClass );
                    ++$i;
                }
                $days = array();
                $lastDay = getdate( mktime( 0, 0, 0, $dateInfo['mon']+1, 0, $dateInfo['year'] ) );
                $lastDay = $lastDay['mday'];
                for ( $day = 1; $day <= $lastDay; ++$day )
                {
                    $days[$day] = false;
                }
                foreach ( array_keys( $operatorValue ) as $key )
                {
                    $item =& $operatorValue[$key];
                    $value = null;
                    if ( is_object( $item ) and
                         method_exists( $item, 'hasattribute' ) and
                         method_exists( $item, 'attribute' ) )
                    {
                        if ( $item->hasAttribute( $field ) )
                            $value = $item->attribute( $field );
                    }
                    else if ( is_array( $item ) )
                    {
                        if ( array_key_exists( $field, $item ) )
                            $value = $item[$field];
                    }
                    if ( $value !== null )
                    {
                        $info = getdate( $value );
                        if ( $info['year'] == $dateInfo['year'] and
                             $info['mon'] == $dateInfo['mon'] )
                        {
                            $days[$info['mday']] = true;
                        }
                    }
                }
                $currentDay = false;
                if ( isset( $optional['current'] ) )
                {
                    $info = getdate( $optional['current'] );
                    $currentDay = $info['yday'];
                }
                $dayClass = false;
                if ( isset( $optional['day_class'] ) )
                    $dayClass = $optional['day_class'];
                $link = false;
                if ( isset( $optional['link'] ) )
                    $link = $optional['link'];
                $yearLinkParameter = false;
                $monthLinkParameter = false;
                $dayLinkParameter = false;
                if ( isset( $optional['year_link'] ) )
                     $yearLinkParameter = $optional['year_link'];
                if ( isset( $optional['month_link'] ) )
                     $monthLinkParameter = $optional['month_link'];
                if ( isset( $optional['day_link'] ) )
                     $dayLinkParameter = $optional['day_link'];
                $weeks = array();
                $lastWeek = 0;
                for ( $day = 1; $day <= $lastDay; ++$day )
                {
                    $timestamp = mktime( 0, 0, 0, $dateInfo['mon'], $day, $dateInfo['year'] );
                    $info = getdate( $timestamp );
                    $weekDay = $weekDaysMap[$info['wday']];
                    $week = date( 'W', $timestamp );
                    if ( $info['wday'] == 0 )
                        ++$week;
                    if ( $week < $lastWeek )
                        $week = $lastWeek;
                    $lastWeek = $week;
                    if ( !isset( $weeks[$week] ) )
                    {
                        for ( $i = 0; $i < 7; ++$i )
                        {
                            $weeks[$week][] = false;
                        }
                    }
                    $dayData = array( 'day' => $day,
                                      'link' => false,
                                      'class' => $dayClass,
                                      'highlight' => false );
                    if ( $currentDay == $info['yday'] )
                    {
                        if ( isset( $optional['current_class'] ) )
                            $dayData['class'] = $optional['current_class'];
                        $dayData['highlight'] = true;
                    }
                    if ( $days[$day] )
                    {
                        $dayLink = $link;
                        if ( $dayLink )
                        {
                            if ( $yearLinkParameter )
                                $dayLink .= '/year/' . $info['year'];
                            if ( $monthLinkParameter )
                                $dayLink .= '/month/' . $info['mon'];
                            if ( $dayLinkParameter )
                                $dayLink .= '/day/' . $info['mday'];
                        }
                        $dayData['link'] = $dayLink;
                    }
                    $weeks[$week][$weekDay] = $dayData;
                }
                $next = false;
                if ( isset( $optional['next'] ) )
                    $next = $optional['next'];
                if ( $next )
                {
                    $nextTimestamp = mktime( 0, 0, 0, $dateInfo['mon'] + 1, 1, $dateInfo['year'] );
                    $nextInfo = getdate( $nextTimestamp );
                    $month['next'] = array( 'month' => $locale->longMonthName( $nextInfo['mon'] ),
                                            'year' => $nextInfo['year'] );
                    $nextLink = $next['link'];
                    if ( $yearLinkParameter )
                        $nextLink .= '/year/' . $nextInfo['year'];
                    if ( $monthLinkParameter )
                        $nextLink .= '/month/' . $nextInfo['mon'];
                    if ( $dayLinkParameter )
                        $nextLink .= '/day/' . $nextInfo['mday'];
                    $month['next']['link'] = $nextLink;
                }
                else
                    $month['next'] = false;
                $previous = false;
                if ( isset( $optional['previous'] ) )
                    $previous = $optional['previous'];
                if ( $previous )
                {
                    $previousTimestamp = mktime( 0, 0, 0, $dateInfo['mon'] - 1, 1, $dateInfo['year'] );
                    $previousInfo = getdate( $previousTimestamp );
                    $month['previous'] = array( 'month' => $locale->longMonthName( $previousInfo['mon'] ),
                                                'year' => $previousInfo['year'] );
                    $previousLink = $previous['link'];
                    if ( $yearLinkParameter )
                        $previousLink .= '/year/' . $previousInfo['year'];
                    if ( $monthLinkParameter )
                        $previousLink .= '/month/' . $previousInfo['mon'];
                    if ( $dayLinkParameter )
                        $previousLink .= '/day/' . $previousInfo['mday'];
                    $month['previous']['link'] = $previousLink;
                }
                else
                    $month['previous'] = false;
                $month['weeks'] = $weeks;
                $operatorValue = $month;
            }
        }
    }

    /// \privatesection
    var $Operators;
};

?>
