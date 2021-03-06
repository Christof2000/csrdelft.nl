@extends('layout-extern.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern')
@endsection

@section('oweebanner')
	<a href="/lidworden" class="owee-banner">
		<div class="logo">
			<img src="/images/owee/owee2020.svg" alt="C.S.R. - Machtig Mooi">
		</div>
		<div class="tekst">
			<p>Kom je volgend jaar in Delft studeren?
				<br>Lees hier alles over de OWee en lid worden bij C.S.R.</p>
			<div><span>Alles over de </span>OWee & lid worden</div>
		</div>
	</a>
	<script>
		document.body.className += ' ' + 'met-owee-banner';
	</script>
@endsection

@section('body')
	<!-- Banner -->
	<section id="banner">
		<div class="inner">
			<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
			<h1>C.S.R. Delft</h1>
		</div>
	</section>

	<!-- Wrapper -->
	<section id="wrapper">

		<!-- One -->
		<section id="one" class="wrapper first kleur1">
			<div class="inner">
				<span class="image"><img src="/fotoalbum/Publiek/Voorpagina/_resized/CSR_Delft.jpg" alt="Foto vereniging"/></span>
				<div class="content">
					<h2 class="major">C.S.R. Delft</h2>
					<p>De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in
						Delft, rijk aan tradities die zijn ontstaan in haar {{vereniging_leeftijd()}}-jarig bestaan. Het is een breed gezelschap
						van zo'n 220 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke
						eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede
						vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen
						uithalen.
					</p>
					<a href="/vereniging" class="special">Lees meer over C.S.R.</a>
				</div>
			</div>
		</section>

		<!-- Two -->
		<section id="two" class="wrapper alt kleur2">
			<div class="inner">
				<div class="content">
					<div class="een-minuut">
						<div id="hero">
							<h1 class="major">C.S.R. IN 1 MINUUT</h1>
							<noscript class="lazy-load">
							<div class="bb-video">
								<iframe src="https://www.youtube-nocookie.com/embed/AE8RE8e5qI4?hl=nl" title="C.S.R. Delft in 1 minuut" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
							</div>
							</noscript>
						</div>
						<noscript class="lazy-load">
							<div class="sociaal">
								<div class="youtube">
									<p>Wil je zien hoe
										de vereniging in elkaar zit? Bekijk de serie 'Delft studie is maar de helft!'</p>
									<div class="bb-video">
										<iframe src="https://www.youtube-nocookie.com/embed/a7hhtoo_kzY?hl=nl" title="Delft studie is maar de helft! Aflevering 1 uit 3" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
									</div>
								</div>
								<div class="instagram">
									<p>Bekijk en volg ook ons Instagram account voor meer van onze vereniging!</p>
									<div class="insta-grid">
										<a href="https://www.instagram.com/csrdelft/"><img src="/dist/images/instagram.svg" alt="Instagram logo in C.S.R. kleuren"></a>
										<p id="insta-tag">@csrdelft</p>
									</div>
								</div>
							</div>
						</noscript>

						<p>
							De OWee gaat er anders uitzien, voor meer informatie zie de <a href="https://owee.nl">OWee website</a>.<br>
							Maar wij willen je wel vast digitaal een beetje van C.S.R. laten zien.
							We willen jou daar goede handvaten voor geven. De belangrijkste informatie wordt gedeeld via deze website dus kijk even rond.
							Wil je meer weten? Kijk op ons <a href="https://www.youtube.com/user/CivitasFilms">YouTube kanaal</a>,
							onze <a href="https://www.instagram.com/csrdelft">Instagram pagina</a> en de <a href="https://www.owee.nl"> OWee </a> website.
						</p>
						<p>We hopen je snel te kunnen spreken, digitaal of op anderhalve meter.</p>
						<p>Liefs,<br>OWeeCie en PromoCie</p>
					</div>
				</div>
			</div>
		</section>

		<section id="three" class="wrapper kleur3">
			<div class="inner">
				<noscript class="lazy-load">
					<span class="image"><img src="/fotoalbum/Publiek/Voorpagina/_resized/CSR_in_de_OWee.jpg" alt="Sfeerfoto buiten"/></span>
				</noscript>
				<div class="content">
					<h2 class="major">C.S.R. in de OWee</h2>
					<p>Ter aanvang van elk studiejaar wordt er een OntvangstWeek georganiseerd, ofwel de OWee.
						Tijdens deze week is er de uitgelezen kans om kennis te maken met de universiteit en hogescholen,
						studieverenigingen, studentenverenigingen, sport- en cultuurcentrum, kerken en nog veel meer! Al
						deze groepen zullen zichzelf tijdens deze week op verschillende momenten en manieren presenteren.
						Ook C.S.R. doet mee aan deze gezellige week. Van 14 t/m 16 augustus zijn wij online te zien en van
						17 t/m 22 augustus heb je de gelegenheid om fysiek de sfeer van C.S.R. en Delft te proeven!</p>
				</div>
			</div>
		</section>

		<!-- Four -->
		<section id="four" class="wrapper alt kleur4">
			<div class="inner">
				<noscript class="lazy-load">
					<span class="image"><img src="/fotoalbum/Publiek/Voorpagina/_resized/Interesse_vragen.jpg" alt="Owee Commissie"/></span>
				</noscript>
				<div class="content">
					<h2 class="major">Interesse/vragen</h2>
					<p>Uiteraard is er de mogelijkheid om ons tijdens de OWee te spreken. Er is daarnaast ook gelegenheid
						om onze sociëteit Confide aan de Oude Delft 9 te bezoeken. Buiten de OWee om kan jij ook gewoon
						al jouw vragen aan ons stellen.

						Ben jij bijvoorbeeld geinteresseerd om een C.S.R. activiteit bij te wonen of een C.S.R. huis te bezoeken?
						Laat dat dan aan ons weten en kom erachter wat er allemaal mogelijk is.</p>
					<a href="/lidworden#owee-form" class="special">Laat je interesse weten!</a>
					<p class="lidworden">Wil je lid worden? Inschrijven kan in de OWee (17 t/m 22 augustus).
						Voorinschrijven is zelfs ook al mogelijk, zodat er tijdens de OWee meteen contact met je kan worden opgenomen.
						Doe dit door <a href="/lidworden#owee-form">hier</a> te klikken. Zorg ervoor dat je de week van 24 t/m 30 augustus vrij houdt voor de novitiaatsweek</p>
					<a href="/lidworden" class="special">Meer informatie over lid worden</a>
				</div>
			</div>
		</section>

		<!-- Five -->
		<section id="five" class="wrapper kleur5">
			<div class="inner">
				<div class="content">
					<h2 class="major">Foto's</h2>
					<noscript class="lazy-load">
						<div class="grid">
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr1.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr1.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr1.jpg"/>
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr2.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr2.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr2.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr3.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr3.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr3.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr4.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr4.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr4.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr5.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr5.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr5.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr6.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr6.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr6.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr7.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr7.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr7.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr8.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr8.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr8.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/csr9.jpg"
							   data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/csr9.jpg"
									 src="/fotoalbum/Publiek/Voorpagina/_resized/csr9.jpg">
							</a>
						</div>
					</noscript>
				</div>
			</div>
		</section>


		<!-- Footer -->
		<section id="footer">
			<div class="inner">
				<h2 class="major">Contact</h2>
{{--				<h2 class="major">Interesseformulier</h2>--}}
{{--				@include('layout-extern.form')--}}
				<ul class="contact zonder-interesseformulier">
					<li class="fa-home">
						Soci&euml;teit Confide <br/>
						Oude Delft 9<br/>
						2611 BA Delft
					</li>
					<li class="fa-phone">06-19470413</li>
					<li class="fa-envelope"><a href="mailto:{{$_ENV['EMAIL_ABACTIS']}}">{{$_ENV['EMAIL_ABACTIS']}}</a></li>
					<li class="fa-instagram"><a href="https://www.instagram.com/csrdelft/" target="_blank" rel="noopener noreferrer">Like
							onze foto's op Instagram en volg de laatste posts</a></li>
					<li class="fa-map-marker">
						<noscript class="lazy-load">
							<iframe title="Confide op Google Maps" height="300" frameborder="0" style="border:0"
									src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2456.0445350385166!2d4.360246300000008!3d52.0060664!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c5b5c03dabb5b3%3A0xea6a437036970629!2sOude+Delft+9!5e0!3m2!1sen!2s!4v1404470858468">
							</iframe>
						</noscript>
					</li>
				</ul>
				<noscript class="lazy-load">
					<ul class="sponsors">
						<li>
							<a href="https://www.dosign.com/nl-nl/carriere/download-dosign-students-app/?utm_source=Banner%20Students&utm_medium=sv%20C.S.R.&utm_campaign=Promotie%20app ">
								<img src='https://csrdelft.nl/plaetjes/banners/dosign.jpg' alt='Dosign advertentie'>
							</a>
						</li>
						<li>
							<a href="https://www.allekabels.nl/">
								<img width=40% src='https://csrdelft.nl/plaetjes/banners/allekabels.png' alt='Alle kabels'>
							</a>
						</li>
						<li>
							<a href="http://mechdes.nl/">
								<img src='https://csrdelft.nl/plaetjes/banners/mechdes.gif' alt='Mechdes advertentie'>
							</a>
						</li>
						<li>
							<a href="http://galjemadetachering.nl/">
								<img src='https://csrdelft.nl/plaetjes/banners/galjema_banner.jpg' alt='Galjema advertentie'>
							</a>
						</li>
						<li>
							<a href="https://www.pricewise.nl/energie-vergelijken/">
								<img width=25% src="https://csrdelft.nl/plaetjes/banners/pricewise.svg" alt="Pricewise">
							</a>
						</li>
						<li>
							<a href="https://www.hoyhoy.nl/autoverzekering/">
								<img src='https://csrdelft.nl/plaetjes/banners/logo_hoyhoy.png' alt='Autoverzekering - hoyhoy'/>
							</a>
						</li>
						<li>
							<a href="https://www.geld.nl/">
								<img src="https://csrdelft.nl/plaetjes/banners/geld.png"
									 alt="Geld.nl Vergelijk website">
							</a>
						</li>
						<li>
							<a href="https://www.allianzdirect.nl/autoverzekering/">
								<img width=60% src='https://csrdelft.nl/plaetjes/banners/allianzdirect.jpg' alt='Allianz Direct'>
							</a>
						</li>
						<li>
							<a href="http://www.tudelft.nl/">
								<img src="https://csrdelft.nl/plaetjes/banners/TU_Delft_logo_White.png"
									 alt="TUDelft">
							</a>
						</li>
					</ul>
				</noscript>
				<ul class="copyright">
					<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
							href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
				</ul>
			</div>
		</section>

	</section>
@endsection
