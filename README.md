# Funded Grant Database - REDCap External Module

## About

The Funded Grant Database is designed to facilitate the collection and management of information about funded grants at an institution. It also allows the administrator of the database to control access to the grant materials and track who has viewed/downloaded which grants.

The interface provides robust searching/filtering.

The module additionally requires two REDCap projects to support it. The data dictionaries [here](https://github.com/AndrewPoppe/funded_grant_database/tree/main/data_dictionary) can be used to create those projects. The project IDs for those projects (as well as several aesthetic options) should be configured in the external modules section of the control center.

This is a system external module, but enabling the module in a project will add a link to the database in the project's left-side menu.

## Usage

1. Access to the database can be granted using the *users* project 
1. Grants can be added using the *grants* project
1. The database will be accessible at ***\<your redcap url\>*/ExternalModules/?prefix=funded_grant_database&page=src/grants**
1. Use REDCap's built-in url shortener to give this path a more friendly URL if you like

## Attribution

This EM is based on a plugin originally created at Vanderbilt by Scott Pearson, Jon Scherdin, and Rebecca Helton (email: datacore at vumc.org). 