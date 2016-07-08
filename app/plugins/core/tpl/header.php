<!DOCTYPE html>
<html lang="<?php print current_locale(); ?>" xml:lang="<?php print current_locale(); ?>">
	<head>
		<meta name="charset" content="utf8" />
		<title><?php print var_get('html/title') . ' - Administration - ' . get_site_setting('sitename'); ?></title>

		<link rel="stylesheet" href="/css/karma.css" media="all">
		<link rel="stylesheet" href="/css/backend.css" media="screen,tv,projection">

		<link href="/js/jquery.datetimepicker.css" rel="stylesheet">

	</head>
	<body>
		<img src="/img/pts.jpg"  alt="Logo de PHP Tool Suite" style="float: left;display:block;" height="50" />
		
	<?php if( var_get('html/adminMenu', true) ){ ?>
		<ul class="menu inline">
			<li><a href="/admin">Tableau de bord</a></li>
		<?php
		$menu = trigger('html/adminMenu', array());
		foreach ($menu as $item => $link) {
			if( is_array($link) ){
				print '<li><a href="'.$link['url'].'">'.$item.'</a><ul class="lvl2">';
				foreach( $link['children'] as $item => $l ){
					print '<li><a href="'.$l.'">'.$item.'</a></li>';
				}
				print '</ul></li>';
			}else{
				print '<li><a href="'.$link.'">'.$item.'</a></li>';
			}
		}
		?>
			<li><a href="/admin/settings">Configuration</a></li>
			<li><a href="/admin/plugins">Plugins</a></li>
			<li><a href="/">Voir le site</a></li>
		</ul>
	<?php } ?>
		<?php print trigger('html/body'); ?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="//cdn.ckeditor.com/4.5.9/full/ckeditor.js"></script>
		<script src="/js/jquery.datetimepicker.min.js"></script>
		<script src="/js/all.js"></script>
	</body>
</html>