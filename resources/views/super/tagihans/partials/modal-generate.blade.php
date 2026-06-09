<div class="modal fade" id="genModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="genForm">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="id_bulan" required>
                                @foreach ($bulans as $b)
                                    <option value="{{ $b->id_bulan }}">{{ $b->id_bulan }} - {{ $b->bulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" value="{{ date('Y') }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Override</label>
                            <input type="number" step="0.01" class="form-control" name="jumlah"
                                placeholder="Optional">
                            <div class="form-text">Biarkan kosong untuk pakai harga paket pelanggan</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Pelanggan (opsional)</label>
                            <select class="form-select" name="id_pelanggans[]" multiple>
                                @foreach ($pelanggans as $p)
                                    <option value="{{ $p->id_pelanggan }}">{{ $p->nama }} ({{ $p->id_pelanggan }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Kosongkan untuk semua pelanggan</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnGenSubmit" class="btn btn-primary btn-sm">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
