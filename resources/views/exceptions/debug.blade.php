@extends('templates/template.blade.php')

@section('content')
    <hr>
    <h1> {{ $status }} {{ $class }}: {{ $message }} in {{ $file }} on {{ $line }} line</h1>
    <hr>
    @foreach ($trace as $id => $item)
        <h2>Trace {{ $id + 1 }}</h2>
        <ul>
            <li><strong>File:</strong> {{ $item['file'] ?? 'unknown' }}</li>
            <li><strong>Line:</strong> {{ $item['line'] ?? 'unknown' }}</li>
            <li><strong>Class:</strong> {{ $item['class'] ?? 'unknown' }}</li>
            <li><strong>Type:</strong> {{ $item['type'] ?? 'unknown' }}</li>
            <li><strong>Function:</strong> {{ $item['function'] ?? 'unknown' }}</li>
            <li><strong>Arguments:</strong> {{ isset($item['args']) ? count($item['args']) : 'unknown' }}</li>
        </ul>
    @endforeach
@endsection