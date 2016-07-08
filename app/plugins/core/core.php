<?php

var_set('core/adminRoute', '/admin');

on('plugin/core/meta', function (){
	return array(
		'name' => __('Core'),
		'description' => __('Ajoute les éléments de base à l\'administration du site.')
	);
});

function tpl_var($name, $defaultValue=null) {
	// We first try with a file template
	if( is_file(var_get('frontend/tplPath', '').$name.'.php') ){
		ob_start();
		include(var_get('frontend/tplPath', '').$name.'.php');
		$v = ob_get_contents();
		ob_end_clean();
		return $v;
	}

	// Else it can be defined in memory
	// Or in a trigger
	$back = var_get($name, $defaultValue);
	if( is_array($back) ){
		$back = trigger($name, $back);
		return $back;
	}
	return !is_null($t = trigger($name)) ? $t : $back;
}

function set_site_setting($data, $replace=false){
	$schema = new Schema(var_get('cms/schema', array()));
	$siteModel = $schema->getModel('site');
	$setting = $siteModel->using('settings', 's')->where('site.id='.var_get('website/id'))->where('s.name='.sql_quote($data['name']))->limit(1)->get();
	$data['value'] = @serialize($data['value']);
	if( $setting && $replace ){
		return $siteModel->using('settings', 's')->where('site.id='.var_get('website/id'))->where('s.name='.sql_quote($data['name']))->replace('settings', $data)->commit();
	}else if ( !$setting ){
		return $siteModel
		->where('site.id='.var_get('website/id'))
		->insert(array('settings' => $data))->commit();
	}
}

function delete_site_setting($name){
	$schema = new Schema(var_get('cms/schema', array()));
	$siteModel = $schema->getModel('site');
	return $siteModel->using('settings', 's')->delete('s')->where('site.id='.var_get('website/id'))->where('s.name='.sql_quote($name))->commit();
}

function get_site_setting($name, $defaultValue=null){
	$schema = new Schema(var_get('cms/schema', array()));
	$siteModel = $schema->getModel('site');
	$value = $siteModel->select('settings.*')->using('settings')->where('site.id='.var_get('website/id'))->where('name='.sql_quote($name))->limit(1)->get();
	$value = @unserialize($value['value']);
	if( $value ) return $value;
	return $defaultValue;
}
	
function get_site_settings(){
	$schema = new Schema(var_get('cms/schema', array()));
	$siteModel = $schema->getModel('site');
	$value = $siteModel->select('settings.*')->using('settings')->where('site.id='.var_get('website/id'))->orderBy('settings.name ASC')->get();
	return $value;
}

on('plugin/core/init', function (){
	plugin_require('response');

	route(var_get('core/adminRoute'), function (){
		var_set('html/title', 'Tableau de bord');

		on('html/body', function (){
			$html = title('Tableau de bord', 1);
			return $html;
		});

		require(dirname(__FILE__).'/tpl/header.php');
	});

	route(var_get('core/adminRoute').'/settings', function () {
		var_set('html/title', 'Configuration');

		$schema = new Schema(var_get('cms/schema', array()));
		$siteModel = $schema->getModel('site');
		$settingsModel = $schema->getModel('setting');

		if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'deleteSetting' ){
			$siteModel->using('settings', 's')->delete('s')->where('site.id='.var_get('website/id'))->where('s.id='.(int)$_REQUEST['sid'])->commit();
		}

		if( isset($_REQUEST['btnSubmit']) ){
			$settingsIDs = $siteModel->select('settings.*')->using('settings')->where('site.id='.var_get('website/id'))->get();
			foreach ($settingsIDs as &$set) {
				if( isset($_REQUEST[$set['name']]) ){
					$rep = array('value' => @serialize($_REQUEST[$set['name']]));
					$settingsModel = $settingsModel->where('id='.$set['id'])->replace('', $rep)->commit();
				}
			}
		}
		if( isset($_REQUEST['btnAdd']) ){
			$siteModel->where('site.id='.var_get('website/id'))->insert(array('settings' => array(
				'name' => $_REQUEST['new_name'],
				'title' => $_REQUEST['new_title'], 
				'description' => $_REQUEST['new_description'], 
				'value' => @serialize($_REQUEST['new_value']))))->commit();
		}

		on('html/body', function (){
			$html = title('Configuration du CMS', 1);
			$html .= p(__('Vous pouvez modifier les options de configuration de base dans ce panneau ou ajouter de nouvelles variables.'));

			$settings = get_site_settings();
			
			$html .= '<form method="POST">';
			foreach( $settings as $set ){
				//$set['value'] = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $set['value'] ); 
				$value = @unserialize($set['value']);
				if( is_array($value) ) continue;
				$html .= '<p>'.Model::generateField(new TextAreaField(array(
					'label' => isset($set['title']) ? b($set['title']) : $set['name'], 'name' => $set['name'], 'value' => $value)));
				$html .= br().i($set['description']);
				$html .= '<a href="?action=deleteSetting&sid='.$set['id'].'">X</a></p>';
			}
			$html .= p('<input type="submit" name="btnSubmit" value="Enregistrer" />');
			$html .= '</form>';

			$html .= '<form method="POST"><h2>Ajouter une variable</h2>';
			$html .= Model::generateField(new TextField(array('name' => 'new_title', 'label' => __('Intitulé'))));
			$html .= Model::generateField(new TextField(array('name' => 'new_name', 'label' => __('Identifiant'))));
			$html .= Model::generateField(new TextAreaField(array('name' => 'new_value', 'label' => __('Valeur'))));
			$html .= Model::generateField(new TextAreaField(array('name' => 'new_description', 'label' => __('Description'))));
			$html .= p('<input type="submit" name="btnAdd" value="Ajouter" />');
			$html .= '</form>';
			return $html;
		});

		require(dirname(__FILE__).'/tpl/header.php');
	});

	route(var_get('core/adminRoute').'/plugins', function (){
		var_set('html/title', 'Plugins');
		
		$schema = new Schema(var_get('cms/schema', array()));
		$siteModel = $schema->getModel('site');
		
		$plugins_loaded = get_site_setting('plugins_loaded');
		
		$truncateTablesField = new BooleanField(array('name' => 'truncateTables', 'label' => __('Supprimer les données des tables à la désinstallation.')));
		$destroyTablesField = new BooleanField(array('name' => 'destroyTables', 'label' => __('Supprimer les tables en bases de donnée à la désinstallation.')));
		
		if( isset($_REQUEST['btnSend']) ){

			$_REQUEST['plugins'][] = 'core';

			var_set('core/destroyTables', isset($_REQUEST['destroyTables']));

			$siteModel
				->replace('settings', array('value' => @serialize($_REQUEST['plugins'])))
				->where('settings.name="plugins_loaded"')
				->where('site_settings.id_site='.var_get('website/id'))
				->commit();

			// Install plugins
			foreach( $_REQUEST['plugins'] as $plugin ){
				if( !in_array($plugin, $plugins_loaded) )
					trigger('plugin/'.$plugin.'/install');
			}

			// Uninstall plugins
			foreach ($plugins_loaded as $plugin ) {
				if( !in_array($plugin, $_REQUEST['plugins']) ){
					trigger('plugin/'.$plugin.'/uninstall');
				}
			}

			$plugins_loaded = $_REQUEST['plugins'];
			redirect(var_get('core/adminRoute').'/plugins?updated');
		}
		on('html/body', function () use( $plugins_loaded, $destroyTablesField, $truncateTablesField ) {
			$html = title('Plugins installés', 1);
			$html .= '<form method="POST" class="form_plugins">';
			$plugins = glob(APP_PATH.'plugins/*');

			// Loads the plugins
			foreach ($plugins as $plugin) {
								
				$meta = trigger('plugin/'.basename($plugin, '.php').'/meta', array());

				if( isset($meta['name']) ){
					$pluginField = new BooleanField(array('name' => 'plugins[]', 'label' => $meta['name'].' : ' . $meta['description']));
					$pluginField->attributes['value'] = basename($plugin, '.php');
					$pluginField->attributes['disabled'] = basename($plugin, '.php') == 'core';
					$pluginField->attributes['checked'] = in_array($pluginField->attributes['value'], $plugins_loaded);
					$html .= '<div>'.Model::generateField($pluginField) . '</div>';
				}
			}

			$html .= p('<strong>Préférences</strong>');
			$html .= p(Model::generateField($destroyTablesField).Model::generateField($truncateTablesField));
			$html .= p('<input type="submit" name="btnSend" value="Modifier" />');
			$html .= '</form>';
			return $html;
		});
		require(dirname(__FILE__).'/tpl/header.php');
	});

});