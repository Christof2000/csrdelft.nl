@extends('forum.base')

@section('titel', 'Forum')

@section('breadcrumbs')
	@parent
	» <a href="/forum/recent">Recent</a>
@endsection

@section('content')
	{!! getMelding() !!}

	<div class="forum-header btn-toolbar">
		@can(P_ADMIN)
			<div class="btn-group mr-2">
				<a href="/forum/aanmaken" class="btn btn-light post popup" title="Deelforum aanmaken">@icon('add')</a>
			</div>
		@endcan

		@include('forum.partial.head_buttons')

		@php($zoekform->view())
	</div>

	<h1>Forum</h1>

	@foreach($categorien as $categorie)
		<div class="forumcategorie">
			<h3><a name="{{$categorie->categorie_id}}">{{$categorie->titel}}</a></h3>
			<div class="forumdelen">
				@foreach($categorie->getForumDelen() as $deel)
					<div class="forumdeel">
						<h4><a href="/forum/deel/{{$deel->forum_id}}">{{$deel->titel}}</a></h4>
						<p class="forumdeel-omschrijving">{{$deel->omschrijving}}</p>
					</div>
				@endforeach
			</div>
		</div>
	@endforeach

	@include('forum.partial.rss_link')
@endsection
