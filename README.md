# Yale University Funded Grant Database

Requires two REDCap projects, with project-ids configurable in config.php. Data dictionaries for two REDCap projects exist in the data_dictionary/ directory. This directory should be placed in REDCap's plugins/ directory. (We have not yet converted this to an External Module.)

README.docx is present only for historical purposes. It is not up-to-date and describes an older version of the system.

A composer.json file is included, and a small number of composer tools is required for the full operation.

## Installation

1. Download the latest release zip/tar and unpack somplace
1. Copy the entire directory into the `plugins` directory of the REDCap server
1. Run `composer install` inside the `funded_grant_database` directory
1. Grab the two data dictionaries from the `data_dictionary` folder
1. Create two projects in REDCap (one for <ins>grants</ins>, one for <ins>users</ins>), and import the appropriate data dictionary
1. Edit `config.php` with the PIDs for the two projects you created
1. The database should now be accessible at ***\<your redcap url\>*/plugins/funded_grant_database/grants.php**

## Usage

1. Access to the database can be granted using the *users* project 
1. Grants can be added using the *grants* project