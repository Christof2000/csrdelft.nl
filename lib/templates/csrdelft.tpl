<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="nl" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>C.S.R. Delft | {$csrdelft->getTitel()}</title>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<meta name="author" content="PubCie C.S.R. Delft" />
	<meta name="robots" content="index, follow" />
	<link rel="stylesheet" href="/layout/undohtml.css" type="text/css" />
	<link rel="stylesheet" href="/layout/default.css" type="text/css" />
	{foreach from=$csrdelft->getStylesheets() item=sheet}
		<link rel="stylesheet" href="/layout/{$sheet}" type="text/css" />
	{/foreach}
	<script type="text/javascript" src="/layout/menu.js"></script>
	<script type="text/javascript" src="/layout/csrdelft.js"></script>
	{foreach from=$csrdelft->getScripts() item=script}
		<script type="text/javascript" src="/layout/{$script}"></script>
	{/foreach}	
	<link rel="alternate" title="C.S.R. Delft RSS" type="application/rss+xml" href="http://csrdelft.nl/communicatie/forum/rss.xml" />
	<link rel="shortcut icon" href="{$csr_pics}layout/favicon.ico" />
</head>
<body>
<div id="container">
	{$csrdelft->_menu->view()}
	<div id="main">
		{if $csrdelft->_zijkolom!==false}
			<div id="mainleft">
				{if is_object($csrdelft->_zijkolom)}
					<div class="block">
						{$csrdelft->_zijkolom->view()}
					</div>
				{else}
					{section name=object loop=$csrdelft->_zijkolom}
						<div class="block">
							{$object->view()}
						</div>
					{/section}
				{/if}
			</div>
		{/if}
		<div id="mainright">
			{$csrdelft->_body->view()}
		</div>
		<div id="footer">
			Gemaakt door <a href="mailto:pubcie@csrdelft.nl" title="PubCie der C.S.R. Delft">PubCie der C.S.R. Delft</a> | <a href="http://validator.w3.org/check/referrer" title="Valideer">XHTML 1.0</a>
		</div>	
	</div>
</div>

</body>

</html>
