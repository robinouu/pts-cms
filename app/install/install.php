<?php

plugin_require(array('response', 'file'));

var_set('install', array(
	'defaultTheme' => 'frontend'
));

route('/install', function (){

	var_set('html/title', 'Installation du site');
	$sitenameField = new TextField(array('name' => 'sitename', 'label' => 'Nom du site'));
	$hostField = new TextField(array('name' => 'host', 'label' => 'Nom de l\'hôte', 'value' => 'localhost'));
	$usernameField = new TextField(array('name' => 'user', 'label' => 'Nom de l\'utilisateur', 'value' => 'root'));
	$passwordField = new PasswordField(array('name' => 'password', 'label' => 'Mot de passe', 'value' => ''));
	$dbField = new TextField(array('name' => 'db', 'label' => 'Nom de la base', 'value' => 'cms'));
	
	$step = 1;
	if( isset($_REQUEST['btnSend']) ){
		$sql = sql_connect($dbSettings = array(
			'host' => $_REQUEST['host'],
			'db' => $_REQUEST['db'],
			'user' => $_REQUEST['user'],
			'pass' => $_REQUEST['password']));

		if( $sql ){

			$_REQUEST['plugins'][] = 'core';

			// Updates the config file with new db settings
			plugin_require('fs');
			mkdir_recursive(ROOT_PATH.'config');
			if( !is_file(ROOT_PATH.'config/config.json') )
				$settings = array();
			else
				$settings = json_load(ROOT_PATH.'config/config.json');
			$settings['db'] = $dbSettings;
			json_save(ROOT_PATH.'config/config.json', $settings, true);


			// Install CMS database
			$schema = new Schema(var_get('cms/schema', array()));
			$schema->generateTables();

			$siteModel = $schema->getModel('site');
			$siteModel->insert(array(
				'domain' => url_website(root_url()),
				'settings' => array(
					array('name' => 'sitename', 'value' => $_REQUEST['sitename']),
					array('name' => 'theme', 'value' => var_get('install/defaultTheme')),
					array('name' => 'plugins_loaded', 'value' => @serialize($_REQUEST['plugins']))
				)
			))->commit();

			// Install plugins
			foreach( $_REQUEST['plugins'] as $plugin ){
				trigger('plugin/'.$plugin.'/install');
			}

			on('html/body', function (){
				$html = title('Installation terminée');
				$html .= p('Et voilà ! Votre site est maintenant installé. Vous pouvez supprimer le dossier app/install');
				return $html;
			});

			$step = 2;

		}
	}

	if( $step == 1 ){
		on('html/body', function () use( $sitenameField, $dbField, $hostField, $usernameField, $passwordField){
			$html = title('Installation du site');

			$html .= '<form method="POST">';
			$html .= p('Bienvenue sur votre nouveau site. Vous allez devoir paramétrer quelques informations de base avant de pouvoir utiliser la gestion de contenu.');

			$html .= title('Etape 1 - Informations du site', 2);
			$html .= Model::generateField($sitenameField);

			$html .= title('Etape 2 - Plugins', 2);
			$html .= p('Choisissez les plugins essentiels à votre site applicatif');
			$plugins = glob(APP_PATH.'plugins/*');
			
			foreach ($plugins as $plugin){
				if( basename($plugin, '.php') == 'core' ) continue;

				$meta = trigger('plugin/'.basename($plugin, '.php').'/meta', array());

				if( isset($meta['name']) ){
					$pluginField = new BooleanField(array('name' => 'plugins[]', 'label' => $meta['name'].' : ' . $meta['description']));
					$pluginField->attributes['value'] = basename($plugin, '.php');
					$pluginField->attributes['disabled'] = basename($plugin, '.php') == 'core';
					$html .= '<div>'.Model::generateField($pluginField) . '</div>';
				}
			}

			$html .= title('Etape 3 - Base de données', 2);
			$html .= p('Pour que le site puisse se connecter à la base de donnée, veuillez entrer les identifiants suivants');
			$html .= Model::generateField($hostField);
			$html .= Model::generateField($usernameField);
			$html .= Model::generateField($passwordField);
			$html .= Model::generateField($dbField);


			$html .= p('<input type="submit" name="btnSend" value="Valider" />');
			$html .= '</form>';

			return $html;
		});
	}

	require_once(APP_PATH.'install/main.php');
});