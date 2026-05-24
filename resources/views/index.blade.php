@extends('layouts.base')

@section('title', 'To Do List For ' . now()->format('F jS, Y'))

@section('body')
<script>
    var chartData = {!! json_encode($chartData) !!}
</script>
<div class="no-print">
    <div class="row mb-3">
        <div class="col-6 text-start">
            <b>To Do List For {{ $user->getFullname() }}</b>
        </div>
        <div class="col-6 text-end">
            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu" aria-expanded="{{ $countOpen > 0 ? 'true' : 'false' }}" aria-controls="collapseMenu">
                &#x2630;
            </button>
        </div>
    </div>
    <div class="row top-link collapse {{ $countOpen > 0 ? 'show' : '' }}" id="collapseMenu">
        <div class="col-sm-12 col-lg-4 text-lg-start">
            @if ($itemStats->doneTotal() > 0)
                Items Done:<br />
                &nbsp;&nbsp;
                Today: <a href="{{ route('show_done', ['view' => 'today']) }}">{{ $itemStats->doneToday() }}</a>,
                Yesterday: <a href="{{ route('show_done', ['view' => 'yesterday']) }}">{{ $itemStats->doneYesterday() }}</a>
                <br />

                &nbsp;&nbsp;
                This Week: <a href="{{ route('show_done', ['view' => 'week']) }}">{{ $itemStats->doneThisWeek() }}</a>,
                Last Week: <a href="{{ route('show_done', ['view' => 'last-week']) }}">{{ $itemStats->doneLastWeek() }}</a>
                <br />

                &nbsp;&nbsp;
                This Month: <a href="{{ route('show_done', ['view' => 'month']) }}">{{ $itemStats->doneThisMonth() }}</a>,
                Last Month: <a href="{{ route('show_done', ['view' => 'last-month']) }}">{{ $itemStats->doneLastMonth() }}</a>
                <br />

                &nbsp;&nbsp;
                3 / 6 / 9 / 12 Months:
                <a href="{{ route('show_done', ['view' => 'month3']) }}">{{ $itemStats->donePreviousMonths(3) }}</a> /
                <a href="{{ route('show_done', ['view' => 'month6']) }}">{{ $itemStats->donePreviousMonths(6) }}</a> /
                <a href="{{ route('show_done', ['view' => 'month9']) }}">{{ $itemStats->donePreviousMonths(9) }}</a> /
                <a href="{{ route('show_done', ['view' => 'month12']) }}">{{ $itemStats->donePreviousMonths(12) }}</a>
                <br />

                &nbsp;&nbsp;
                By Year:
                @foreach ($itemStats->getYearlySummary() as $year => $count)
                    @if ($count > 0)
                        <a href="{{ route('show_done', ['view' => 'year', 'year' => $year]) }}">{{ $year }}</a>
                    @endif
                @endforeach
                <br />

                Items Done Since Start: <a href="{{ route('show_done') }}">{{ $itemStats->doneTotal() }}</a>
                <br />

                Average Turnaround: {{ number_format($itemStats->getAverage(), 1) }} days
                <br />
            @endif

            Items Shown / Open: {{ $countShown }} / {{ $countOpen }}
        </div>
        <div class="col-sm-12 col-lg-4 chartContainer">
            <canvas id="tasksByWeek" class="mainChart"></canvas>
        </div>
        <div class="col-sm-12 col-lg-4 text-lg-end">
            Filter Closed Items:&nbsp;&nbsp;
            @foreach ($filterClosedValues as $value => $label)
                @if ($config->getFilterClosed() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['filter_closed' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            <br />

            Filter Deleted Items:&nbsp;&nbsp;
            @foreach ($filterDeletedValues as $value => $label)
                @if ($config->getFilterDeleted() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['filter_deleted' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            <br />

            Filter Priority:&nbsp;&nbsp;
            @foreach ($filterPriorityValues as $value => $label)
                @if ($config->getFilterPriority() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['filter_priority' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            <br />

            Show Priority:&nbsp;&nbsp;
            @foreach ($showPriorityValues as $value => $label)
                @if ($config->getShowPriority() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['show_priority' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            <br />

            Filter Freshness:&nbsp;&nbsp;
            @foreach ($filterFreshnessValues as $value => $label)
                @if ($config->getFilterFreshness() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['filter_freshness' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            <br />

            Filter Aging:&nbsp;&nbsp;
            @foreach ($filterAgingValues as $value => $label)
                @if ($config->getFilterAging() === $value)
                    {{ $label }}
                @else
                    <a href="{{ route('index', ['filter_aging' => $value]) }}">{{ $label }}</a>
                @endif
            @endforeach
            days old
            <br />

            Show Inactive Sections:&nbsp;&nbsp;
            @if ($config->getShowInactive())
                Yes <a href="{{ route('index', ['show_inactive' => 'n']) }}">No</a>
            @else
                <a href="{{ route('index', ['show_inactive' => 'y']) }}">Yes</a> No
            @endif
            <br />

            Display Settings: <a href="{{ route('index', ['reset_display_settings' => 1]) }}">Reset</a>
        </div>
    </div>
    <hr />
</div>

<div class="print">
    @if ($itemStats->doneTotal() > 0)
        <p align="center"><b>{{ now()->format('F jS, Y') }}</b> - <b>{{ $user->getFullname() }}</b></p>
    @else
        <p align="center"><b>{{ $user->getFullname() }}</b></p>
    @endif
</div>

<form method=POST>
    @csrf
    {!! $list !!}

    <div class="no-print">
        <hr />
        <br />

        <div class="row">
            <div class="col-sm-12 col-lg-4 mb-2 text-sm-center text-lg-start">
                @if ($hasItems)
                    @if ($showAdvanced)
                        <div class="mb-2">
                    @endif
                            <input type="submit" class="btn btn-secondary" name="editButton" value="Edit" />
                            <input type="submit" class="btn btn-primary" name="markDoneButton" value="Mark Done" />
                            <input type="submit" class="btn btn-secondary" name="prioritizeButton" value="Prioritize" />
                    @if ($showAdvanced)
                        </div>
                        <div>
                            <input type="submit" class="btn btn-secondary" name="duplicateButton" value="Duplicate" />
                            <input type="submit" class="btn btn-danger" name="markDoneButton" value="Delete" />
                            <input type="submit" class="btn btn-primary" name="markDoneButton" value="Mark Done Yesterday" />
                        </div>
                    @endif
                @else
                    <button type="button" class="btn btn-secondary" disabled="disabled">Edit</button>
                    <button type="button" class="btn btn-primary" disabled="disabled">Mark Done</button>
                    <button type="button" class="btn btn-secondary" disabled="disabled">Prioritize</button>
                @endif
            </div>
            <div class="col-sm-12 col-lg-4 mb-2 text-center">
                <a href="{{ route('account') }}" class="btn btn-secondary">My Account</a>
                <a href="{{ route('logout') }}" class="btn btn-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </div>
            <div class="col-sm-12 col-lg-4 mb-2 text-sm-center text-lg-end">
                @if ($showAdvanced)
                    <div class="mb-2">
                @endif
                @if ($hasSections)
                        <a href="{{ route('item_bulk_add') }}" class="btn btn-primary">Bulk</a>
                        <a href="{{ route('item_edit', ['op' => 'add']) }}" class="btn btn-secondary">Add New</a>
                @else
                        <button type="button" class="btn btn-primary" disabled="disabled">Bulk</button>
                        <button type="button" class="btn btn-secondary" disabled="disabled">Add New</button>
                @endif
                        <a href="{{ route('section_edit') }}" class="btn btn-secondary">Edit Sections</a>
                @if ($showAdvanced)
                    </div>
                    <div>
                        <button type="button" class="btn btn-success" id="select-all">Select All</button>
                        <button type="button" class="btn btn-success" id="select-none">Select None</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
@endsection
