<?php
//
// Created on: <06-Oct-2002 16:01:10 amos>
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

/*! \file function_definition.php
*/

$FunctionList = array();
$FunctionList['object'] = array( 'name' => 'object',
                                 'operation_types' => array( 'read' ),
                                 'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                         'class' => 'eZContentFunctionCollection',
                                                         'method' => 'fetchContentObject' ),
                                 'parameter_type' => 'standard',
                                 'parameters' => array( array( 'name' => 'object_id',
                                                               'type' => 'integer',
                                                               'required' => true ) ) );
$FunctionList['version'] = array( 'name' => 'version',
                                  'operation_types' => array( 'read' ),
                                  'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                          'class' => 'eZContentFunctionCollection',
                                                          'method' => 'fetchContentVersion' ),
                                  'parameter_type' => 'standard',
                                  'parameters' => array( array( 'name' => 'object_id',
                                                                'type' => 'integer',
                                                                'required' => true ),
                                                         array( 'name' => 'version_id',
                                                                'type' => 'integer',
                                                                'default' => false,
                                                                'required' => true ) ) );
$FunctionList['node'] = array( 'name' => 'node',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                       'class' => 'eZContentFunctionCollection',
                                                       'method' => 'fetchContentNode' ),
                               'parameter_type' => 'standard',
                               'parameters' => array( array( 'name' => 'node_id',
                                                             'type' => 'integer',
                                                             'required' => true ) ) );
$FunctionList['locale_list'] = array( 'name' => 'locale_list',
                                      'operation_types' => array( 'read' ),
                                      'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                              'class' => 'eZContentFunctionCollection',
                                                              'method' => 'fetchLocaleList' ),
                                      'parameter_type' => 'standard',
                                      'parameters' => array( ) );
$FunctionList['translation_list'] = array( 'name' => 'translation_list',
                                           'operation_types' => array( 'read' ),
                                           'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                   'class' => 'eZContentFunctionCollection',
                                                                   'method' => 'fetchTranslationList' ),
                                           'parameter_type' => 'standard',
                                           'parameters' => array( ) );
$FunctionList['non_translation_list'] = array( 'name' => 'object',
                                               'operation_types' => array( 'read' ),
                                               'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                       'class' => 'eZContentFunctionCollection',
                                                                       'method' => 'fetchNonTranslationList' ),
                                               'parameter_type' => 'standard',
                                               'parameters' => array( array( 'name' => 'object_id',
                                                                             'type' => 'integer',
                                                                             'required' => true ),
                                                                      array( 'name' => 'version',
                                                                             'type' => 'integer',
                                                                             'required' => true ) ) );
$FunctionList['object'] = array( 'name' => 'object',
                                 'operation_types' => array( 'read' ),
                                 'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                         'class' => 'eZContentFunctionCollection',
                                                         'method' => 'fetchObject' ),
                                 'parameter_type' => 'standard',
                                 'parameters' => array( array( 'name' => 'object_id',
                                                               'type' => 'integer',
                                                               'required' => true ) ) );
$FunctionList['class'] = array( 'name' => 'object',
                                'operation_types' => array( 'read' ),
                                'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                        'class' => 'eZContentFunctionCollection',
                                                        'method' => 'fetchClass' ),
                                'parameter_type' => 'standard',
                                'parameters' => array( array( 'name' => 'class_id',
                                                              'type' => 'integer',
                                                              'required' => true ) ) );
$FunctionList['class_attribute_list'] = array( 'name' => 'object',
                                               'operation_types' => array( 'read' ),
                                               'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                       'class' => 'eZContentFunctionCollection',
                                                                       'method' => 'fetchClassAttributeList' ),
                                               'parameter_type' => 'standard',
                                               'parameters' => array( array( 'name' => 'class_id',
                                                                             'type' => 'integer',
                                                                             'required' => true ),
                                                                      array( 'name' => 'version_id',
                                                                             'type' => 'integer',
                                                                             'required' => false,
                                                                             'default' => 0 ) ) );
$FunctionList['class_attribute'] = array( 'name' => 'object',
                                          'operation_types' => array( 'read' ),
                                          'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                  'class' => 'eZContentFunctionCollection',
                                                                  'method' => 'fetchClassAttribute' ),
                                          'parameter_type' => 'standard',
                                          'parameters' => array( array( 'name' => 'attribute_id',
                                                                        'type' => 'integer',
                                                                        'required' => true ),
                                                                 array( 'name' => 'version_id',
                                                                        'type' => 'integer',
                                                                        'required' => false,
                                                                        'default' => 0 ) ) );
$FunctionList['list'] = array( 'name' => 'tree',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                       'class' => 'eZContentFunctionCollection',
                                                       'method' => 'fetchObjectTree' ),
                               'parameter_type' => 'standard',
                               'parameters' => array( array( 'name' => 'parent_node_id',
                                                             'type' => 'integer',
                                                             'required' => true ),
                                                      array( 'name' => 'sort_by',
                                                             'type' => 'array',
                                                             'required' => false,
                                                             'default' => array() ),
                                                      array( 'name' => 'offset',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'limit',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'depth',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => 1 ),
                                                      array( 'name' => 'class_id',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'class_filter_type',
                                                             'type' => 'string',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'class_filter_array',
                                                             'type' => 'array',
                                                             'required' => false,
                                                             'default' => false ) ) );
$FunctionList['list_count'] = array( 'name' => 'list_count',
                                     'operation_types' => array( 'read' ),
                                     'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                             'class' => 'eZContentFunctionCollection',
                                                             'method' => 'fetchObjectTreeCount' ),
                                     'parameter_type' => 'standard',
                                     'parameters' => array( array( 'name' => 'parent_node_id',
                                                                   'type' => 'integer',
                                                                   'required' => true ),
                                                            array( 'name' => 'class_filter_type',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => false ),
                                                            array( 'name' => 'class_filter_array',
                                                                   'type' => 'array',
                                                                   'required' => false,
                                                                   'default' => false ),
                                                            array( 'name' => 'depth',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => 1 ) ) );
$FunctionList['tree'] = array( 'name' => 'tree',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                       'class' => 'eZContentFunctionCollection',
                                                       'method' => 'fetchObjectTree' ),
                               'parameter_type' => 'standard',
                               'parameters' => array( array( 'name' => 'parent_node_id',
                                                             'type' => 'integer',
                                                             'required' => true ),
                                                      array( 'name' => 'sort_by',
                                                             'type' => 'array',
                                                             'required' => false,
                                                             'default' => array() ),
                                                      array( 'name' => 'offset',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'limit',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'depth',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'class_id',
                                                             'type' => 'integer',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'class_filter_type',
                                                             'type' => 'string',
                                                             'required' => false,
                                                             'default' => false ),
                                                      array( 'name' => 'class_filter_array',
                                                             'type' => 'array',
                                                             'required' => false,
                                                             'default' => false ) ) );

$FunctionList['tree_count'] = array( 'name' => 'tree_count',
                                     'operation_types' => array( 'read' ),
                                     'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                             'class' => 'eZContentFunctionCollection',
                                                             'method' => 'fetchObjectTreeCount' ),
                                     'parameter_type' => 'standard',
                                     'parameters' => array( array( 'name' => 'parent_node_id',
                                                                   'type' => 'integer',
                                                                   'required' => true ),
                                                            array( 'name' => 'class_filter_type',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => false ),
                                                            array( 'name' => 'class_filter_array',
                                                                   'type' => 'array',
                                                                   'required' => false,
                                                                   'default' => false ),
                                                            array( 'name' => 'depth',
                                                                   'type' => 'string',
                                                                   'required' => false,
                                                                   'default' => 0 ) ) );


$FunctionList['trash_count'] = array( 'name' => 'trash_count',
                                      'operation_types' => array( 'read' ),
                                      'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                              'class' => 'eZContentFunctionCollection',
                                                              'method' => 'fetchTrashObjectCount' ),
                                      'parameter_type' => 'standard',
                                      'parameters' => array(  ) );

$FunctionList['trash_object_list'] = array( 'name' => 'trash_object_list',
                                            'operation_types' => array( 'read' ),
                                            'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                    'class' => 'eZContentFunctionCollection',
                                                                    'method' => 'fetchTrashObjectList' ),
                                            'parameter_type' => 'standard',
                                            'parameters' => array( array( 'name' => 'offset',
                                                                          'type' => 'integer',
                                                                          'required' => false,
                                                                          'default' => false ),
                                                                   array( 'name' => 'limit',
                                                                          'type' => 'integer',
                                                                          'required' => false,
                                                                          'default' => false ) ) );

$FunctionList['draft_count'] = array( 'name' => 'draft_count',
                                      'operation_types' => array( 'read' ),
                                      'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                              'class' => 'eZContentFunctionCollection',
                                                              'method' => 'fetchDraftVersionCount' ),
                                      'parameter_type' => 'standard',
                                      'parameters' => array(  ) );

$FunctionList['draft_version_list'] = array( 'name' => 'draft_version_list',
                                             'operation_types' => array( 'read' ),
                                             'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                     'class' => 'eZContentFunctionCollection',
                                                                     'method' => 'fetchDraftVersionList' ),
                                             'parameter_type' => 'standard',
                                             'parameters' => array( array( 'name' => 'offset',
                                                                           'type' => 'integer',
                                                                           'required' => false,
                                                                           'default' => false ),
                                                                    array( 'name' => 'limit',
                                                                           'type' => 'integer',
                                                                           'required' => false,
                                                                           'default' => false ) ) );

$FunctionList['version_count'] = array( 'name' => 'version_count',
                                      'operation_types' => array( 'read' ),
                                      'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                              'class' => 'eZContentFunctionCollection',
                                                              'method' => 'fetchVersionCount' ),
                                      'parameter_type' => 'standard',
                                      'parameters' => array( array( 'name' => 'contentobject',
                                                                           'type' => 'object',
                                                                           'required' => true) ) );

$FunctionList['version_list'] = array( 'name' => 'version_list',
                                             'operation_types' => array( 'read' ),
                                             'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                     'class' => 'eZContentFunctionCollection',
                                                                     'method' => 'fetchVersionList' ),
                                             'parameter_type' => 'standard',
                                             'parameters' => array( array( 'name' => 'contentobject',
                                                                           'type' => 'object',
                                                                           'required' => true),
                                                                    array( 'name' => 'offset',
                                                                           'type' => 'integer',
                                                                           'required' => false,
                                                                           'default' => false ),
                                                                    array( 'name' => 'limit',
                                                                           'type' => 'integer',
                                                                           'required' => false,
                                                                           'default' => false ) ) );



$FunctionList['can_instantiate_class_list'] = array( 'name' => 'can_instantiate_class_list',
                                                     'operation_types' => array( 'read' ),
                                                     'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                             'class' => 'eZContentFunctionCollection',
                                                                             'method' => 'canInstantiateClassList' ),
                                                     'parameter_type' => 'standard',
                                                     'parameters' => array( array( 'name' => 'group_id',
                                                                                   'type' => 'integer',
                                                                                   'required' => false,
                                                                                   'default' => 0 ),
                                                                            array( 'name' => 'parent_node',
                                                                                   'type' => 'object',
                                                                                   'required' => false,
                                                                                   'default' => 0 ) ) );

$FunctionList['can_instantiate_classes'] = array( 'name' => 'can_instantiate_classes',
                                                  'operation_types' => array( 'read' ),
                                                  'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                          'class' => 'eZContentFunctionCollection',
                                                                          'method' => 'canInstantiateClasses' ),
                                                  'parameter_type' => 'standard',
                                                  'parameters' => array( array( 'name' => 'parent_node',
                                                                                'type' => 'object',
                                                                                'required' => false,
                                                                                'default' => 0 ) ) );
$FunctionList['contentobject_attributes'] = array( 'name' => 'contentobject_attributes',
                                                   'operation_types' => array( 'read' ),
                                                   'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                                           'class' => 'eZContentFunctionCollection',
                                                                           'method' => 'contentobjectAttributes' ),
                                                   'parameter_type' => 'standard',
                                                   'parameters' => array( array( 'name' => 'version',
                                                                                 'type' => 'object',
                                                                                 'required' => false,
                                                                                 'default' => 0 ),
                                                                          array( 'name' => 'language_code',
                                                                                 'type' => 'string',
                                                                                 'required' => false,
                                                                                 'default' => '' ) ) );

$FunctionList['bookmarks'] = array( 'name' => 'bookmarks',
                                    'operation_types' => array( 'read' ),
                                    'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                            'class' => 'eZContentFunctionCollection',
                                                            'method' => 'fetchBookmarks' ),
                                    'parameter_type' => 'standard',
                                    'parameters' => array( ) );

$FunctionList['recent'] = array( 'name' => 'recent',
                                 'operation_types' => array( 'read' ),
                                 'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                         'class' => 'eZContentFunctionCollection',
                                                         'method' => 'fetchRecent' ),
                                 'parameter_type' => 'standard',
                                 'parameters' => array( ) );

$FunctionList['section_list'] = array( 'name' => 'section_list',
                                       'operation_types' => array( 'read' ),
                                       'call_method' => array( 'include_file' => 'kernel/content/ezcontentfunctioncollection.php',
                                                               'class' => 'eZContentFunctionCollection',
                                                               'method' => 'fetchSectionList' ),
                                       'parameter_type' => 'standard',
                                       'parameters' => array( ) );

?>
