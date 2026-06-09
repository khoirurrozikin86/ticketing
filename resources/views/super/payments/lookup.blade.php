@extends('layouts.admin')
@section('title', 'Pembayaran (Lookup)')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
            <li class="breadcrumb-item active">Pembayaran (Lookup)</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Cari ID Pelanggan / Nama</label>
                        <input type="text" class="form-control" id="keyword"
                            placeholder="Contoh: AS06000000001 / SERVER ASRI">
                        <div class="form-text">Ketik minimal 2 karakter</div>
                    </div>

                    <div id="results" class="row g-3">
                        {{-- hasil cards dimuat via AJAX --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Modal Pembayaran (reuse) --}}
@include('super.tagihans.partials.modal-pay')

@push('styles')
    <style>
        .card-hover {
            transition: box-shadow .2s ease-in-out;
        }

        .card-hover:hover {
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, .12);
        }

        .badge-soft {
            background: rgba(108, 117, 125, .1);
            color: #6c757d;
        }

        .badge-success-soft {
            background: rgba(25, 135, 84, .12);
            color: #198754;
        }

        .badge-warning-soft {
            background: rgba(255, 193, 7, .15);
            color: #b58100;
        }

        .dataTables_wrapper .dropdown {
            position: static;
        }

        .dataTables_wrapper .dropdown-menu {
            z-index: 2000;
        }
    </style>
@endpush

@push('vendor-scripts')
    {{-- Pastikan bootstrap.bundle & feather sudah ada di layout --}}
@endpush

@push('scripts')
    <script>
        (function($) {
            'use strict';

            const $kw = $('#keyword');
            const $results = $('#results');

            // Debounce ketik
            let t = null;
            $kw.on('input', function() {
                clearTimeout(t);
                const val = $(this).val().trim();
                if (val.length < 2) {
                    $results.html('');
                    return;
                }
                t = setTimeout(() => doSearch(val), 250);
            });

            function doSearch(q) {
                $.get(@json(route('super.payments.find')), {
                        q
                    })
                    .done(res => {
                        $results.html(res.html || '');
                        if (window.feather) feather.replace();
                    })
                    .fail(() => {
                        $results.html('<div class="text-danger">Gagal memuat data</div>');
                    });
            }

            // === Pembayaran (pakai modal yang sudah ada) ===
            const payModalEl = document.getElementById('payModal');
            const bsPayModal = new bootstrap.Modal(payModalEl);
            const $payForm = $('#payForm');
            const $btnPaySubmit = $('#btnPaySubmit');

            $(document).on('click', '.btn-pay-now', function() {
                const tagihanId = $(this).data('tagihan-id');
                const amount = $(this).data('amount'); // default isi nilai jumlah tagihan
                $('#pay_tagihan_id').val(tagihanId);
                $('#pay_amount').val(amount);
                $('#pay_paid_at').val((new Date()).toISOString().slice(0, 10));
                bsPayModal.show();
            });

            $payForm.on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData($payForm[0]);
                $btnPaySubmit.prop('disabled', true).text('Saving...');
                $.ajax({
                        url: @json(route('super.payments.store')),
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        if (window.Swal) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: res.message || 'Payment recorded',
                                timer: 1600,
                                showConfirmButton: false
                            });
                        }
                        bsPayModal.hide();
                        // refresh cards jika masih ada keyword
                        const v = $kw.val().trim();
                        if (v.length >= 2) doSearch(v);
                    })
                    .fail(xhr => {
                        const msg = xhr.responseJSON?.message || 'Failed';
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        } else alert(msg);
                    })
                    .always(() => $btnPaySubmit.prop('disabled', false).text('Bayar'));
            });

        })(jQuery);
    </script>
@endpush
