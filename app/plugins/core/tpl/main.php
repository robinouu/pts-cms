<ul>
	<li><a href="/admin">Tableau de bord</a></li>
<?php
$menu = trigger('plugin/core/menu', array());
foreach ($menu as $item => $link) {
	print '<li><a href="'.$link.'">'.$item.'</a></li>';
}
?>
	<li><a href="/admin/plugins">Plugins</a></li>
</ul>