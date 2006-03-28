Improved multi-language in eZ publish
=====================================

:Author: Jan Borsodi, Jan Kudlicka
:Revision: 4

The current implementation (3.7) of multi-language support in eZ publish has
some shortcomings. The improvements to the system are described in the
following sections, they describe the problem and provides a way to solve it,
some even has a suggested technical solution.

This document replaces the `old multi-language specification`_.

New concepts
````````````

Problem:
  The current concept keeps all languages inside one version and will allow
  only one person to modify their data. This makes it impossible for multiple
  persons to edit each language separately and at the same time, instead they
  will have to wait for one language to be updated and published.

To remedy this the languages will be made more self-contained and
independent. This allows languages to be created and edited separately by
multiple users, it also simplifies things like approval since you can consider
one language only.

The major changes to content model are:

- A user only edits one version and language at a time. UI changes will be made
  to make it possible to edit multiple languages of an object.
- Objects can be created in any language on any site-access, the first language
  of the object will be recorded.
- Objects can be translated to as many languages as necessary.
- Available languages can be controlled per site-access, allowing languages to
  be filtered.

See attachment for `an overview`_ of the tables, new tables are highlighted in blue.

.. _an overview: datamodel.png

Global translations
-------------------

A new table (*ezcontent_language*) is introduced which replaces the
old table (*ezcontent_translation*).
Each entry in the table gets a new *id* which is a representation of
the bitnumber as a value. ie. 2^bitnum. Id 1 (2^0) is reserved by
the system.
The language is stored with a locale code (e.g. nor-NO) and a name, in
addition it also has the field *disabled* which can be used to disable
a language on the site.

The maximum number of languages is 31 (or 63?).

The global table looks like the following::

  ezcontent_language
  +----+----------+--------+--------------------------+
  | id | disabled | locale | name                     |
  +----+----------+--------+--------------------------+
  |  2 |        0 | eng-GB | English (United Kingdom) |
  |  4 |        0 | rus-RU | Russian                  |
  |  8 |        0 | slk-SK | Slovak                   |
  | 16 |        0 | eng-US | English (American)       |
  | 32 |        0 | nor-NO | Norwegian (Bokmal)       |
  +----+----------+--------+--------------------------+


Initial language
----------------

Whenever an object is created for the first time it will store the language the
object was created in. This allows the system to find one language on the
object when none of the others can be used (e.g. filtered away). The value will
be stored in the field *initial_language_id* using the *language_id* from
the *ezcontent_language* table.

The new field will look like::

  ezcontentobject
  +-----+----------------------+
  | id  | initial_language_id  |
  +-----+----------------------+
  |   1 |                    2 | (eng-GB)
  |   2 |                    2 | (eng-GB)
  |   5 |                    2 | (eng-GB)
  |  10 |                    2 | (eng-GB)
  |  42 |                   16 | (eng-US)
  |  44 |                    4 | (rus-RU)
  +-----+----------------------+

Available languages
-------------------

Problem:
  There is currently no record of what languages are available on an object,
  unless one accesses the attributes.

To make it easier to figure out which languages are available on an object a
new attribute is added which returns the languages as an array of
locale codes.

The new attribute is called *available_languages* and will use the
*language_mask* field combined with the global language table to
figure out the correct codes.

The result of this attribute is shown below::

  Object  1: array( 'eng-GB' )
  Object  2: array( 'eng-GB', 'slk-SK' )
  Object  5: array( 'eng-GB', 'rus-RU' )
  Object 10: array( 'eng-GB' )
  Object 42: array( 'eng-US' )
  Object 44: array( 'rus-RU' )

Language priority and filtering
-------------------------------

Problem:
  The current model did not have a proper prioritization of the available
  languages. Often you will want to limit the languages shown for a site-access
  and also choose which is the preferred.

With the new concepts is no longer directly known which languages to use per
object, instead this must be calculated based on the requested languages in
prioritized order and the available languages (in object).

The requested languages might be different per site-access so the solution must
be fast and scalable.

A language priority list is basically a list of languages to show and the first
language will get higher priority than the second and so on...

Filtering languages
~~~~~~~~~~~~~~~~~~~

To perform proper language filtering and prioritization a new bitfield
algorithm is introduced. All languages used in an eZ publish instance
are identified by powers of 2 to be able to use bitmap *and* for selecting
rows containing information on objects or attributes in prioritized languages.
Id 1 (2^0) is reserved for marking objects which are always available.

To show how the bitfield algorithm works, let's have a look at the
ezcontentobject table. A new field is introduced to identify languages in which
last published version of an object exist. This field contains the sum of id
values of these languages. Because id values are powers of 2 it is possible to
identify the languages and/or to select objects published in any of prioritized
languages::

  ezcontentobject
  +-----+---------------+
  | id  | language_mask |
  +-----+---------------+
  |   1 |             3 | %00011
  |   2 |            10 | %01010
  |   5 |             6 | %00110
  |  10 |             2 | %00010
  |  42 |            16 | %10000
  |  44 |             4 | %00100
  +-----+---------------+

Note that language_mask 3 in the first row denotes that the language is English
(id 2) and the object is always available (id 1), even if the prioritized list
does not contain English (eng-GB).

For example, to filter objects which exist in Slovak (id 8) or Russian (id 4),
we might run the following SQL::

  SELECT id FROM ezcontentobject
  WHERE language_mask & 12 > 0

Whenever the available languages change for the object (i.e. new version
published) it the bitfield is updated.

Another bitfield is used in tables containing the language_code attribute. This
bitfield represents the same information as the language_code but uses the id
from ezcontent_language table to make easy to select correct rows with respect
to the list of prioritized languages.

Consider the following example::

  ezcontentobject_attribute
  +----+---------+------------------+---------------+-------------+
  | id | version | contentobject_id | language_code | language_id |
  +----+---------+------------------+---------------+-------------+
  | 10 |       1 |                1 |        eng-GB |           3 |
  | 31 |       1 |                2 |        eng-GB |           2 |
  | 32 |       1 |                2 |        slk-SK |           8 |
  | 46 |       1 |                5 |        eng-GB |           2 |
  | 47 |       1 |                5 |        rus-RU |           4 |
  +----+---------+------------------+---------------+-------------+   

To select content object attributes in prioritized languages, we might use a
single SQL containing a condition using bitfields, bitmap *and* operation and
simple arithmetic operations (multiplication, division and sum).

Consider the following prioritized language list: slk-SK, eng-GB. To check if
the attribute is in slk-SK we can check if language_id & 8 is not zero etc.
If we perform the following operation::

  ( language_id & 8 ) / 2
  + language_id & 2
  + language_id & 1

we will get the bitfield value having 1 on 0th bit if the attribute is always
available (i.e. the object containing it have to be shown even if it is not in
any of prioritized languages), having 1 on 1st bit if the attribute is
available in eng-GB and having 1 on 2nd bit if the attribute is available in
slk-SK.

To find out if the attribute is in one of the prioritized languages and that
there is no translation of this attribute in more prioritized language, we
select those which hold the following condition::

  ( (     language_mask - language_id ) & 8 ) / 2 
      + ( language_mask - language_id ) & 2 
      + ( language_mask - language_id ) & 1
  < ( language_id & 8 ) / 2
    + language_id & 2
    + language_id & 1

where *language_mask* is the ezcontentobject attribute containing the bitfield of
available languages.

Names and path
~~~~~~~~~~~~~~

The name and (translated) path will no longer be fetched in the main SQL but
done as a second SQL, this makes the initial subtree SQL faster.

All languages
~~~~~~~~~~~~~

To solve the issues with cronjobs and admin interfaces which must always list
all available languages we introduce a special code called *all-AL* which is
always available. When used the SQLs will include all objects even if it does
not have any of the other languages.

The first bit of the bitfield will be examined when this is enabled since it is
reserved for this task.

Translated attributes
---------------------

With the new database concepts it will be possible for multiple users editing
the same object but in different languages (and versions). This means that
storage of attributes needs to be changed since one version does not reflect
all languages anymore.

The system will remove the current_version fields from the DB and instead
introduce a *status* field on the attributes (same to version status) which is
used to fetch the published data. When content/edit stores the data it will
only do it for the language it edits.

In short it means that for one version of an object there will only be
attribute data for one language.

The table will then look like::

  ezcontentobject_attribute
  +----+------------------+---------+---------------+-------------+--------+
  | id | contentobject_id | version | language_code | language_id | status |
  +----+------------------+---------+---------------+-------------+--------+
  |  7 |                5 |       1 | eng-GB        |           2 |      2 |
  |  8 |                5 |       1 | eng-GB        |           2 |      2 |
  |  7 |                5 |       2 | rus-RU        |           4 |      1 |
  |  8 |                5 |       2 | rus-RU        |           4 |      1 |
  |  7 |                5 |       3 | eng-GB        |           2 |      1 |
  |  8 |                5 |       3 | eng-GB        |           2 |      1 |
  |  7 |                5 |       4 | slk-SK        |           8 |      0 |
  |  8 |                5 |       4 | slk-SK        |           8 |      0 |
  | 22 |               44 |       1 | eng-GB        |           2 |      1 |
  | 23 |               44 |       1 | eng-GB        |           2 |      1 |
  +----+------------------+---------+---------------+-------------+--------+

Node assignment
---------------

As with `Translated attributes`_ there are now issues with the node assignments as
well. For instance if two users edits the same object in different languages at
the same time they will each get a copy of the published node assignment, if
they both modify it there will be conflicts when publishing.

The avoid this conflict the system will no longer allowed locations to be
added, removed or moved from the admin interface. Any changes to locations will
have to be done from the admin interface (locations tab). To make it easier to
perform these tasks from the user site a new view is added which provides this
functionality.

To make sure it is still possible to hide and change sorting the first time an
object is published there will be some UI elements available for the first
version of an object.

The UI might look something like::

  +-Initial settings-----------------------------------------------+
  |                                                                |
  | Visibility: [ Visible ]  Sorting: [ Published ] [ Descending ] |
  |             [ Hidden  ]           [ Section   ] [ Ascending  ] |
  |                                   [ Name      ]                |
  |                                                                |
  +----------------------------------------------------------------+

Object relations
----------------

Currently all relations are now stored only per version and not per
language. This means that there will be possible conflicts when two languages
are edited at the same time.

Solution 1:

Make relations per language, then when a new translation is made the relations
are copied to this new language.

Solution 2:

When publishing a version/language make sure the relation list is merged
together with the previous published data. This means that removed relations
must be marked as removed and not just deleted from the database.

URL aliases
-----------

Problem:
  Currently URL aliases are only built for one of the languages (the main
  language). For instance if you have a French site where you only show french
  translations of the URLs it is desirable to show the french URL alias for the
  users. The translated URL will point to the same internal URL but will use
  language priority to choose what to show.

Instead of introducing a new table for this or changing the subtree table, the
existing URL alias table is extended. This table will now have a new field
called *language_code* which tells which language the url is made for. The
system will then create/update these entries for all languages of an object
when it is published.

The table will look like::

  ezurlalias
  +---------------------------+-------------+--------------------------------------+
  | destination_url           | language_id | path_id_string                       |
  +---------------------------+-------------+--------------------------------------+
  | content/view/full/2       |           2 |                                      |
  | content/view/full/13      |          32 | folder_1/pingvinen_har_en_megafon    |
  | content/view/full/13      |           2 | folder_1/the_penguin_has_a_megaphone |
  +---------------------------+-------------+--------------------------------------+

The PHP class eZContentObjectTreeNode will have this translated url-alias
available as a function attribute (*localized_path*), if it is null in the
object it will fetch it from the DB. To avoid having to perform lost of
repeated SQL calls for each node all fetches nodes should be associated with
some collection, then we can go over the nodes in the collection and collect
multiple node IDs and use that for the SQL. (The existing cache system might
also suffice).

The fetch calls should get the possibility to fetch the name and path
immediately, this means that if you know you will use the name and/or path it
can be fetched in one go (after the main SQL). A new parameter is added to the
fetch functions for this.

The upgrade script must fill this table with info from the tree-node table for
each missing language. The existing script *"updateniceurls.php"* will be
extended with this.

Search
------

Problem:
  When searching you will always search in all languages, not only do you get
  too many objects in the result it will also not distinguish words which are
  the same in many languages but with different meaning. e.g. the word *to*
  exists in both English and Norwegian but has a different meaning.

Add *language_code* on word table, this will separate words per language and
solve the issue when one word exists in two or more languages but with
different meaning. Another issues it solves is when you search in specific
language and the word you are looking for does not exist in this language but
in other ones, then there is no need to include that in the search (it could
even tell the user that).

An example on how it could look::

  ezsearch_word
  +------+--------------+-------+-------------+
  | id   | object_count | word  | language_id |
  +------+--------------+-------+-------------+
  |    1 |            5 | to    |           2 |
  |    2 |            2 | to    |          32 |
  |    3 |            1 | kaker |          32 |
  |    4 |            1 | go    |           2 |
  +------+--------------+-------+-------------+

Ignoring translation
--------------------

Problem:
  Some objects will need to always be available no matter which site-access is
  used. For instance user objects will need to be fetched even if they are not
  translated to the current language.

The current solution already has this class attributes but must be extended
further.

Class
~~~~~

Add a new field called *always_available* (default 0) to *class* table which
defines if objects of this class are always available. If this is set to 1 any
object created from it will always be available.
One of the major uses for this is for classes which needs to be always
available, for instance users or user groups.

When creating objects the objects will copy the current setting in the
class, any changes to the setting in the class will not affect existing
objects. This allows this setting to be switched per object at a later time.

By default eZ publish will come with this setting turned on for Folders, Users
and User Groups. In addition the system will make some objects always
available, meaning you cannot turn it off, currently this will be top-level
objects (Content, Users, Media) but may be extended in the future.

An extensible API will be made to support the *always available* switch, this
will allow extensions to determine which extra objects to marks as always
available.

Attributes
~~~~~~~~~~

Add a new field called *ignore_language* (default 0) to *object attribute*
table which can be used when an attribute should not be translated (i.e. chosen
in class). This means that datatypes no longer have to copy the values when
translation is disabled.

This attribute will only be editable when the user edits the language which the
object was initially made in.

Image datatype
++++++++++++++

The image datatype will need to be examined due to this change. Some internal
changes to the storage format (XML) and file storage might need to change.

Objects
~~~~~~~

When an object is always available the first bit (bit 0) will be set to 1. This
means it will be fetched no matter which priority list is used. It also means
that the object can still be translated. For objects which has none of the
available languages it will pick the initial language.

Workflows
`````````

Each version of an object can now only contain language, this means that there
is no need to change the workflow system or calls to the 'publish'
process. Instead the workflow events *approval* and *multiplexer* will get
support for filtering on language, they will store a language mask in the
*data_int2* DB field to choose which language to match on, a value of 0 means
any language.

Permissions
```````````

Problem:
  Now any user can create translations as long has he can create/edit the
  object. Being able to restrict access based on language is very useful since
  a translator might not be allowed to modify the original content. Also a
  translator currently needs create access while she is only going to extended
  an object which is already created.

One must be able to choose languages per access function. This means new policy
values for content/create and content/edit are required in order to define
languages.

- content/create : specify if user is allowed to create new object in specified
  language. 
- content/edit : specify if user has got access to edit the specified
  language(s) and create new translation for specified language(s).
- content/translate : specify if user has got access to translate intro the
  specified language(s). This is checked in addition to the *read* and *edit*
  permissions.

To translate you need *content/read* and *content/edit*, or just
*content/translate*, for the language to translate into, this means users can
translate objects without having *content/create* rights.

Discuss:
  Is there a need for language limitation on content/read. If the user cannot
  read it in one language then he can still read it in others. What is the
  purpose of having this?

View-cache
``````````

With the new multilanguage feature a view-cache is no longer made for one
specific language but for a prioritized list of languages, this means that the
directory storage must change. We replace the singular language code and
sitesdesign with the name of the site-access  e.g. if we have the languages
*nor-NO*, *eng-GB* and *ger-DE* in the site-access *no* we get::

  var/cache/no/full/

We also optimize the cleaning code to use the *glob* call with *GLOB_BRACE*
expansion, e.g::

  glob( "var/cache/{admin,mysite}/{full,line}/1/5/15-*.cache",
        GLOB_BRACE );

Doing this change will mean that we need to use AvailableSiteAccessList
(*site.ini*) when cleaning up caches. All of these changes can optimize the
::cleanup() method of the viewcache considerably while also giving proper
multi-language support.

Trash
`````

Currently objects are restored from trash by going to content/edit and then
getting a new version. This will not work very well with the new system where
there is only one language per version.

A new view in the content module will be made to handle restoration of trash
objects (content/restore). It will allow the user to restore to old location
or browser for a new location.

Cronjobs
````````

Cronjobs must be run with all languages enabled to ensure that they can reach
any object. The cronjob system will set the *all-AL* code before starting the
cronjobs.


Configurability
```````````````

Currently the system will use the site-access settings to determine which
languages to fetch. However sometimes it is crucial to able to override this
per session or function call. This means that most fetches should have an
ability to choose the priority list.

Also a global variable must be set by index.php and cronjob.php which is used
by the system. The variable is set with the value from the site-access but can
be overridden by PHP code. e.g.::

  update with correct INI names.
  $languages = $ini->variable( 'Language', 'PriorityList' );
  $GLOBALS['eZLanguagePriorityList'] = $languages;

Searching
---------

When searching it must be possible to override the default language of the
site-access. This can be used to narrow down the languages or to fetch objects
in languages normally not accessible.

The language must be able to be specified in two forms:

- Using a parameter to the search template fetch function.
- Using a GET/POST parameter from an HTML form.

The languages which can be chosen must be one of the languages defined in the
site-access.

Fetching node/object lists
--------------------------

When fetching list (template fetch function) of nodes or objects it must be
possible to override the default language of the site-access. This can be used
to narrow down the languages or to fetch objects in languages normally not
accessible. The fetch function will get a parameter called *language_list*
which is an array of languages to act as the priority list (instead of the one
defined in site-access).

The existing parameters *only_translated* and *language* to the fetch functions
*content/list*, *content/tree*, *content/list_count* and *content/tree_count*
will be deprecated since they are not valid anymore. If they are used the
system will give a warning about it.

The treemenu() operator will also get the *language_list* parameter.

Clear API
`````````

Fetching the current language to use for one page request should be done from a
clean and simple API. It must be able to perform:

- Fetching the language settings for the site-access.
- Fetching the language settings for the page request (uses the global
  priority variable)

Site preferences
----------------

A new setting is added which controls what is the default language for the
site. This is used to determine the default choice in dropdowns or when
creating objects (when language has not been chosen). In addition each
site-access must be able to set the language priority list.

eZContentObject::defaultLanguage()
----------------------------------

This should be changed to return the languages based on the priority list.
Check the usage of this function, sometimes it might be that they want to use
*initial_language* for the object, e.g. for creating objects without having a
language specified.

User interface
``````````````

The user interface will need to updated because of the enhanced multilanguage
features. The main idea is to make it easier for the users to manage
translations.

List languages in draft list
----------------------------

Each language must show up as a separate entry in the draft list. The list will
pick the *edit_language* for the version. The user will not notice this
technical detail unless he looks at the URLs.

Creating and editing
--------------------

When creating a new object it should be possible to choose which language it
should be made from, for instance using a drop-down list. The default selection
should be the highest prioritized language of the site-access.

If the language was not specified in the url/variables (e.g. content/edit/5/5)
the system should go to a new page giving the user a choice over which language
to create in, this also has the default selected.

Editing should be similar as the create operation but with a dropdown (either
combobox or JS popup).

See attachment for a `mockup of the UI`_.

.. _mockup of the UI: edit_mockup.png

Editing multiple languages
--------------------------

Problem:
  Since you are only able to edit one object at a time it will be cumbersome
  for one person to make objects in multiple languages, e.g. the user might be
  fluent in two languages and can do the translation himself.

To ease the translation process it will still be able to edit two or more
translations (UI wise) at the same time, however the process will change from
earlier. Internally the system actually edits two versions of the same object
but the user should not be able to spot unless he examines the URLs.

Now the system will display all possible translations in the edit
interface, if a draft is available for the specific language the user will be
able to switch to it (thus storing the current language) quickly. If another
users owns the draft it will not be editable but the user will see who made
it. If a language has not yet been translated to it will be displayed as
inactive, creating the specific translation is also a simple operation.

When the user publishes one translation the system will automatically publish
any other draft of the same object by the current user.

Conflicts
~~~~~~~~~

A typical conflict is if two people simultaneously edits the same
language for the same object. A scenario might be:

1. User *John* creates article in language *nor-NO* and publishes it. (version
   1)
2. User *Michael* translates it into language *ger-DE* and
   publishes it. (version 2)
3. User *Michael* edits language *ger-DE* again and works on it for a
   while, then stores the draft and continues with other tasks. (version 3)
4. Meanwhile user *Ivanova* edits the language *ger-DE* (from the last
   published data i.e. version 2) and then publishes the new data. (version 4)
5. User *Michael* gets back to the object and continues *ger-DE* and
   publishes it. (version 3)

Now the object will contain the last published data for *ger-DE* which
was made by *Michael* (version 3), the changes by *John* has been forgotten.

To solve this issue there will be made some addition checks in the
system for these conflicts and give the user the possibility to
resolve them. This is similar to version check we have today in eZ
publish but will be a bit smart and will eventually replace it.

Problem 1 - Translating or editing a language which already has a
draft by you.
This one is easy, this means that you should simply continue the draft
from where it was. The user should not be bothered with warnings or
dialogs in this case.


Problem 2 - Translating or editing a language which already has a
draft by another user.
The second user (in time) should be informed that the language is
already being edited. The user should be presented with some possible
actions before continuing, the actions are:

1. Copy the last published version and edit that.
2. Copy the draft and edit that.
3. Forget about the editing.

While choosing the other draft should be displayed on the page.

For #1 and #2 the system should also inform the first user that
someone else is editing the same language, e.g. by sending an
email. Also the other draft is marked with a special status.

When the first user returns to the edit the system should first
display a warning page and inform that another user has made a copy
and is currently editing it. The user must be given some possible
actions, they are:

1. Continue editing.
2. Copy data from other draft.
3. Discard draft.

While choosing the other draft should be displayed on the page.


Context menus
-------------

The JS popup menus must get two extra entries, one for editing the object in a
given language and one for translating it to a language.

Can the two be merged together, need to decide during UI design phase.

Translating
-----------

When *Detailed* viewmode is enabled in the children listing a new translate
button must be made available. If clicked it pops up a menu allowing
translation to the globally available languages, if Javascript is not available
clicking it goes to a new module-view which allows the user to pick a language.

Extra considerations
````````````````````

While the ML features are implemented there are some considerations which needs
to be examined. They are explained here.

External patches
----------------

These external patches must be considered while implementing the ML features.

Temporary drafts
~~~~~~~~~~~~~~~~

http://pubsvn.ez.no/community/trunk/hacks/untoucheddrafts/patches/3.7.2/untoucheddrafts.patch

This might need some changes due to the new content/edit system with proper
language support.


Other issues
````````````

There are some additional issues with multi-lingual content which will not be
covered by this implementation. These issues are explained shortly with a
reason why it is not implemented.

Choosing translation per node
-----------------------------

Description:
The user should be able to choose which translation to use per placement. This
means that the priority list would change depending on which node is chosen,
e.g. the norwegian article is only shown in the norwegian subtree while the
english is shown in the english subtree.

Result:
This is a highly complicated techincal issue which cannot be solved without
major changes to the tree structure.


.. _old multi-language specification: http://ez.no/community/developer/specs/improved_content_multilangue_support


updates:
ALTER TABLE ezcontentobject ADD column initial_language_id int not null;


..
   Local Variables:
   mode: rst
   fill-column: 79
   End:
   vim: et syn=rst tw=79
