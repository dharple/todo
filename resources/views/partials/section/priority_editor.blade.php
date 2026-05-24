<div class="section">
    <span class="section-label">
        {{ $section->getName() }}
    </span>
    <ul class="list">
        @foreach ($items as $item)
            <li>
                <input class="list-item" id="item-{{ $item->getId() }}" type="text" size="3" align="right" name="itemPriority[{{ $item->getId() }}]" value="{{ $item->getPriority() }}" />
                <label class="list-item-label" for="item-{{ $item->getId() }}">
                    <span class="@if ($item->getStatus() !== 'Open') closed @elseif ($item->getStatus() === 'Deleted') deleted @elseif ($item->getPriority() <= $priorityHigh) high-priority @elseif ($item->getPriority() >= $priorityLow) low-priority @endif">
                        {{ $item->getTask() }}
                    </span>
                </label>
                <input type="hidden" name="ids[]" value="{{ $item->getId() }}">
            </li>
        @endforeach
    </ul>
</div>
