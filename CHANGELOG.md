# Changelog

## Version 1.17

Compatibility: requires Kimai 1.14

- Added: column toggle dialog
- Added: url field
- Added: improved form layout and usability
- Added: translations for form
- Fixed: deprecation warnings about controller config
- Fixed: better internal API (technical debt)
- Fixed: 500 error when saving empty label
- Fixed: custom fields for expenses being displayed even with deactivated expense bundle

## Version 1.16

Compatibility: requires Kimai 1.11

- Added: define key and title independently for choice lists 
- Fixed: allow longer default values (eg. for long choice-lists)

## Version 1.15

Compatibility: requires Kimai 1.11

- Added: Using new core installer to prevent not-found migrations
- Added: Default visibility changed to `true`
- Fixed: Composer 2 compatibility

## Version 1.14

Compatibility: requires Kimai 1.10.2

- Added sorting of custom-fields (only works reliable with Kimai 1.10.2)
- Configure "section name" for user-preferences (to separate groups of preferences)
- Improved responsiveness for small screens
- Added help link

## Version 1.13

Compatibility: requires Kimai 1.9

- Fixed translation in overview
- Updated documentation

## Version 1.12

Compatibility: requires Kimai 1.9

- Added API to fetch available meta fields (eg. to support apps)
- Allow to use meta-fields in subclass of base entities (for devs only)
- Fix validation bug when updating user meta field #48 

## Version 1.11

Compatibility: requires Kimai 1.9

- Support custom-fields with expenses
- Added phpstan for static code analysis (internal: no user feature)
- Fixed directory separator for installer on Windows
- Disallow to change field-type after it was created (changing the type causes bugs if the field was already used)

## Version 1.10

Compatibility: requires Kimai 1.9

- Allow digits in internal name
- Allow invoice template as meta-field (mainly to be used with projects or customers)
- Bugfix: prevent creation of user-preferences with already existing names
- Bugfix: allow empty help text

## Version 1.9

Compatibility: requires Kimai 1.7

- Added "email" type
- Added "textarea" type
- Bugfix: protected fields which were saved, became visible to all users 

## Version 1.8

Compatibility: requires Kimai 1.7

- Added own permission section for "user roles & permission screen"
- Force strict rules on internal field name: allowed are lower case character and underscore
- Fix reload bug for first time users

## 1.7

Compatibility: requires Kimai 1.6.2

- New config, which allows to add a permission/user-role to limit access to a custom field for certain users

## 1.6

Compatibility: requires Kimai 1.6.2

- Allow to order user preferences

## 1.5

Compatibility: requires Kimai 1.6

- Improve permission handling (auto register for ROLE_SUPER_ADMIN, as preparation for Kimai 1.6)

## 1.4.1

Compatibility: requires Kimai 1.4

- Fix compatibility with Kimai 1.4

## 1.4

Compatibility: requires Kimai 1.6 (by accident)

- Fix editing checkbox (boolean) fields
- Fix default values for custom fields
- Allow optional user preferences
- Format date/datetime and boolean values in admin list
- Fix problems with invalid date and datetime default values

## 1.3

Compatibility: requires Kimai 1.4

- Support for showing all visible fields and user preferences
- Support for exporting all visible fields and user preferences
- Support to set a label for each field
- Added support for help labels
- Added new database columns (run the update: `bin/console kimai:bundle:metafields:install`)

## 1.2

- Allow date and datetime as input fields
- Added installer command (+ migration support)

## 1.1.1

- Support default value for user-preferences

## 1.1

- Support for adding user preferences

## 1.0

- Initial release
