-- MySQL dump 8.22
--
-- Host: localhost    Database: tmp
---------------------------------------------------------
-- Server version	3.23.56

--
-- Table structure for table 'ezapprove_items'
--

CREATE TABLE ezapprove_items (
  id int(11) NOT NULL auto_increment,
  workflow_process_id int(11) NOT NULL default '0',
  collaboration_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezapprove_items'
--



--
-- Table structure for table 'ezbasket'
--

CREATE TABLE ezbasket (
  id int(11) NOT NULL auto_increment,
  session_id varchar(255) NOT NULL default '',
  productcollection_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezbasket'
--



--
-- Table structure for table 'ezbinaryfile'
--

CREATE TABLE ezbinaryfile (
  contentobject_attribute_id int(11) NOT NULL default '0',
  version int(11) NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  original_filename varchar(255) NOT NULL default '',
  mime_type varchar(50) NOT NULL default '',
  PRIMARY KEY  (contentobject_attribute_id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezbinaryfile'
--



--
-- Table structure for table 'ezcollab_group'
--

CREATE TABLE ezcollab_group (
  id int(11) NOT NULL auto_increment,
  parent_group_id int(11) NOT NULL default '0',
  depth int(11) NOT NULL default '0',
  path_string varchar(255) NOT NULL default '',
  is_open int(11) NOT NULL default '1',
  user_id int(11) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  priority int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY ezcollab_group_path (path_string),
  KEY ezcollab_group_depth (depth)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_group'
--



--
-- Table structure for table 'ezcollab_item'
--

CREATE TABLE ezcollab_item (
  id int(11) NOT NULL auto_increment,
  type_identifier varchar(40) NOT NULL default '',
  creator_id int(11) NOT NULL default '0',
  status int(11) NOT NULL default '1',
  data_text1 text NOT NULL,
  data_text2 text NOT NULL,
  data_text3 text NOT NULL,
  data_int1 int(11) NOT NULL default '0',
  data_int2 int(11) NOT NULL default '0',
  data_int3 int(11) NOT NULL default '0',
  data_float1 float NOT NULL default '0',
  data_float2 float NOT NULL default '0',
  data_float3 float NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_item'
--



--
-- Table structure for table 'ezcollab_item_group_link'
--

CREATE TABLE ezcollab_item_group_link (
  collaboration_id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  is_read int(11) NOT NULL default '0',
  is_active int(11) NOT NULL default '1',
  last_read int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (collaboration_id,group_id,user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_item_group_link'
--



--
-- Table structure for table 'ezcollab_item_message_link'
--

CREATE TABLE ezcollab_item_message_link (
  id int(11) NOT NULL auto_increment,
  collaboration_id int(11) NOT NULL default '0',
  participant_id int(11) NOT NULL default '0',
  message_id int(11) NOT NULL default '0',
  message_type int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_item_message_link'
--



--
-- Table structure for table 'ezcollab_item_participant_link'
--

CREATE TABLE ezcollab_item_participant_link (
  collaboration_id int(11) NOT NULL default '0',
  participant_id int(11) NOT NULL default '0',
  participant_type int(11) NOT NULL default '1',
  participant_role int(11) NOT NULL default '1',
  is_read int(11) NOT NULL default '0',
  is_active int(11) NOT NULL default '1',
  last_read int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (collaboration_id,participant_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_item_participant_link'
--



--
-- Table structure for table 'ezcollab_item_status'
--

CREATE TABLE ezcollab_item_status (
  collaboration_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  is_read int(11) NOT NULL default '0',
  is_active int(11) NOT NULL default '1',
  last_read int(11) NOT NULL default '0',
  PRIMARY KEY  (collaboration_id,user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_item_status'
--



--
-- Table structure for table 'ezcollab_notification_rule'
--

CREATE TABLE ezcollab_notification_rule (
  id int(11) NOT NULL auto_increment,
  user_id varchar(255) NOT NULL default '',
  collab_identifier varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_notification_rule'
--



--
-- Table structure for table 'ezcollab_profile'
--

CREATE TABLE ezcollab_profile (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  main_group int(11) NOT NULL default '0',
  data_text1 text NOT NULL,
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_profile'
--



--
-- Table structure for table 'ezcollab_simple_message'
--

CREATE TABLE ezcollab_simple_message (
  id int(11) NOT NULL auto_increment,
  message_type varchar(40) NOT NULL default '',
  creator_id int(11) NOT NULL default '0',
  data_text1 text NOT NULL,
  data_text2 text NOT NULL,
  data_text3 text NOT NULL,
  data_int1 int(11) NOT NULL default '0',
  data_int2 int(11) NOT NULL default '0',
  data_int3 int(11) NOT NULL default '0',
  data_float1 float NOT NULL default '0',
  data_float2 float NOT NULL default '0',
  data_float3 float NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcollab_simple_message'
--



--
-- Table structure for table 'ezcontent_translation'
--

CREATE TABLE ezcontent_translation (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  locale varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontent_translation'
--



--
-- Table structure for table 'ezcontentbrowsebookmark'
--

CREATE TABLE ezcontentbrowsebookmark (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY ezcontentbrowsebookmark_user (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentbrowsebookmark'
--



--
-- Table structure for table 'ezcontentbrowserecent'
--

CREATE TABLE ezcontentbrowserecent (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY ezcontentbrowserecent_user (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentbrowserecent'
--


INSERT INTO ezcontentbrowserecent (id, user_id, node_id, created, name) VALUES (2,14,45,1061234306,'Flowers');

--
-- Table structure for table 'ezcontentclass'
--

CREATE TABLE ezcontentclass (
  id int(11) NOT NULL auto_increment,
  version int(11) NOT NULL default '0',
  name varchar(255) default NULL,
  identifier varchar(50) NOT NULL default '',
  contentobject_name varchar(255) default NULL,
  creator_id int(11) NOT NULL default '0',
  modifier_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id,version),
  KEY ezcontentclass_version (version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentclass'
--


INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (1,0,'Folder','folder','<name>',14,14,1024392098,1048494694);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (2,0,'Article','article','<title>',14,14,1024392098,1048494722);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (3,0,'User group','user_group','<name>',14,14,1024392098,1048494743);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (4,0,'User','user','<first_name> <last_name>',14,14,1024392098,1048494759);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (5,0,'Image','image','<name>',8,14,1031484992,1048494784);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (6,0,'Forum','forum','<name>',14,14,1052384723,1052384870);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (7,0,'Forum message','forum_message','<topic>',14,14,1052384877,1052384943);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (8,0,'Product','product','<title>',14,14,1052384951,1052385067);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (9,0,'Product review','product_review','<title>',14,14,1052385080,1052385252);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (10,0,'Info page','info_page','<name>',14,14,1052385274,1052385353);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (11,0,'Link','link','<title>',14,14,1052385361,1052385453);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (12,0,'File','file','<name>',14,14,1052385472,1052385669);
INSERT INTO ezcontentclass (id, version, name, identifier, contentobject_name, creator_id, modifier_id, created, modified) VALUES (13,0,'Comment','comment','<subject>',14,14,1052385685,1052385756);

--
-- Table structure for table 'ezcontentclass_attribute'
--

CREATE TABLE ezcontentclass_attribute (
  id int(11) NOT NULL auto_increment,
  version int(11) NOT NULL default '0',
  contentclass_id int(11) NOT NULL default '0',
  identifier varchar(50) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  data_type_string varchar(50) NOT NULL default '',
  is_searchable int(1) NOT NULL default '0',
  is_required int(1) NOT NULL default '0',
  placement int(11) NOT NULL default '0',
  data_int1 int(11) default NULL,
  data_int2 int(11) default NULL,
  data_int3 int(11) default NULL,
  data_int4 int(11) default NULL,
  data_float1 float default NULL,
  data_float2 float default NULL,
  data_float3 float default NULL,
  data_float4 float default NULL,
  data_text1 varchar(50) default NULL,
  data_text2 varchar(50) default NULL,
  data_text3 varchar(50) default NULL,
  data_text4 varchar(255) default NULL,
  data_text5 text,
  is_information_collector int(11) NOT NULL default '0',
  can_translate int(11) default '1',
  PRIMARY KEY  (id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentclass_attribute'
--


INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (123,0,2,'enable_comments','Enable comments','ezboolean',0,0,5,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (119,0,1,'description','Description','ezxmltext',1,0,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (116,0,5,'name','Name','ezstring',1,1,1,150,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (6,0,3,'name','Name','ezstring',1,1,1,255,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (7,0,3,'description','Description','ezstring',1,0,2,255,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (9,0,4,'last_name','Last name','ezstring',1,1,2,255,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (12,0,4,'user_account','User account','ezuser',0,1,3,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (118,0,5,'image','Image','ezimage',0,0,3,2,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (4,0,1,'name','Name','ezstring',1,1,1,255,0,0,0,0,0,0,0,'Folder','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (122,0,2,'thumbnail','Thumbnail','ezimage',0,0,4,2,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (121,0,2,'body','Body','ezxmltext',1,0,3,20,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (120,0,2,'intro','Intro','ezxmltext',1,1,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (1,0,2,'title','Title','ezstring',1,1,1,255,0,0,0,0,0,0,0,'New article','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (117,0,5,'caption','Caption','ezxmltext',1,0,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (8,0,4,'first_name','First name','ezstring',1,1,1,255,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (127,0,7,'topic','Topic','ezstring',1,1,1,150,0,0,0,0,0,0,0,'New topic','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (128,0,7,'message','Message','eztext',1,1,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (126,0,6,'description','Description','ezxmltext',1,0,3,15,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (125,0,6,'icon','Icon','ezimage',0,0,2,1,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (124,0,6,'name','Name','ezstring',1,1,1,150,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (134,0,8,'photo','Photo','ezimage',0,0,6,1,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (133,0,8,'price','Price','ezprice',0,1,5,1,0,0,0,1,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (132,0,8,'description','Description','ezxmltext',1,0,4,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (131,0,8,'intro','Intro','ezxmltext',1,0,3,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (130,0,8,'product_nr','Product nr.','ezstring',1,0,2,40,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (129,0,8,'title','Title','ezstring',1,1,1,100,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (139,0,9,'review','Review','ezxmltext',1,0,5,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (138,0,9,'geography','Town, Country','ezstring',1,1,4,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (137,0,9,'reviewer_name','Reviewer Name','ezstring',1,1,3,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (136,0,9,'rating','Rating','ezenum',1,0,2,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (135,0,9,'title','Title','ezstring',1,1,1,50,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (142,0,10,'image','Image','ezimage',0,0,3,1,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (141,0,10,'body','Body','ezxmltext',1,0,2,20,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (140,0,10,'name','Name','ezstring',1,1,1,100,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (146,0,12,'name','Name','ezstring',1,1,1,0,0,0,0,0,0,0,0,'New file','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (148,0,12,'file','File','ezbinaryfile',0,1,3,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (147,0,12,'description','Description','ezxmltext',1,0,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (145,0,11,'link','Link','ezurl',0,0,3,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (144,0,11,'description','Description','ezxmltext',1,0,2,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (143,0,11,'title','Title','ezstring',1,1,1,100,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (151,0,13,'message','Message','eztext',1,1,3,10,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (150,0,13,'author','Author','ezstring',1,1,2,0,0,0,0,0,0,0,0,'','','','',NULL,0,1);
INSERT INTO ezcontentclass_attribute (id, version, contentclass_id, identifier, name, data_type_string, is_searchable, is_required, placement, data_int1, data_int2, data_int3, data_int4, data_float1, data_float2, data_float3, data_float4, data_text1, data_text2, data_text3, data_text4, data_text5, is_information_collector, can_translate) VALUES (149,0,13,'subject','Subject','ezstring',1,1,1,40,0,0,0,0,0,0,0,'','','','',NULL,0,1);

--
-- Table structure for table 'ezcontentclass_classgroup'
--

CREATE TABLE ezcontentclass_classgroup (
  contentclass_id int(11) NOT NULL default '0',
  contentclass_version int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  group_name varchar(255) default NULL,
  PRIMARY KEY  (contentclass_id,contentclass_version,group_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentclass_classgroup'
--


INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (1,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (2,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (4,0,2,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (5,0,3,'Media');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (3,0,2,'');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (6,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (7,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (8,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (9,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (10,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (11,0,1,'Content');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (12,0,3,'Media');
INSERT INTO ezcontentclass_classgroup (contentclass_id, contentclass_version, group_id, group_name) VALUES (13,0,1,'Content');

--
-- Table structure for table 'ezcontentclassgroup'
--

CREATE TABLE ezcontentclassgroup (
  id int(11) NOT NULL auto_increment,
  name varchar(255) default NULL,
  creator_id int(11) NOT NULL default '0',
  modifier_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentclassgroup'
--


INSERT INTO ezcontentclassgroup (id, name, creator_id, modifier_id, created, modified) VALUES (1,'Content',1,14,1031216928,1033922106);
INSERT INTO ezcontentclassgroup (id, name, creator_id, modifier_id, created, modified) VALUES (2,'Users',1,14,1031216941,1033922113);
INSERT INTO ezcontentclassgroup (id, name, creator_id, modifier_id, created, modified) VALUES (3,'Media',8,14,1032009743,1033922120);

--
-- Table structure for table 'ezcontentobject'
--

CREATE TABLE ezcontentobject (
  id int(11) NOT NULL auto_increment,
  owner_id int(11) NOT NULL default '0',
  section_id int(11) NOT NULL default '0',
  contentclass_id int(11) NOT NULL default '0',
  name varchar(255) default NULL,
  current_version int(11) default NULL,
  is_published int(11) default NULL,
  published int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  status int(11) default '0',
  remote_id varchar(100) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject'
--


INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (1,14,1,1,'Root folder',1,0,1033917596,1033917596,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (4,14,2,3,'Users',1,0,0,0,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (10,14,2,4,'Anonymous User',1,0,1033920665,1033920665,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (11,14,2,3,'Guest accounts',1,0,1033920746,1033920746,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (12,14,2,3,'Administrator users',1,0,1033920775,1033920775,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (13,14,2,3,'Editors',1,0,1033920794,1033920794,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (14,14,2,4,'Administrator User',1,0,1033920830,1033920830,1,NULL);
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (40,14,2,4,'test test',1,0,1053613020,1053613020,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (41,14,3,1,'Media',1,0,1060695457,1060695457,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (42,14,1,10,'About gallery',1,0,1061232666,1061232666,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (43,14,1,1,'Flowers',1,0,1061232785,1061232785,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (44,14,1,5,'Sunflower',1,0,1061232844,1061232844,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (45,14,1,5,'A blue one',1,0,1061232872,1061232872,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (46,14,1,5,'Flower 3',1,0,1061234271,1061234271,1,'');
INSERT INTO ezcontentobject (id, owner_id, section_id, contentclass_id, name, current_version, is_published, published, modified, status, remote_id) VALUES (47,14,1,5,'Flower 4',1,0,1061234305,1061234305,1,'');

--
-- Table structure for table 'ezcontentobject_attribute'
--

CREATE TABLE ezcontentobject_attribute (
  id int(11) NOT NULL auto_increment,
  language_code varchar(20) NOT NULL default '',
  version int(11) NOT NULL default '0',
  contentobject_id int(11) NOT NULL default '0',
  contentclassattribute_id int(11) NOT NULL default '0',
  data_text text,
  data_int int(11) default NULL,
  data_float float default NULL,
  attribute_original_id int(11) default '0',
  sort_key_int int(11) NOT NULL default '0',
  sort_key_string varchar(50) NOT NULL default '',
  PRIMARY KEY  (id,version),
  KEY ezcontentobject_attribute_contentobject_id (contentobject_id),
  KEY ezcontentobject_attribute_language_code (language_code),
  KEY sort_key_int (sort_key_int),
  KEY sort_key_string (sort_key_string)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject_attribute'
--


INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (1,'eng-GB',1,1,4,'My folder',NULL,NULL,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (2,'eng-GB',1,1,119,'<?xml version=\"1.0\"><section><paragraph>This folder contains some information about...</paragraph></section>',NULL,NULL,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (7,'eng-GB',1,4,7,'Main group',NULL,NULL,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (8,'eng-GB',1,4,6,'Users',NULL,NULL,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (1,'eng-GB',2,1,4,'My folder',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (2,'eng-GB',2,1,119,'<?xml version=\"1.0\"><section><paragraph>This folder contains some information about...</paragraph></section>',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (21,'eng-GB',1,10,12,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (22,'eng-GB',1,11,6,'Guest accounts',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (19,'eng-GB',1,10,8,'Anonymous',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (20,'eng-GB',1,10,9,'User',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (23,'eng-GB',1,11,7,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (24,'eng-GB',1,12,6,'Administrator users',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (25,'eng-GB',1,12,7,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (26,'eng-GB',1,13,6,'Editors',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (27,'eng-GB',1,13,7,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (28,'eng-GB',1,14,8,'Administrator',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (29,'eng-GB',1,14,9,'User',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (30,'eng-GB',1,14,12,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (95,'eng-GB',1,40,8,'test',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (96,'eng-GB',1,40,9,'test',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (97,'eng-GB',1,40,12,'',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (98,'eng-GB',1,41,4,'Media',0,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (99,'eng-GB',1,41,119,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\" />',1045487555,0,0,0,'');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (100,'eng-GB',1,42,140,'About gallery',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (101,'eng-GB',1,42,141,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\">\n  <section>\n    <header>Proin consectetuer lacus</header>\n    <paragraph>\n      <line>Proin consectetuer lacus nec neque. Vivamus volutpat elit id purus. Nulla varius dictum est. Maecenas sapien pede, mattis mattis, mollis in, pulvinar a, mi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus arcu. Vestibulum justo. Sed rhoncus. Suspendisse quis turpis nec turpis pretium scelerisque. Fusce dignissim, metus ut vestibulum rutrum, risus purus scelerisque est, eu venenatis velit magna ac quam. Morbi non risus ut arcu eleifend consequat. Ut est augue, malesuada vitae, porttitor pellentesque, egestas vitae, nunc. Curabitur feugiat. Ut sit amet dui. Etiam fermentum. Nulla ornare magna non urna. Cras pulvinar imperdiet turpis.</line>\n      <line> Phasellus eu felis non diam faucibus viverra. Pellentesque sit amet mi. Cras euismod leo vel libero. Sed vel sapien. Mauris aliquam enim ac ante. Nam vestibulum, metus et blandit vulputate, arcu arcu scelerisque ante, at dapibus ipsum turpis vitae felis. Sed aliquet tempus ipsum. Sed facilisis arcu in nulla. Maecenas et orci. Morbi ornare massa. Sed dui metus, scelerisque sed, vestibulum non, dictum a, purus. Proin dignissim semper odio.</line>\n    </paragraph>\n    <paragraph>\n      <ul>\n        <li>Phasellus</li>\n        <li>Dictum</li>\n        <li>Dignissim</li>\n      </ul>\n    </paragraph>\n  </section>\n</section>',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (102,'eng-GB',1,42,142,'',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (103,'eng-GB',1,43,4,'Flowers',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (104,'eng-GB',1,43,119,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\" />',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (105,'eng-GB',1,44,116,'Sunflower',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (106,'eng-GB',1,44,117,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\">\n  <paragraph>Or not?</paragraph>\n</section>',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (107,'eng-GB',1,44,118,'',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (108,'eng-GB',1,45,116,'A blue one',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (109,'eng-GB',1,45,117,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\">\n  <paragraph>And green.</paragraph>\n</section>',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (110,'eng-GB',1,45,118,'',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (111,'eng-GB',1,46,116,'Flower 3',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (112,'eng-GB',1,46,117,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\">\n  <paragraph>White one...</paragraph>\n</section>',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (113,'eng-GB',1,46,118,'',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (114,'eng-GB',1,47,116,'Flower 4',0,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (115,'eng-GB',1,47,117,'<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\"\n         xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"\n         xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\">\n  <paragraph>Actually a tree.</paragraph>\n</section>',1045487555,0,0,0,'0');
INSERT INTO ezcontentobject_attribute (id, language_code, version, contentobject_id, contentclassattribute_id, data_text, data_int, data_float, attribute_original_id, sort_key_int, sort_key_string) VALUES (116,'eng-GB',1,47,118,'',0,0,0,0,'0');

--
-- Table structure for table 'ezcontentobject_link'
--

CREATE TABLE ezcontentobject_link (
  id int(11) NOT NULL auto_increment,
  from_contentobject_id int(11) NOT NULL default '0',
  from_contentobject_version int(11) NOT NULL default '0',
  to_contentobject_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject_link'
--



--
-- Table structure for table 'ezcontentobject_name'
--

CREATE TABLE ezcontentobject_name (
  contentobject_id int(11) NOT NULL default '0',
  name varchar(255) default NULL,
  content_version int(11) NOT NULL default '0',
  content_translation varchar(20) NOT NULL default '',
  real_translation varchar(20) default NULL,
  PRIMARY KEY  (contentobject_id,content_version,content_translation)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject_name'
--


INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (1,'Root folder',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (4,'Users',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (10,'Anonymous User',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (11,'Guest accounts',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (12,'Administrator users',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (13,'Editors',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (14,'Administrator User',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (40,'test test',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (41,'Media',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (42,'About gallery',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (43,'Flowers',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (44,'Sunflower',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (45,'A blue one',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (46,'Flower 3',1,'eng-GB','eng-GB');
INSERT INTO ezcontentobject_name (contentobject_id, name, content_version, content_translation, real_translation) VALUES (47,'Flower 4',1,'eng-GB','eng-GB');

--
-- Table structure for table 'ezcontentobject_tree'
--

CREATE TABLE ezcontentobject_tree (
  node_id int(11) NOT NULL auto_increment,
  parent_node_id int(11) NOT NULL default '0',
  contentobject_id int(11) default NULL,
  contentobject_version int(11) default NULL,
  contentobject_is_published int(11) default NULL,
  depth int(11) NOT NULL default '0',
  path_string varchar(255) NOT NULL default '',
  sort_field int(11) default '1',
  sort_order int(1) default '1',
  priority int(11) NOT NULL default '0',
  path_identification_string text,
  main_node_id int(11) default NULL,
  PRIMARY KEY  (node_id),
  KEY ezcontentobject_tree_path (path_string),
  KEY ezcontentobject_tree_p_node_id (parent_node_id),
  KEY ezcontentobject_tree_co_id (contentobject_id),
  KEY ezcontentobject_tree_depth (depth)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject_tree'
--


INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (1,1,0,1,1,0,'/1/',1,1,0,NULL,1);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (2,1,1,1,1,1,'/1/2/',1,1,0,'',2);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (5,1,4,1,0,1,'/1/5/',1,1,0,'users',5);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (11,5,10,1,1,2,'/1/5/11/',1,1,0,'users/anonymous_user',11);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (12,5,11,1,1,2,'/1/5/12/',1,1,0,'users/guest_accounts',12);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (13,5,12,1,1,2,'/1/5/13/',1,1,0,'users/administrator_users',13);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (14,5,13,1,1,2,'/1/5/14/',1,1,0,'users/editors',14);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (15,13,14,1,1,3,'/1/5/13/15/',1,1,0,'users/administrator_users/administrator_user',15);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (42,12,40,1,1,3,'/1/5/12/42/',9,1,0,'users/guest_accounts/test_test',42);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (43,1,41,1,1,1,'/1/43/',9,1,0,'media',43);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (44,2,42,1,1,2,'/1/2/44/',9,1,0,'about_gallery',44);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (45,2,43,1,1,2,'/1/2/45/',9,1,0,'flowers',45);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (46,45,44,1,1,3,'/1/2/45/46/',9,1,0,'flowers/sunflower',46);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (47,45,45,1,1,3,'/1/2/45/47/',9,1,0,'flowers/a_blue_one',47);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (48,45,46,1,1,3,'/1/2/45/48/',9,1,0,'flowers/flower_3',48);
INSERT INTO ezcontentobject_tree (node_id, parent_node_id, contentobject_id, contentobject_version, contentobject_is_published, depth, path_string, sort_field, sort_order, priority, path_identification_string, main_node_id) VALUES (49,45,47,1,1,3,'/1/2/45/49/',9,1,0,'flowers/flower_4',49);

--
-- Table structure for table 'ezcontentobject_version'
--

CREATE TABLE ezcontentobject_version (
  id int(11) NOT NULL auto_increment,
  contentobject_id int(11) default NULL,
  creator_id int(11) NOT NULL default '0',
  version int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  status int(11) NOT NULL default '0',
  workflow_event_pos int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezcontentobject_version'
--


INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (1,1,14,1,0,0,1,1,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (4,4,14,1,0,0,1,1,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (436,1,14,2,1033919080,1033919080,1,1,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (438,10,14,1,1033920649,1033920665,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (439,11,14,1,1033920737,1033920746,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (440,12,14,1,1033920760,1033920775,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (441,13,14,1,1033920786,1033920794,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (442,14,14,1,1033920808,1033920830,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (471,40,14,1,1053613007,1053613020,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (472,41,14,1,1060695450,1060695457,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (473,42,14,1,1061232647,1061232666,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (474,43,14,1,1061232771,1061232785,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (475,44,14,1,1061232824,1061232844,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (476,45,14,1,1061232852,1061232872,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (477,46,14,1,1061234247,1061234271,1,0,0);
INSERT INTO ezcontentobject_version (id, contentobject_id, creator_id, version, created, modified, status, workflow_event_pos, user_id) VALUES (478,47,14,1,1061234280,1061234305,1,0,0);

--
-- Table structure for table 'ezdiscountrule'
--

CREATE TABLE ezdiscountrule (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezdiscountrule'
--



--
-- Table structure for table 'ezdiscountsubrule'
--

CREATE TABLE ezdiscountsubrule (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  discountrule_id int(11) NOT NULL default '0',
  discount_percent float default NULL,
  limitation char(1) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezdiscountsubrule'
--



--
-- Table structure for table 'ezdiscountsubrule_value'
--

CREATE TABLE ezdiscountsubrule_value (
  discountsubrule_id int(11) NOT NULL default '0',
  value int(11) NOT NULL default '0',
  issection int(1) NOT NULL default '0',
  PRIMARY KEY  (discountsubrule_id,value,issection)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezdiscountsubrule_value'
--



--
-- Table structure for table 'ezenumobjectvalue'
--

CREATE TABLE ezenumobjectvalue (
  contentobject_attribute_id int(11) NOT NULL default '0',
  contentobject_attribute_version int(11) NOT NULL default '0',
  enumid int(11) NOT NULL default '0',
  enumelement varchar(255) NOT NULL default '',
  enumvalue varchar(255) NOT NULL default '',
  PRIMARY KEY  (contentobject_attribute_id,contentobject_attribute_version,enumid),
  KEY ezenumobjectvalue_co_attr_id_co_attr_ver (contentobject_attribute_id,contentobject_attribute_version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezenumobjectvalue'
--



--
-- Table structure for table 'ezenumvalue'
--

CREATE TABLE ezenumvalue (
  id int(11) NOT NULL auto_increment,
  contentclass_attribute_id int(11) NOT NULL default '0',
  contentclass_attribute_version int(11) NOT NULL default '0',
  enumelement varchar(255) NOT NULL default '',
  enumvalue varchar(255) NOT NULL default '',
  placement int(11) NOT NULL default '0',
  PRIMARY KEY  (id,contentclass_attribute_id,contentclass_attribute_version),
  KEY ezenumvalue_co_cl_attr_id_co_class_att_ver (contentclass_attribute_id,contentclass_attribute_version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezenumvalue'
--


INSERT INTO ezenumvalue (id, contentclass_attribute_id, contentclass_attribute_version, enumelement, enumvalue, placement) VALUES (2,136,0,'Ok','3',2);
INSERT INTO ezenumvalue (id, contentclass_attribute_id, contentclass_attribute_version, enumelement, enumvalue, placement) VALUES (1,136,0,'Poor','2',1);
INSERT INTO ezenumvalue (id, contentclass_attribute_id, contentclass_attribute_version, enumelement, enumvalue, placement) VALUES (3,136,0,'Good','5',3);

--
-- Table structure for table 'ezforgot_password'
--

CREATE TABLE ezforgot_password (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  hash_key varchar(32) NOT NULL default '',
  time int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezforgot_password'
--



--
-- Table structure for table 'ezgeneral_digest_user_settings'
--

CREATE TABLE ezgeneral_digest_user_settings (
  id int(11) NOT NULL auto_increment,
  address varchar(255) NOT NULL default '',
  receive_digest int(11) NOT NULL default '0',
  digest_type int(11) NOT NULL default '0',
  day varchar(255) NOT NULL default '',
  time varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezgeneral_digest_user_settings'
--



--
-- Table structure for table 'ezimage'
--

CREATE TABLE ezimage (
  contentobject_attribute_id int(11) NOT NULL default '0',
  version int(11) NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  original_filename varchar(255) NOT NULL default '',
  mime_type varchar(50) NOT NULL default '',
  alternative_text varchar(255) NOT NULL default '',
  PRIMARY KEY  (contentobject_attribute_id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezimage'
--


INSERT INTO ezimage (contentobject_attribute_id, version, filename, original_filename, mime_type, alternative_text) VALUES (107,1,'phph8inGr.jpg','flower2.jpg','image/jpeg','');
INSERT INTO ezimage (contentobject_attribute_id, version, filename, original_filename, mime_type, alternative_text) VALUES (110,1,'php2Kecsf.jpg','flower.jpg','image/jpeg','');
INSERT INTO ezimage (contentobject_attribute_id, version, filename, original_filename, mime_type, alternative_text) VALUES (113,1,'phpRFANTu.jpg','flower3.jpg','image/jpeg','');
INSERT INTO ezimage (contentobject_attribute_id, version, filename, original_filename, mime_type, alternative_text) VALUES (116,1,'phpUQzr4Z.jpg','flower4.jpg','image/jpeg','');

--
-- Table structure for table 'ezimagevariation'
--

CREATE TABLE ezimagevariation (
  contentobject_attribute_id int(11) NOT NULL default '0',
  version int(11) NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  additional_path varchar(255) default NULL,
  requested_width int(11) NOT NULL default '0',
  requested_height int(11) NOT NULL default '0',
  width int(11) NOT NULL default '0',
  height int(11) NOT NULL default '0',
  PRIMARY KEY  (contentobject_attribute_id,version,requested_width,requested_height)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezimagevariation'
--



--
-- Table structure for table 'ezinfocollection'
--

CREATE TABLE ezinfocollection (
  id int(11) NOT NULL auto_increment,
  contentobject_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezinfocollection'
--



--
-- Table structure for table 'ezinfocollection_attribute'
--

CREATE TABLE ezinfocollection_attribute (
  id int(11) NOT NULL auto_increment,
  informationcollection_id int(11) NOT NULL default '0',
  data_text text,
  data_int int(11) default NULL,
  data_float float default NULL,
  contentclass_attribute_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezinfocollection_attribute'
--



--
-- Table structure for table 'ezkeyword'
--

CREATE TABLE ezkeyword (
  id int(11) NOT NULL auto_increment,
  keyword varchar(255) default NULL,
  class_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezkeyword'
--



--
-- Table structure for table 'ezkeyword_attribute_link'
--

CREATE TABLE ezkeyword_attribute_link (
  id int(11) NOT NULL auto_increment,
  keyword_id int(11) NOT NULL default '0',
  objectattribute_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezkeyword_attribute_link'
--



--
-- Table structure for table 'ezmedia'
--

CREATE TABLE ezmedia (
  contentobject_attribute_id int(11) NOT NULL default '0',
  version int(11) NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  original_filename varchar(255) NOT NULL default '',
  mime_type varchar(50) NOT NULL default '',
  width int(11) default NULL,
  height int(11) default NULL,
  has_controller int(1) default NULL,
  is_autoplay int(1) default NULL,
  pluginspage varchar(255) default NULL,
  quality varchar(50) default NULL,
  controls varchar(50) default NULL,
  is_loop int(1) default NULL,
  PRIMARY KEY  (contentobject_attribute_id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezmedia'
--



--
-- Table structure for table 'ezmessage'
--

CREATE TABLE ezmessage (
  id int(11) NOT NULL auto_increment,
  send_method varchar(50) NOT NULL default '',
  send_weekday varchar(50) NOT NULL default '',
  send_time varchar(50) NOT NULL default '',
  destination_address varchar(50) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  body text,
  is_sent int(1) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezmessage'
--



--
-- Table structure for table 'ezmodule_run'
--

CREATE TABLE ezmodule_run (
  id int(11) NOT NULL auto_increment,
  workflow_process_id int(11) default NULL,
  module_name varchar(255) default NULL,
  function_name varchar(255) default NULL,
  module_data text,
  PRIMARY KEY  (id),
  UNIQUE KEY ezmodule_run_workflow_process_id_s (workflow_process_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezmodule_run'
--



--
-- Table structure for table 'eznode_assignment'
--

CREATE TABLE eznode_assignment (
  id int(11) NOT NULL auto_increment,
  contentobject_id int(11) default NULL,
  contentobject_version int(11) default NULL,
  parent_node int(11) default NULL,
  sort_field int(11) default '1',
  sort_order int(1) default '1',
  is_main int(11) NOT NULL default '0',
  from_node_id int(11) default '0',
  remote_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'eznode_assignment'
--


INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (2,1,1,1,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (4,8,2,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (144,4,1,1,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (147,210,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (146,209,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (145,1,2,1,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (148,9,1,2,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (149,10,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (150,11,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (151,12,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (152,13,1,5,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (153,14,1,13,1,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (181,40,1,12,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (182,41,1,1,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (183,42,1,2,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (184,43,1,2,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (185,44,1,45,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (186,45,1,45,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (187,46,1,45,9,1,1,0,0);
INSERT INTO eznode_assignment (id, contentobject_id, contentobject_version, parent_node, sort_field, sort_order, is_main, from_node_id, remote_id) VALUES (188,47,1,45,9,1,1,0,0);

--
-- Table structure for table 'eznotificationcollection'
--

CREATE TABLE eznotificationcollection (
  id int(11) NOT NULL auto_increment,
  event_id int(11) NOT NULL default '0',
  handler varchar(255) NOT NULL default '',
  transport varchar(255) NOT NULL default '',
  data_subject text NOT NULL,
  data_text text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'eznotificationcollection'
--



--
-- Table structure for table 'eznotificationcollection_item'
--

CREATE TABLE eznotificationcollection_item (
  id int(11) NOT NULL auto_increment,
  collection_id int(11) NOT NULL default '0',
  event_id int(11) NOT NULL default '0',
  address varchar(255) NOT NULL default '',
  send_date int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'eznotificationcollection_item'
--



--
-- Table structure for table 'eznotificationevent'
--

CREATE TABLE eznotificationevent (
  id int(11) NOT NULL auto_increment,
  status int(11) NOT NULL default '0',
  event_type_string varchar(255) NOT NULL default '',
  data_int1 int(11) NOT NULL default '0',
  data_int2 int(11) NOT NULL default '0',
  data_int3 int(11) NOT NULL default '0',
  data_int4 int(11) NOT NULL default '0',
  data_text1 text NOT NULL,
  data_text2 text NOT NULL,
  data_text3 text NOT NULL,
  data_text4 text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'eznotificationevent'
--


INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (1,0,'ezpublish',41,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (2,0,'ezpublish',42,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (3,0,'ezpublish',43,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (4,0,'ezpublish',44,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (5,0,'ezpublish',45,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (6,0,'ezpublish',46,1,0,0,'','','','');
INSERT INTO eznotificationevent (id, status, event_type_string, data_int1, data_int2, data_int3, data_int4, data_text1, data_text2, data_text3, data_text4) VALUES (7,0,'ezpublish',47,1,0,0,'','','','');

--
-- Table structure for table 'ezoperation_memento'
--

CREATE TABLE ezoperation_memento (
  id int(11) NOT NULL auto_increment,
  memento_key varchar(32) NOT NULL default '',
  memento_data text NOT NULL,
  main int(11) NOT NULL default '0',
  main_key varchar(32) NOT NULL default '',
  PRIMARY KEY  (id,memento_key)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezoperation_memento'
--



--
-- Table structure for table 'ezorder'
--

CREATE TABLE ezorder (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  productcollection_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  is_temporary int(11) NOT NULL default '1',
  order_nr int(11) NOT NULL default '0',
  data_text_2 text,
  data_text_1 text,
  account_identifier varchar(100) NOT NULL default 'default',
  ignore_vat int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezorder'
--



--
-- Table structure for table 'ezorder_item'
--

CREATE TABLE ezorder_item (
  id int(11) NOT NULL auto_increment,
  order_id int(11) NOT NULL default '0',
  description varchar(255) default NULL,
  price float default NULL,
  vat_value int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezorder_item'
--



--
-- Table structure for table 'ezpolicy'
--

CREATE TABLE ezpolicy (
  id int(11) NOT NULL auto_increment,
  role_id int(11) default NULL,
  function_name varchar(255) default NULL,
  module_name varchar(255) default NULL,
  limitation char(1) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezpolicy'
--


INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (317,3,'*','content','*');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (308,2,'*','*','*');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (326,1,'read','content','');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (325,1,'login','user','*');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (319,3,'login','user','*');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (323,5,'*','content','*');
INSERT INTO ezpolicy (id, role_id, function_name, module_name, limitation) VALUES (324,5,'login','user','*');

--
-- Table structure for table 'ezpolicy_limitation'
--

CREATE TABLE ezpolicy_limitation (
  id int(11) NOT NULL auto_increment,
  policy_id int(11) default NULL,
  identifier varchar(255) NOT NULL default '',
  role_id int(11) default NULL,
  function_name varchar(255) default NULL,
  module_name varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezpolicy_limitation'
--


INSERT INTO ezpolicy_limitation (id, policy_id, identifier, role_id, function_name, module_name) VALUES (249,326,'Class',0,'read','content');

--
-- Table structure for table 'ezpolicy_limitation_value'
--

CREATE TABLE ezpolicy_limitation_value (
  id int(11) NOT NULL auto_increment,
  limitation_id int(11) default NULL,
  value varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezpolicy_limitation_value'
--


INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (435,249,'1');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (436,249,'10');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (437,249,'10');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (438,249,'11');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (439,249,'11');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (440,249,'12');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (441,249,'12');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (442,249,'13');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (443,249,'13');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (444,249,'2');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (445,249,'2');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (446,249,'5');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (447,249,'5');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (448,249,'6');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (449,249,'6');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (450,249,'7');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (451,249,'7');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (452,249,'8');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (453,249,'8');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (454,249,'9');
INSERT INTO ezpolicy_limitation_value (id, limitation_id, value) VALUES (455,249,'9');

--
-- Table structure for table 'ezpreferences'
--

CREATE TABLE ezpreferences (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  name varchar(100) default NULL,
  value varchar(100) default NULL,
  PRIMARY KEY  (id),
  KEY ezpreferences_name (name)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezpreferences'
--



--
-- Table structure for table 'ezproductcollection'
--

CREATE TABLE ezproductcollection (
  id int(11) NOT NULL auto_increment,
  created int(11) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezproductcollection'
--



--
-- Table structure for table 'ezproductcollection_item'
--

CREATE TABLE ezproductcollection_item (
  id int(11) NOT NULL auto_increment,
  productcollection_id int(11) NOT NULL default '0',
  contentobject_id int(11) NOT NULL default '0',
  item_count int(11) NOT NULL default '0',
  price double default NULL,
  is_vat_inc int(11) default NULL,
  vat_value float default NULL,
  discount float default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezproductcollection_item'
--



--
-- Table structure for table 'ezproductcollection_item_opt'
--

CREATE TABLE ezproductcollection_item_opt (
  id int(11) NOT NULL auto_increment,
  item_id int(11) NOT NULL default '0',
  option_item_id int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  value varchar(255) NOT NULL default '',
  price float NOT NULL default '0',
  object_attribute_id int(11) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezproductcollection_item_opt'
--



--
-- Table structure for table 'ezrole'
--

CREATE TABLE ezrole (
  id int(11) NOT NULL auto_increment,
  version int(11) default '0',
  name varchar(255) NOT NULL default '',
  value char(1) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezrole'
--


INSERT INTO ezrole (id, version, name, value) VALUES (1,0,'Anonymous','');
INSERT INTO ezrole (id, version, name, value) VALUES (2,0,'Administrator','*');
INSERT INTO ezrole (id, version, name, value) VALUES (3,0,'Editor','');
INSERT INTO ezrole (id, version, name, value) VALUES (5,3,'Editor',NULL);

--
-- Table structure for table 'ezsearch_object_word_link'
--

CREATE TABLE ezsearch_object_word_link (
  id int(11) NOT NULL auto_increment,
  contentobject_id int(11) NOT NULL default '0',
  word_id int(11) NOT NULL default '0',
  frequency float NOT NULL default '0',
  placement int(11) NOT NULL default '0',
  prev_word_id int(11) NOT NULL default '0',
  next_word_id int(11) NOT NULL default '0',
  contentclass_id int(11) NOT NULL default '0',
  published int(11) NOT NULL default '0',
  section_id int(11) NOT NULL default '0',
  contentclass_attribute_id int(11) NOT NULL default '0',
  identifier varchar(255) NOT NULL default '',
  integer_value int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY ezsearch_object_word_link_object (contentobject_id),
  KEY ezsearch_object_word_link_word (word_id),
  KEY ezsearch_object_word_link_frequency (frequency),
  KEY ezsearch_object_word_link_identifier (identifier),
  KEY ezsearch_object_word_link_integer_value (integer_value)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsearch_object_word_link'
--


INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (26,40,5,0,0,0,5,4,1053613020,2,8,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (27,40,5,0,1,5,0,4,1053613020,2,9,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (28,41,6,0,0,0,0,1,1060695457,3,4,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (29,42,7,0,0,0,8,10,1061232666,1,140,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (30,42,8,0,1,7,9,10,1061232666,1,140,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (31,42,9,0,2,8,10,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (32,42,10,0,3,9,11,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (33,42,11,0,4,10,9,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (34,42,9,0,5,11,10,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (35,42,10,0,6,9,11,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (36,42,11,0,7,10,12,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (37,42,12,0,8,11,13,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (38,42,13,0,9,12,14,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (39,42,14,0,10,13,15,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (40,42,15,0,11,14,16,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (41,42,16,0,12,15,17,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (42,42,17,0,13,16,18,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (43,42,18,0,14,17,19,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (44,42,19,0,15,18,20,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (45,42,20,0,16,19,21,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (46,42,21,0,17,20,22,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (47,42,22,0,18,21,23,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (48,42,23,0,19,22,24,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (49,42,24,0,20,23,25,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (50,42,25,0,21,24,26,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (51,42,26,0,22,25,26,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (52,42,26,0,23,26,27,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (53,42,27,0,24,26,28,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (54,42,28,0,25,27,29,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (55,42,29,0,26,28,30,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (56,42,30,0,27,29,31,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (57,42,31,0,28,30,32,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (58,42,32,0,29,31,33,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (59,42,33,0,30,32,34,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (60,42,34,0,31,33,35,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (61,42,35,0,32,34,28,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (62,42,28,0,33,35,36,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (63,42,36,0,34,28,37,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (64,42,37,0,35,36,38,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (65,42,38,0,36,37,39,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (66,42,39,0,37,38,40,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (67,42,40,0,38,39,41,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (68,42,41,0,39,40,42,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (69,42,42,0,40,41,43,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (70,42,43,0,41,42,44,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (71,42,44,0,42,43,45,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (72,42,45,0,43,44,32,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (73,42,32,0,44,45,46,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (74,42,46,0,45,32,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (75,42,47,0,46,46,48,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (76,42,48,0,47,47,49,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (77,42,49,0,48,48,50,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (78,42,50,0,49,49,51,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (79,42,51,0,50,50,12,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (80,42,12,0,51,51,51,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (81,42,51,0,52,12,52,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (82,42,52,0,53,51,53,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (83,42,53,0,54,52,54,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (84,42,54,0,55,53,55,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (85,42,55,0,56,54,56,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (86,42,56,0,57,55,57,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (87,42,57,0,58,56,32,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (88,42,32,0,59,57,58,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (89,42,58,0,60,32,59,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (90,42,59,0,61,58,18,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (91,42,18,0,62,59,53,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (92,42,53,0,63,18,22,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (93,42,22,0,64,53,60,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (94,42,60,0,65,22,61,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (95,42,61,0,66,60,62,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (96,42,62,0,67,61,63,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (97,42,63,0,68,62,64,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (98,42,64,0,69,63,65,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (99,42,65,0,70,64,66,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (100,42,66,0,71,65,67,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (101,42,67,0,72,66,59,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (102,42,59,0,73,67,57,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (103,42,57,0,74,59,45,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (104,42,45,0,75,57,68,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (105,42,68,0,76,45,69,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (106,42,69,0,77,68,57,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (107,42,57,0,78,69,22,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (108,42,22,0,79,57,70,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (109,42,70,0,80,22,71,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (110,42,71,0,81,70,72,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (111,42,72,0,82,71,73,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (112,42,73,0,83,72,74,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (113,42,74,0,84,73,75,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (114,42,75,0,85,74,72,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (115,42,72,0,86,75,76,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (116,42,76,0,87,72,77,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (117,42,77,0,88,76,78,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (118,42,78,0,89,77,57,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (119,42,57,0,90,78,79,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (120,42,79,0,91,57,80,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (121,42,80,0,92,79,81,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (122,42,81,0,93,80,82,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (123,42,82,0,94,81,83,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (124,42,83,0,95,82,19,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (125,42,19,0,96,83,84,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (126,42,84,0,97,19,63,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (127,42,63,0,98,84,67,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (128,42,67,0,99,63,85,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (129,42,85,0,100,67,86,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (130,42,86,0,101,85,29,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (131,42,29,0,102,86,87,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (132,42,87,0,103,29,51,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (133,42,51,0,104,87,44,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (134,42,44,0,105,51,60,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (135,42,60,0,106,44,88,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (136,42,88,0,107,60,67,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (137,42,67,0,108,88,89,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (138,42,89,0,109,67,36,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (139,42,36,0,110,89,90,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (140,42,90,0,111,36,74,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (141,42,74,0,112,90,79,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (142,42,79,0,113,74,80,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (143,42,80,0,114,79,31,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (144,42,31,0,115,80,86,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (145,42,86,0,116,31,91,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (146,42,91,0,117,86,92,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (147,42,92,0,118,91,93,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (148,42,93,0,119,92,94,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (149,42,94,0,120,93,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (150,42,47,0,121,94,93,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (151,42,93,0,122,47,24,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (152,42,24,0,123,93,95,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (153,42,95,0,124,24,96,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (154,42,96,0,125,95,97,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (155,42,97,0,126,96,64,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (156,42,64,0,127,97,33,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (157,42,33,0,128,64,98,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (158,42,98,0,129,33,32,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (159,42,32,0,130,98,56,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (160,42,56,0,131,32,39,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (161,42,39,0,132,56,99,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (162,42,99,0,133,39,100,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (163,42,100,0,134,99,45,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (164,42,45,0,135,100,45,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (165,42,45,0,136,45,53,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (166,42,53,0,137,45,33,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (167,42,33,0,138,53,101,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (168,42,101,0,139,33,102,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (169,42,102,0,140,101,34,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (170,42,34,0,141,102,51,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (171,42,51,0,142,34,72,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (172,42,72,0,143,51,88,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (173,42,88,0,144,72,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (174,42,47,0,145,88,103,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (175,42,103,0,146,47,104,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (176,42,104,0,147,103,34,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (177,42,34,0,148,104,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (178,42,47,0,149,34,105,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (179,42,105,0,150,47,45,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (180,42,45,0,151,105,28,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (181,42,28,0,152,45,19,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (182,42,19,0,153,28,23,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (183,42,23,0,154,19,39,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (184,42,39,0,155,23,37,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (185,42,37,0,156,39,66,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (186,42,66,0,157,37,84,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (187,42,84,0,158,66,106,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (188,42,106,0,159,84,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (189,42,47,0,160,106,81,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (190,42,81,0,161,47,56,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (191,42,56,0,162,81,53,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (192,42,53,0,163,56,47,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (193,42,47,0,164,53,32,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (194,42,32,0,165,47,67,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (195,42,67,0,166,32,21,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (196,42,21,0,167,67,30,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (197,42,30,0,168,21,18,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (198,42,18,0,169,30,9,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (199,42,9,0,170,18,55,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (200,42,55,0,171,9,107,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (201,42,107,0,172,55,108,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (202,42,108,0,173,107,44,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (203,42,44,0,174,108,21,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (204,42,21,0,175,44,55,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (205,42,55,0,176,21,0,10,1061232666,1,141,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (206,43,109,0,0,0,0,1,1061232785,1,4,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (207,44,110,0,0,0,111,5,1061232844,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (208,44,111,0,1,110,112,5,1061232844,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (209,44,112,0,2,111,0,5,1061232844,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (210,45,30,0,0,0,113,5,1061232872,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (211,45,113,0,1,30,114,5,1061232872,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (212,45,114,0,2,113,115,5,1061232872,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (213,45,115,0,3,114,116,5,1061232872,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (214,45,116,0,4,115,0,5,1061232872,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (215,46,117,0,0,0,118,5,1061234271,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (216,46,118,0,1,117,119,5,1061234271,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (217,46,119,0,2,118,114,5,1061234271,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (218,46,114,0,3,119,0,5,1061234271,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (219,47,117,0,0,0,120,5,1061234305,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (220,47,120,0,1,117,121,5,1061234305,1,116,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (221,47,121,0,2,120,30,5,1061234305,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (222,47,30,0,3,121,122,5,1061234305,1,117,'',0);
INSERT INTO ezsearch_object_word_link (id, contentobject_id, word_id, frequency, placement, prev_word_id, next_word_id, contentclass_id, published, section_id, contentclass_attribute_id, identifier, integer_value) VALUES (223,47,122,0,4,30,0,5,1061234305,1,117,'',0);

--
-- Table structure for table 'ezsearch_return_count'
--

CREATE TABLE ezsearch_return_count (
  id int(11) NOT NULL auto_increment,
  phrase_id int(11) NOT NULL default '0',
  time int(11) NOT NULL default '0',
  count int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsearch_return_count'
--



--
-- Table structure for table 'ezsearch_search_phrase'
--

CREATE TABLE ezsearch_search_phrase (
  id int(11) NOT NULL auto_increment,
  phrase varchar(250) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsearch_search_phrase'
--



--
-- Table structure for table 'ezsearch_word'
--

CREATE TABLE ezsearch_word (
  id int(11) NOT NULL auto_increment,
  word varchar(150) default NULL,
  object_count int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY ezsearch_word (word)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsearch_word'
--


INSERT INTO ezsearch_word (id, word, object_count) VALUES (5,'test',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (6,'media',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (7,'about',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (8,'gallery',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (9,'proin',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (10,'consectetuer',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (11,'lacus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (12,'nec',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (13,'neque',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (14,'vivamus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (15,'volutpat',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (16,'elit',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (17,'id',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (18,'purus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (19,'nulla',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (20,'varius',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (21,'dictum',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (22,'est',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (23,'maecenas',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (24,'sapien',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (25,'pede',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (26,'mattis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (27,'mollis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (28,'in',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (29,'pulvinar',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (30,'a',3);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (31,'mi',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (32,'vestibulum',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (33,'ante',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (34,'ipsum',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (35,'primis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (36,'faucibus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (37,'orci',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (38,'luctus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (39,'et',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (40,'ultrices',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (41,'posuere',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (42,'cubilia',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (43,'curae',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (44,'phasellus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (45,'arcu',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (46,'justo',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (47,'sed',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (48,'rhoncus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (49,'suspendisse',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (50,'quis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (51,'turpis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (52,'pretium',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (53,'scelerisque',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (54,'fusce',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (55,'dignissim',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (56,'metus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (57,'ut',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (58,'rutrum',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (59,'risus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (60,'eu',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (61,'venenatis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (62,'velit',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (63,'magna',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (64,'ac',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (65,'quam',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (66,'morbi',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (67,'non',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (68,'eleifend',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (69,'consequat',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (70,'augue',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (71,'malesuada',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (72,'vitae',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (73,'porttitor',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (74,'pellentesque',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (75,'egestas',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (76,'nunc',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (77,'curabitur',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (78,'feugiat',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (79,'sit',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (80,'amet',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (81,'dui',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (82,'etiam',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (83,'fermentum',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (84,'ornare',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (85,'urna',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (86,'cras',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (87,'imperdiet',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (88,'felis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (89,'diam',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (90,'viverra',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (91,'euismod',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (92,'leo',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (93,'vel',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (94,'libero',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (95,'mauris',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (96,'aliquam',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (97,'enim',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (98,'nam',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (99,'blandit',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (100,'vulputate',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (101,'at',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (102,'dapibus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (103,'aliquet',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (104,'tempus',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (105,'facilisis',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (106,'massa',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (107,'semper',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (108,'odio',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (109,'flowers',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (110,'sunflower',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (111,'or',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (112,'not',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (113,'blue',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (114,'one',2);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (115,'and',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (116,'green',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (117,'flower',2);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (118,'3',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (119,'white',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (120,'4',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (121,'actually',1);
INSERT INTO ezsearch_word (id, word, object_count) VALUES (122,'tree',1);

--
-- Table structure for table 'ezsection'
--

CREATE TABLE ezsection (
  id int(11) NOT NULL auto_increment,
  name varchar(255) default NULL,
  locale varchar(255) default NULL,
  navigation_part_identifier varchar(100) default 'ezcontentnavigationpart',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsection'
--


INSERT INTO ezsection (id, name, locale, navigation_part_identifier) VALUES (1,'Standard section','nor-NO','ezcontentnavigationpart');
INSERT INTO ezsection (id, name, locale, navigation_part_identifier) VALUES (2,'Users','','ezusernavigationpart');
INSERT INTO ezsection (id, name, locale, navigation_part_identifier) VALUES (3,'Media','','ezmedianavigationpart');

--
-- Table structure for table 'ezsession'
--

CREATE TABLE ezsession (
  session_key varchar(32) NOT NULL default '',
  expiration_time int(11) unsigned NOT NULL default '0',
  data text NOT NULL,
  PRIMARY KEY  (session_key),
  KEY expiration_time (expiration_time)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsession'
--



--
-- Table structure for table 'ezsubtree_notification_rule'
--

CREATE TABLE ezsubtree_notification_rule (
  id int(11) NOT NULL auto_increment,
  address varchar(255) NOT NULL default '',
  use_digest int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezsubtree_notification_rule'
--



--
-- Table structure for table 'eztrigger'
--

CREATE TABLE eztrigger (
  id int(11) NOT NULL auto_increment,
  name varchar(255) default NULL,
  module_name varchar(200) NOT NULL default '',
  function_name varchar(200) NOT NULL default '',
  connect_type char(1) NOT NULL default '',
  workflow_id int(11) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY eztrigger_def_id (module_name,function_name,connect_type)
) TYPE=MyISAM;

--
-- Dumping data for table 'eztrigger'
--



--
-- Table structure for table 'ezurl'
--

CREATE TABLE ezurl (
  id int(11) NOT NULL auto_increment,
  url varchar(255) default NULL,
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  is_valid int(11) NOT NULL default '1',
  last_checked int(11) NOT NULL default '0',
  original_url_md5 varchar(32) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezurl'
--



--
-- Table structure for table 'ezurl_object_link'
--

CREATE TABLE ezurl_object_link (
  url_id int(11) NOT NULL default '0',
  contentobject_attribute_id int(11) NOT NULL default '0',
  contentobject_attribute_version int(11) NOT NULL default '0',
  PRIMARY KEY  (url_id,contentobject_attribute_id,contentobject_attribute_version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezurl_object_link'
--



--
-- Table structure for table 'ezurlalias'
--

CREATE TABLE ezurlalias (
  id int(11) NOT NULL auto_increment,
  source_url text NOT NULL,
  source_md5 varchar(32) default NULL,
  destination_url text NOT NULL,
  is_internal int(11) NOT NULL default '1',
  forward_to_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY ezurlalias_source_md5 (source_md5)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezurlalias'
--


INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (12,'','d41d8cd98f00b204e9800998ecf8427e','content/view/full/2',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (13,'users','9bc65c2abec141778ffaa729489f3e87','content/view/full/5',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (14,'users/anonymous_user','a37b7463e2c21098fa1a729dad4b4437','content/view/full/11',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (15,'users/guest_accounts','02d4e844e3a660857a3f81585995ffe1','content/view/full/12',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (16,'users/administrator_users','1b1d79c16700fd6003ea7be233e754ba','content/view/full/13',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (17,'users/editors','0bb9dd665c96bbc1cf36b79180786dea','content/view/full/14',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (18,'users/administrator_users/administrator_user','f1305ac5f327a19b451d82719e0c3f5d','content/view/full/15',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (19,'users/guest_accounts/test_test','27a1813763d43de613bf05c31df7a6ef','content/view/full/42',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (20,'media','62933a2951ef01f4eafd9bdf4d3cd2f0','content/view/full/43',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (21,'about_gallery','1e94f1acda820814aa22ed94235d2fad','content/view/full/44',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (22,'flowers','7d37c580f9c36fa004af865448a6e278','content/view/full/45',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (23,'flowers/sunflower','361c42112cf2acd5ca74c99fa2731aee','content/view/full/46',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (24,'flowers/a_blue_one','9dcb161374eaaca0a7112d55c4e4509a','content/view/full/47',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (25,'flowers/flower_3','8f3a60e6c1712faa225402ccffb4fe74','content/view/full/48',1,0);
INSERT INTO ezurlalias (id, source_url, source_md5, destination_url, is_internal, forward_to_id) VALUES (26,'flowers/flower_4','50336154c8f588bc45846725f3f22347','content/view/full/49',1,0);

--
-- Table structure for table 'ezuser'
--

CREATE TABLE ezuser (
  contentobject_id int(11) NOT NULL default '0',
  login varchar(150) NOT NULL default '',
  email varchar(150) NOT NULL default '',
  password_hash_type int(11) NOT NULL default '1',
  password_hash varchar(50) default NULL,
  PRIMARY KEY  (contentobject_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezuser'
--


INSERT INTO ezuser (contentobject_id, login, email, password_hash_type, password_hash) VALUES (10,'anonymous','nospam@ez.no',2,'4e6f6184135228ccd45f8233d72a0363');
INSERT INTO ezuser (contentobject_id, login, email, password_hash_type, password_hash) VALUES (14,'admin','nospam@ez.no',2,'c78e3b0f3d9244ed8c6d1c29464bdff9');
INSERT INTO ezuser (contentobject_id, login, email, password_hash_type, password_hash) VALUES (40,'test','test@test.com',2,'be778b473235e210cc577056226536a4');

--
-- Table structure for table 'ezuser_accountkey'
--

CREATE TABLE ezuser_accountkey (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  hash_key varchar(32) NOT NULL default '',
  time int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezuser_accountkey'
--



--
-- Table structure for table 'ezuser_discountrule'
--

CREATE TABLE ezuser_discountrule (
  id int(11) NOT NULL auto_increment,
  discountrule_id int(11) default NULL,
  contentobject_id int(11) default NULL,
  name varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezuser_discountrule'
--



--
-- Table structure for table 'ezuser_role'
--

CREATE TABLE ezuser_role (
  id int(11) NOT NULL auto_increment,
  role_id int(11) default NULL,
  contentobject_id int(11) default NULL,
  PRIMARY KEY  (id),
  KEY ezuser_role_contentobject_id (contentobject_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezuser_role'
--


INSERT INTO ezuser_role (id, role_id, contentobject_id) VALUES (29,1,10);
INSERT INTO ezuser_role (id, role_id, contentobject_id) VALUES (25,2,12);
INSERT INTO ezuser_role (id, role_id, contentobject_id) VALUES (30,3,13);
INSERT INTO ezuser_role (id, role_id, contentobject_id) VALUES (28,1,11);

--
-- Table structure for table 'ezuser_setting'
--

CREATE TABLE ezuser_setting (
  user_id int(11) NOT NULL default '0',
  is_enabled int(1) NOT NULL default '0',
  max_login int(11) default NULL,
  PRIMARY KEY  (user_id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezuser_setting'
--


INSERT INTO ezuser_setting (user_id, is_enabled, max_login) VALUES (10,1,1000);
INSERT INTO ezuser_setting (user_id, is_enabled, max_login) VALUES (14,1,10);
INSERT INTO ezuser_setting (user_id, is_enabled, max_login) VALUES (23,1,0);
INSERT INTO ezuser_setting (user_id, is_enabled, max_login) VALUES (40,1,0);

--
-- Table structure for table 'ezvattype'
--

CREATE TABLE ezvattype (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  percentage float default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezvattype'
--


INSERT INTO ezvattype (id, name, percentage) VALUES (1,'Std',0);

--
-- Table structure for table 'ezwaituntildatevalue'
--

CREATE TABLE ezwaituntildatevalue (
  id int(11) NOT NULL auto_increment,
  workflow_event_id int(11) NOT NULL default '0',
  workflow_event_version int(11) NOT NULL default '0',
  contentclass_id int(11) NOT NULL default '0',
  contentclass_attribute_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id,workflow_event_id,workflow_event_version),
  KEY ezwaituntildateevalue_wf_ev_id_wf_ver (workflow_event_id,workflow_event_version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezwaituntildatevalue'
--



--
-- Table structure for table 'ezwishlist'
--

CREATE TABLE ezwishlist (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  productcollection_id int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezwishlist'
--



--
-- Table structure for table 'ezworkflow'
--

CREATE TABLE ezworkflow (
  id int(11) NOT NULL auto_increment,
  version int(11) NOT NULL default '0',
  is_enabled int(1) NOT NULL default '0',
  workflow_type_string varchar(50) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  creator_id int(11) NOT NULL default '0',
  modifier_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow'
--



--
-- Table structure for table 'ezworkflow_assign'
--

CREATE TABLE ezworkflow_assign (
  id int(11) NOT NULL auto_increment,
  workflow_id int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  access_type int(11) NOT NULL default '0',
  as_tree int(1) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow_assign'
--



--
-- Table structure for table 'ezworkflow_event'
--

CREATE TABLE ezworkflow_event (
  id int(11) NOT NULL auto_increment,
  version int(11) NOT NULL default '0',
  workflow_id int(11) NOT NULL default '0',
  workflow_type_string varchar(50) NOT NULL default '',
  description varchar(50) NOT NULL default '',
  data_int1 int(11) default NULL,
  data_int2 int(11) default NULL,
  data_int3 int(11) default NULL,
  data_int4 int(11) default NULL,
  data_text1 varchar(50) default NULL,
  data_text2 varchar(50) default NULL,
  data_text3 varchar(50) default NULL,
  data_text4 varchar(50) default NULL,
  placement int(11) NOT NULL default '0',
  PRIMARY KEY  (id,version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow_event'
--



--
-- Table structure for table 'ezworkflow_group'
--

CREATE TABLE ezworkflow_group (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  creator_id int(11) NOT NULL default '0',
  modifier_id int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow_group'
--


INSERT INTO ezworkflow_group (id, name, creator_id, modifier_id, created, modified) VALUES (1,'Standard',14,14,1024392098,1024392098);

--
-- Table structure for table 'ezworkflow_group_link'
--

CREATE TABLE ezworkflow_group_link (
  workflow_id int(11) NOT NULL default '0',
  group_id int(11) NOT NULL default '0',
  workflow_version int(11) NOT NULL default '0',
  group_name varchar(255) default NULL,
  PRIMARY KEY  (workflow_id,group_id,workflow_version)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow_group_link'
--


INSERT INTO ezworkflow_group_link (workflow_id, group_id, workflow_version, group_name) VALUES (1,1,0,'Standard');

--
-- Table structure for table 'ezworkflow_process'
--

CREATE TABLE ezworkflow_process (
  id int(11) NOT NULL auto_increment,
  process_key varchar(32) NOT NULL default '',
  workflow_id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  content_id int(11) NOT NULL default '0',
  content_version int(11) NOT NULL default '0',
  node_id int(11) NOT NULL default '0',
  session_key varchar(32) NOT NULL default '0',
  event_id int(11) NOT NULL default '0',
  event_position int(11) NOT NULL default '0',
  last_event_id int(11) NOT NULL default '0',
  last_event_position int(11) NOT NULL default '0',
  last_event_status int(11) NOT NULL default '0',
  event_status int(11) NOT NULL default '0',
  created int(11) NOT NULL default '0',
  modified int(11) NOT NULL default '0',
  activation_date int(11) default NULL,
  event_state int(11) default NULL,
  status int(11) default NULL,
  parameters text,
  memento_key varchar(32) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table 'ezworkflow_process'
--



CREATE TABLE ezsite_data (
  id int(11) auto_increment NOT NULL,
  name varchar(60) NOT NULL default '',
  value text NOT NULL default '',
  PRIMARY KEY (id)
);

INSERT INTO ezsite_data (name, value) VALUES('ezpublish-version', '3.2.0');
INSERT INTO ezsite_data (name, value) VALUES('ezpublish-release', '2');

CREATE INDEX ezorder_item_order_id ON ezorder_item( order_id );
CREATE INDEX ezproductcollection_item_productcollection_id ON ezproductcollection_item( productcollection_id );
CREATE INDEX ezurlalias_source_url ON ezurlalias(source_url(255));
CREATE INDEX ezcontentobject_attribute_co_id_ver_lang_code ON ezcontentobject_attribute( contentobject_id, version, language_code);
