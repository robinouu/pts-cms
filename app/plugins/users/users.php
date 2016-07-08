<?php

var_set('users', array(
	'signinRoute' => '/signin',
	'defaultAccount' => array(
		'username' => 'admin',
		'auth' => array(
			'provider' => 'internal',
			'secret' => sha1('admin')
		)
	),
	'schema' => array(
		'auth' => array(
			'fields' => array(
				'provider' => new TextField(array('maxlength' => 40)),
				'secret' => new PasswordField(array('name' => 'secret', 'label' => __('Mot de passe'))),
			)
		),
		'user' => array(
			'fields' => array(
				'username' => new TextField(array('name' => 'username', 'label' => __('Nom d\'utilisateur'), 'maxlength' => 40)),
				'auth' => new RelationField(array('data' => 'auth', 'hasMany' => true)),
				'settings' => new RelationField(array('data' => 'setting', 'hasMany' => true))
			)
		)
	)
));

on('plugin/users/meta', function (){
	return array(
		'name' => __('Utilisateurs'),
		'description' => __('Active la gestion des utilisateurs sur le site.')
	);
});

on('plugin/users/install', function () {

	$schema = new Schema(var_get('users/schema'));
	$schema->generateTables();

	$userModel = $schema->getModel('user');
	$userModel->insert(var_get('users/defaultAccount'))->commit();
});

on('plugin/users/uninstall', function (){
	$schema = new Schema(var_get('users/schema'));
	if( var_get('core/destroyTables', false) ) {
		return $schema->destroyTables();
	}
	if( var_get('core/truncateTables', false) ){
		return $schema->truncateTables();
	}
});

on('plugin/users/init', function () {
	$schema = new Schema(var_get('users/schema'));
	$schema->getModel('user')->crud(array(
		'route' => 'admin',
		'method' => 'POST',
	));
	route(var_get('users/signinRoute', ''), function () use ($schema) {
		$userModel = $schema->getModel('user');
		$authModel = $schema->getModel('auth');
		$error = array();

		if( isset($_REQUEST['btnSignin'], $_REQUEST['username'], $_REQUEST['secret']) ){
			if( !isset($_REQUEST['username']) || !trim($_REQUEST['username']) )
				$error[] = __('Veuillez entrer le champ "' . $userModel->getField('username')->getFieldName() . '".');
			elseif( !isset($_REQUEST['secret']) || !trim($_REQUEST['secret']) )
				$error[] = __('Veuillez entrer le champ "' . $authModel->getField('secret')->getFieldName() . '".');
			if( !sizeof($error) ){
				$user = $userModel->using('auth', 'a')
					->where('username='.sql_quote($_REQUEST['username']))
					->where('a.secret='.sql_quote(sha1($_REQUEST['secret'])))
					->where('a.provider="internal"')
					->limit(1)
					->get();
				if( $user ){
					session_var_set('users/userLogged', $user);
					redirect(var_get('core/adminRoute'));
				}else{
					$error[] = __('Mauvais identifiants.');
				}
			}
		}

		var_set('html/mainMenu', false);

		on('html/body', function () use($userModel, $authModel, $error){
			$html = '';
			$html .= title('Administration');
			$html .= title('Connexion', 2);

			$html .= '<form method="POST">';
			$schema = new Schema(var_get('users/schema'));
			$html .= '<p class="error">'.implode(br(), $error).'</p>';
			$html .= Model::generateField($userModel->getField('username'));
			$html .= Model::generateField($authModel->getField('secret'));
			$html .= p('<input type="submit" name="btnSignin" value="'.__('Se connecter').'" />');
			$html .= '</form>';

			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');
	});

	on('error', function ($data) {
		if( isset($data['ctx']) && $data['ctx'] == 'adminRoute' ){
			redirect(var_get('users/signinRoute', '/'));
		}
	});
	
	route(var_get('core/adminRoute', '').'.*', function (){

		if( !session_var_get('users/userLogged') ){
			trigger('error', array('ctx' => 'adminRoute', 'msg' => __('User is not logged.')));
			die;
		}
	});
});

