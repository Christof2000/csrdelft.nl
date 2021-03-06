<?php
/**
 * @var $voorkeuren \CsrDelft\entity\commissievoorkeuren\VoorkeurVoorkeur[]
 * @var $commissieFormulier \CsrDelft\view\commissievoorkeuren\CommissieFormulier
 * @var $opties string[]
 */
?>
@extends('layout')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
		'/' => 'main',
		'/ledenlijst' => 'Ledenlijst',
		'/commissievoorkeuren' => 'Commissievoorkeuren',
		'' => $commissie->naam,
	]) !!}
@endsection

@section('content')
	<h1>{{$commissie->naam}}</h1>
	<div class="col-md-6">
		<table class="commissievoorkeuren">
			<tr>
				<th>Lid</th>
				<th>Interesse</th>
			</tr>
			@php($opties = ['', 'nee', 'misschien', 'ja'])
			@foreach($voorkeuren as $voorkeur)
				@if($voorkeur->profiel->isLid() && $voorkeur->voorkeur >= 2)
					<tr @if($voorkeur->heeftGedaan()) style="opacity: .50" @endif >
						<td><a href="/commissievoorkeuren/lidpagina/{{$voorkeur->uid}}">{{$voorkeur->profiel->getNaam()}}</a>
						</td>
						<td>{{$opties[$voorkeur->voorkeur]}}</td>
					</tr>
				@endif
			@endforeach
		</table>
	</div>
	<div class="col-md-6">
		@php($commissieFormulier->view())
	</div>
@endsection
