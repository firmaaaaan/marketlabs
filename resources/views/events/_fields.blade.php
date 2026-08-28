@foreach ($fields as $field)
    @if ($field['type'] === 'textarea')
        <div>
            <label class="block text-sm font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </label>
            <textarea name="{{ $field['key'] }}" rows="3" required="{{ $field['required'] ? 'required' : null }}"
                      class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"></textarea>
        </div>
    @elseif ($field['type'] === 'select')
        <div>
            <label class="block text-sm font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </label>
            <select name="{{ $field['key'] }}" {{ $field['required'] ? 'required' : null }}
                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                <option value="">-- Pilih --</option>
                @foreach ($field['options'] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    @elseif ($field['type'] === 'radio')
        <div>
            <p class="text-sm font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </p>
            <div class="mt-2 flex flex-wrap gap-4">
                @foreach ($field['options'] as $option)
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" name="{{ $field['key'] }}" value="{{ $option }}" {{ $field['required'] ? 'required' : null }}
                               class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        {{ $option }}
                    </label>
                @endforeach
            </div>
        </div>
    @elseif ($field['type'] === 'checkbox')
        <div>
            <label class="flex items-center gap-2.5 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="{{ $field['key'] }}" value="1" {{ $field['required'] ? 'required' : null }}
                       class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </label>
        </div>
    @elseif ($field['type'] === 'file')
        <div>
            <label class="block text-sm font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </label>
            <input type="file" name="{{ $field['key'] }}" {{ $field['required'] ? 'required' : null }}
                   class="mt-1.5 w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700">
        </div>
    @else
        <div>
            <label class="block text-sm font-semibold text-slate-700">
                {{ $field['label'] }}
                @if ($field['required'])<span class="text-red-500">*</span>@endif
            </label>
            <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text') }}"
                   name="{{ $field['key'] }}" {{ $field['required'] ? 'required' : null }}
                   class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
        </div>
    @endif
@endforeach