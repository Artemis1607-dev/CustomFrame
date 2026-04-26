@extends('templates/template.blade.php')

@section('content')

<h1>Hello World!</h1>
@if(!empty($foo))
<strong>Model's data:</strong>
@foreach ($foo as $key => $value)
<output>{{ $key . ' => ' . $value}}</output>
@endforeach
@endif

@endsection