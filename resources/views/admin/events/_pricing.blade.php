<div>
    <label for="fee" class="block text-sm font-semibold text-slate-600">Biaya Pendaftaran (Rp)</label>
    <input type="number" id="fee" name="fee" value="{{ old('fee', $event->fee ?? null) }}" min="0" step="0.01"
           placeholder="0 = gratis" oninput="updateFeePreview()"
           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    @error('fee')
        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="discount" class="block text-sm font-semibold text-slate-600">Potongan / Diskon (Rp)</label>
    <input type="number" id="discount" name="discount" value="{{ old('discount', $event->discount ?? null) }}" min="0" step="0.01"
           placeholder="0 = tanpa diskon" oninput="updateFeePreview()"
           class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
    @error('discount')
        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
    <p id="fee-preview" class="mt-2 text-sm font-semibold text-emerald-700"></p>
</div>

<script>
    function updateFeePreview() {
        var fee = parseFloat(document.getElementById('fee').value) || 0;
        var discount = parseFloat(document.getElementById('discount').value) || 0;
        var net = Math.max(0, fee - discount);
        var el = document.getElementById('fee-preview');
        var format = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };
        if (net <= 0) {
            el.textContent = 'Biaya akhir: Gratis';
        } else if (discount > 0) {
            el.textContent = 'Biaya akhir: ' + format(net) + ' (hemat ' + format(discount) + ')';
        } else {
            el.textContent = 'Biaya akhir: ' + format(net);
        }
    }
    document.addEventListener('DOMContentLoaded', updateFeePreview);
</script>