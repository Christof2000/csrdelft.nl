<div class="row">
	<div class="col-sm-8">{{$regel->label}}</div>
	<div class="col-sm-4">
		@foreach($regel->opties as $value => $optie)
			@if($value != 0)
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="{{$regel->module}}_{{$regel->id}}" id="{{$regel->module}}_{{$regel->id}}_{{$optie}}" value="{{$optie}}"
								 @if($optie=== $regel->waarde) checked="checked" @endif >
					<label class="form-check-label" for="{{$regel->module}}_{{$regel->id}}_{{$optie}}">{{$optie}}</label>
				</div>
			@endif

		@endforeach
	</div>
</div>
