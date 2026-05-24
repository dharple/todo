@extends('layouts.base')

@section('title', 'Item Edit')

@section('body')
@include('partials.page.head', ['title' => 'Item Edit'])

<form method="POST" action="{{ route('item_edit') }}">
    @csrf
    <input type="hidden" name="op" value="{{ $op }}">

    @if ($op === 'edit')
        Editing...
    @else
        Adding...
    @endif
    <br />
    <br />

    @foreach ($items as $item)
        @php $itemId = $item->getId() ?? 'new'; @endphp
        Item Id #: {{ $itemId }}
        <input type="hidden" name="ids[]" value="{{ $itemId }}">
        <br />
        <br />

        Section:
        @php
            $selectedSection = $item->getId() ? $item->section_id : $sectionOverride;
            $selectName = 'section[' . $itemId . ']';
        @endphp
        @include('partials.form.section')
        <br />

        <span style="vertical-align: top; padding-right: 3pt;">Task:</span>
        <textarea name="task[{{ $itemId }}]" rows=1 cols=60>{{ $item->getTask() }}</textarea>
        <br />

        @if ($op === 'edit')
            Status:
            <select name="status[{{ $itemId }}]">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @if ($status === $item->getStatus()) selected @endif>
                    {{ $status }}
                </option>
            @endforeach
            </select>
            <br />
        @else
            <input type="hidden" name="status[{{ $itemId }}]" value="Open">
        @endif

        Priority:
        @php
            $selectedPriority = $item->getId() ? $item->getPriority() : ($priorityLevels['normal'] ?? 5);
            $selectName = 'priority[' . $itemId . ']';
        @endphp
        @include('partials.form.priority')
        <br />

        @if ($op === 'edit')
            Completed:
            <input type=text name="completed[{{ $itemId }}]" value="{{ $item->getCompleted() ? $item->getCompleted()->format('Y-m-d H:i:s') : '' }}">
            <br />
        @endif

        <br />
        <hr />
    @endforeach

    <input type="submit" name="submitButton" value="Do It" />
    @if ($op === 'add')
        <input type="submit" name="submitButton" value="Do It, Then Add Another">
    @endif

</form>

@endsection
