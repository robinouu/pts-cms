<div id="header">

	<img src="/img/pts.jpg" style="float: left" />

	<nav id="nav" role="navigation">
		<ul class="menu inline container">
			<li><a href="/"><?php print __('Accueil'); ?></a></li>
			<li><a href="https://github.com/robinouu/php-tool-suite/archive/master.zip"><?php print __('Télécharger'); ?></a></li>
			<li><a href="https://github.com/robinouu/php-tool-suite"><?php print __('GitHub'); ?></a></li>
			<li><a href="/documentation"><?php print __('Documentation'); ?></a></li>
			<li><a href="/licence"><?php print __('Licence'); ?></a></li>
			<li><a href="/contact"><?php print __('Contact'); ?></a></li>
		<?php
		$menu = trigger('html/mainMenu', array());
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
		</ul>
	</nav>

</div>