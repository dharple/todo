@extends('layouts.base')

@section('title', 'Item Bulk Add')

@section('body')
@include('partials.page.head', ['title' => 'Item Bulk Add'])

<form method="POST" action="{{ route('app_item_bulk_add') }}">
    @csrf

    Bulk Adding...<br /><br />

    Section:
    @include('partials.form.section')
    <br />

    Priority:
    @php $selectedPriority = $selectedPriority; @endphp
    @include('partials.form.priority')
    <br />

    <table>
        <tr>
            <td>
                Tasks (newline separated):<br />
                <textarea name="tasks" cols=60 rows=15></textarea><br />
            </td>
        </tr>
    </table>

    <hr />

    <input type="submit" name="submitButton" value="Do It">
    <input type="submit" name="submitButton" value="Do It, Then Add Another">

</form>

@endsection
