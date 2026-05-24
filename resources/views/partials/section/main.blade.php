<div class="section mb-3">
    <div class="row mb-1">
        <div class="col-12">
            <span class="section-label">
                <a class="section-link" href="{{ route('app_index', ['filter_section' => $filterSection == $section->getId() ? 0 : $section->getId()]) }}">{{ $section->getName() }}</a>
                @if ($section->getStatus() === 'Inactive')
                    (Inactive)
                @endif
            </span>
        </div>
    </div>
    @foreach ($items as $item)
        <div class="row item-row">
            <div class="col-1 text-end no-print">
                @if ($showPriority === 'y' || ($showPriority === 'above_normal' && $item->getPriority() < $priorityNormal))
                    {{ $item->getPriority() }}
                @endif
                <span>
                    <input class="list-item" id="item-{{ $item->getId() }}" type="checkbox" name="itemIds[]" value="{{ $item->getId() }}"/>
                </span>
            </div>
            <div class="col-1 text-end print">
                @if ($showPriority === 'y' || ($showPriority === 'above_normal' && $item->getPriority() < $priorityNormal))
                    {{ $item->getPriority() }}
                @endif
            </div>
            <div class="col-1 text-end print">
                <span class="align-top checkboxes">
                    @if ($item->getStatus() === 'Open')
                        &#x2610;
                    @elseif ($item->getStatus() === 'Closed')
                        &#x2611;
                    @else
                        &#x2612;
                    @endif
                </span>
            </div>
            <div class="col text-start">
                <label class="list-item-label @if ($item->getStatus() === 'Open' && $item->getPriority() <= $priorityHigh) high-priority @endif" for="item-{{ $item->getId() }}">
                    <span class="@if ($item->getStatus() === 'Closed') closed @elseif ($item->getStatus() === 'Deleted') deleted @elseif ($item->getPriority() <= $priorityHigh) high-priority @elseif ($item->getPriority() >= $priorityLow) low-priority @endif">
                        {{ $item->getTask() }}
                    </span>
                </label>
            </div>
        </div>
    @endforeach
</div>
