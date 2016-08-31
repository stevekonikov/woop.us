<?php

/**

categories:
old-news
new-news
old-page
new-page
markdown?

idea:
parse the html sources, group them based on content
create an index (archive html) for news stuff
use an html template and shove all the contents into that
create an index page
create an atom feed (from archive I guess)

generate a bunch of output HTMLs :-)

**/
function plain(){
	header("Content-Type: text/plain");
}

function page_id($page) {
	return str_replace(".html", "",str_replace("old/", "",$page));
}
function html_files() {
	return array_merge(glob("*.html"), glob("old/*.html"));
}
function tidy_title($title) {
	return str_replace(" :: Woop clan","", $title);
}
$template = new DOMDocument;
$template->loadHTMLFile("template.html");
$contains_time = "<address>Posted on ";
function get_title($document) {
	return tidy_title($document->getElementsByTagName('title')[0]->nodeValue);
}
function get_page($document) {
	$arr = [];
	$content = $document->getElementById('content');
	if ( !!$document->getElementById('menu') && !!$content) {
		$arr['content'] = $content;
	} else if ( !!$content) {
		$arr['content'] = $content;
	} else {
		$arr['content'] = $document->getElementsByTagName("body")[0];
	}
	global $contains_time;
	if ( strpos($document->saveHTML(), $contains_time) !== false ) {
		$add = $document->getElementsByTagName("address")[0];
		if ( $add){
			$value = $add->nodeValue;
			if ( preg_match('/^Posted on [^,]+, ([^ ]+ [^ ]+ [^ ]+) by (.*)$/', $add->nodeValue, $m) ) {
				$arr['old timestamp'] = $value;
$arr['person'] = $m[2];
$arr['date'] = $m[1];
$arr['date parsed'] = date_parse_from_format('jS F Y', $arr['date']);
			}

	}
	}
	$title = get_title($document);
	if ( strlen($title) > 0 ) {
		$arr['title'] = $title;
	}
	return $arr;
}

$pages = html_files();
$page_id_to_html = array_combine(array_map('page_id', $pages), $pages);
function display_page($template, $page_id, $page_path) {
	$sub_doc = new DOMDocument;
	@$sub_doc->loadHTMLFile($page_path);

	$page_data = get_page($sub_doc);
	$page_data['id'] = $page_id;
	$ma = $template->getElementById('menu_active');
	if ( $ma ) { $ma->removeAttribute('id'); }
	$template_content = $template->getElementById('content');
	while($template_content->childNodes->length) { $template_content->removeChild($template_content->firstChild); }
	foreach($page_data['content']->childNodes as $childNode) {
		$our_child_node = $template->importNode($childNode, true);
		$template_content->appendChild($our_child_node);
	}
	foreach($template->getElementById("menu")->childNodes  as $menu_item) {
		if ( $menu_item instanceof DOMElement) {
		$href = $menu_item->getElementsByTagName("a")[0]->getAttribute("href");
		if ( str_replace(".html", "", $href) === $page_data['id']) {
			$menu_item->setAttribute('id', 'menu_active');
		}}
	}
	if ( isset($page_data['title'] )) {
		$template->getElementsByTagName("title")->nodeValue = $page_data['title'];
	}
}
function load_news($items) {
$newsitems = [];
	foreach($items as $item_id => $item_file) {
			$sub_doc = new DOMDocument;
	@$sub_doc->loadHTMLFile($item_file);
		$page_data = get_page($sub_doc);
		$page_data['id'] = $item_id;
		if ( isset($page_data['old timestamp']) && !empty($page_data['old timestamp']) ) {
			$newsitems[] = [
				'id' => $item_id,
				'title' => $page_data['title'],
				'timestamp' => $page_data['old timestamp'], 
				'date' => $page_data['date parsed'],
				'file' => $item_file
			];
		}
	}
	usort($newsitems, function($a, $b) { return $a['date'] <=> $b['date']; });
	return array_reverse($newsitems);

}
function display_index($template, $items) {
	$news_items = array_slice(load_news($items),0,7);
	$template_content = $template->getElementById('content');
	while($template_content->childNodes->length) { $template_content->removeChild($template_content->firstChild); }

			$sub_doc = new DOMDocument;
	foreach($news_items as $news_item) {
		$blog_post = $template->createElement("div");
		$blog_post->setAttribute("class", "blog_post");
		$template_content->appendChild($blog_post);
		$hr = $template_content->appendChild($template->createElement("hr"));
		$hr->setAttribute('class', 'ul');
		$h2 = $blog_post->appendChild($template->createElement("h2"));
		$a = $h2->appendChild($template->createElement("a"));
		$a->appendChild($template->createTextNode($news_item['title']));
		$a->setAttribute("href", $news_item['id']);
		$item_content = $blog_post->appendChild($template->createElement("div"));
		$item_content->setAttribute("class", "content");

		@$sub_doc->loadHTMLFile($news_item['file']);
		$our_sub_doc = $template->importNode($sub_doc->getElementById('content'), true);
		foreach($our_sub_doc->childNodes as $itm) {
			$cln = $item_content->appendChild($itm->cloneNode(true));
		}
	}
	$xpath = new DOMXpath($template);
	foreach($xpath->query("(//*[@id='rss_icon']) | (//div[@class='content']/h2) | (//hr[not(@class='ul')])") as $icn) {
		$icn->parentNode->removeChild($icn);
	}
	$index_item = $xpath->query("//ul[@id='menu']//a[@href='/']")->item(0);
	$index_item->parentNode->setAttribute('id', "menu_active");
	$archive_a = $template_content->appendChild($template->createElement("a"));
	$archive_a->appendChild($template->createTextNode("Archive"));
	$archive_a->setAttribute("href", "archive");
}
function display_archive($template, $items) {
	
$newsitems = load_news($items);

	$template_content = $template->getElementById('content');
	while($template_content->childNodes->length) { $template_content->removeChild($template_content->firstChild); }
$h = $template->createElement("h2");
$h->appendChild($template->createTextNode("Archive"));
	$template_content->appendChild($h);
	$ul = $template_content->appendChild($template->createElement("ul"));

	foreach($newsitems as $newsitem) {
		$li = $ul->appendChild($template->createElement("li"));
		$a = $template->createElement('a');
		$a ->appendChild($template->createTextNode($newsitem['title']));
		$a->setAttribute("href", $newsitem['id']);
		$t = $template->createTextNode($newsitem['timestamp']);
		$li->appendChild($a);
		$li->appendChild($template->createElement('br'));
		$li ->appendChild($t);
	}
	$template->getElementsByTagName("title")->nodeValue = "News Archive";
}

function generate_it($template, $items, $target_path) {
	@mkdir($target_path);
	system("rsync -r files images media $target_path/");
	foreach($items as $id => $item) {
		$path = $target_path .'/'.$id.'.html';
		display_page($template, $id, $item);
		if ( str_replace("out/out","",$path) != $path) {
			var_dump("WTF ".$path." (".$id.")");
		}
		$template->saveHTMLFile($path);
		echo "saved to $path\n";
	}
	display_archive($template, $items);
	$template->saveHTMLFile("$target_path/archive.html");
	display_index($template, $items);
	$template->saveHTMLFile("$target_path/index.html");
	echo "completed!";
}
?>