<?php


on('plugin/i18n/meta', function (){
	return array(
		'name' => __('Traductions'),
		'description' => __('Active les traductions des modules et thèmes du site.'),
	);
});

on('plugin/i18n/install', function () {

}, 9999);

on('plugin/i18n/init', function () {
	plugin_require(array('response', 'i18n'));

	on('html/adminMenu', function () {
		return array(
			__('Configuration') => array('children' => array(
				__('Localisation') => '/admin/translate')
			)
		);
	}, 100);
	
	$filename='config/'.current_locale().'.json';

	if( isset($_REQUEST['translate']) ){
		$i = 0;
		$data = array();
		foreach ($_REQUEST['tr_value'] as $v) {
			$data[$_REQUEST['tr_name'][$i]] = $v;
			++$i;
		}
		json_save($filename, $data, true);
	}

	if( current_locale() == '' )
		set_locale(preferred_locale());


	$translations = array();//translations_from_dir(ROOT_PATH.'lib/pts/lib/');
	if( is_file($filename) ){
		$translations = json_load($filename);
		load_translations(array('translations' => $translations));
	}else{
		$translations = array();
	}

	$translationsStr = translations_from_dir(ROOT_PATH.'app/plugins/');
	$translationPairs = array();
	foreach ($translationsStr as $t) {
		$translationPairs[] = array(
			'tr_name' => Model::generateField(new TextField(array(
				'name'=> 'tr_name[]',
				'value' => $t,
				'label' => $t,
				'hidden' => true
			))), 
			'tr_value' => Model::generateField(new TextField(array(
				'name'=> 'tr_value[]',
				'label' => '',
				'value' => isset($translations[$t]) ? $translations[$t] : ''
			)))
		);
	}


	route(var_get('core/adminRoute').'/translate', function () use( $translationPairs ){
		var_set('html/title', __('Traduction du site'));
		on('html/body', function () use( $translationPairs ){
			$html = title(__('Traduction du site'));
			$html .= '<form method="POST">';
			$html .= p('Traduction vers ' . current_locale());
			$html .= Model::generateField(new TextField(array('name' => 'tr_locale', 'label' => 'Locale chargée', 'value' => current_locale())));
			$html .= '<input type="submit" name="set_locale" value="Charger les traductions" />';
			$html .= '</form>';
			$html .= '<form method="POST">';
			$html .= table(array('body' => $translationPairs));
			$html .= '<input type="submit" name="translate" value="Enregistrer les traductions" />';
			$html .= '</form>';
			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');
	});
});

on('plugin/i18n/uninstall', function (){
	/*if( var_get('core/truncateTables', false) ){
		sql_truncate('data_page');
	}
	if( var_get('core/destroyTables', false) ) {
		sql_delete_table('data_page');
	}*/
});