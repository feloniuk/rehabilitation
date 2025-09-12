@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">{{ $page->title }}</h1>
        <div class="prose max-w-none">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection