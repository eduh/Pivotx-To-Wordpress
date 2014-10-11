Pivotx-To-Wordpress
===================

### H3Pivotx to Wordpress WXR  (P2W)

PivotX is an blog framework and when migrating to Wordpress a little conversion should be done. 
Best is to get the PivotX content from the database itself and create a WXR (Wordpress extended RSS)  file. 
This file can be imported in Wordpress with the Wordpress Importer.
Post, comments,tags and categories are imported as well as the post date.

Usage is very simple:  just place the p2w.php script somewhere in the pivotx site and execute it. Save the output file somewhere (use the xml source if the output is not rendered as xml in your browser) and upload it to Wordpress. 

Before executing you should setup your database connection at the beginning of the file.
You can set some defines at the beginning of the file to play around with single records or a small set or do specific transforms.
define('MAX_ROWS' ,'10');  //0= output all rows... or give a number. Makes it easy for testing just a small content set
define('SINGLE_SHOT' ,'0');  //0=disable single shot... or give a pivotx id. This will export only one specific post. Overrides MAX_ROWS
define('TRANSFORM_HTML' ,'1');  //1=transform some specific pivotx markup like  [[popup and [[image  to html and where appropriate to thickbox wordpress plugin. 0=do not transform
define('MAP_EXCERPT' ,'1');  //1=Map the pivotx 'introduction' to the WP excerpt field 0=no excerpt
define('MAP_CATEGORIES' ,'1');  //1=Rename or map categories using the $arrCatMap array 1=map 0=do not transform. 


### H3Post Counter information (p2w_wzup.php)

When using the Pivotx wzup extension it is possible to convert the post counts. Use p2w_wzup.php script on the file which holds the counters. In the script simply adjust the path to the file and run the script. The output is the sql statements needed to run on wordpress when using the top10 plugin at http://ajaydsouza.com/wordpress/plugins/top-10/
