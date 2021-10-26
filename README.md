# Custom-fields plugin for Kimai 2

A Kimai plugin, which allows configuring additional fields for timesheets, customers, projects, activities and expenses.

You can test it in the ["Plugins" demo](https://www.kimai.org/demo/).

## Features

Configure additional fields for the following entities:

- `Timesheets`
- `Customers`
- `Projects`
- `Activities`
- `User`
- `Expenses` - see [Expenses Plugin](https://www.kimai.org/store/expenses-bundle.html)

The custom fields will be shown on the "create and edit entity" forms and can have the following types:

- `string` (simple text field)
- `integer` (number without decimal point)
- `number` (number with decimal places)
- `duration`
- `money`
- `language` (dropdown of languages, shown in the users language)
- `currency` (dropdown of currencies, shown in the users language)
- `country` (dropdown of countries, shown in the users language)
- `color` (browser specific input element to select a color)
- `date`
- `datetime`
- `email`
- `url` (url will be linked in listings and detail pages)
- `textarea` (multi-line text field)
- `invoice template` (dropdown)
- `checkbox` (on/off)
- `choice-list` (drop-down)

## Documentation

You can create as many fields as you want for each data type, where each field:

- is either optional or mandatory
- has its own visibility, so the access can be restricted:
    - to certain customer/project/activity combinations (eg. a "location" field will only be shown for customer X and project Y)
    - to users with certain permissions or roles
- can be described with a name and help text
- has a maximum length of 255 character

The custom-field data is then available in:

- Data-tables will display all visible fields
- Exports (HTML and Spreadsheets include all visible fields)
- Timesheet exports (include visible timesheet fields)
- API (collections and entities)
- Invoice templates (custom templates have access to all fields)

You can change the "weight" of custom-fields, so they show up in the order you define. 

Be aware:

- Restricted fields won't be visible on the create forms, as Kimai initially can't know if the rule will apply: in these cases the fields will only be shown in the edit forms
- Sensitive data can be configured as "invisible", so it will not show up in the above mentioned places
- Custom fields for users are not exported via the API (this is a limitation in the core application) 

### Field types

#### Checkbox

Use the value `1` as default value for a pre-checked box or `0` for an unchecked box.

If a checkbox is marked as mandatory, the user has to check it in order to submit the form.

#### Choice-list 

"Choice-list" is a different word for "Select-box" or "Drop-down". 
You have to add the entries as comma separated list into the default-value field.
For example a list consisting of fruits would look like this: `Banana,Apple,Orange,Pineapple,Peach`.

As the first entry is pre-selected, you can add an empty field to the dropdown by starting the list 
with a leading `,` like this: `,Banana,Apple,Orange,Pineapple,Peach`. 
Combined with the mandatory flag, this will force your users to select an entry from the list to be able to submit the form.

Choice list is also capable to configure title and values independently.
Let's assume you have non-human friendly IDs for the value, but want to show a human friendly text, you can separate 
each value from its title by using a pipe `|` character: `,01|Banana,02|Apple,Orange,foo|Pineapple,0815|Peach`.

#### Invoice template

A select box that is useful if you want to generate automatic invoices via command line / cronjobs.

The Kimai command `bin/console kimai:invoice:create` supports invoice templates via custom-field ([see docs](https://www.kimai.org/documentation/invoices.html#create-invoices-with-cronjobs)).
The option parameter `--template-meta` takes the internal name of the custom field that will identify the invoice template to be used.

## Installation

This plugin is compatible with the following Kimai releases:

| Bundle version    | Minimum Kimai 2 version   |
| ---               |---                        |
| 1.17              | 1.14                      |
| 1.15 - 1.16       | 1.11                      |
| 1.14              | 1.10.2                    |
| 1.10 - 1.13       | 1.9                       |
| 1.8 - 1.9         | 1.7                       |
| 1.6 - 1.7         | 1.6.2                     |
| 1.5               | 1.6                       |
| 1.3.2 - 1.4.1     | 1.4                       |
| 1.1.1 - 1.2       | 1.1                       |
| 1.0               | 1.0                       |

### Copy files

Extract the ZIP file and upload the included directory and all files to your Kimai installation to the new directory:  
`var/plugins/MetaFieldsBundle/`

The file structure needs to like like this afterwards:

```bash
var/plugins/
├── MetaFieldsBundle
│   ├── MetaFieldsBundle.php
|   └ ... more files and directories follow here ... 
```

### Clear cache

After uploading the files, Kimai needs to know about the new plugin. It will be found, once the cache was re-built:

```bash
cd kimai2/
bin/console kimai:reload --env=prod
```

### Create database

Run the following command:

```bash
bin/console kimai:bundle:metafields:install
```

This will install all required databases.

### First test

When logged in as `SUPER_ADMIN`, you should now see the custom-fields administration screen.

If this was successful, you can now think about giving permissions to other users as well.

## Permissions

This bundle introduces new permissions, which limit access to certain functions:

| Permission Name           | Description |
|---                        |--- |
| `configure_meta_fields`   | allows to administrate the custom field definitions |

By default, these are assigned to each user with the role `ROLE_SUPER_ADMIN`.

**Please adjust all permission settings in your administration.** 

## Updating the plugin

Updating the bundle works the same way as the installation does. 

- Delete the directory `var/plugins/MetaFieldsBundle/` (to remove deleted files)
- Execute all installation steps again:
  - Copy files
  - Clear cache
  - Update database with `bin/console kimai:bundle:metafields:install` 

## Screenshot

Screenshots are available [in the store page](https://www.kimai.org/store/custom-fields-bundle.html).

## Uninstall

- Delete the extension directory `var/plugins/MetaFieldsBundle/`
- Create a database backup
- Remove the database tables if you don't want to keep the data:
```sql
DROP TABLE kimai2_meta_field_rules;
DROP TABLE bundle_migration_metafields;
```
- [Reload your cache](https://www.kimai.org/documentation/configurations.html) with the cache command

Be aware: the stored meta fields and their values are still available in your Kimai database!

## Dump SQL for manual installation

```
bin/console doctrine:migrations:migrate --configuration=var/plugins/MetaFieldsBundle/Migrations/metafields.yaml --write-sql
```
