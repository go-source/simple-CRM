# Instructions

Package requires both component zipped files and FOF3x zipped, plus the package xml and installation script.

## Prepare the component

It is a regular component, just pack according to Joomla standards.

At this stage we have not setup the update server and version check so, re-installing the component does not change database tables.

## Package

Simple CRM requires FOF3 https://github.com/akeeba/fof, that needs to be included in the installation package.

Download the production-stable version of FOF from https://www.akeebabackup.com/download.html

Zip or rename to lib_fof30.zip (because this is the name used in the package install script).

Add these files to and zip all together:
- com_gscrm.zip
- lib_fof30.zip
- LICENSE.txt
- pkg_gscrm.xml
- script.gscrm.php
