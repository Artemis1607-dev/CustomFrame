@extends('templates/template.blade.php')

@section('content')

<h1>Session:</h1>

@if (!isset($role))
<p>No credentials</p>
@elseif ($role === 'user')
<p>User credentials</p>
{{ var_dump($_SESSION) }}

<p>
@if ($auth === false)
{{ 'Re-authenticate' }}
@endif
</p>

@elseif ($role === 'admin')
<p>Admin credentials</p>
{{ var_dump($_SESSION) }}
@endif

@endsection