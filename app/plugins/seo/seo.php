<?php


on('plugin/seo/meta', function (){
	return array(
		'name' => __('SEO'),
		'description' => __('Active les fonctionnalités de référencement naturel sur le site.'),
	);
});

on('plugin/seo/init', function () {

	$id = get_site_setting('seo/googleAnalyticsID');
	if( $id ){
		$googleAnalyticsFunc = "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga('create', '".$id."', 'auto');
				ga('send', 'pageview');";

		on('html/scripts', function () use ($googleAnalyticsFunc) { return array('ga' => $googleAnalyticsFunc); });
	}

	// OG Title
	$sitename = get_site_setting('sitename');
	$url_website = url_website();
	on('html/meta', function () use ($url_website, $sitename) { return array(
		'og:type' => 'website',
		'og:url' => $url_website,
		'og:image' => get_site_setting('logo'),
		'og:site_name' => $sitename,
		'og:description' => var_get('html/meta/description'),
		'og:title' => var_get('html/title', ''),
		'twitter:card' => 'summary',
		'twitter:site' => get_site_setting('seo/twitterCard/site'),
		'twitter:title' => var_get('html/title', ''),
		'twitter:description' => var_get('html/meta/description'),
		'twitter:image' => get_site_setting('logo')
	); });

	on('html/head', function ()  use ($url_website, $sitename)  {
?>
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "WebSite",
  "name": "<?php print $sitename; ?>",
  "url": "<?php print $url_website; ?>"
}

</script>
<?php
	});
});

on('plugin/seo/install', function () {
	set_site_setting(array(
		'name' => 'seo/googleAnalyticsID',
		'title' => __('Google Analytics ID'),
		'description' => __('Il s\'agit de votre code de suivi lié à votre site internet, que vous trouverez dans l\'administration de <a href="https://analytics.google.com/analytics/web/" target="_blank">Google Analytics</a>.'),
		'value' => ''
	));
	set_site_setting(array(
		'name' => 'seo/twitterCard/site',
		'title' => __('Twitter'),
		'description' => __('Votre identifiant Twitter lié à votre site internet ou organisation'),
		'value' => ''
	));

}, 9999);

on('plugin/seo/uninstall', function (){
	delete_site_setting('seo/googleAnalyticsID');
});