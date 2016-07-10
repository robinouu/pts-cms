<?php
/**
 * Configuration du template de base
 */

var_set('html', array(
	'title' => 'CMS - PHP Tool Suite',
	'meta' => array(
		'viewport' => 'width=device-width, initial-scale=1.0',
		'author' => 'Robin RUAUX',
		'description' => 'CMS basé sur le framework PHP Tool Suite, permet de configurer un site ou une application PHP.',
		'generator' => 'PHP Tool Suite (CMS)',
	),
	'stylesheets' => array('/css/karma.css', '/css/backend.css', '/js/jquery.datetimepicker.css'),
	'scripts' => array('https://code.jquery.com/jquery-2.2.4.min.js', '/js/jquery.datetimepicker.min.js', '/js/all.js')
));

var_set('frontend/tplPath', dirname(__FILE__).'/tpl/');

route('/', function (){

	$schema = new Schema(var_get('models/schema'));
	try {
		$modelModel = $schema->getModel('model');

		$pageModelData = $modelModel->where('slug="page"')->limit(1)->get();
		$pageModel = new Model($pageModelData['slug']);

		$articleData = $modelModel->where('slug="article"')->limit(1)->get();
		$articleModel = new Model($articleData['slug']);

		$prefix = var_get('sql/prefix');
		var_set('sql/prefix', 'data_');
		var_set('pages', $pageModel->get());
		var_set('articles', $articleModel->get());
		var_set('sql/prefix', $prefix);

		var_set('html/title', __('Accueil') . ' - ' . get_site_setting('sitename'));
		var_set('html/page', 'page');

	}catch(Exception $e){

	}

	require_once(dirname(__FILE__).'/tpl/html.php');
});

route('/docs/(.*)', function ($req){
	plugin_require('sanitize');
	$slug = slug(str_replace('.', '-', $req[1]));
	$file = dirname(__FILE__).'/../../docs/html/'.$slug.'.html';
	if( is_file($file) ){
		var_set('html/title', $slug . ' - ' . get_site_setting('sitename'));
		var_set('html/page', 'docs');
		on('html/docs', function () use ($file) { return '<div class="page">'.file_get_contents($file).'</div>'; } );
	}
	require_once(dirname(__FILE__).'/tpl/html.php');
});

response_no_route(function (){

	try {
		$schema = new Schema(var_get('models/schema'));
		$modelModel = $schema->getModel('model');

		$pageModelData = $modelModel->where('slug="page"')->limit(1)->get();
		$pageModel = new Model($pageModelData['slug']);

		$prefix = var_get('sql/prefix');
		var_set('sql/prefix', 'data_');
		var_set('pages', $pages = $pageModel->get());
		var_set('sql/prefix', $prefix);

		if( $pages ){
			foreach ($pages as $page) {
				if( $page['url'] == request_route() ){
					on('html/body', function () use ($page) { return '<div class="page">'.$page['content'].'</div>'; } );
					var_set('html/page', 'page');
					var_set('html/title', $page['title'] . ' - ' . get_site_setting('sitename'));
				}
			}
		}else{
			response_code(404);
		}
	}catch(Exception $e){
		
		
	}

	require_once(dirname(__FILE__).'/tpl/html.php');
});