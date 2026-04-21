@extends('templates/template.blade.php')

@section('content')

@if(isset($error))
    <div style="color: red; padding-bottom: 8px;">
        {{ $error }}
    </div>
@endif

<form action="/login/signin" method="post">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="submit" value="Login">
</form>

@endsection