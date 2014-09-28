Pivotx-To-Wordpress
===================

Pivotx to Wordpress WXR  (P2W)

PivotX is an blog framework and when migrating to Wordpress a little conversion should be done. 
Best is to get the PivotX content from the database itself and create a WXR (Wordpress extended RSS)  file. 
This file can be imported in Wordpress with the Wordpress Importer.
Post, comments,tags and categories are imported as well as the post date.

Usage is very simple:  just place the script somewhere in the pivotx site and execute it. Save the output file somewhere (that is, the WXR source) and upload it to Wordpress.

Before executing you should setup your database connection at the beginning of the file.
You can set some defines at the beginning of the file to play around with single records or a small set.
define('MAX_ROWS' ,'10');  //0= output all rows... or give a number. Makes it easy for testing just a small content set

define('SINGLE_SHOT' ,'0');  //0=disable single shot... or give a pivotx id. This will export only one specific post. Overrides MAX_ROWS


