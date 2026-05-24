<div class="container done">

@php $lastDate = ''; @endphp

@foreach ($items as $item)
    @php $thisDate = $item->completed->format('F jS, Y'); @endphp

    @if ($lastDate !== '' && $thisDate !== $lastDate)
        <div class="row">
            <div class="col-12">
                &nbsp;
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-2 text-end">
            @if ($thisDate !== $lastDate)
                {{ $thisDate }}
            @endif
        </div>
        <div class="col-6 col-md-6">
            {{ $item->getTask() }}
        </div>
        <div class="col-6 col-md-2 text-end">
            ({{ $item->section->name }})
        </div>
    </div>
    @php $lastDate = $thisDate; @endphp
@endforeach

</div>
