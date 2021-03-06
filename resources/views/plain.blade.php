@section('titel', $titel)

<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
</head>
<body>

<div class="container">
	<nav
		aria-label="breadcrumb">{!! csr_breadcrumbs(get_breadcrumbs($_SERVER['REQUEST_URI'])) !!}</nav>
	@section('content')
		@if(isset($content))
			@php($content->view())
		@endif
	@show
</div>
</body>
</html>
