@extends('layout')

@section('content')
	<h1>Civisaldo Beheer</h1>
	@can(P_FISCAAT_READ)
		<ul class="nav nav-tabs mb-2">
			<li class="nav-item">
				@link('Overzicht', '/fiscaat', 'nav-link', 'active')
			</li>
			<li class="nav-item">
				@link('Producten Beheer', '/fiscaat/producten', 'nav-link', 'active')
			</li>
			<li class="nav-item">
				@link('Saldo Beheer', '/fiscaat/saldo', 'nav-link', 'active')
			</li>
			<li class="nav-item">
				@link('Bestellingen Beheer', '/fiscaat/bestellingen', 'nav-link', 'active')
			</li>
			<li class="nav-item">
				@link('Pin Transacties', '/fiscaat/pin', 'nav-link', 'active')
			</li>
			@can(P_FISCAAT_MOD)
				<li class="nav-item">
					@link('Bulk afschrijven', '/fiscaat/afschrijven', 'nav-link', 'active')
				</li>
			@endcan
		</ul>
	@endcan
	<div>
		@yield('civisaldocontent')
	</div>
@endsection


