<?php
/* Extensive Hit Counter 2.0
  � Copyright 2007-2017 Illumin Design, Co.
  http://www.illumindesign.net/


  Written by: Bobby Ratliff
  Tested by: Aaron Mihalchan
  
  This is the file that counts the hits. I suggest
  putting this into a global file so that it catches
  every page accessed. To include this script in a
  PHP file, use the following code:

  include("ehcounter.inc");

  Make sure this is in the same directory as the
  file in which you are including this script.



  This script requires connection to a MySQL database.

$ip = realip();
$ref = $r; //$_SERVER["HTTP_REFERER"];
$browser =  $_SERVER['HTTP_USER_AGENT'];
$end_of_today = strtotime(date("j M Y")." 23:59:59");
$begin_of_today = strtotime(date("j M Y")." 00:00:00");
*/

# FUNCTIONS ========================================================================================

function get_country ($ip)
{
  $cq = @mysql_query("SELECT `ci` FROM `ip` WHERE INET_ATON('".$ip."') BETWEEN `start` AND `end` LIMIT 1");
  if (mysql_num_rows($cq) > 0)
  {
    $ci = mysql_result($cq,0);
    $country[0] = strtoupper(mysql_result(mysql_query("SELECT `cc` FROM `cc` WHERE `ci`=".$ci." LIMIT 1"), 0));
    $country[1] = mysql_result(mysql_query("SELECT `cn` FROM `cc` WHERE `ci`=".$ci." LIMIT 1"), 0);
  }
  else
  {
    $country[0] = "NA";
    $country[1] = "Unknown";
  }
  return $country;
}

function get_location ($ip)
{
  $qc = mysql_query("SELECT l.city,l.region FROM locations l JOIN blocks b ON (l.locId=b.locId) WHERE INET_ATON('".$ip."') >= b.startIpNum AND INET_ATON('".$ip."') <= b.endIpNum LIMIT 1;") or die (mysql_error());
  while ($r = mysql_fetch_assoc($qc))
  {
    $location[0] = $r['city'];
    $location[1] = $r['region'];
  }
  
  return $location;
}

function get_language ($lang)
{
  $lang = substr($lang, 0, 2);
  return strtoupper($lang);
}

function get_browserinfo($useragent)
{
  if (preg_match("/((?:Edge)\/[\d\.]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/((?:Chrome)\/[\d\.]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/((?:Firefox)\/[\d\.]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/((?:Safari)\/[\d\.]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/MSIE ([\d]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/((?:Opera)\/[\d\.]+)/i", $useragent, $t))
    $browser = str_replace("/", " ", $t[1]);
  elseif (preg_match("/Googlebot/i", $useragent))
    $browser = 'Google';
  else
    $browser = truncate($r['browser'], 25);
  
  $browser = explode(" ", $browser);
  return $browser;
}

function get_osinfo($useragent)
{
  // OPERATING SYSTEM DETECTION ===================================================================
  if (preg_match("/((?:Android) [\d\.]+)/i", $useragent, $t))
  {
    $os = str_replace("/", " ", $t[1]);
    $os = explode(" ", $os);
  }
  elseif (preg_match("/(Linux[xi\d\._ ]*)/i", $useragent, $t))
  {
    $os = $t[1];
    $os = explode(" ", $os);
  }
  elseif (preg_match("/iphone/i", $useragent))
  {
    $os[0] = "iOS (iPhone)";
    preg_match("/(\d{1,2}_\d{1,2}_*\d{0,2})/", $useragent, $version);
    $os[1] = str_replace("_", ".", $version[0]);
  }
  elseif (preg_match("/ipad/i", $useragent))
  {
    $os[0] = "iOS (iPad)";
    preg_match("/(\d{1,2}_\d{1,2}_*\d{0,2})/", $useragent, $version);
    $os[1] = str_replace("_", ".", $version[0]);
  }
  elseif (preg_match("/((?:Macintosh|Mac OS X) [\d_\.]+)/i", $useragent, $t))
  {
    $os = str_replace("_", ".", $t[1]);
    $os = explode(" ", $os);
  }
  elseif (preg_match("/(Windows NT 10\.0)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = '10';
  }
  elseif (preg_match("/(Windows NT 6\.3)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = '8.1';
  }
  elseif (preg_match("/(Windows NT 6\.2)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = '8';
  }
  elseif (preg_match("/(Windows NT 6\.1)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = '7';
  }
  elseif (preg_match("/(Windows NT 6\.0)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = 'Vista';
  }
  elseif (preg_match("/(Windows NT 5\.2)/i", $useragent))
  {
    $os[0] = 'Windows Server';
    $os[1] = '2003';
  }
  elseif (preg_match("/(Windows NT 5\.1)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = 'XP';
  }
  elseif (preg_match("/(Windows NT 5\.0)/i", $useragent))
  {
    $os[0] = 'Windows';
    $os[1] = '2000';
  }
  
  return $os;
}

# VARIABLES AND INDEXES ============================================================================

$ref = $r;
$ip = realip();
$now = strtotime("now");
$useragent = $_SERVER['HTTP_USER_AGENT'];
$browser = get_browserinfo($useragent);
$os = get_osinfo($useragent);
$end_of_today = strtotime(date("j M Y")." 23:59:59");
$begin_of_today = strtotime(date("j M Y")." 00:00:00");
$language = get_language($_SERVER['HTTP_ACCEPT_LANGUAGE']);
$country = get_country($ip);
$location = get_location($ip);
$year = date("Y", $now);
$month = date("n", $now);
$day = date("j", $now);
$week = date("W", $now);
$dayofyear = date("z", $now);
$dayofweek = date("N", $now);
$hour = date("G", $now);
$minute = date("i", $now);
$minuteofday = ($hour * 60) + $minute;
$mobile = preg_match('/(tablet|pad|mobile|phone|symbian|android|ipod|ios|blackberry|webos)/i', $useragent) ? 1 : 0;
$bot = preg_match('/(bot)/i', $useragent) ? 1 : 0;



# PROCESS DATABASE STUFF ===========================================================================

$q = mysql_query("SELECT * FROM `stats` WHERE `ip`='".$ip."' AND `ref`='".mysql_real_escape_string($ref)."' AND `page`='".mysql_real_escape_string($_SERVER['REQUEST_URI'])."' ORDER BY `date` DESC");
$exists = mysql_num_rows($q);
if (!$exists)
{
  $date = strtotime("now");
  mysql_query("INSERT INTO `stats` (
    `id`,
    `site`,
    `page`,
    `ip`,
    `browser`,
    `os`,
    `osversion`,
    `browserbase`,
    `browserversion`,
    `ref`,
    `date`,
    `update`,
    `hits`,
    `language`,
    `country`,
    `countrycode`,
    `region`,
    `city`,
    `year`,
    `week`,
    `month`,
    `day`,
    `hour`,
    `minute`,
    `dayofyear`,
    `dayofweek`,
    `minuteofday`,
    `bot`,
    `mobile`
  ) VALUES (
    NULL,
    '".mysql_real_escape_string($site)."',
    '".mysql_real_escape_string($page)."',
    '".$ip."',
    '".$useragent."',
    '".$os[0]."',
    '".$os[1]."',
    '".$browser[0]."',
    '".$browser[1]."',
    '".mysql_real_escape_string($ref)."',
    '".$date."',
    '".$date."',
    '1',
    '".$language."',
    '".$country[1]."',
    '".$country[0]."',
    '".$location[1]."',
    '".$location[0]."',
    '".$year."',
    '".$week."',
    '".$month."',
    '".$day."',
    '".$hour."',
    '".$minute."',
    '".$dayofyear."',
    '".$dayofweek."',
    '".$minuteofday."',
    '".$bot."',
    '".$mobile."'
  );") or die(mysql_error());
}
else
{
  $i = mysql_fetch_array($q);
  if ($i['date'] < $begin_of_today)
  {
    //echo date("z", $i['date'])."<br>".date("z", strtotime("now"));
    $date = strtotime("now");
    mysql_query("INSERT INTO `stats` (
      `id`,
      `site`,
      `page`,
      `ip`,
      `browser`,
      `os`,
      `osversion`,
      `browserbase`,
      `browserversion`,
      `ref`,
      `date`,
      `update`,
      `hits`,
      `language`,
      `country`,
      `countrycode`,
      `region`,
      `city`,
      `year`,
      `week`,
      `month`,
      `day`,
      `hour`,
      `minute`,
      `dayofyear`,
      `dayofweek`,
      `minuteofday`,
      `bot`,
      `mobile`
    ) VALUES (
      NULL,
      '".mysql_real_escape_string($site)."',
      '".mysql_real_escape_string($page)."',
      '".$ip."',
      '".$useragent."',
      '".$os[0]."',
      '".$os[1]."',
      '".$browser[0]."',
      '".$browser[1]."',
      '".mysql_real_escape_string($ref)."',
      '".$date."',
      '".$date."',
      '1',
      '".$language."',
      '".$country[1]."',
      '".$country[0]."',
      '".$location[1]."',
      '".$location[0]."',
      '".$year."',
      '".$week."',
      '".$month."',
      '".$day."',
      '".$hour."',
      '".$minute."',
      '".$dayofyear."',
      '".$dayofweek."',
      '".$minuteofday."',
      '".$bot."',
      '".$mobile."'
    );") or die(mysql_error());
  }
  else
  {
    $id = $i["id"];
    $hits = $i["hits"]; $hits++;
    $update = strtotime("now");
    mysql_query("UPDATE `stats` SET `hits`='".$hits."', `update`='".$update."' WHERE `id`='".$id."' LIMIT 1") or die(mysql_error());
  }
}

?>
