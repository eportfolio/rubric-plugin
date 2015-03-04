Rubric-plugin
=============

Rubric plugin for Mahara

This is a plugin for implementing Rubrics in Mahara.

RELEASE NOTE
=============
Rubric plugin version 1.0 is available.
This plugin is provided as artefact and blocktype.

Installation
=============
1. Unzip the file into the $MAHARA_HOME/artefact directory
2. Rename the directory from rubric-plugins-xxxxx to rubric
3. [IMPOTANT!] Change ownership
e.g. If httpd is running as user 'apache', please change ownership of rubric directory.
   # chown -R apache:apache rubric/
3. Go to Mahara->administration->Extentions
4. Install the artefact rubric-plugin
5. Install the blocktype rubric-plugin

You can try a sample of template. Open 'Content>Rubric>Manage template' and import template_sample.csv file.
