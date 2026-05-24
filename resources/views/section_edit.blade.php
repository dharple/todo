@extends('layouts.base')

@section('title', 'Section Edit')

@section('body')
@include('partials.page.head', ['title' => 'Section Edit'])

<form method="POST">
    @csrf

    Add New:<br />
    <input type="text" name="add_name" />
    <input type="submit" name="submitButton" value="Add" />

    <br />
    <br />
    <hr />
    <br />

    Rename:<br />
    <select name="edit_section_id">
        <option value="">Choose...</option>
        @foreach ($sections as $section)
            <option value="{{ $section->getId() }}">{{ $section->getName() }}</option>
        @endforeach
    </select>
    to
    <input type="text" name="edit_name" />
    <input type="submit" name="submitButton" value="Rename" />

    <br />
    <br />
    <hr />
    <br />

    Change Status:<br />
    <select name="toggle_section_id">
        <option value="">Choose...</option>
        <option value="all">All</option>
        @foreach ($sections as $section)
            <option value="{{ $section->getId() }}">{{ $section->getName() }}@if ($section->getStatus() === 'Inactive') (Inactive)@endif</option>
        @endforeach
    </select>
    to
    <input type="submit" name="submitButton" value="Deactivate" />
    <input type="submit" name="submitButton" value="Activate" />
    <br />
    <input type="checkbox" name="resetStartTimes" value="yes" /> <span class="note">Reset start times when making an inactive section active?</span>

    <br />
    <br />
    <span class="note">Note: Open items that are in sections that are switched from Inactive to Active will have their created stamp set to the current time.</span>

    <br />
    <br />
    <hr />
    <br />

</form>

@endsection
