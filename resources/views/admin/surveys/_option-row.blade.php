<div class="option-row flex items-start gap-2 border border-slate-100 rounded-lg p-3">
    <div class="flex-1 grid sm:grid-cols-3 gap-2">
        <input type="text" name="options[{{ $index }}][label]" value="{{ $option->label ?? '' }}" placeholder="Texto de la opción"
               class="rounded-lg border-slate-300 text-sm">
        <input type="text" name="options[{{ $index }}][image_url]" value="{{ $option->image_url ?? '' }}" placeholder="URL de imagen (opcional)"
               class="rounded-lg border-slate-300 text-sm">
        <input type="file" name="options[{{ $index }}][image]" accept="image/*" class="text-xs">
    </div>
    <select name="options[{{ $index }}][group]" class="option-group-select rounded-lg border-slate-300 text-xs" style="display:none">
        <option value="principal" {{ ($option->option_group ?? '') === 'principal' ? 'selected' : '' }}>Principal</option>
        <option value="secundaria" {{ ($option->option_group ?? '') === 'secundaria' ? 'selected' : '' }}>Secundaria</option>
    </select>
    <button type="button" class="remove-option-btn text-red-500 text-xs px-2">✕</button>
</div>
