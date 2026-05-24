@extends('layouts.base')

@section('title', 'Priority Editor')

@section('body')
@include('partials.page.head', ['title' => 'Priority Editor'])

<form method="POST" action="{{ route('item_prioritize') }}">
    @csrf
    {!! $list !!}
    <br />
    <input type="submit" name="submitButton" value="Update" @if (!$hasItems) disabled @endif />
</form>
@endsection
