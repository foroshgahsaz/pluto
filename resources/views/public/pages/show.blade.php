@extends('public.layout')

@section('title', $page->title.' — '.$branding['title'])

@section('content')
    <h1 class="page-title">{{ $page->title }}</h1>
    <div class="prose">{!! $page->body !!}</div>
@endsection
