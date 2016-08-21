<?php
require_once("generate.php");
$page_id = str_replace(".html", "",substr($_SERVER['REQUEST_URI'], 1));

if ( isset($page_id_to_html[$page_id])) {
	display_page($template, $page_id, $page_id_to_html[$page_id]);
	echo $template->saveHTML();
}
else if ( $page_id == '' ) {
	display_index($template, $page_id_to_html);
	echo $template->saveHTML();
} else if ( $page_id == 'archive') {
	display_archive($template, $page_id_to_html);
	echo $template->saveHTML();
} else if ( $page_id == 'sitemap') {
	display_sitemap($template, $page_id_to_html);
	echo $template->saveHTML();
} else if ( $page_id =='generate') {
	generate_it($template, $page_id_to_html, 'out');
}
else {return false;}
?>
