@extends('layouts.base')

@section('title', 'Items Done - ' . $period)

@section('body')

<div class="no-print">
    <div class="container done">
        <div class="row">
            <div class="col-12 col-md-6 text-center text-md-start">
                <b>Items Done - {{ $period }}</b>
            </div>
            <div class="col-12 col-md-6 text-center text-md-end">
                @if ($view !== 'year')
                    @if ($sort === 'section')
                        <a href="{{ route('app_show_done', ['sort' => 'task', 'view' => $view]) }}">Sort by Task</a>
                    @else
                        <a href="{{ route('app_show_done', ['sort' => 'section', 'view' => $view]) }}">Sort by Section</a>
                    @endif
                @else
                    @if ($sort === 'section')
                        <a href="{{ route('app_show_done', ['sort' => 'task', 'view' => $view, 'year' => $year]) }}">Sort by Task</a>
                    @else
                        <a href="{{ route('app_show_done', ['sort' => 'section', 'view' => $view, 'year' => $year]) }}">Sort by Section</a>
                    @endif
                @endif
                |
                <a href="{{ route('app_index') }}">Home</a>
            </div>
        </div>
    </div>

    <hr />
</div>

@if ($sort === 'section')
    @include('partials.show_done.by_section')
@else
    @include('partials.show_done.by_task')
@endif

@endsection
