<?php
session_start();

define('ROOT_PATH', dirname(__FILE__).'/');
define('APP_PATH', ROOT_PATH.'app/');

require_once('lib/pts/lib/core.inc.php');
//require_once('pts.phar');
plugin_require('i18n');
if( current_locale() == '' ){
	set_locale(preferred_locale());
}

plugin_require(array('sql', 'field', 'model', 'file', 'request'));


var_set('cms/schema', array(
	'setting' => array(
		'fields' => array(
			'name' => new TextField(),
			'title' => new TextField(),
			'description' => new TextAreaField(),
			'value' => new TextField(array('maxlength' => -1))
		)
	),
	'site' => array(
		'fields' => array(
			'domain' => new TextField(),
			'settings' => new RelationField(array('data' => 'setting', 'hasMany' => true))
		)
	)
));

if( is_file(ROOT_PATH.'config/config.json') )
	$settings = json_load(ROOT_PATH.'config/config.json');
if( isset($settings) && isset($settings['db']) ){
	sql_connect($settings['db']);

	// Loads the website informations
	$schema = new Schema(var_get('cms/schema'));
	$siteModel = $schema->getModel('site');
	var_set('website', $website = $siteModel->where('domain='.sql_quote(url_website()))->limit(1)->get());
	if( !$website ) {
		log_var('Website could not be found on the database. Make sure you installed it at : ' . url_website() . '/install');
		die;
	}

	// Install application libraries
	ob_start();
	plugin_load_dir(dirname(__FILE__).'/app/plugins');
	$content = ob_get_contents();
	ob_end_clean();

	$plugins_loaded = get_site_setting('plugins_loaded');
	if( !$plugins_loaded ){
		$plugins_loaded = array('core');
		set_site_setting(array('name' => 'plugins_loaded', 'value' => $plugins_loaded));
	}
	foreach ($plugins_loaded as $plugin) {
		trigger('plugin/'.$plugin.'/init');
	}
}

if( is_file($installPath = dirname(__FILE__).'/app/install/install.php'))
	plugin_load_file($installPath);

plugin_load_file(dirname(__FILE__).'/app/frontend/indexController.php');