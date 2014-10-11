<?php
//Convert PivotX blog to Wordpress WXR file
//See https://github.com/eduh/Pivotx-To-Wordpress/
set_time_limit(0);
//Do not display erros but write a log
error_reporting(E_ALL);
ini_set('display_errors',0);

//Set your database connection here
$host='localhost';  
$user='user here';
$password='password here';
$db='db here';
init_pivotxdb($host,$user,$password,$db);

define('OUTPUT_XML' ,'0'); //1=sent xml header 0=normal use
define('DEBUG_QUERY' ,'0');  //1= output the query in xml 0=normal use
define('MAX_ROWS' ,'0');  //0= all or give a number. Makes it easy for testing just a small set
//define('MAX_ROWS' ,'3');  //0= all rows (normal use)... or give a number. Makes it easy for testing just a small content set
define('SINGLE_SHOT' ,'0');  //0=disable single shot (normal use)... or give a number. This will export only one specific post. Overrides MAX_ROWS
//define('SINGLE_SHOT' ,'310');  //this is a post with lots of comments  ...vuvuzela
//define('SINGLE_SHOT' ,'584');  //this is test post 'lutberg'
//define('SINGLE_SHOT' ,'493');  //this is test post 
//define('SINGLE_SHOT' ,'643');  //this is test post with spotify llinks
define('TRANSFORM_HTML' ,'1');  //transform some pivotx markup like  [[popup and [[image  to html and where appropriate to thickbox wordpress plugin
define('MAP_EXCERPT' ,'1');  //Map the pivotx 'introduction' to the WP excerpt field
define('MAP_CATEGORIES' ,'1');  //Rename or map categories using the $arrCatMap array 1=map 0=do not transform
//Put your categories in this array to map them to another wordpress category. The key is the pivotx category, the value as it goes to wordpress. Cats that are not present will be mapped 1:1
$arrCatMap=array(
	'ambient-music'=>'ambient music',
	'dreamscenes'=>'dreamscenes',
	'exclusives'=>'exclusives',
	'mixes--podcasts'=>'mixes--podcasts',
	'other-music'=>'other-music',
	'other-news'=>'other-news',
	'reviews'=>'reviews',
	'selling-my-stuff'=>'elling-my-stuff',
	'videos'=>'videos',
	'weird-or-forgotten-music'=>'weird-or-forgotten-music');
define('br','<br/>');
define('N',"\n");
define('RN',"\r\n");
define('pre',"<pre>");
define('pree',"</pre>");



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
		p2w_format($row['body'],$row['title']);
		echo '<item>'.N;
			echo '<pubDate>'.date("D, d M Y H:i:s T", strtotime($row['publish_date'])).'</pubDate>'.N;
			echo '<dc:creator>YOUR NAME</dc:creator>'.N;
			echo '<guid isPermaLink="false">http://www.yoursitehere.com//wordpress/?p='.$row['uid'].'</guid>'.N;
			echo '<description>YOUR DESCRIPTION</description>'.N;
			echo '<title><![CDATA['. htmlentities($row['title']).']]></title>'.N;
			//echo '<content:encoded><![CDATA['. str_replace(array(']','['), '',$row['introduction']).str_replace(array(']','['), '',$row['body']).']]></content:encoded>'.N;
			//echo '<excerpt:encoded><![CDATA['. str_replace(array(']','['), '',$row['introduction']).']]></excerpt:encoded>'.N;
			if(TRANSFORM_HTML=='1')
			{
				echo '<content:encoded><![CDATA[';
				echo p2w_format($row['introduction'].$row['body']);
				echo ']]></content:encoded>'.N;
			}else{
				echo '<content:encoded><![CDATA['. $row['introduction'].$row['body'].']]></content:encoded>'.N;
			}
			if(MAP_EXCERPT=='1')
			{
				if(TRANSFORM_HTML=='1')
				{
					echo '<excerpt:encoded><![CDATA['.p2w_format($row['introduction']).']]></excerpt:encoded>'.N;
				}else{
					echo '<excerpt:encoded><![CDATA['.$row['introduction'].']]></excerpt:encoded>'.N;
				}
			}
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

function p2w_format($in)
{
/*
<a href="http://www.ambientblog.net/blog/images/ds-final-square-750.jpg" title="Flower" class="thickbox">
  <img src="http://www.ambientblog.net/blog/images/ds-final-square-750.thumb.jpg" alt="Flower" />ssss
</a>
*/
$out=array();
$arrStart=explode('[[',$in);
//echo pre;
//print_r($arrStart);exit;


foreach($arrStart as $v)
{
	$str=strstr($v,']]', TRUE);
	if($str)
	{
		if(substr(trim($v),0,5)=='popup')
		{
			//Array ( [0] => popup file= [1] => sonmi451-fourpeaks.jpg [2] => description= [3] => (thumbnail) [4] => alt= [5] => Four Peaks [6] => ]]
			$arrTmp=explode('"',$v);
			$strTmp='<p style="text-align:center;"><a href="http://www.ambientblog.net/blog/images/'.$arrTmp[1].'" description="'.$arrTmp[5].'" title="'.$arrTmp[5].'" class="thickbox">
			  <img src="http://www.ambientblog.net/blog/images/'.str_replace(array('jpg','png'), array('thumb.jpg','thumb.png'),$arrTmp[1]).'" alt="'.$arrTmp[5].'" />
			</a>';

			//print_r($arrtmp);exit;
			//echo $str.substr($v,strlen($str));exit;
			$out[]=$strTmp.substr($v,strlen($str)+2);
		}
		if(substr(trim($v),0,5)=='image')
		{
			if(substr(trim($v),0,6)=='image:')
			{
				$arrTmp=explode(':',$v);
				$strTmp='<p style="text-align:center;"><img src="http://www.ambientblog.net/blog/images/'.$arrTmp[1].'" title="'.$arrTmp[2].'" alt="'.$arrTmp[2].'" class="pivotx-image"/>';
			}else{
				$arrTmp=explode('"',$v);
				$strTmp='<p style="text-align:center;"><img src="http://www.ambientblog.net/blog/images/'.$arrTmp[1].'" title="'.$arrTmp[3].'" alt="'.$arrTmp[3].'" class="pivotx-image"/>';
			}
			/*
			echo pre;
			print_r($arrTmp);
			echo pree;
			*/
			
			$out[]=$strTmp.substr($v,strlen($str)+2);
		}
		//$out .= $tmp.substr($v,strlen($str)+2);		
	}else{
		$out[]=$v;
	}
}
	$str=implode($out);
	$str=str_replace(array('/blog/pivotx/jwplayer.php','/blog/pivotx/jwplayerd.php','<p><p style="text-align:center;">'),array('/jwp/jwplayer.php','/jwp/jwplayerd.php','<p style="text-align:center;">'),$str);
	return $str;
	//exit;
	//echo $out;exit;
	//[[popup file="sonmi451-fourpeaks.jpg" description="(thumbnail)" alt="Four Peaks" ]]
	//[[image file="glonti.jpg" alt="Rezo Glonti " ]]	
	//	return $out;
}



function get_pivotx_categories($id)
{
	global $arrCatMap;
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
			if(MAP_CATEGORIES=='1' && array_key_exists($row['category'],$arrCatMap))
			{
				$cat .= '<category domain="category" nicename="'.$arrCatMap[$row['category']].'"><![CDATA['.$arrCatMap[$row['category']].']]></category>'.N;
			}else{
				$cat .= '<category domain="category" nicename="'.$row['category'].'"><![CDATA['.$row['category'].']]></category>'.N;
			}
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
		<link>http://www.ambientblog.net/blog</link>
		<description>This is a personal weblog about my current (ambient) favourites. </description>
		<pubDate>Mon, 22 Sep 2014 22:14:29 +0000</pubDate>
		<language>en</language>
		<wp:wxr_version>1.1</wp:wxr_version>
		<wp:base_site_url>http://www.ambientblog.net</wp:base_site_url>
		<wp:base_blog_url>http://www.ambientblog.net/blog</wp:base_blog_url>
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

/*
Clear the db before load
TRUNCATE TABLE wp_term_relationships;
TRUNCATE TABLE wp_posts;
TRUNCATE TABLE wp_comments;
DELETE FROM wp_terms WHERE term_id>2;
DELETE FROM wp_term_taxonomy WHERE term_id>2;
*/


?>
