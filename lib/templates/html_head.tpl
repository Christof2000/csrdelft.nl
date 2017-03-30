<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="index, follow" />
<meta name="author" content="PubCie C.S.R. Delft" />
<meta name="description" content="{Instellingen::get('stek', 'beschrijving')}">
<meta name="google-site-verification" content="zLTm1NVzZPHx7jiGHBpe4HeH1goQAlJej2Rdc0_qKzE" />
<meta name="apple-itunes-app" content="app-id=1112148892, app-argument={$smarty.const.CSR_ROOT}{$REQUEST_URI}">
<meta property="og:url" content="{$smarty.const.CSR_ROOT}{$REQUEST_URI}" />
<meta property="og:title" content="C.S.R. Delft | {$titel}" />
<meta property="og:locale" content="nl_nl" />
<meta property="og:image" content="{$smarty.const.CSR_ROOT}/assets/layout/plaetjes/beeldmerk.png" />
<meta property="og:description" content="{Instellingen::get('stek', 'beschrijving')}" />
<title>C.S.R. Delft - {$titel}</title>
<link rel="shortcut icon" href="{$smarty.const.CSR_ROOT}/assets/layout/plaetjes/favicon.ico" />
<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="{$smarty.const.CSR_ROOT}/forum/rss.xml" />
{foreach from=$stylesheets item=sheet}
<link rel="stylesheet" href="{$sheet}" type="text/css" />
{/foreach}
{foreach from=$scripts item=script}
<script type="text/javascript" src="{$script}"></script>
{/foreach}
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-19828019-4']);
	_gaq.push(['_trackPageview']);
	(function () {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ga, s);
	})();
</script>