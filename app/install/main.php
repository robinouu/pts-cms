<!DOCTYPE html>
<html lang="<?php print current_locale(); ?>" xml:lang="<?php print current_locale(); ?>">
	<head>
		<meta name="charset" content="utf8" />
		<title><?php print var_get('html/title'); ?></title>
	</head>
	<body>
		<?php print trigger('html/body'); ?>
	</body>
</html>