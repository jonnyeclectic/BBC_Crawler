<?php include_once('simple_html_dom.php'); 	//file for getting html of site
//Programmer: Jonathan Reyes
//Course: CSCE 4444
//Program: Web crawler that returns headlines of catagories for BBC.com
define("htmlHyperlink", '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>');

$site = 'http://www.bbc.com'; 				//prepend tag
$output_file = 'BBC.txt';		//store results in this file
$urlStack = array();						//stack of url results
$checked_urls = array();					//temporary stack of all urls (duplicates, junk, and irrelevant urls too)
$found = false;								//This will help us sort out which urls we want or not

function crawl($category){
 global $site, $found;
 $html = file_get_html($site . '/news/' . $category . '/');	//Store page html
 
 foreach($html->find("a") as $tag){			 		//Parse through html of page
 	$article = article($category, $tag->href);		//Check if cateogry is apart of the tag
 	display($article, $site);				 		//Make url clean and present it with headline
 }
}


function article($category, $tag){ 						//If this is a political/tech/entertainment site article
	global $found;
	global $site;
	
	if($category === 'science_and_environment'){
		$subject = 'science-environment';
	}
	elseif($category === 'entertainment_and_arts'){
		$subject = 'entertainment-arts';
	}
	else{
		$subject = $category;
	}
	
	if(strpos($tag, $subject . '-') !== false && strpos($tag, $site) === false && strpos($tag, '/news/magazine') === false && strpos($tag, 'player') === false){	//If url has category in its name and is not a video
		$found = true;							 		//This will help us decide if we should print the url and make sure we only do it once.
		return $tag;
	}
	else{
		$found = false;									//Do not save this url, it is not relevant or it is a video
		return 0;										//Return zero so we know to skip this url.
	}
}
 
function make_links_clickable($url)
{														//This uses html to make the URL clickable hyperlinks
   return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $url);
														//This is a regular expression that uses html
} 

function unique($url, $copy){
	global $urlStack, $checked_urls, $site, $found;
	
	if(isset($checked_urls[$url]) === true )  			//if we already found this url, then return. (Use isset() to confirm we have array index)
		return false;
	
	$checked_urls[$url] = $found;						//did we find articles for this subject?
	if($checked_urls[$url]){							//if we did, log the url so we don't have duplicates.
		array_push($urlStack,$url."\n"); 				//store this url onto stack of unique urls.
		$html = file_get_html($copy);					//store html of url ($copy is the regular url, not the hyperlink)
		foreach($html->find('h1[class="story-header"]') as $tag){	//Parse through html of page and return headers/titles of articles
			$hyperlinkTitle = '<font size="1" face="helvetica"><a href="'.$copy.'" >'.$tag.'</a></font>';}		//html makes the hyperlink display the article title with font manipulation in html
		if(isset($hyperlinkTitle) === true )  			//if we already found this url, then return. (Use isset() to confirm we have array index)
			print $hyperlinkTitle;

		foreach($html->find('p[id="story_continues_1"]') as $tag){	//Parse through html of page and return headers/titles of articles
					$headlinesStory = '<font size="2" face="helvetica"><b>'.$tag.'</b></font>';}		//html makes the hyperlink display the article title with font manipulation in html
		if(isset($headlinesStory) === true )  			//if we already found this url, then return. (Use isset() to confirm we have array index)
			print $headlinesStory;
		else{
			foreach($html->find('p[class="introduction"]') as $tag){	//Parse through html of page and return headers/titles of articles
					$headlines = '<font size="2" face="helvetica"><b>'.$tag.'</b></font>';}		//html makes the hyperlink display the article title with font manipulation in html
			if(isset($headlines) === true )  			//if we already found this url, then return. (Use isset() to confirm we have array index)
				print $headlines;
			else{
				foreach($html->find("p") as $tag){	//Parse through html of page and return headers/titles of articles
					$headline = '<font size="2" face="helvetica"><b>'.$tag.'</b></font>';}		//html makes the hyperlink display the article title with font manipulation in html
				if(isset($headline) === true && $headline[0] !== '<')  			//if we already found this url, then return. (Use isset() to confirm we have array index)
					print $headline;
				}
		}
		
		return true;
	}
	else 
		return false;
}

function display($article, $copy){	
	global $site;
	if($article !== 0){											//If we found an article url, prepare link.
		if(strpos($article, 'www') !== false){ 					//If url already has proper extenstion:
			$copy = $article;									//Makes a copy of the article url before making it a hyperlink
			unique(make_links_clickable($article), $copy);} 	//Check if duplicate, make hyperlink, and print
		else if($article !== ""){								   //Prepend url with http://www.cnn.com
			$copy = $site . $article;							   //Makes a copy of the article url before making it a hyperlink
			unique(make_links_clickable($site . $article), $copy); //Check if duplicate, make hyperlink, and print
		}		
	}
}

function pull_list(){											//List of subjects we are interested in
	?><body font="Helvetica" bgcolor="#90EE90"><small>Programmer: Jonathan Reyes <br>Read the full article by clicking on the title hyperlink</br></small><h1>Health</h1><?php								//Colors the background light green*/
	crawl('health');

}

pull_list();													  //Main
foreach($urlStack as $url) {
	file_put_contents($output_file, $url . "<br/>", FILE_APPEND); //Write urls that we searched for to output file
} ?>
