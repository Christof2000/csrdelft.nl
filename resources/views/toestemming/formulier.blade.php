<div>
	<p>{!!instelling('privacy', 'beleid_kort')!!}</p>
	<p>{!!instelling('privacy', 'beschrijving_bestuur')!!}</p>
	<p>@include('toestemming.regel', ['regel' => $akkoordVereniging])</p>
	<hr />
	<p>{!!instelling('privacy', 'beschrijving_bijzonder')!!}</p>
	<p>@include('toestemming.regel', ['regel' => $akkoordBijzonder])</p>
	<hr />
	<p>{!!instelling('privacy', 'beschrijving_foto_extern')!!}</p>
	<p>@include('toestemming.regel', ['regel' => $akkoordExternFoto])</p>
	<hr />
	<p>{!!instelling('privacy', 'beschrijving_foto_intern')!!}</p>
	<p>@include('toestemming.regel', ['regel' => $akkoordInternFoto])</p>
	<hr />
	<p>{!!instelling('privacy', 'beschrijving_vereniging')!!}</p>
	<div class="form-group">
		<label><input type="radio" name="toestemming-intern" id="toestemming-ja" @if($akkoord == 'ja') checked="checked" @endif /> Mijn gegevens mogen gedeeld worden voor interne doeleinden. Dit geldt totdat ik dat verander.</label>
	</div>
	<p>
		<label><input type="radio" name="toestemming-intern" id="toestemming-nee" @if($akkoord == 'nee') checked="checked" @endif/> Ik wil graag instellen welke gegevens met gedeeld worden.</label>
	</p>

	<div id="toestemming-opties" style=" @if($akkoord != 'nee')display:none; @endif clear: both;"><p>Maak een keuze, voor ieder veld moet een waarde ingevuld worden. Commissies die bepaalde gegevens nodig hebben om te kunnen functioneren blijven deze mogelijkheid houden.</p>
		@foreach ($fields as $field)
			@include('toestemming.regel', ['regel' => $field])
		@endforeach
	</div>
</div>

<script type="text/javascript">
    (function () {
        var toestemmingJa = $('#toestemming-ja'),
            toestemmingNee = $('#toestemming-nee'),
            toestemmingOpties = $('#toestemming-opties');

        toestemmingNee.on('change', function () {
            if (this.checked) {
                toestemmingOpties.show();
            } else {
                toestemmingOpties.hide();
            }
        });

        toestemmingJa.on('change', function () {
            if (this.checked) {
                toestemmingOpties.find('input[value="ja"]').prop('checked', true);
                toestemmingOpties.hide();
            }
        });
    })();
</script>
