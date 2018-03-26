# Database-XH
This CMSimple-XH plugin can integrate data from a MySQL database. Although CMSimple-XH is designed to use plain files, in some cases it is usefull to have the possibility of accessing a database. The plugin can manage the MySQL connection, can display data using simple templates and can also edit the data of the tables.

The plugin is under developement and functionality will increase.

## Backend
In the backend the administrator primary can set up the basic informations to connect the MySQL server. The stored queries and templates can be edited, as well as the languge file.

## Display database data
To display the content of a MySQL table a simple plugin call has to be placed on a page. Three parameters can be defined, a query, a template and an optional filter string:

{{{database("query", "template" [, "filter"]);}}}

### Query
The query string can be a valid MySQL query or the name of a predefined query file. The query files can be created and edited in the backend. Without a template or filter parameter, the query is interpreted as a command, like 'edit'.

### Template
The template parameter holds the name of a predefinied template. The templates can be created and edited in the backend. The simple HTML files control, how the database data will be displayed on the page. In the HTML structure different tags will control the data usage.

#### Field place holder
The name of a database field enclosed in curved brackets will be replaced by the content of the corresponding field from the database.

Database field 'name'='Thomas'
{name} will be replaced by Thomas

Different functions can be added to the field name, that can alter the content (ToDo change to plugin system):

* {date:fieldName} The MySQL ISO date is converted to a human readable format (ToDo: set format in configuration)
* {notnull:fieldName} If the field exists but is empty, an 'optional display' (see below) will be removed

If a field don't exist, the field placeholder is removed.

#### Iterate
A basic function is to iterate through records. The section of the template, which should be repeated, using successive records, have to be enclosed by the tags [startList] and [endList]. These tags can be placed at any place in the template, but the HTML code inside has to produce valid code. During the plugin call the part before and after the list tags are displayed one time, while the inner section is repeated as many times as records are found.

#### Optional display
With the 'optional display' function a HTML section can be omitted, if all record fields, that are enclosed, are empty. So a line of a list will not be displayed, if there is no data to be shown. If the 'optional display' is not used, the HTML code is rendered with no data. The HTML section, which should be omitted, has to be enclosed by square brackets:

<div>Name: {name}</div>
[<div>Address: {address}</div>]

The first div will be rendered, even if the name field is empty, the second div will not be shown if the address field is empty. The 'optional display' areas can NOT be nested.

## Editing
Editing is in a very early state of developement and certainly will change a lot. The editing of database content will be possible in any plugin call, when the fields are marked to be editable. The following function are planned:

* Login for editing (ToDo)
* Define fields in the template, that should be editable
* Change content by double click
* Offer selector boxes for related fields (ToDo)
* Create new entries for related fields (ToDo)