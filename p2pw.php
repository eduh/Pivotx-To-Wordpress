<?php

//Convert PivotX blog to Wordpress
//See https://github.com/eduh/Pivotx-To-Wordpress/

//Set your MySQL database connection here...localhost is most likely
$host='localhost';
$user='user here ';
$password='password here';
$db='db here';
init_pivotxdb($host,$user,$password,$db);

define('OUTPUT_XML' ,'0'); //1=sent xml header 0=just output 
define('DEBUG_QUERY' ,'0');  //1= output the query in xml
define('MAX_ROWS' ,'0');  //0= all or give a number. Makes it easy for testing just one row
//define('MAX_ROWS' ,'10');  //0= all rows... or give a number. Makes it easy for testing just a small content set
define('SINGLE_SHOT' ,'0');  //0=disable single shot... or give a number. This will export only one specific post. Overrides MAX_ROWS
//define('SINGLE_SHOT' ,'310');  //this is a post with lots of comments  ...vuvuzela
//define('SINGLE_SHOT' ,'584');  //this is test post 'lutberg'
define('br','<br/>');
define('N',"\n");
define('RN',"\r\n");
error_reporting(E_ALL);
ini_set('display_errors',1);

if(OUTPUT_XML=='1')
{
	header('Content-type: text/xml');
	echo '<?xml version="1.0" encoding="UTF-8" ?>';
}
show_header();
get_pivotx_entries();
show_footer();


function get_pivotx_entries()
{
	$comment_id=1;
	if(SINGLE_SHOT=='0')
	{
		if(MAX_ROWS=='0')
		{
			$query='SELECT * FROM pivotx_entries order by uid';
		}else{
			$query='SELECT * FROM pivotx_entries LIMIT ' . intval(MAX_ROWS)  ;
		}
	}else{
		 $query='SELECT * FROM pivotx_entries where uid='.intval(SINGLE_SHOT);
	}
	$result=mysql_query($query);
	while ($row = mysql_fetch_assoc($result)) {
		echo '<item>'.N;
			echo '<pubDate>'.date("D, d M Y H:i:s T", strtotime($row['publish_date'])).'</pubDate>'.N;
			echo '<dc:creator>Peter van Cooten</dc:creator>'.N;
			echo '<guid isPermaLink="false">http://localhost/wordpress/?p='.$row['uid'].'</guid>'.N;
			echo '<description>Beschrijving hier</description>'.N;
			echo '<title><![CDATA['. htmlentities($row['title']).']]></title>'.N;
			echo '<content:encoded><![CDATA['. str_replace(array(']','['), '',$row['introduction']).str_replace(array(']','['), '',$row['body']).']]></content:encoded>'.N;
			echo '<excerpt:encoded><![CDATA['. str_replace(array(']','['), '',$row['introduction']).']]></excerpt:encoded>'.N;
			echo '<wp:post_name>'.$row['uri'].'</wp:post_name>'.N;
			echo '<wp:post_id>'.$row['uid'].'</wp:post_id>'.N;
			echo '<wp:post_date>'.$row['date'].'</wp:post_date>'.N;
			echo '<wp:post_date_gmt>'.$row['date'].'</wp:post_date_gmt>'.N;
			echo  '<wp:post_type>post</wp:post_type>'.N; 
			
			//Some default stuff
			show_static_post();
			//insert categories when present
 			$cat=get_pivotx_categories($row['uid']);
			//insert tags when present
 			$tag=get_pivotx_tags($row['uid']);
			//insert comments when present
 			$comments=get_pivotx_comments($row['uid'],$comment_id);
		echo '</item>'.N;
		$comment_id++;
	}
}


function get_pivotx_categories($id)
{
	$cat='';
	$query='SELECT * FROM pivotx_categories where target_uid='.$id;
	$result=mysql_query($query);
	if (mysql_numrows($result)==0)
	{
		return FALSE;
	}
	if(DEBUG_QUERY=='1')			
	{
		echo '<querycat>'.$query.'</querycat>';
	}
	while ($row = mysql_fetch_assoc($result)) {
			$cat .= '<category domain="category" nicename="'.$row['category'].'"><![CDATA['.$row['category'].']]></category>'.N;
	}
		if($cat!='')
		{
			echo $cat;
		}
}

function get_pivotx_tags($id)
{
	$tag='';
	$query='SELECT * FROM pivotx_tags where target_uid='.$id;
	$result=mysql_query($query);
	if (mysql_numrows($result)==0)
	{
		return FALSE;
	}
	if(DEBUG_QUERY=='1')			
	{
		echo '<querytag>'.$query.'</querytag>';
	}
	$find=array('nästesjö;' , 'kã¶ner','steinbrã¼chel','crónica','köner', 'abattoir_fermé', 'würden', 'francisco_lópez', 'pärt');
	$replace=array('nastesjo', 'kaner', 'steinbrachel', 'cronica','koner' , 'abattoir_ferme', 'wurden', 'francisco_lopez','part');

	while ($row = mysql_fetch_assoc($result)) {
			$tag_value=str_replace($find,$replace,$row['tag']);
			//$tag_value='qqq';
			$tag .= '<category domain="post_tag" nicename="'.$tag_value.'"><![CDATA['.$tag_value.']]></category>'.N;
	}
		if($tag!='')
		{
			echo $tag;
		}
}

function get_pivotx_comments($id,$comment_id)
{
	$comment='';
	$query='SELECT * FROM pivotx_comments where entry_uid='.$id;
	$result=mysql_query($query);
	if (mysql_numrows($result)==0)
	{
		return FALSE;
	}
	if(DEBUG_QUERY=='1')			
	{
		echo '<querycomments>'.$query.'</querycomments>';
	}
	//$find=array('nästesjö;' , 'kã¶ner','steinbrã¼chel','crónica','köner', 'abattoir_fermé', 'würden', 'francisco_lópez', 'pärt');
	//$replace=array('nastesjo', 'kaner', 'steinbrachel', 'cronica','koner' , 'abattoir_ferme', 'wurden', 'francisco_lopez','part');
	while ($row = mysql_fetch_assoc($result)) {
			//$tag_value=str_replace($find,$replace,$row['tag']);
			$comment .=
			 	'<wp:comment>'.N.
				'<wp:comment_id>'.$comment_id.'</wp:comment_id>'.N. 
				'<wp:comment_author><![CDATA['.$row['name'].']]></wp:comment_author>'.N.
				'<wp:comment_author_email>'.$row['email'].'</wp:comment_author_email>'.N.
				'<wp:comment_author_url>'.$row['url'].'</wp:comment_author_url>'.N.
				'<wp:comment_author_IP>'.$row['ip'].'</wp:comment_author_IP>'.N.
				'<wp:comment_date>'.$row['date'].'</wp:comment_date>'.N.
				'<wp:comment_date_gmt>'.$row['date'].'</wp:comment_date_gmt>'.N.
				'<wp:comment_content><![CDATA['.$row['comment'].']]></wp:comment_content>'.N.
				'<wp:comment_approved>1</wp:comment_approved>'.N.
				'<wp:comment_type></wp:comment_type>'.N.
				'<wp:comment_parent>0</wp:comment_parent>'.N.
				'<wp:comment_user_id>0</wp:comment_user_id>'.N.
			  '</wp:comment>'.N;
			  $comment_id++;

	}
		if($comment!='')
		{
			echo $comment;
		}
}
/*

		<wp:comment>
			<wp:comment_id>5</wp:comment_id>
			<wp:comment_author><![CDATA[eduh]]></wp:comment_author>
			<wp:comment_author_email>eduh@xs4all.nl</wp:comment_author_email>
			<wp:comment_author_url></wp:comment_author_url>
			<wp:comment_author_IP>127.0.0.1</wp:comment_author_IP>
			<wp:comment_date>2014-09-27 06:33:27</wp:comment_date>
			<wp:comment_date_gmt>2014-09-27 06:33:27</wp:comment_date_gmt>
			<wp:comment_content><![CDATA[ssssssssssssss]]></wp:comment_content>
			<wp:comment_approved>1</wp:comment_approved>
			<wp:comment_type></wp:comment_type>
			<wp:comment_parent>0</wp:comment_parent>
			<wp:comment_user_id>1</wp:comment_user_id>
		</wp:comment>
*/


function init_pivotxdb($host,$user,$password,$db)
{
	$link=@mysql_connect($host,$user,$password);
	if(!$link)
	{
		echo 'Could not connect to DB . ' . mysql_error(); 
		exit;
	}
	$db_selected = mysql_select_db($db, $link);
}


function init_pivotxdb_ext($cfg_file)
{
	require_once($cfg_file);
	//require_once('test_sql_connect_local.php');
	//require_once('test_sql_connect_ambient.php');
	$link=@mysql_connect($host,$user,$password);
	if(!$link)
	{
		echo 'Could not connect to DB . ' . mysql_error(); 
		exit;
	}
	$db_selected = mysql_select_db($db, $link);
	//require_once('test_sql_connect_eduh.php');
	//require_once('test_sql_connect_ambient.php');
	//require_once('test_sql_connect_lab.php');
}

function show_header()
{
	echo '<!-- generator="WordPress/3.2.1" created="2014-09-22 22:15" -->
	<rss version="2.0"
		xmlns:excerpt="http://wordpress.org/export/1.1/excerpt/"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:wp="http://wordpress.org/export/1.1/"
	>
	<channel>
		<title>AMBIENT BLOG</title>
		<link>http://localhost/wordpress</link>
		<description>This is a personal weblog about my current (ambient) favourites. </description>
		<pubDate>Mon, 22 Sep 2014 22:14:29 +0000</pubDate>
		<language>en</language>
		<wp:wxr_version>1.1</wp:wxr_version>
		<wp:base_site_url>http://localhost/wordpress</wp:base_site_url>
		<wp:base_blog_url>http://localhost/wordpress</wp:base_blog_url>
		<wp:author><wp:author_id>1</wp:author_id>
		<wp:author_login>JohnDoe</wp:author_login>
		<wp:author_email>john@doe.nl</wp:author_email>
		<wp:author_display_name><![CDATA[eduh]]></wp:author_display_name>
		<wp:author_first_name><![CDATA[]]></wp:author_first_name>\
		<wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>
		<wp:category><wp:term_id>1</wp:term_id>
		<wp:category_nicename>uncategorized</wp:category_nicename>
		<wp:category_parent></wp:category_parent>
		<wp:cat_name><![CDATA[Uncategorized]]></wp:cat_name></wp:category>
		<generator>http://wordpress.org/?v=3.2.1</generator>';
}

function show_static_post()
{
		echo '<wp:comment_status>open</wp:comment_status>
			<wp:ping_status>open</wp:ping_status>
			<wp:status>publish</wp:status>
			<wp:post_parent>0</wp:post_parent>
			<wp:menu_order>0</wp:menu_order>
			<wp:post_password></wp:post_password>
			<wp:is_sticky>0</wp:is_sticky>
			<wp:postmeta>
				<wp:meta_key>_edit_last</wp:meta_key>
				<wp:meta_value><![CDATA[1]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key>_wp_page_template</wp:meta_key>
				<wp:meta_value><![CDATA[default]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key>_edit_last</wp:meta_key>
				<wp:meta_value><![CDATA[1]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key>_wp_page_template</wp:meta_key>
				<wp:meta_value><![CDATA[default]]></wp:meta_value>
			</wp:postmeta>';
}

function show_footer()
{
echo'
</channel>
</rss>';
}

/*
Thickbox
<a href="http://www.ambientblog.net/blog/images/ds-final-square-750.jpg" title="Flower" class="thickbox">
  <img src="http://www.ambientblog.net/blog/images/ds-final-square-750.thumb.jpg" alt="Flower" />ssss
</a>
*/


?>
