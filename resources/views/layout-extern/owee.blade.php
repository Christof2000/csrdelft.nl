@extends('layout-extern.layout')

@section('titel', 'Machtig mooi - OWee 2020')

@section('styles')
	@stylesheet('extern')
	@stylesheet('extern-owee')
	@script('extern-owee')
@endsection

@section('body')
	<!-- Banner -->
	<section id="banner">
		<div class="inner">
			<a href="/">
				<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
				<h1>C.S.R. Delft</h1>
			</a>
		</div>
	</section>

	<div class="owee-pagina">
		<div class="bbl atl hero">
			<div class="content">
				<img src="/images/owee/owee2020.svg" alt="C.S.R. - Machtig Mooi">
			</div>
		</div>

		<div class="content pt-5 pb-5">
			<div class="row align-items-center">
				<div class="col-md-6 mb-4 mb-md-0">
					<h1>Word lid van C.S.R.</h1>
					<p>C.S.R. Delft is de grootste christelijke vereniging van Delft. Lid zijn van onze studentenvereniging betekent voor jou dat je nieuwe vriendschappen maakt voor het leven en samen geniet van de activiteiten die de vereniging biedt. Het betekent dat je je geloof blijft voeden en je kan verdiepen met kringen, bidgroepjes en zangavonden. Lid zijn zorgt voor prachtige momenten tijdens je studententijd die je je leven lang niet gaat vergeten!</p>

					<div class="mt-4">
						<a href="#contact" class="cta secondary" onclick="document.getElementById('lid-worden').checked = true">Ik wil lid worden</a>
						<a href="#contact" class="cta primary" onclick="document.getElementById('lid-spreken').checked = true">Eerst een lid spreken</a>
					</div>
				</div>
				<div class="col-md-6">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/AE8RE8e5qI4?hl=nl" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
			</div>
		</div>

		<div class="bbl atl buttons">
			<div class="content">
				<div class="row pt-4">
					<a class="col-6 col-md-4 mb-4" href="/vereniging/geloof">
						<img src="/images/owee/geloof.png" alt="Geloof">
						<span class="overlay">Geloof</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/vorming">
						<img src="/images/owee/vorming.png" alt="Vorming">
						<span class="overlay">Vorming</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/gezelligheid">
						<img src="/images/owee/gezelligheid.png" alt="Gezelligheid">
						<span class="overlay">Gezelligheid</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/sport">
						<img src="/images/owee/sport.png" alt="Sport">
						<span class="overlay">Sport</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/ontspanning">
						<img src="/images/owee/ontspanning.png" alt="Ontspanning">
						<span class="overlay">Ontspanning</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/societeit">
						<img src="/images/owee/societeit.png" alt="Societeit">
						<span class="overlay">Soci&euml;teit</span>
					</a>
				</div>
			</div>
		</div>

		<div class="content videos">
			<div class="row pt-4">
				<div class="col-12 mb-4">
					<h2>C.S.R. op YouTube</h2>
				</div>
				<div class="col-12 col-sm-6 col-md-4 mb-4">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/videoseries?list=PLXBOhyG24-WnNgg2RloapxC5X73J1Zxvi&hl=nl" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<div class="col-12 col-sm-6 col-md-4 mb-4">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/01kzRDhdcYw?hl=nl" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<a class="col-12 col-md-4 mb-4" href="https://www.youtube.com/user/CivitasFilms" target="_blank" rel="noopener noreferrer">
					<div class="iframe-container youtube-container">
						<div class="youtube">
							<div>
								<i class="fab fa-youtube"></i>
								<div>Bekijk meer op YouTube</div>
							</div>
						</div>
					</div>
				</a>
			</div>
		</div>

		<div class="bbl atl interest">
			<div class="content">
				<div class="pt-5 pt-md-4 pb-4 pb-sm-2">
					<h2>Interesse in C.S.R.?</h2>
					<p>Hieronder kan je je interesse aangeven door je gegevens achter te laten, wij houden je dan op de hoogte met nieuws over bijvoorbeeld open avonden of de OWee. Wanneer je al weet dat je lid wilt worden komend jaar, kan je hieronder ook je gegevens achterlaten voor je voorinschrijving.</p>
				</div>
			</div>
		</div>

		<a id="contact"></a>
		<div class="content pt-4 pb-4">
			<div class="row">
				<div class="col-md-5 col-lg-4 mb-4 mb-md-0">
					<a class="whatsapp" href="https://wa.me/31639667236" target="_blank" rel="noopener noreferrer">
						<i class="fab fa-whatsapp mr-3 mr-md-0"></i>
						<div class="call mt-3 mb-3">Vragen?<br>App met <br class="d-none d-md-inline">Maartje!</div>
						<div class="maartje"></div>
						<div class="cta">0639667236</div>
					</a>
				</div>
				<div class="col-md-7 col-lg-8">
					<script type="text/javascript">
						var captchaLoaded = false;

						function checkVisible(elm) {
							var rect = elm.getBoundingClientRect();
							var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
							return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
						}

						var onloadCallback = function() {
							window.addEventListener('scroll', function () {

								if (checkVisible(document.getElementById('captcha')) && !captchaLoaded) {
									captchaLoaded = true;
									grecaptcha.render('captcha', {
										'sitekey': '6Lc9TCITAAAAAGglcvgYvSwL-ci4A3Hkv8s1xRIX',
										'hl': 'nl',
									});
								}
							})
						};
					</script>
					<form action="" class="formulieren" id="owee-form">
						@csrf
						<label for="lid-worden" class="owee"><span class="d-none d-sm-inline">Ik wil </span>lid worden</label>
						<input id="lid-worden" class="owee" type="radio" name="optie" value="lid-worden" checked>
						<label for="lid-spreken" class="owee"><span class="d-none d-sm-inline">Eerst een </span>lid spreken</label>
						<input id="lid-spreken" class="owee" type="radio" name="optie" value="lid-spreken">
						<div class="interesseformulier">
							<p class="lid-worden">Normaal gesproken kan je tijdens de OWee in onze sociëteit langskomen om je in te schrijven, maar vanwege de bijzondere omstandigheden zal een deel van de inschrijvingen dit jaar digitaal plaatsvinden. Wanneer je hieronder je gegevens achterlaat wordt er tijdens de OWee direct contact met je opgenomen om je inschrijving af te ronden. Hiermee verzeker je jezelf dus van een plekje op onze aankomende ledenlijst.</p>
							<p class="lid-spreken">Wil je meer weten over de vereniging? Een gesprek met een lid kan helpen, je kan al je vragen kwijt en erachter komen of de vereniging bij jou past. Laat je gegevens hier achter, we zullen dan zo snel mogelijk (binnen 1-2 dagen) contact met je opnemen!</p>

							<div id="melding"></div>
							<div class="velden" id="formulierVelden">
								<label for="naam" class="owee">Je naam</label>
								<input type="text" id="naam" name="naam">

								<label for="email" class="owee">Je e-mailadres</label>
								<input type="text" id="email" name="email">

								<label for="telefoon" class="owee">Je mobiele telefoonnummer</label>
								<input type="tel" id="telefoon" name="telefoon">

								<div class="field" id="captcha"></div>

								<p>Met het verzenden van dit formulier ga je akkoord met de <a href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf" target="_blank">privacyverklaring van C.S.R. Delft</a>.</p>

								<input class="lid-worden" type="submit" id="submitButton" value="Inschrijven">
								<input class="lid-spreken" type="submit" id="submitButton" value="Verzenden">
							</div>
						</div>
					</form>
					<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
				</div>
			</div>
		</div>

		<div class="bbl atl notes">
			<div class="content">
				<div class="row pt-5 pb-4">
					<div class="col-md-6 mb-4 mb-md-0">
						<h2>Novitaatsweek</h2>
						<p>Wanneer je lid wilt worden bij C.S.R. doorloop je een novitiaatsweek
							(ook wel de kennismakingstijd, afgekort KMT). Deze tijd zal plaatsvinden
							na de OWee (25 augustus t/m 29 augustus 2020), in deze week zal je elkaar,
							de vereniging en haar leden leren kennen. De activiteiten die tijdens
							de KMT worden ondernomen zullen op een respectvolle en veilige manier verlopen.</p>
					</div>
					<div class="col-md-6">
						<h2>Documenten</h2>
						<p>C.S.R. heeft samen met de andere grote studentenverenigingen (DSC, Virgiel,
							Sint Jansbrug en DSB) en de TU Delft een gedragsovereenkomst opgesteld waarin
							afspraken staan waar we elkaar aan zullen houden. Hierin staan afspraken hoe
							we in het algemeen met elkaar omgaan binnen de vereniging en hoe we binnen een
							KMT met nieuwe leden omgaan. In de onderstaande drie documenten kun je de
							afspraken die wij als C.S.R. met de TU Delft hebben gemaakt lezen.</p>
							<ul>
								<li><a href="https://csrdelft.nl/download/Code%20of%20Ethics%202016-2017.pdf" target="_blank">Code Of Ethics</a></li>
								<li><a href="https://d1rkab7tlqy5f1.cloudfront.net/TUDelft/Onderwijs/Praktische_zaken/Communicatieconvenant%20TU%20Delft%20--getekende%20versie-website.pdf" target="_blank" rel="noopener noreferrer">Communicatieconvenant</a></li>
								<li><a href="https://d1rkab7tlqy5f1.cloudfront.net/TUDelft/Onderwijs/Praktische_zaken/gedragsovereenkomst%20TU%20Delft%20en%20de%20vijf%20grote%20verenigingen%20getekende%20versie%20website.pdf" target="_blank" rel="noopener noreferrer">Gedragsovereenkomst</a></li>
							</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<div id="wrapper">
		<section id="footer">
			<div class="inner">
				<ul class="copyright">
					<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
							href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
				</ul>
			</div>
		</section>
	</div>
@endsection
