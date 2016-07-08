<?php

var_set('models', array(
	'schema' => array(
		'model' => array(
			'fields' => array(
				'name' => new TextField(array('label' => __('Nom du modèle'))),
				'slug' => new TextField(array('name' => 'slug', 'label' => __('Identifiant du modèle'))),
				'description' => new TextField(array('maxlength' => -1, 'label' => __('Description du modèle'))),
				'fields' => new RelationField(array('data' => 'field', 'hasMany' => true)),
			)
		),
		'field' => array(
			'fields' => array(
				'tp' => new SelectField(array('datas' => array(
					'textfield' => 'Champ texte', 'textareafield' => '&Eacute;diteur de texte', 'datetimefield' => 'Date/heure', 'datefield' => 'Date', 'booleanfield' => 'Checkbox', 'emailfield' => 'Email', 'passwordfield' => 'Mot de passe', 'numberfield' => 'Nombre entier ou flottant', 'relationfield' => 'Relation'
				), 'label' => __('Type du champ'))),
				'name' => new TextField(array('name' => 'name', 'label' => __('Nom du champ'))),
				'slug' => new TextField(array('name' => 'slug', 'label' => __('Identifiant du champ'))),
				'description' => new TextField(array('maxlength' => -1, 'label' => __('Description'))),
				'options' => new TextField(array('maxlength' => -1, 'label' => __('Options du champ')))
			)
		)
	)
));

on('plugin/models/meta', function (){
	return array(
		'name' => __('Modèles de données'),
		'description' => __('Active les modèles de données et leur gestion de contenu dans l\'administration')
	);
});

on('plugin/models/install', function () {
	$schema = new Schema(var_get('models/schema'));
	$schema->generateTables();
});

on('plugin/models/uninstall', function (){
	$schema = new Schema(var_get('models/schema'));
	if( var_get('core/destroyTables', false) ) {
		return $schema->destroyTables();
	}
	if( var_get('core/truncateTables', false) ){
		return $schema->truncateTables();
	}
});

function model_field_to_field($field){
	$schema = new Schema(var_get('models/schema'));
	$field['options'] = @unserialize($field['options']);
	if( !$field['options'] ) {
		$field['options'] = array();
	}
	$fn = array_merge($field['options'], array('name' => $field['slug'], 'label' => $field['name']));
	if( $field['tp'] == 'textfield' )
		return new TextField($fn);
	if( $field['tp'] == 'textareafield' )
		return new TextAreaField($fn);
	elseif( $field['tp'] == 'datetimefield' )
		return new DateTimeField($fn);
	elseif( $field['tp'] == 'passwordfield' )
		return new PasswordField($fn);
	elseif( $field['tp'] == 'emailfield' )
		return new EmailField($fn);
	elseif( $field['tp'] == 'datefield' )
		return new DateField($fn);
	elseif( $field['tp'] == 'booleanfield' )
		return new BooleanField($fn);
	elseif( $field['tp'] == 'numberfield' )
		return new NumberField($fn);
	elseif( $field['tp'] == 'relationfield' )
		return new RelationField($fn);
}
function model_generate($model) {
	$schema = new Schema(var_get('models/schema'));
	$modelModel = $schema->getModel('model');

	$model_fields = $modelModel->select('f.*')->using('fields', 'f')->where('model.id='.$model['id'])->get();
	$db = array($model['slug'] => array('fields' => array()));
	if( $model_fields ){
		foreach( $model_fields as $mf ){
			$field = model_field_to_field($mf);
			$db[$model['slug']]['fields'][$mf['slug']] = $field;
		}
		$s = new Schema($db);
		$prefix = var_get('sql/prefix');
		var_set('sql/prefix', $prefix . 'data_');
		$s->generateTables();
		var_set('sql/prefix', $prefix);
	}
};

function model_update($model, $newModel) {
	
	$schema = new Schema(var_get('models/schema'));
	$modelModel = $schema->getModel('model');

	$dbOld = array($model['slug'] => array('fields' => array()));
	$db = array($newModel['slug'] => array('fields' => array()));
	$oldFields = $modelModel->select('f.*')->using('fields', 'f')->where('model.id='.$model['id'])->get();
	$fields = $modelModel->select('f.*')->using('fields', 'f')->where('model.id='.$newModel['id'])->get();

	if( $model_fields ){
		foreach( $model_fields as $mf ){
			$mf['options'] = @unserialize($mf['options']);
			if( !$mf['options'] ) {
				$mf['options'] = array();
			}
			$fn = array_merge($mf['options'], array('name' => $mf['slug'], 'label' => $mf['name']));
			
			if( $mf['tp'] == 'textfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new TextField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new TextField($fn);
			}elseif( $mf['tp'] == 'datetimefield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new DateTimeField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new DateTimeField($fn);
			}elseif( $mf['tp'] == 'datefield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new DateField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new DateField($fn);
			}elseif( $mf['tp'] == 'timefield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new TimeField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new TimeField($fn);
			}elseif( $mf['tp'] == 'relationfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new RelationField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new RelationField($fn);
			}elseif( $mf['tp'] == 'passwordfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new PasswordField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new PasswordField($fn);
			}elseif( $mf['tp'] == 'emailfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new EmailField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new EmailField($fn);
			}elseif( $mf['tp'] == 'booleanfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new BooleanField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new BooleanField($fn);
			}elseif( $mf['tp'] == 'numberfield' ){
				$dbOld[$m['slug']]['fields'][$mf['slug']] = new NumberField($fold);
				$db[$m['slug']]['fields'][$mf['slug']] = new NumberField($fn);
			}
		}

		$s = new Schema($dbOld);
		$prefix = var_get('sql/prefix');
		var_set('sql/prefix', $prefix . 'data_');
		$s->updateTables($db);
		var_set('sql/prefix', $prefix);

		// Remove old columns
		/*foreach( $model_fields as $mf ){
			if ($field_exists = sql_column_exists($m['slug'], $mf['slug'])) {
				sql_remove_column($tableName, $mf['slug']);
			}
		}*/
	}
};


on('plugin/models/init', function (){
	plugin_require('response');

	$schema = new Schema(var_get('models/schema'));
	$modelModel = $schema->getModel('model');
	$fieldModel = $schema->getModel('field');

	on('html/adminMenu', function () use( $modelModel ){
		$datas = array();
		$models = $modelModel->get();
		if( $models ){
			foreach( $models as $model ){
				$datas[$model['name']] = var_get('core/adminRoute').'/datas/'.$model['slug'];
			}
		}
		return array(
			__('Contenu') => array('url' => '#', 'children' => $datas),
			__('Modèles') => array(
				'url' => var_get('core/adminRoute').'/models',
				'children' => array(
					__('Nouveau') => var_get('core/adminRoute').'/models/add'
				)
			)
		);
	});

	$modelModel->crud(array(
		'route' => var_get('core/adminRoute')
	));
	$fieldModel->crud(array(
		'route' => var_get('core/adminRoute')
	));

	$models = $modelModel->get();
	if( $models ){
		foreach ($models as $m) {
			model_generate($m);
			$dataModel = new Model($m['slug']);
			$dataModel->crud(array(
				'route' => var_get('core/adminRoute'),
				'sqlPrefix' => 'data_'
			));
			route(var_get('core/adminRoute').'/datas/'.$m['slug'].'/delete/([0-9]+)', function ($req) use($m, $dataModel){
				$id = (int)$req[1];

				plugin_require('crawler');
				$url = url_website().var_get('core/adminRoute').'/'.$m['slug'].'/delete';
				$json = crawler_post_url($url, array('ids[]' => $id));

				redirect(var_get('core/adminRoute').'/datas/'.$m['slug']);
			});

			route(var_get('core/adminRoute').'/datas/'.$m['slug'].'/add', function () use($m, $dataModel){
		
				$success = false;
				$error = array();
				if( isset($_REQUEST['btnSubmit']) ){
					plugin_require('crawler');
					$url = url_website().var_get('core/adminRoute').'/'.$m['slug'].'/create';
					$json = crawler_post_url($url, $_REQUEST);

					$json = json_decode($json, true);
					if( $json['success'] )
						$success = __('Le contenu a été ajouté.');
					else
						$error[] = __('Le contenu n\'a pas pu être ajouté.');
					redirect(var_get('core/adminRoute').'/datas/'.$m['slug']);
				}

				var_set('html/title', $m['name'] . ' - ' . __('Nouveau contenu'));


				on('html/body', function () use ($m, $dataModel, $success, $error) {
					$html = title(sprintf(__('Ajout d\'un contenu de type %s'), $m['name']));
					if( $success ){
						$html .= p($success, array('class'=>'klog'));
					}
					if( sizeof($error) ){
						$html .= rtrim('<ul class="kerror"><li>'.implode('</li><li>', $error), '</li>').'</ul>';
					}
					$html .= '<form action="" method="POST" class="form_'.$m['slug'].'">';
					

					$html .= $dataModel->generateFields(array_keys($dataModel->getFields()), function (&$field){
						$field->attributes['sqlPrefix'] = 'data_';
						if( isset($field->attributes['maxlength']) && ($field->attributes['maxlength'] > 255 || $field->attributes['maxlength'] < 0 )){
							if( !isset($field->attributes['class']) ){
								$field->attributes['class'] = '';
							}
							$field->attributes['class'] .= 'editor';
						}
						return $field;
					});

					$html .= p('<input type="submit" name="btnSubmit" value="'.__('Ajouter').'" />');
					$html .= '</form>';

					return $html;
				});
				require(dirname(__FILE__).'/../core/tpl/header.php');
			});

			route(var_get('core/adminRoute').'/datas/'.$m['slug'].'/edit/([0-9]+)', function ($req) use($m, $dataModel){
			
				$did = (int)$req[1];

				$success = false;
				$error = array();
				if( isset($_REQUEST['btnSubmit']) ){
					plugin_require('crawler');
					$url = url_website().var_get('core/adminRoute').'/'.$m['slug'].'/edit/'.$did;
					$json = crawler_post_url($url, $_REQUEST);
					$json = json_decode($json, true);
					if( $json['success'] )
						$success = __('Le contenu a été modifié.');
					else
						$error[] = __('Le contenu n\'a pas pu être modifié.');
				}

				$prefix = var_get('sql/prefix');
				var_set('sql/prefix', $prefix . 'data_');
				$data = $dataModel->where('id='.$did)->limit(1)->get();
				var_set('sql/prefix', $prefix);

				$_REQUEST = array_merge($_REQUEST, $data);
			

				var_set('html/title', $m['name'] . ' - ' . __('Edition de contenu'));


				on('html/body', function () use ($m, $dataModel, $success, $error) {
					$html = title(sprintf(__('&Eacute;dition d\'un contenu de type %s'), $m['name']));
					if( $success ){
						$html .= p($success, array('class'=>'klog'));
					}
					if( sizeof($error) ){
						$html .= rtrim('<ul class="kerror"><li>'.implode('</li><li>', $error), '</li>').'</ul>';
					}
					$html .= '<form action="" method="POST" class="form-edit form_'.$m['slug'].'">';
					$html .= $dataModel->generateFields(array_keys($dataModel->getFields()), function (&$field) {
						if( isset($field->attributes['maxlength']) && ($field->attributes['maxlength'] > 255 || $field->attributes['maxlength'] < 0 )){
							if( !isset($field->attributes['class']) ){
								$field->attributes['class'] = '';
							}
							$field->attributes['class'] .= 'editor';
						}
					});
					$html .= p('<input type="submit" name="btnSubmit" value="'.__('&Eacute;diter').'" />');
					$html .= '</form>';

					return $html;
				});
				require(dirname(__FILE__).'/../core/tpl/header.php');
			});

			route(var_get('core/adminRoute').'/datas/'.$m['slug'], function () use($m){

				$prefix = var_get('sql/prefix');
				var_set('sql/prefix', $prefix . 'data_');
				$dataModel = new Model($m['slug']);
				$list = $dataModel->orderBy('id DESC')->get();
				var_set('sql/prefix', $prefix);

				/*$json = json_load(url_website().var_get('core/adminRoute').'/'.$m['slug'].'/read');
				var_dump($json);*/

				var_set('html/title', $m['name']);
				on('html/body', function () use ($list, $m, $dataModel) {
					$html = title($m['name']);
					$html .= '<form method="POST">';
					$html .= hyperlink(sprintf(__('Ajouter %s'), lcfirst($m['name'])), var_get('core/adminRoute').'/datas/'.$m['slug'].'/add');
					if( $list ){
						foreach ($list as &$l) {
							foreach( $l as $key => $value ){
								if( $key != 'id' && !in_array($key, array_keys($dataModel->getFields())) ){
									unset($l[$key]);
									continue;
								}
								else
									$l[$key] = substr($value, 0, 255);
							}
							$l['actions'] = '<a href="'.var_get('core/adminRoute').'/datas/'.$m['slug'].'/edit/'.$l['id'].'">&Eacute;diter</a> | <a href="'.var_get('core/adminRoute').'/datas/'.$m['slug'].'/delete/'.$l['id'].'">Supprimer</a>';
							unset($l['id']);
						}
						$html .= table(array(
							'caption' => sprintf(__('Liste des données de type %s'), $m['name']),
							'head' => null,
							'body' => $list,
							'foot' => null
						));
					}
					$html .= '</form>';
					return $html;
				});

				require(dirname(__FILE__).'/../core/tpl/header.php');
			});
		}
	}

	route(var_get('core/adminRoute').'/models', function () use( $modelModel ){
		$json = json_load(url_website().var_get('core/adminRoute').'/model/read');
		$list = $json['datas'];

		on('html/body', function () use ($list) {
			$html = title(__('Modèles de données'));
			if( $list ){
				foreach ($list as &$l) {
					$l['name'] = '<a href="'.var_get('core/adminRoute').'/models/edit/'.$l['id'].'">'.$l['name'].'</a>';
					$l['actions'] = '<a href="'.var_get('core/adminRoute').'/models/delete/'.$l['id'].'">Supprimer</a>';
					unset($l['id']);
				}
				$html .= '<form method="POST">';
				$html .= table(array(
					'caption' => __('Liste des modèles en base de donnée'),
					'head' => null,
					'body' => $list,
					'foot' => null
				));
				$html .= '</form>';
			}
			return $html;
		});

		require(dirname(__FILE__).'/../core/tpl/header.php');
	});

	route(var_get('core/adminRoute').'/models/delete/([0-9]+)', function ($req) use( $modelModel ){

		$id = (int)$req[1];

		plugin_require('crawler');

		$model = $modelModel->where('id='.$id)->limit(1)->get();
		$model_fields = $modelModel->using('fields', 'f')->select('f.*')->where('model.id='.$model['id'])->get();

	 	$modelModel->where('model.id='.$model['id'])->delete('fields')->commit();

		$url = url_website().var_get('core/adminRoute').'/model/delete';
		$json = crawler_post_url($url, array('ids[]' => $model['id']));

		redirect(var_get('core/adminRoute').'/models');
	});

	route(var_get('core/adminRoute').'/models/edit/([0-9]*)/field/delete/([0-9]+)', function ($req) use( $modelModel ){

		$id = (int)$req[1];
		$fid = (int)$req[2];
		$model = $modelModel->where('id='.$id)->limit(1)->get();

		$modelModel->using('fields', 'f')->delete('fields')->where('f.id IN('.$fid.')')->commit();
		
		plugin_require('crawler');
		$url = url_website().var_get('core/adminRoute').'/field/delete';
		$json = crawler_post_url($url, array('ids[]' => $fid));
		redirect(var_get('core/adminRoute').'/models/edit/'.$id);
	});

	route(var_get('core/adminRoute').'/models/edit/([0-9]*)/field/edit/([0-9]+)', function ($req) use( $modelModel, &$fieldModel ){


		$id = (int)$req[1];
		$fid = (int)$req[2];
		
		$model = $modelModel->where('id='.$id)->limit(1)->get();
		$field = $modelModel->select('f.*')->using('fields', 'f')->where('f.id='.$fid)->limit(1)->get();

		$_REQUEST['options'] = $field['options'] = @unserialize($field['options']);

		$url = url_website().var_get('core/adminRoute').'/field/edit/'.$field['id'];
		plugin_require(array('sanitize', 'crawler'));

		if( isset($_REQUEST['btnSubmit']) ){
			

			$_REQUEST['slug'] = (!trim($_REQUEST['slug']) ? slug($_REQUEST['name']) : $_REQUEST['slug']);

			if( isset($_REQUEST['option_name'], $_REQUEST['option_value']) &&
				trim($_REQUEST['option_name']) && trim($_REQUEST['option_value']) ) {
				if( is_double($_REQUEST['option_value']) )
					$_REQUEST['option_value'] = (double)$_REQUEST['option_value'];
				else if( is_numeric($_REQUEST['option_value']) )
					$_REQUEST['option_value'] = (int)$_REQUEST['option_value'];
				else
					$_REQUEST['option_value'] = $_REQUEST['option_value'];
				
				$_REQUEST['options'][$_REQUEST['option_name']] = $_REQUEST['option_value'];
			}else{
				//$_REQUEST['options'] = '';
			}
			
			$_REQUEST['options'] = @serialize($_REQUEST['options']);
			$json = crawler_post_url($url, $_REQUEST);

			/*
				Il faut aussi enregistrer la nouvelle colonne en base de donnée depuis le field enregistré.
				On modifie ses propriétés si besoin
				On modifie son nom si besoin
			*/
			$newField = $modelModel->select('f.*')->using('fields', 'f')->where('f.id='.$fid)->limit(1)->get();

			$prefix = var_get('sql/prefix');
			var_set('sql/prefix', $prefix . 'data_');
			
			$f = model_field_to_field($newField);

			// Rename ?
			$column_exists = false;
			if( $field['slug'] != $_REQUEST['slug'] ){
				$type = $f->getSQLField();
				$type = $type['type'];

				$column_exists = sql_column_exists($model['slug'], $_REQUEST['slug']);
				if( $column_exists )
					sql_remove_column($model['slug'], $_REQUEST['slug']);
				sql_rename_column($model['slug'], $field['slug'], $_REQUEST['slug'], $type);
			}else{
			
			}

			// Properties
			sql_update_column($model['slug'], $_REQUEST['slug'], $f);

			var_set('sql/prefix', $prefix);

			redirect(var_get('core/adminRoute').'/models/edit/'.$model['id'].'/field/edit/'.$field['id']);
		}elseif( isset($_REQUEST['deleteOption']) ){
			unset($_REQUEST['options'][urldecode($_REQUEST['deleteOption'])]);
			$fieldModel->replace('', array('options' => @serialize($_REQUEST['options'])))->where('id='.$field['id'])->commit();
			$options = $_REQUEST['options'];
			$_REQUEST = array_merge($_REQUEST, $field);
			$_REQUEST['options'] = $options;
		}else{
			$_REQUEST = array_merge($_REQUEST, $field);
		}


		var_set('html/title', '&Eacute;dition d\'un champ');
		on('html/body', function () use( $modelModel, $fieldModel, $model, $field ){
			$html = title(sprintf(__('&Eacute;dition du champ %s/%s'), $model['name'], $field['name']));
			$html .= '<form method="POST" class="form_models">';
			$html .= $fieldModel->generateFields(array('name', 'slug', 'tp', 'description'));

			$values = array();

			if( $_REQUEST['options'] ){
				foreach ($_REQUEST['options'] as $key => $value) {
					$values[] = array('name' => $key, 'value' => $value, '<a href="'.var_get('core/adminRoute').'/models/edit/'.$model['id'].'/field/edit/'.$field['id'].'?deleteOption='.urlencode($key).'">X</a>');
				}
			}
			$html .= table(array(
				'body' => $values,
				'foot' => '<tr><td colspan="'.sizeof(current($values)).'">'.p('Ajouter une option : ').'<div><input type="text" name="option_name" value="" placeholder="Nom de l\'option" /></div><div><textarea name="option_value" placeholder="Valeur de l\'option"></textarea></div></td></tr>'
			));

			$html .= p('<input type="submit" name="btnSubmit" value="'.__('&Eacute;diter').'" />');
			$html .= hyperlink('Retour vers ' . $model['name'], var_get('core/adminRoute').'/models/edit/'.$model['id']);
			$html .= '</form>';

			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');

	});

	route(var_get('core/adminRoute').'/models/edit/([0-9]*)/field/add', function ($req) use( $modelModel, &$fieldModel ){

		$id = (int)$req[1];
		$model = $modelModel->where('id='.$id)->limit(1)->get();

		if( isset($_REQUEST['btnSubmit']) ){
			plugin_require('crawler');
			if (!trim($_REQUEST['slug']) )
				$_REQUEST['slug'] = slug($_REQUEST['name']);
			if( trim($_REQUEST['slug']) ){
				$url = url_website().var_get('core/adminRoute').'/field/create';
				$json = crawler_post_url($url, $_REQUEST);
				$json = json_decode($json, true);
				$modelModel->insert(array(
					'fields' => array($json['id'])
				))->where('id='.$id)->commit();

				$field = $fieldModel->where('id='.$json['id'])->limit(1)->get();
				$f = model_field_to_field($field);

				$prefix = var_get('sql/prefix');
				var_set('sql/prefix', $prefix . 'data_');

				// Properties
				var_dump($model['slug'], $f->attributes['name']);
				sql_update_column($model['slug'], $_REQUEST['slug'], $f);

				var_set('sql/prefix', $prefix);
			}
			redirect(var_get('core/adminRoute').'/models/edit/'.$model['id']);
		}

		var_set('html/title', 'Nouveau champ de donnée dans ' . $model['name']);
		on('html/body', function () use( $modelModel, $fieldModel, $model ){
			$html = title(sprintf(__('Nouveau champ de donnée dans %s'), $model['name']));
			$html .= '<form action="" method="POST" class="form_models">';
			$html .= $fieldModel->generateFields(array('name', 'slug', 'tp', 'description'));
			$html .= p('<input type="submit" name="btnSubmit" value="'.__('&Eacute;diter').'" />');
			$html .= '</form>';

			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');
	});


	route(var_get('core/adminRoute').'/models/edit/([0-9]*)', function ($req) use( $modelModel ){
		
		$id = (int)$req[1];
		$model = $modelModel->where('id='.$id)->limit(1)->get();
		$success = false;
		if( isset($_REQUEST['btnSubmit']) ){
			plugin_require('crawler');
			if (!trim($_REQUEST['slug']) )
				$_REQUEST['slug'] = slug($_REQUEST['name']);
			$url = url_website().var_get('core/adminRoute').'/model/edit/'.$id;
			$json = crawler_post_url($url, $_REQUEST);
			$json = json_decode($json, true);
			if( $json['success'] )
				$success = __('Le modèle a été modifié.');
			else
				$error[] = __('Le modèle n\'a pas pu être modifié.');
		}else{
			$_REQUEST = array_merge($_REQUEST, $model);
		}

		var_set('html/title', __('Modèles de données'));

		on('html/body', function () use( $modelModel, $model, $success ){
			$html = title(sprintf(__('Édition du modèle de données "%s"'), $model['name']));
			if( $success ){
				$html .= p($success, array('class'=>'klog'));
			}
			$html .= '<form action="" method="POST" class="form_models">';

			$html .= $modelModel->generateFields(array('name', 'slug', 'description'));
			$html .= p('<input type="submit" name="btnSubmit" value="'.__('Éditer').'" />');

			$fields = $modelModel->using('fields', 'f')->select('f.*')->where('model.id='.$model['id'])->get();
			foreach( $fields as &$f ){
				$f['name'] = '<a href="'.var_get('core/adminRoute').'/models/edit/'.$model['id'].'/field/edit/'.$f['id'].'">'.$f['name'].'</a>';
				$f['actions'] = '<a href="'.var_get('core/adminRoute').'/models/edit/'.$model['id'].'/field/delete/'.$f['id'].'">Supprimer</a>';
				unset($f['options']);
			}
			$html .= title(__('Champs de données'), 2);

			$html .= hyperlink(__('Ajouter un champ'), var_get('core/adminRoute').'/models/edit/'.$model['id'].'/field/add');
			$html .= table(array(
				'caption' => __('Liste des champs de données associés au modèle.'),
				'head' => null,
				'body' => $fields,
				'foot' => null
			));
			$html .= '</form>';
			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');
	});
	
	route(var_get('core/adminRoute').'/models/add', function () use( $modelModel ){

		$success = false;
		if( isset($_REQUEST['btnSubmit']) ){
			$modelModel->insert(array(
				'name' => $_REQUEST['name'],
				'slug' => trim($_REQUEST['slug']) ? $_REQUEST['slug'] : slug($_REQUEST['name']),
				'description' => $_REQUEST['description']
			))->commit();
			$success = __('Le modèle a été ajouté en base de donnée. Vous pouvez dès à présent le configurer');
		}

		on('html/body', function () use( $modelModel, $success ){
			$html = title(__('Nouveau modèle de données'));

			if( $success ){
				$html .= p($success, array('class'=>'klog'));
			}
			$html .= '<form action="" method="POST" class="form_models">';
			foreach( $modelModel->fields as $fieldName => $field ){
				if( in_array($fieldName, array('fields')) ) continue;
				$field->attributes['name'] = $fieldName;
				$html .= tag('label', isset($field->attributes['label']) ? $field->attributes['label'] : ucfirst($fieldName), array('for' => $field->attributes['id']));
				$html .= $field->getHTMLTag();
			}
			$html .= p('<input type="submit" name="btnSubmit" value="'.__('Ajouter').'" />');
			$html .= '</form>';
			return $html;
		});
		require(dirname(__FILE__).'/../core/tpl/header.php');
	});

});
