<?php
DEFINE ('MAX_FILE_SIZE', 100000000);
# Do not crawl pages that are already crawled within 14 days. 
# This is just a security check to ensure our crawler don't visit the same page within specified number of days.
DEFINE ('DAYS_OLD', '14'); 

include("config.php");
include('classes/DomDocumentParser.php');
/*
 *	Added simple_html_dom which will be intially used as secondry parser until primary dom parser is fully replaced.
 *	Added PHPCrawl Library will be used as primary crawler for future versions.
 */
include_once("clases/simple_html_dom.php");
include_once("lib/PHPCrawl/libs/PHPCrawler.class.php");

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();

/*
 *
 */
class HLCrawler extends PHPCrawler
{
	public function handleDocumentInfo(PHPCrawlerDocumentInfo $p)
	{
		// Just detect linebreak for output ("\n" in CLI-mode, otherwise "<br>").
		if (PHP_SAPI == "cli") $lb = "\n";
		else $lb = "<br />";

		// Print the URL and the HTTP-status-Code
		echo "Page requested: ".$p->url." (".$p->http_status_code.")".$lb;
		
		// Print the refering URL
		echo "Referer-page: ".$p->referer_url.$lb;
		
		// Print if the content of the document was be recieved or not
		if ($p->received == true)
		  echo "Content received: ".$p->bytes_received." bytes".$lb;
		else
		  echo "Content not received".$lb; 
		
		// Now you should do something with the content of the actual
		// received page or file ($p->source), we skip it in this example 
		
		
		
		$url = $p->url;
		$status_code = $p->http_status_code;
		$source = $p->source;
		if ($status_code == 200 && $source != '') {
			$html = str_get_html($source);
			if (is_object($html)) {
				// s for Set or insert
				$meta_info = array(
					"meta_desc" => "",
					"meta_author" => "",
					"meta_keywords" => "",
					"meta_og_locale" => "",
					"meta_og_type" => "",
					"meta_og_title" => "",
					"meta_og_desc" => "",
					"meta_og_site_name" => "",
					"meta_title" => "",
					"meta_og_lat"=>"",
					"meta_og_lon"=>"",
					"meta_og_st_ad"=>"",
					"meta_og_loc"=>"",
					"meta_og_region"=>"",
					"meta_og_post_c"=>"",
					"meta_og_country"=>"",
				);

				//var_dump($html);
				$meta_description = $html->find('meta[name=description]', 0);
				if ($meta_description) {
					$meta_info['meta_desc'] = $meta_description->content;
					unset($meta_description);
				}
				$meta_author = $html->find('meta[name=author]', 0);
				if ($meta_author) {
					$meta_info['meta_author'] = $meta_author->content;
					unset($meta_author);
				}
				$meta_keywords = $html->find('meta[name=keywords]', 0);
				if ($meta_keywords) {
					$meta_info['meta_keywords'] = $meta_keywords->content;
					unset($meta_keywords);
				}
				$meta_og_locale = $html->find('meta[property=og:locale]', 0);
				if ($meta_og_locale) {
					$meta_info['meta_og_locale'] = $meta_og_locale->content;
					unset($meta_og_locale);
				}
				$meta_og_type = $html->find('meta[property=og:type]', 0);
				if ($meta_og_type) {
					$meta_info['meta_og_type'] = $meta_og_type->content;
					unset($meta_og_type);
				}
				$meta_og_title = $html->find('meta[property=og:title]', 0);
				if ($meta_og_title) {
					$meta_info['meta_og_title'] = $meta_og_title->content;
					unset($meta_og_title);
				}
				$meta_og_description = $html->find('meta[property=og:description]', 0);
				if ($meta_og_description) {
					$meta_info['meta_og_desc'] = $meta_og_description->content;
					unset($meta_og_description);
				}
				$meta_og_site_name = $html->find('meta[property=og:site_name]', 0);
				if ($meta_og_site_name) {
					$meta_info['meta_og_site_name'] = $meta_og_site_name->content;
					unset($meta_og_site_name);
				}
				$meta_og_lat = $html->find('meta[name=og:latitude]', 0);
				if ($meta_og_lat) {
					$meta_info['meta_og_lat'] = $meta_og_lat->content;
					unset($meta_og_lat);
				}
				$meta_og_lon = $html->find('meta[name=og:longitude]', 0);
				if ($meta_og_lon) {
					$meta_info['meta_og_lon'] = $meta_og_lon->content;
					unset($meta_og_lon);
				}
				$meta_og_st_ad = $html->find('meta[name=og:street-address]', 0);
				if ($meta_og_st_ad) {
					$meta_info['meta_og_st_ad'] = $meta_og_st_ad->content;
					unset($meta_og_st_ad);
				}
				$meta_og_loc = $html->find('meta[name=og:locality]', 0);
				if ($meta_og_loc) {
					$meta_info['meta_og_loc'] = $meta_og_loc->content;
					unset($meta_og_loc);
				}
				$meta_og_region = $html->find('meta[name=og:region]', 0);
				if ($meta_og_region) {
					$meta_info['meta_og_region'] = $meta_og_region->content;
					unset($meta_og_region);
				}
				$meta_og_post_c = $html->find('meta[name=og:postal-code]', 0);
				if ($meta_og_post_c) {
					$meta_info['meta_og_post_c'] = $meta_og_post_c->content;
					unset($meta_og_post_c);
				}
				$meta_og_country = $html->find('meta[name=og:country-name]', 0);
				if ($meta_og_country) {
					$meta_info['meta_og_country'] = $meta_og_country->content;
					unset($meta_og_country);
				}
				
				$meta_title = $html->find('title', 0);
				if ($meta_title) {
					$meta_info['meta_title'] = $meta_title->innertext;
					unset($meta_title);
				}
				
				$html->clear();
				unset($html);
				
				if(isset($url)){ // if url is set
					if(!in_array($url, $alreadyCrawled)){ // Check if its a new URL
						$alreadyCrawled[] = $url;
						//$crawling = $url; // Do not need this.
						
						
						$last_date_crawled = $con->prepare("SELECT id, last_date_crawled, hash FROM search_index WHERE url = '".$url."' ORDER BY last_date_crawled DESC LIMIT 1"); 
						$last_date_crawled->execute();
						
						if($last_date_crawled->rowCount() == 0){
							// Add New URL
							addURL($url, $meta_info);
						}
						else{
							// Existing Site
							// Add URL if older than 14 days
							while ($last_date_crawled_result = $last_date_crawled->fetch(PDO::FETCH_OBJ)){
								if (strtotime($last_date_crawled_result->last_date_crawled) < strtotime('-'.DAYS_OLD.' days')){
									addURL($url, $meta_info);
								}
							}
						}
						
					} 
					
				}
				
				
			}
		}
		
	}
}

function crawl($url)
{
	echo "Crawl Started: ".$url." \n";
	$crawler = new HLCrawler();
	$crawler->setURL($url);
	$crawler->addContentTypeReceiveRule('#text/html#');
	$crawler->addURLFilterRule('#(jpg|gif|png|pdf|jpeg|svg|css|js|avi|mov|dat|exe|mp4|mp3|flv)$# i');
	if (!isset($GLOBALS['bgFull'])) {
		//$crawler->setTrafficLimit(2000 * 1024);
	}
	$crawler->enableCookieHandling(true); 
	$crawler->obeyRobotsTxt(false);
	$crawler->obeyNoFollowTags(true);
	$crawler->setUserAgentString( SITE_NAME.' ('.BASE_URL.'/about.php)');
	/*
		0 - The crawler will follow EVERY link, even if the link leads to a different host or domain.
			If you choose this mode, you really should set a limit to the crawling-process (see limit-options),
			otherwise the crawler maybe will crawl the whole WWW!

		1 - The crawler only follow links that lead to the same domain like the one in the root-url.
			E.g. if the root-url (setURL()) is "http://www.foo.com", the crawler will follow links to "http://www.foo.com/..."
			and "http://bar.foo.com/...", but not to "http://www.another-domain.com/...".

		2 - The crawler will only follow links that lead to the same host like the one in the root-url.
			E.g. if the root-url (setURL()) is "http://www.foo.com", the crawler will ONLY follow links to "http://www.foo.com/...", but not
			to "http://bar.foo.com/..." and "http://www.another-domain.com/...". This is the default mode.

		3 - The crawler only follows links to pages or files located in or under the same path like the one of the root-url.
			E.g. if the root-url is "http://www.foo.com/bar/index.html", the crawler will follow links to "http://www.foo.com/bar/page.html" and
			"http://www.foo.com/bar/path/index.html", but not links to "http://www.foo.com/page.html". 
	*/
	$crawler->setFollowMode(1); 
	$crawler->setFollowRedirects(true);
	$crawler->setFollowRedirectsTillContent(true);
	$crawler->setRequestLimit(3, true);
	//$crawler->setPageLimit(1);
	$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);
	$crawler->setWorkingDirectory("tmp/");
	$crawler->go();
	// Output crawl report
	$report = $crawler->getProcessReport();
	echo "Summary:\n";
	echo "Links followed: " . $report->links_followed . "\n";
	echo "Documents received: " . $report->files_received . "\n";
	echo "Bytes received: " . $report->bytes_received . " bytes\n";
	echo "Process runtime: " . $report->process_runtime . " sec\n"; 
	echo "Memory peak usage: " . (($report->memory_peak_usage / 1024) / 1024) . " MB\n";
	echo "Abort Reason: " . $report->abort_reason. " \n";
}
# To Get Excerpt
function getExcerpt($str, $startPos=0, $maxLength=100) {
	if(strlen($str) > $maxLength) {
		$excerpt   = substr($str, $startPos, $maxLength-3);
		$lastSpace = strrpos($excerpt, ' ');
		$excerpt   = substr($excerpt, 0, $lastSpace);
		$excerpt  .= '...';
	} else {
		$excerpt = $str;
	}
	
	return $excerpt;
}
#	Prepare Text From HTML
function prp_txt_frm_html($content){
	$content = preg_replace("/\s+/", ' ', $content);
	$content = substr($content, 0, 1) == ' ' ? substr_replace($content, '', 0, 1) : $content;
	$content = substr($content, -1) == ' ' ? substr_replace($content, '', -1, 1) : $content;
	$content = html_entity_decode(strip_tags($content), ENT_QUOTES);
	
	return $content;
}
function addURL($url, $meta_info){
	global $con;
	if (filter_var($url, FILTER_VALIDATE_URL)) {
		
		foreach($meta_info as $meta_info_single => $k){
			$meta_info_cleaned[$meta_info_single] = prp_txt_frm_html($k);
		}
		if(isset($meta_info_cleaned['meta_og_lat']) && $meta_info_cleaned['meta_og_lat'] == ''){
			$meta_info_cleaned['meta_og_lat'] = 0;
		}
		if(isset($meta_info_cleaned['meta_og_lon']) && $meta_info_cleaned['meta_og_lon'] == ''){
			$meta_info_cleaned['meta_og_lon'] = 0;
		}
		
		//var_dump($meta_info_cleaned);
		if (!linkExists($url)) {
			
			$query = $con->prepare("INSERT INTO search_index(meta_title, url, meta_desc, meta_og_locale, meta_og_type, meta_og_title, meta_og_desc', meta_og_site_name, meta_og_lat, meta_og_lon, meta_og_st_ad, meta_og_loc, meta_og_region, meta_og_post_c, meta_og_country, date_crawled) VALUES (':meta_title', ':url', ':meta_desc', ':meta_og_locale', ':meta_og_type', ':meta_og_title', ':meta_og_desc', ':meta_og_site_name', ':meta_og_lat', ':meta_og_lon', ':meta_og_st_ad', ':meta_og_loc', ':meta_og_region', ':meta_og_post_c', ':meta_og_country', :date_crawled)");
			$query->bindParam(":meta_title", getExcerpt($meta_info_cleaned['meta_title'],0, 50));
			$query->bindParam(":url", $url);
			$query->bindParam(":meta_desc", getExcerpt($meta_info_cleaned['meta_desc'],0, 200));
			$query->bindParam(":meta_og_locale", $meta_info_cleaned['meta_og_locale']);
			$query->bindParam(":meta_og_type", $meta_info_cleaned['meta_og_type']);
			$query->bindParam(":meta_og_title", $meta_info_cleaned['meta_og_title']);
			$query->bindParam(":meta_og_desc", getExcerpt($meta_info_cleaned['meta_og_desc'],0, 200));
			$query->bindParam(":meta_og_site_name", $meta_info_cleaned['meta_og_site_name']);
			$query->bindParam(":meta_og_lat", $meta_info_cleaned['meta_og_lat']);
			$query->bindParam(":meta_og_lon", $meta_info_cleaned['meta_og_lon']);
			$query->bindParam(":meta_og_st_ad", $meta_info_cleaned['meta_og_st_ad']);
			$query->bindParam(":meta_og_loc", $meta_info_cleaned['meta_og_loc']);
			$query->bindParam(":meta_og_region", $meta_info_cleaned['meta_og_region']);
			$query->bindParam(":meta_og_post_c", $meta_info_cleaned['meta_og_post_c']);
			$query->bindParam(":meta_og_country", $meta_info_cleaned['meta_og_country']);
			$query->bindParam(":date_crawled", 'NOW()');
			$query->execute();

        } else {
			
			$query = $con->prepare("UPDATE search_index(meta_title, url, meta_desc, meta_og_locale, meta_og_type, meta_og_title, meta_og_desc', meta_og_site_name, meta_og_lat, meta_og_lon, meta_og_st_ad, meta_og_loc, meta_og_region, meta_og_post_c, meta_og_country, date_crawled) VALUES (':meta_title', ':url', ':meta_desc', ':meta_og_locale', ':meta_og_type', ':meta_og_title', ':meta_og_desc', ':meta_og_site_name', ':meta_og_lat', ':meta_og_lon', ':meta_og_st_ad', ':meta_og_loc', ':meta_og_region', ':meta_og_post_c', ':meta_og_country', :date_crawled) WHERE url=':url'");
			$query->bindParam(":meta_title", getExcerpt($meta_info_cleaned['meta_title'],0, 50));
			$query->bindParam(":url", $url);
			$query->bindParam(":meta_desc", getExcerpt($meta_info_cleaned['meta_desc'],0, 200));
			$query->bindParam(":meta_og_locale", $meta_info_cleaned['meta_og_locale']);
			$query->bindParam(":meta_og_type", $meta_info_cleaned['meta_og_type']);
			$query->bindParam(":meta_og_title", $meta_info_cleaned['meta_og_title']);
			$query->bindParam(":meta_og_desc", getExcerpt($meta_info_cleaned['meta_og_desc'],0, 200));
			$query->bindParam(":meta_og_site_name", $meta_info_cleaned['meta_og_site_name']);
			$query->bindParam(":meta_og_lat", $meta_info_cleaned['meta_og_lat']);
			$query->bindParam(":meta_og_lon", $meta_info_cleaned['meta_og_lon']);
			$query->bindParam(":meta_og_st_ad", $meta_info_cleaned['meta_og_st_ad']);
			$query->bindParam(":meta_og_loc", $meta_info_cleaned['meta_og_loc']);
			$query->bindParam(":meta_og_region", $meta_info_cleaned['meta_og_region']);
			$query->bindParam(":meta_og_post_c", $meta_info_cleaned['meta_og_post_c']);
			$query->bindParam(":meta_og_country", $meta_info_cleaned['meta_og_country']);
			$query->bindParam(":date_crawled", 'NOW()');
			$query->execute();
        }
	}
	else{
		echo 'URL is not valid. Please Try Again. \n';
	}
}
function linkExists($url){
    global $con;
    $query = $con->prepare("SELECT * FROM sites WHERE url = :url"); //
    $query->bindParam(":url", $url);
    $query->execute();
    return $query->rowCount() != 0;
}
function insertLink($url, $title, $description, $keywords){
    global $con;
    $query = $con->prepare("INSERT INTO sites(url, title, description, keywords) VALUES (:url, :title, :description, :keywords)");
    $query->bindParam(":url", $url);
    $query->bindParam(":title", $title);
    $query->bindParam(":description", $description);
    $query->bindParam(":keywords", $keywords);
    return $query->execute();
}
function insertImage($url, $src, $alt, $title){
    global $con;
    $query = $con->prepare("INSERT INTO images(siteUrl, imageUrl, alt, title) VALUES (:siteUrl, :imageUrl, :alt, :title)");
    $query->bindParam(":siteUrl", $url);
    $query->bindParam(":imageUrl", $src);
    $query->bindParam(":alt", $alt);
    $query->bindParam(":title", $title);
    return $query->execute();
}
function createLink($src, $url) {
    $scheme = parse_url($url)["scheme"]; // http or https
    $host = parse_url($url)["host"]; // website.domain

    if(substr($src, 0, 2) == "//") {
        $src =  $scheme . ":" . $src;
    }
    else if(substr($src, 0, 1) == "/") {
        $src = $scheme . "://" . $host . $src;
    }
    else if(substr($src, 0, 2) == "./") {
        $src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
    }
    else if(substr($src, 0, 3) == "../") {
        $src = $scheme . "://" . $host . "/" . $src;
    }
    else if(substr($src, 0, 4) != "http" & substr($src, 0, 5) != "https") {
        $src = $scheme . "://" . $host . "/" . $src;
    }
    return $src;
}

function getDetails($url){
    global $alreadyFoundImages;
    $parser = new DomDocumentParser($url);
    $titleArray = $parser->getTitleTags();
    if(sizeof($titleArray) == 0 || $titleArray->item(0) == NULL){
        return;
    }
    $title = $titleArray->item(0)->nodeValue; //Get noi dung title url
    $title = str_replace("\n", "", $title);
    if($title == ""){
        return;
    }
    $description = "";
    $keywords = "";
    $metaArray = $parser->getMetaTags();

    foreach ($metaArray as $meta){
        if($meta->getAttribute("name") == "description"){
            $description = $meta->getAttribute("content");
        }
        if($meta->getAttribute("name") == "keywords"){
            $keywords = $meta->getAttribute("content");
        }
    }

    $description = str_replace("\n" , "", $description);
    $keywords = str_replace("\n", "", $keywords);
    if(linkExists($url)){
        echo "$url already exists<br>";
    }else if(insertLink($url, $title, $description, $keywords)){
        echo "Insert success $url to database<br>";
    }else{
        echo "failed insert $url";
    }
    $imageArray = $parser->getImages();
    foreach ($imageArray as $image){
        $src = $image->getAttribute("src");
        $alt = $image->getAttribute("alt");
        $title = $image->getAttribute("title");
        if(!$title && !$alt){
            continue;
        }
        $src = createLink($src, $url);
        if(!in_array($src, $alreadyFoundImages)){
            $alreadyFoundImages[] = $src;
            insertImage($url, $src, $alt, $title);
        };
    }
}

function followLinks($url) {
    global $alreadyCrawled;
    global $crawling;

    $parser = new DomDocumentParser($url);//phân tích dom;
    $link_lists = $parser->getLinks();
    foreach ($link_lists as $link){
        $href = $link->getAttribute("href");//get only href
        if(strpos($href, "#") !== false){ //check url have str #
            continue;
        }else if(substr($href, 0 ,11) == "javascript:"){//check and remove javascipt
            continue;
        }
        $href = createLink($href, $url);
        if(!in_array($href, $alreadyCrawled)){
            $alreadyCrawled[] = $href;
            $crawling = $href;
            getDetails($href);
        }
    }
    array_shift($crawling);
    foreach ($crawling as $site) {
        followLinks($site);
    }
}
/*
if(isset($_POST['url'])){
    $start_url = $_POST['url'];
    followLinks($start_url);
}*/

global $con;
$query = $con->prepare("SELECT * FROM user_submitted_urls WHERE is_crawled != ':is_crawled'"); 
$query->bindParam(":is_crawled", 0);
$query->execute();
if($query->rowCount() != 0){
	while($row=$query->fetch(PDO::FETCH_OBJ)) {
		
		$last_date_crawled = $con->prepare("SELECT id, last_date_crawled, hash FROM search_index WHERE url = '".$row->url."' ORDER BY last_date_crawled DESC LIMIT 1"); 
		$last_date_crawled->execute();
		
		if($last_date_crawled->rowCount() == 0){
			// New URL
			crawl($row->url);
		}
		else{
			// Existing Site
			// Crawl if older than 14 days
			while ($last_date_crawled_result = $last_date_crawled->fetch(PDO::FETCH_OBJ)){
				if (strtotime($last_date_crawled_result->last_date_crawled) < strtotime('-'.DAYS_OLD.' days')){
					crawl($row->url);
				}
			}
		}
    }
}
else{
	echo 'No links found. Please add a site using submit-url.php before initiating a crawl.';
}

