<div class="container done">

@php $lastDate = ''; $lastSection = ''; @endphp

@foreach ($items as $item)
    @php
        $thisDate = $item->completed_at->format('F jS, Y');
        $thisSection = $item->section->name;
    @endphp

    @if ($lastDate !== '' && $thisDate !== $lastDate)
        <div class="row">
            <div class="col-12">
                <hr width="90%">
            </div>
        </div>
    @elseif ($lastSection !== '' && $thisSection !== $lastSection)
        <div class="row">
            <div class="col-12">
                &nbsp;
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-2 text-start text-md-end">
            @if ($thisDate !== $lastDate)
                <div class="mb-2 mb-md-0">{{ $thisDate }}</div>
            @endif
        </div>
        <div class="col-6 col-md-2">
            @if ($thisSection !== $lastSection || $thisDate !== $lastDate)
                {{ $item->section->name }}
            @endif
        </div>
        <div class="col-6 col-md-8">
            {{ $item->getTask() }}
        </div>
    </div>
    @php $lastDate = $thisDate; $lastSection = $thisSection; @endphp
@endforeach

</div>
