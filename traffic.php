<?php
/* Extensive Hit Counter 2.1
  � Copyright 2007-2018 Illumin Design, Co.
  http://www.illumindesign.net/

  Written by: Bobby Ratliff
  Tested by: Aaron Mihalchan
  
  This is the file that counts the hits. I suggest
  putting this into a global file so that it catches
  every page accessed. To include this script in a
  PHP file, use the following code:

  include("traffic.php");

  *This script requires PDO connection.
  
  *GeoIP is required for some of the functions.

*/

# FUNCTIONS ========================================================================================

function get_country ($ip)
{
    $country = array('','');
    try {
        $dbh = new PDO('mysql:host=myHost;dbname=myDB', 'myUser', 'myPassword');
        $cq = $dbh->prepare("SELECT `ci` FROM `ip` WHERE INET_ATON(?) BETWEEN `start` AND `end` LIMIT 1");
        $cq->execute(array($ip));
        if ($cq->rowCount() > 0) {
            $ci = $cq->fetch();
            $cc = $dbh->prepare("SELECT `cc`,`cn` FROM `cc` WHERE `ci`=? LIMIT 1");
            $cc->execute(array($ci[0]));
            $country = $cc->fetch(PDO::FETCH_NUM);
        } else {
            $country[0] = "NA";
            $country[1] = "Unknown";
        }
    } catch (PDOException $e) {
        $location[0] = "Error: get_country()";
        $location[1] = $e->getMessage();
    }

    return $country;
}

function get_location ($ip)
{
    $location = array('','');
    try {
        $dbh = new PDO('mysql:host=myHost;dbname=myDB', 'myUser', 'myPassword');
        foreach ($dbh->query("SELECT l.city,l.region FROM locations l JOIN blocks b ON (l.locId=b.locId) WHERE INET_ATON('" . $ip . "') >= b.startIpNum AND INET_ATON('" . $ip . "') <= b.endIpNum LIMIT 1;") as $r) {
            $location[0] = $r['city'];
            $location[1] = $r['region'];
        }
    } catch (PDOException $e) {
        $location[0] = "Error: get_location()";
        $location[1] = $e->getMessage();
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
    elseif (preg_match("/((?:OPR)\/[\d\.]+)/i", $useragent, $t))
        $browser = str_replace("OPR/", "Opera ", $t[1]);
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
        $browser = truncate($useragent, 25);

    $browser = explode(" ", $browser);
    return $browser;
}

function get_osinfo($useragent)
{
    # Unix-based Operating Systems =====-----
    if (preg_match("/((?:Android) [\d\.]+)/i", $useragent, $t)) {
        $os = str_replace("/", " ", $t[1]);
        $os = explode(" ", $os);
    } elseif (preg_match("/(Linux[xi\d\._ ]*)/i", $useragent, $t)) {
        $os = $t[1];
        $os = explode(" ", $os);
    } elseif (preg_match("/iphone/i", $useragent)) {
        $os[0] = "iOS (iPhone)";
        preg_match("/(\d{1,2}_\d{1,2}_*\d{0,2})/", $useragent, $version);
        $os[1] = str_replace("_", ".", $version[0]);
    } elseif (preg_match("/ipad/i", $useragent)) {
        $os[0] = "iOS (iPad)";
        preg_match("/(\d{1,2}_\d{1,2}_*\d{0,2})/", $useragent, $version);
        $os[1] = str_replace("_", ".", $version[0]);
    } elseif (preg_match("/((?:Macintosh|Mac OS X) [\d_\.]+)/i", $useragent, $t)) {
        $os = str_replace("_", ".", $t[1]);
        $os = explode(" ", $os);
    }
    # Windows Operating Systems =====-----
    elseif (preg_match("/(Windows NT 5\.0)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = '2000';
    } elseif (preg_match("/(Windows NT 5\.1)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = 'XP';
    } elseif (preg_match("/(Windows NT 5\.2)/i", $useragent)) {
        $os[0] = 'Windows Server';
        $os[1] = '2003';
    } elseif (preg_match("/(Windows NT 6\.0)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = 'Vista';
    } elseif (preg_match("/(Windows NT 6\.1)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = '7';
    } elseif (preg_match("/(Windows NT 6\.2)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = '8';
    } elseif (preg_match("/(Windows NT 6\.3)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = '8.1';
    } elseif (preg_match("/(Windows NT 10\.0)/i", $useragent)) {
        $os[0] = 'Windows';
        $os[1] = '10';
    } else {
        $os[0] = 'Unknown';
        $os[1] = '0.0';
    }

    return $os;
}

function realip()
{
    if (isSet($_SERVER)) {
        if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isSet($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
            $realip = getenv( 'HTTP_X_FORWARDED_FOR' );
        } elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
            $realip = getenv( 'HTTP_CLIENT_IP' );
        } else {
            $realip = getenv( 'REMOTE_ADDR' );
        }
    }

    return $realip;
}

# VARIABLES AND INDEXES ============================================================================

// Request information =====-----
$tI['code']        = 200;
$tI['site']        = $_SERVER['HTTP_HOST'];
$tI['page']        = $_SERVER['REQUEST_URI'];
$tI['ref']         = (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : '';
$tI['language']    = get_language($_SERVER['HTTP_ACCEPT_LANGUAGE']);
if (preg_match("/".$tI['site']."/i", $tI['ref'])) $tI['ref'] = $tI['site'];

// Visitor Information =====-----
$tI['useragent']   = $_SERVER['HTTP_USER_AGENT'];
$tI['bot']         = preg_match('/(bot)/i', $tI['useragent']) ? 1 : 0;
$tI['mobile']      = preg_match('/(tablet|pad|mobile|phone|symbian|android|ipod|ios|blackberry|webos)/i', $tI['useragent']) ? 1 : 0;
$os                = get_osinfo($tI['useragent']);
$browser           = get_browserinfo($tI['useragent']);
$tI['os']          = $os[0];
$tI['osversion']   = $os[1];
$tI['browserbase'] = $browser[0];
$tI['browserversion'] = $browser[1];
unset($os, $browser);

// Geographic Information =====-----
$tI['ip']          = realip();
$country           = get_country($tI['ip']);
$location          = get_location($tI['ip']);
$tI['country']     = $country[1];
$tI['countrycode'] = $country[0];
$tI['region']      = $location[1];
$tI['city']        = $location[0];
unset($country, $location);

// Chronological Information =====-----
$tI['now']         = strtotime("now");
$tI['year']        = date("Y", $tI['now']);
$tI['month']       = date("n", $tI['now']);
$tI['day']         = date("j", $tI['now']);
$tI['week']        = date("W", $tI['now']);
$tI['dayofyear']   = date("z", $tI['now']);
$tI['dayofweek']   = date("N", $tI['now']);
$tI['hour']        = date("G", $tI['now']);
$tI['minute']      = date("i", $tI['now']);
$tI['minuteofday'] = ($tI['hour']  * 60) + $tI['minute'];
$tI['today_start'] = strtotime(date("j M Y")." 00:00:00");
$tI['today_end']   = strtotime(date("j M Y")." 23:59:59");

# PROCESS DATABASE STUFF ===========================================================================

try {
    $sql = $pdo_connection->prepare("SELECT
        `id`,
        `hits`,
        `date`,
        COUNT(*) AS `exists`
    FROM `stats` WHERE
        `ip`= :ip AND
        `ref`= :ref AND
        `page`= :page AND
        `useragent`= :useragent
    ORDER BY `date` DESC LIMIT 1");
    $sql->execute(array(':ip' => $tI['ip'], ':ref' => $tI['ref'], ':page' => $tI['page'], ':useragent' => $tI['useragent']));
    $result = $sql->fetchObject();

    if ($result->exists == 0 || $result->date < $tI['today_start'])
    {
        $sql = $pdo_connection->prepare("INSERT INTO `stats` (
            `id` ,
            `site` ,
            `page` ,
            `ip` ,
            `useragent` ,
            `os` ,
            `osversion` ,
            `browserbase` ,
            `browserversion` ,
            `ref` ,
            `ref_tld` ,
            `date` ,
            `update` ,
            `code` ,
            `hits` ,
            `language` ,
            `country` ,
            `countrycode` ,
            `region` ,
            `city` ,
            `year` ,
            `week` ,
            `month` ,
            `day` ,
            `hour` ,
            `minute` ,
            `dayofyear` ,
            `dayofweek` ,
            `minuteofday` ,
            `bot` ,
            `mobile`
        ) VALUES (
            NULL,
            :site,
            :page,
            :ip,
            :useragent,
            :os,
            :osversion,
            :browserbase,
            :browserversion,
            :ref,
            'ref_tld',
            :date,
            :update,
            :code,
            1,
            :language,
            :country,
            :countrycode,
            :region,
            :city,
            :year,
            :week,
            :month,
            :day,
            :hour,
            :minute,
            :dayofyear,
            :dayofweek,
            :minuteofday,
            :bot,
            :mobile
        )");

        $sql->execute(array(
            ':site'           => $tI['site'],
            ':page'           => $tI['page'],
            ':ip'             => $tI['ip'],
            ':useragent'      => $tI['useragent'],
            ':os'             => $tI['os'],
            ':osversion'      => $tI['osversion'],
            ':browserbase'    => $tI['browserbase'],
            ':browserversion' => $tI['browserversion'],
            ':ref'            => $tI['ref'],
            ':date'           => $tI['now'],
            ':update'         => $tI['now'],
            ':code'           => $tI['code'],
            ':language'       => $tI['language'],
            ':country'        => $tI['country'],
            ':countrycode'    => $tI['countrycode'],
            ':region'         => $tI['region'],
            ':city'           => $tI['city'],
            ':year'           => $tI['year'],
            ':week'           => $tI['week'],
            ':month'          => $tI['month'],
            ':day'            => $tI['day'],
            ':hour'           => $tI['hour'],
            ':minute'         => $tI['minute'],
            ':dayofyear'      => $tI['dayofyear'],
            ':dayofweek'      => $tI['dayofweek'],
            ':minuteofday'    => $tI['minuteofday'],
            ':bot'            => $tI['bot'],
            ':mobile'         => $tI['mobile']
        ));
    }
    else
    {
        $id = $result->id;
        $hits = $result->hits; $hits++;
        $sql = $pdo_connection->prepare("UPDATE `stats` SET `hits`= :hits, `update`= :update WHERE `id`= :id LIMIT 1");
        $sql->execute(array(':hits' => $hits, ':update' => $tI['now'], ':id' => $id));
    }
} catch (PDOException $e) {
    die("<h1>Traffic Error!: ".$e->getMessage()."</h1>".$e->getLine()."<br>".$e->getCode()."<br>".$e->getTraceAsString());
}