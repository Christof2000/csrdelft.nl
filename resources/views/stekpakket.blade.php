@extends('layout')

@section('titel', 'StekPakket kiezen')

@push('styles')
	@stylesheet('app.css')
@endpush

@section('content')
	<StekPakket class="vue-context"></StekPakket>
@endsection
