File (Field) Paths
==================

The File (Field) Paths module extends the default functionality of Drupal's core
File module, Image module and many other File upload modules, by adding the
ability to use entity based tokens in destination paths and filenames.

In simple terms, File (Field) Paths allows you to automatically sort and rename
your uploaded files using token based replacement patterns to maintain a nice
clean filesystem.

Features
--------

* Configurable file paths now use entity tokens in addition to user tokens.
* Configurable filenames.
* Support for:
  * Drupal core File module.
  * Drupal core Image module.
  * Contrib modules that provide a File based field.
* File path and filename cleanup options:
  * Basic built-in cleaning to reduce to alphanumeric characters and hyphens.
  * More advanced cleaning using the Pathauto module.
  * Convert unicode characters into US-ASCII with core Transliteration.
  * Extended transliteration using the contributed Transliteration module. (@TODO Not done)
* Automatically updates unprocessed file paths in any Text fields on the entity. (@TODO Not done)
* Active updates (OPTIONAL) - rename and/or move previously uploaded files 
  whenever the containing entity is saved. 
* Retroactive updates (OPTIONAL) - rename and/or move previously uploaded files 
  in bulk. (@TODO Not done)

Recommended Modules
-------------------

* [Token](http://drupal.org/project/token)
* [Pathauto](http://drupal.org/project/pathauto)
* [Transliteration](http://drupal.org/project/transliteration)


Usage/Configuration
-------------------

Once installed, File (Field) Paths needs to be configured for each file field
you wish to use. Module settings are found on the the settings form for any file based field.

  *Example:*
  
    Administration > Structure > Content types > Article > Manage fields > Image
    http://example.com/admin/structure/types/manage/article/fields/node.article.field_image

* Enable File (Field) Paths? is the master switch that controls whether or not
  FFP is active for this field.
* File path. Enter the desired path here using tokens and/or regular text.
* File path options. 
    * Clean up filepath will use Pathauto if enabled or basic built-in cleaning if
      it is not.
    * Clean up using Transliteration will use the contrib Transliteration module 
      if it is installed or the core transliteration service if it is not.
* File name. Enter the desired name here using tokens and/or regular text.
    * Clean up filename will use Pathauto if enabled or basic built-in cleaning if
      it is not.
    * Clean up using Transliteration will use the contrib Transliteration module
      if it is installed or the core transliteration service if it is not.
* Retroactive update. This will move/rename files for this field on all existing
  entities when the settings are saved. Use this with caution!
* Active updating. This will cause FFP to move/rename files every time the
  containing entity (ie: node, term, user) is saved rather than only the first
  time the file is uploaded. Use caution as it can break existing links.

Frequently Asked Questions
--------------------------

Q. Aren't tokens already supported in the File module?

A. A limited selection of tokens are supported in the File module.

   Entity based tokens allow you to use the Entity ID, Title, creation date and
   much more in your directory/filenames where you would otherwise be unable.


Q. Why aren't my files in the correct folder?

A. When you are creating or updating an entity the full values for the tokens
   may not yet be known by Drupal, so the File (Field) Paths module will upload
   your files to the Fields old file path temporarily and then once you save the
   entity and Drupal is provided with the tokens values the file will be moved
   to the appropriate location.


Q. Why is there a warning on the 'Retroactive updates' feature?

A. Retroactive updates will go through every single entity of the particular
   bundle and move and/or rename the files.

   While there have been no reports of errors caused by the feature, it is quite
   possible that the moving/renaming of these files could break links. It is
   strongly advised that you only use this functionality on your developmental
   servers so that you can make sure not to introduce any linking issues.

History and Maintainers
-----------------------

File (Field) Paths was written and is maintained by Stuart Clark (deciphered).

* http://stuar.tc/lark
* http://twitter.com/Decipher

