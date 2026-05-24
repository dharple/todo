<select name="{{ $selectName ?? 'priority' }}">
    @for ($priority = $priorityLevels['high']; $priority <= $priorityLevels['low']; $priority++)
        <option value="{{ $priority }}" @if ($priority == ($selectedPriority ?? $priorityLevels['normal'])) selected @endif>{{ $priority }}</option>
    @endfor
</select>
