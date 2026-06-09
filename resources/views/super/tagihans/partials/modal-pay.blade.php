<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payForm">
                @csrf {{-- <—— ini penting, agar _token ikut ke FormData --}}
                <div class="modal-header">
                    <h5 class="modal-title">Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tagihan_id" id="pay_tagihan_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jumlah</label>
                            <input type="number" step="0.01" class="form-control" name="amount" id="pay_amount"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Bayar</label>
                            <input type="date" class="form-control" name="paid_at" id="pay_paid_at"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metode</label>
                            <input type="text" class="form-control" name="method" placeholder="cash/transfer/qris">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ref No</label>
                            <input type="text" class="form-control" name="ref_no" placeholder="no ref bank/VA">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnPaySubmit" class="btn btn-primary btn-sm">Bayar</button>
                </div>
            </form>
        </div>
    </div>
</div>
