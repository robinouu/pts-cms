<?php


?>
<!DOCTYPE html>
<html lang="<?php print current_locale(); ?>" xml:lang="<?php print current_locale(); ?>">
	<head>
		<title><?php print tpl_var('html/title', ''); ?></title>
		<meta charset="<?php print tpl_var('html/charset', 'utf-8'); ?>" />
		
<?php
$meta = tpl_var('html/meta', array());
foreach( $meta as $k => $m ){
	if( is_string($k) ){
		print '		<meta name="'.$k.'" content="'.$m.'" />'.PHP_EOL;
	}
}
?>

<?php
$stylesheets = tpl_var('html/stylesheets', array());
foreach ($stylesheets as $k => $media) {
	if( is_string($k) )
		print '		<link rel="stylesheet" href="'.$k.'" media="'.$media.'" />'.PHP_EOL;
	else
		print '		<link rel="stylesheet" href="'.$media.'" media="screen,tv,projection" />'.PHP_EOL;
}
?>

<?php print tpl_var('html/head'); ?>
	</head>
	<body>
<?php print tpl_var('html/header'); ?>
<?php print tpl_var('html/'.var_get('html/page', 'home')); ?>
<?php print tpl_var('html/footer'); ?>

<?php
$scripts = tpl_var('html/scripts', array());
foreach ($scripts as $k => $script) {
	if( is_string($k) )
		print '		<script type="text/javascript">'.$script.'</script>'.PHP_EOL;
	else
		print '		<script type="text/javascript" src="'.$script.'"></script>'.PHP_EOL;
}
?>
	</body>
</html>