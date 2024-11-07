@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>{{ $page->title }}</h1>
    <p>{{ $page->content }}</p>
    <a href="{{ route('cms.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection
