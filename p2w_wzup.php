<?php

//Convert PivotX wzup (extension) counter info to sql statements...which can be loaded to wp_top_ten table from plugin top 10
//http://ajaydsouza.com/wordpress/plugins/top-10/
//See https://github.com/eduh/Pivotx-To-Wordpress/

//!!!!!!!! Watch it The serialized file is most of the times corrupt so you cannot unserialize() it. Therefore this little conversion is written

//REPLACE WITH OWN PATH TO wzupdb.php
$wzupdb='h:\data\htdocs\blog\test\wzupdb.php';
//REPLACE WITH THE HIGHEST ID OR MAKE IT LAAAAARGE (corruption in pivotx sometimes causes very high postid's
$maxpostid=700;

//Here we go
$str=@file_get_contents($wzupdb);
if(!$str)
{
	echo "Looks like $wzupdb cannot be opened";
	exit;
}

//Due to corruption we cannot use unserialize()
$strCount=str_replace(array('{','}'),'',substr($str, strpos($str,'{'),strlen($str)));
$key_val=explode(';',$strCount);
foreach($key_val as $v)
{
	$arrCount[]=$v;
}

//echo '<pre>';print_r($arrCount);exit;

$i=1;
foreach($arrCount as $k=>$v)
{
	if($k % 2 == 0)
	{
		$arrCountImport[str_replace('i:','', $v)]=str_replace('i:','', $arrCount[$i]);
	}
	$i++;
}

//print_r($arrCountImport);

ksort($arrCountImport);
echo '<pre>';
foreach($arrCountImport as $k=>$v)
{
	if (is_numeric($k) && $k<$maxpostid)
	{
		echo 'INSERT into wp_top_ten  VALUES('.$k.','.$v.');' ."\r\n";
	}
}	
?>
