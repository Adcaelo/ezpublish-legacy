<?php
//
// Created on: <17-Apr-2002 11:05:08 amos>
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

$Module =& $Params['Module'];

include_once( 'lib/version.php' );
$ezinfo = array( 'version' => eZPublishSDK::version( true ),
                 'version_alias' => eZPublishSDK::version( true, true ) );


$text = "<a href=\"http://ez.no/developer\"><h3>About eZ publish " . eZPublishSDK::version( true )  ." ( " . eZPublishSDK::version( true, true ). " )</h3></a>
<hr noshade=\"noshade\"  />

<h3>What is eZ publish?</h3>
<p>
eZ publish 3 is a professional PHP application framework with advanced CMS
(content management system) functionality. As a CMS it's most notable feature
is its revolutionary, fully customizable and extendable content model. This is
also what makes it suitable as a platform for general PHP development, allowing
you to develop professional Internet applications fast.
</p>
<p>
Standard CMS functionality, like news publishing, e-commerce and forums is
already implemented and ready for you to use. Its stand-alone libraries can be
used for cross-platform, database independent PHP projects.
</p>
<p>
eZ publish 3 is database, platform and browser independent. Because it is
browser based it can be used and updated from anywhere as long as you have
access to the Internet.
</p>


<h3>Licence</h3>
<p>
eZ publish is dual licensed. You can choose between the GNU GPL and the
eZ publish Professional Licence. The GNU GPL gives you the right to use, modify
and redistribute eZ publish under certain conditions. The GNU GPL licence is
distributed with the software, see the file LICENCE. It is also available at
http://www.gnu.org/licenses/gpl.txt
Using eZ publish under the terms of the GNU GPL is free of charge.
</p>
<p>
The eZ publish Professional Licence gives you the right to use the source code
for making your own commercial software. It allows you full protection of your
work made with eZ publish. You may re-brand, license and close your source
code. eZ publish is not free of charge when used under the terms of the
Professional Licence. For pricing and ordering, please contact us at
info@ez.no.
</p>

<h3>eZ publish features</h3>
<ul>
<li>User defined content classes and objects</li>
<li>Advanced search engine</li>
<li>Role based permissions system</li>
<li> Advanced template engine</li>
<li> Version control</li>
<li> Professional workflow management</li>
<li> Multi-lingual support</li>
<li> Support for Unicode</li>
<li> Task system for easy collaboration</li>
<li> Image conversion and scaling</li>
<li> Database abstraction layer</li>
<li> XML handling and parsing library</li>
<li> SOAP communication library</li>
<li> Localisation and internationalisation libraries</li>
<li> Several other reusable libraries</li>
<li> SDK (software development kit)
  and full documentation</li>
</ul>

<p>
It is released under the <a href=\"http://www.gnu.org/copyleft/gpl.html\">GPL license</a> and can be downloaded from <a href=\"http://ez.no/developer\">ez.no/developer</a>.
You can get commercial support from <a href=\"http://ez.no\">eZ systems</a> at <a href=\"http://ez.no\">ez.no</a>.
</p>";

$Result = array();
$Result['content'] = $text;
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/ezinfo', 'Info' ) ),
                         array( 'url' => false,
                                'text' => ezi18n( 'kernel/ezinfo', 'About' ) ) );

?>
