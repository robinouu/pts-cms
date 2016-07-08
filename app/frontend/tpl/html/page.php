<?php
$html = '';
if( $pages = var_get('pages') ){
	$html .= '<div id="top" class="page wrapper style1 first">';
	foreach ($pages as $page) {
		if( $page['url'] == request_route() ){
			$html .= '<div class="content">'.$page['content'].'</div>';
		}
	}
	$html .= hr();
}
if( $articles = var_get('articles') ){
	foreach ($articles as $article) {
		$d = new DateTime($article['published_at']);
		$html .= '<div class="content"><strong>'.$article['title'].'</strong> - ' . $d->format('d/m/Y H:i') . '<br />'.$article['content'].'</div>'.hr();
	}
}
$html .= '</div>';
print $html;
?>		