@if (($items ?? collect())->isEmpty())
    <div class="col-12 text-muted">Tidak ada hasil.</div>
@else
    @foreach ($items as $it)
        <div class="col-md-6 col-lg-4">
            <div class="card card-hover h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-semibold">{{ $it->nama }}</div>
                            <div class="text-muted small">{{ $it->id_pelanggan }}</div>
                        </div>
                        @if ($it->out_count > 0)
                            <span class="badge badge-warning-soft">{{ $it->out_count }} belum
                                ({{ number_format($it->out_sum, 2) }})
                            </span>
                        @else
                            <span class="badge badge-success-soft">Lunas</span>
                        @endif
                    </div>

                    <div class="mb-2 small">
                        <div class="text-muted">Server</div>
                        <div>{{ $it->server_lokasi ?? '-' }} <span
                                class="text-muted">({{ $it->server_ip ?? '-' }})</span></div>
                        @if (!empty($it->server_mikrotik))
                            <div class="text-muted">Mikrotik: {{ $it->server_mikrotik }}</div>
                        @endif
                    </div>

                    <div class="flex-grow-1">
                        <div class="text-muted small mb-1">Tagihan:</div>
                        @forelse($it->tagihans as $tg)
                            <div
                                class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                                <div class="me-2">
                                    <div class="fw-semibold">{{ $tg->no_tagihan }}</div>
                                    <div class="small text-muted">
                                        {{ $tg->nama_bulan ?? $tg->id_bulan }}/{{ $tg->tahun }} ·
                                        {{ $tg->status }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small">{{ number_format((float) $tg->jumlah_tagihan, 2) }}</div>
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-primary btn-pay-now"
                                            data-tagihan-id="{{ $tg->id }}"
                                            data-amount="{{ (float) $tg->jumlah_tagihan }}" title="Bayar tagihan ini">
                                            Bayar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted small">Tidak ada tagihan terbaru.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
