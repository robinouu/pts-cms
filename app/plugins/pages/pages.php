<?php


on('plugin/pages/meta', function (){
	return array(
		'name' => __('Pages statiques'),
		'description' => __('Active les pages de contenu sur le site.'),
		'require' => array('models')
	);
});

on('plugin/pages/install', function () {

	$schema = new Schema(var_get('models/schema'));

	$modelModel = $schema->getModel('model');
	$fieldModel = $schema->getModel('field');

	$modelModel->insert(array(
		'name' => __('Page'),
		'slug' => __('page'),
		'description' => __('Les pages sont des éléments statiques affichés dans les thèmes selon l\'URL visitée.'),
		'fields' => array(
			array('tp' => 'textfield', 'name' => __('Intitulé'), 'slug' => __('title'), 'description' => __('Titre SEO de la page.')),
			array('tp' => 'textfield', 'name' => __('URL de la page'), 'slug' => __('url'), 'description' => __('URL à laquelle la page va répondre.')),
			array('tp' => 'textareafield', 'name' => __('Contenu'), 'slug' => __('content'), 'description' => __('Contenu de la page'))
		)
	))->commit();

}, 9999);

on('plugin/pages/uninstall', function (){
	$schema = new Schema(var_get('users/schema'));
	$modelModel = $schema->getModel('model');
	$modelModel->using('fields', 'f')->delete('fields')->delete()->where('model.slug="page"')->commit();

	if( var_get('core/truncateTables', false) ){
		sql_truncate('data_page');
	}
	if( var_get('core/destroyTables', false) ) {
		sql_delete_table('data_page');
	}
});