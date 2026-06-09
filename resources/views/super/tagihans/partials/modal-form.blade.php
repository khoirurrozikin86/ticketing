<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">No Tagihan</label>
                            <input type="text" class="form-control" name="no_tagihan"
                                placeholder="(kosongkan untuk auto)">
                        </div>
                        <div class="col-md-3"><label class="form-label">Bulan</label>
                            <input type="text" class="form-control" name="id_bulan" maxlength="2" required>
                        </div>
                        <div class="col-md-3"><label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" value="{{ date('Y') }}"
                                required>
                        </div>
                        <div class="col-md-6"><label class="form-label">ID Pelanggan</label>
                            <input type="text" class="form-control" name="id_pelanggan" required>
                        </div>
                        <div class="col-md-6"><label class="form-label">Jumlah</label>
                            <input type="number" step="0.01" class="form-control" name="jumlah_tagihan" required>
                        </div>
                        <div class="col-md-6"><label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="belum">belum</option>
                                <option value="lunas">lunas</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Tgl Bayar</label>
                            <input type="date" class="form-control" name="tgl_bayar">
                        </div>
                        <div class="col-md-12"><label class="form-label">Remark 1</label>
                            <input type="text" class="form-control" name="remark1">
                        </div>
                        <div class="col-md-6"><label class="form-label">Remark 2</label>
                            <input type="text" class="form-control" name="remark2">
                        </div>
                        <div class="col-md-6"><label class="form-label">Remark 3</label>
                            <input type="text" class="form-control" name="remark3">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSaveItem" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
