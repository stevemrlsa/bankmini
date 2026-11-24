@php
    $denominationsList = \App\Models\DailyReport::DENOMINATIONS;
    $oldDenominations = old('denominations', []);
@endphp

<div class="p-3 p-sm-4 mb-3 rounded-4 border" style="background: #F8FAFC; border-color: rgba(22, 35, 63, 0.1) !important;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.94rem;">
                <i class="bi bi-cash-stack text-success me-2"></i>Rincian Pecahan Uang Fisik di Laci (Denominasi)
            </h6>
            <small class="text-muted" style="font-size: 0.76rem;">
                Masukkan jumlah lembar/keping untuk setiap pecahan uang di laci. Total uang fisik akan dihitung otomatis.
            </small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" id="btnResetDenominations" style="font-size: 0.75rem;">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pecahan
        </button>
    </div>

    <div class="table-responsive mt-2">
        <table class="table table-sm align-middle mb-0" style="font-size: 0.85rem;">
            <thead>
                <tr class="text-muted border-bottom">
                    <th style="width: 34%;">Nominal Pecahan</th>
                    <th class="text-center" style="width: 34%;">Jumlah (Lembar / Keping)</th>
                    <th class="text-end" style="width: 32%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($denominationsList as $denom)
                    @php
                        $oldQty = (int) ($oldDenominations[$denom] ?? $oldDenominations[(string) $denom] ?? 0);
                        $isCoin = $denom <= 1000;
                    @endphp
                    <tr>
                        <td class="fw-semibold text-dark py-2">
                            <span class="badge {{ $isCoin ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : 'bg-success-subtle text-success border-success-subtle' }} border rounded-pill px-2 py-1 me-1" style="font-size: 0.7rem;">
                                {{ $isCoin ? 'Koin/Kertas' : 'Kertas' }}
                            </span>
                            Rp {{ number_format($denom, 0, ',', '.') }}
                        </td>
                        <td class="py-2">
                            <div class="input-group input-group-sm mx-auto" style="max-width: 155px;">
                                <button type="button" class="btn btn-outline-secondary btn-denom-step" data-target="denom_{{ $denom }}" data-step="-1" tabindex="-1">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    name="denominations[{{ $denom }}]"
                                    id="denom_{{ $denom }}"
                                    data-denom="{{ $denom }}"
                                    class="form-control text-center fw-bold denom-qty-input"
                                    value="{{ $oldQty > 0 ? $oldQty : '' }}"
                                    placeholder="0"
                                >
                                <button type="button" class="btn btn-outline-secondary btn-denom-step" data-target="denom_{{ $denom }}" data-step="1" tabindex="-1">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-end fw-semibold text-dark py-2" id="subtotal_{{ $denom }}">
                            Rp {{ number_format($denom * $oldQty, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-top" style="background: rgba(15, 118, 110, 0.06);">
                    <td class="fw-bold text-dark py-2">Total Hasil Hitung Pecahan</td>
                    <td class="text-center fw-semibold text-secondary py-2" id="totalDenomPieces">0 Lembar/Keping</td>
                    <td class="text-end fw-bold text-success fs-6 py-2" id="totalDenomAmount">Rp 0</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('.denom-qty-input');
    const stepBtns = document.querySelectorAll('.btn-denom-step');
    const resetBtn = document.getElementById('btnResetDenominations');
    const physicalInput = document.getElementById('physical_cash');
    const totalPiecesEl = document.getElementById('totalDenomPieces');
    const totalAmountEl = document.getElementById('totalDenomAmount');

    function recalculateDenominations(updatePhysicalField = true) {
        let grandTotal = 0;
        let totalPieces = 0;
        let anyFilled = false;

        qtyInputs.forEach(function (input) {
            const denom = parseInt(input.getAttribute('data-denom'), 10) || 0;
            const rawVal = input.value.trim();
            if (rawVal !== '') {
                anyFilled = true;
            }
            const qty = Math.max(0, parseInt(rawVal, 10) || 0);
            const subtotal = denom * qty;
            grandTotal += subtotal;
            totalPieces += qty;

            const subEl = document.getElementById('subtotal_' + denom);
            if (subEl) {
                subEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                subEl.className = 'text-end py-2 ' + (qty > 0 ? 'fw-bold text-success' : 'fw-semibold text-muted');
            }
        });

        if (totalPiecesEl) {
            totalPiecesEl.textContent = totalPieces.toLocaleString('id-ID') + ' Lembar/Keping';
        }
        if (totalAmountEl) {
            totalAmountEl.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        if (updatePhysicalField && physicalInput && anyFilled) {
            physicalInput.value = grandTotal;
            physicalInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    qtyInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            if (this.value !== '' && parseInt(this.value, 10) < 0) {
                this.value = 0;
            }
            recalculateDenominations(true);
        });
    });

    stepBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const step = parseInt(this.getAttribute('data-step'), 10) || 0;
            const input = document.getElementById(targetId);
            if (!input) return;

            const current = Math.max(0, parseInt(input.value, 10) || 0);
            const next = Math.max(0, current + step);
            input.value = next > 0 ? next : '';
            recalculateDenominations(true);
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            qtyInputs.forEach(function (input) {
                input.value = '';
            });
            recalculateDenominations(false);
            if (physicalInput) {
                physicalInput.value = '';
                physicalInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    recalculateDenominations(false);
});
</script>
@endpush
@endonce
