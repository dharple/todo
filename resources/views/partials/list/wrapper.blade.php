<div class="wrapper {{ $sectionsDrawn > 1 ? 'wrapper-large' : 'wrapper-small' }}">
    @if ($sectionsDrawn > 0)
        {!! $sectionOutput !!}
    @else
        <div class="section mb-3">
            <span class="empty-list">
                No Items
            </span>
        </div>
    @endif
    <div class="section">
        {!! $footer !!}
    </div>
</div>
