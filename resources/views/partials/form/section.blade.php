<select name="{{ $selectName ?? 'section' }}" autofocus>
    @foreach ($sections as $section)
        <option value="{{ $section->getId() }}" @if ($section->getId() == ($selectedSection ?? 0)) selected @endif>{{ $section->getName() }}@if ($section->getStatus() === 'Inactive') (Inactive)@endif</option>
    @endforeach
</select>
