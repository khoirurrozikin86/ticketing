@extends('layouts.admin')
@section('title', 'Tagihan Belum Lunas')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
            <li class="breadcrumb-item active">Tagihan Belum Lunas</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Tagihan Belum Lunas</h6>
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0)" id="btnExport" class="btn btn-outline-secondary btn-sm">Export
                                Excel</a>
                        </div>

                        <div class="">
                            <button id="btnPaySelected" class="btn btn-success btn-sm">
                                Bayar Terpilih
                            </button>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table id="unpaid-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="checkAll">
                                    </th>

                                    <th>No Tagihan</th>
                                    <th>Pelanggan</th>
                                    <th>Server</th>
                                    <th>Bulan</th>
                                    <th>Tahun</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Tgl Bayar</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection









<div class="modal fade" id="payMultiModal">
    <div class="modal-dialog">
        <form id="payMultiForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Bayar Beberapa Tagihan</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_pelanggan" id="pay_pelanggan">

                    <div id="payTagihanContainer"></div>

                    <div class="mb-2">
                        <strong>Total Tagihan:</strong>
                        <span id="totalAmount">0</span>
                    </div>

                    <div class="mb-2">
                        <label>Metode</label>
                        <select class="form-select" name="method" required>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Tanggal Bayar</label>
                        <input type="date" class="form-control" name="paid_at" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Bayar Semua</button>
                </div>
            </div>
        </form>
    </div>
</div>







{{-- Modal Pembayaran (reuse dari modul Tagihan) --}}
@include('super.tagihans.partials.modal-pay')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <style>
        /* aman untuk dropdown di tabel */
        .dataTables_wrapper .dropdown {
            position: static;
        }

        .dataTables_wrapper .dropdown-menu {
            z-index: 2000;
        }
    </style>
@endpush

@push('vendor-scripts')
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush


<script>
    window.toastOk = function(msg) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: msg,
            showConfirmButton: false,
            timer: 2000
        });
    }

    window.toastErr = function(msg) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: msg,
            showConfirmButton: false,
            timer: 2500
        });
    }
</script>


@push('scripts')
    <script>
        (function($) {
            'use strict';

            // CSRF utk semua AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function safeFeatherReplace() {
                if (window.feather && feather.icons) {
                    try {
                        feather.replace();
                    } catch (e) {}
                }
            }

            function toastOk(msg) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        icon: 'success',
                        position: 'top-end',
                        timer: 1800,
                        showConfirmButton: false,
                        title: msg || 'Success'
                    });
                } else alert(msg || 'Success');
            }

            function toastErr(msg) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg || 'Something went wrong'
                    });
                } else alert(msg || 'Error');
            }

            const DT_SEL = '#unpaid-table';
            const dt = $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('super.tagihans.unpaid.dt')),
                columns: [

                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(id, type, row) {
                            if (row.status === 'lunas' || row.jumlah_tagihan <= 0) {
                                return '';
                            }
                            return `
          <input type="checkbox"
                 class="row-check"
                 value="${id}"
                 data-amount="${row.jumlah_tagihan}" 
                  data-pelanggan="${row.id_pelanggan}">
        `;
                        }
                    },


                    {
                        data: 'no_tagihan',
                        name: 'tagihans.no_tagihan'
                    },
                    {
                        data: 'nama_pelanggan',
                        name: 'pelanggans.nama'
                    },
                    {
                        data: 'lokasi_server',
                        name: 'servers.lokasi'
                    },
                    {
                        data: 'nama_bulan',
                        name: 'bulans.bulan'
                    },
                    {
                        data: 'tahun',
                        name: 'tagihans.tahun'
                    },
                    {
                        data: 'jumlah_tagihan',
                        name: 'tagihans.jumlah_tagihan'
                    },
                    {
                        data: 'status',
                        name: 'tagihans.status',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;

                            const val = (data || '').toString().trim().toLowerCase();
                            const isLunas = val === 'lunas' || val === 'paid';

                            const colorClass = isLunas ?
                                'bg-gradient-success text-white shadow-sm' :
                                'bg-gradient-danger text-white shadow-sm';

                            const icon = isLunas ?
                                '<i data-feather="check-circle" class="me-1" style="width:12px;height:12px;"></i>' :
                                '<i data-feather="x-circle" class="me-1" style="width:12px;height:12px;"></i>';

                            const label = isLunas ? 'Lunas' : 'Belum';

                            return `
      <span class="badge ${colorClass} fw-semibold d-inline-flex align-items-center"
            style="font-size:0.68rem;padding:3px 8px;border-radius:8px;">
        ${icon}${label}
      </span>
    `;
                        }
                    },
                    {
                        data: 'tgl_bayar',
                        name: 'tagihans.tgl_bayar'
                    },
                    {
                        data: 'updated_at',
                        name: 'tagihans.updated_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [7, 'desc']
                ]
            }).on('draw.dt', safeFeatherReplace);

            // Export unpaid
            $('#btnExport').on('click', function() {
                window.location = @json(route('super.tagihans.unpaid.export'));
            });



            // === Modal Bayar ===
            const payModalEl = document.getElementById('payModal');
            const bsPayModal = new bootstrap.Modal(payModalEl);
            const $payForm = $('#payForm');
            const $btnPaySubmit = $('#btnPaySubmit');

            // buka modal dari dropdown actions (class: .btn-pay-item)
            $(document).on('click', '.btn-pay-item', function() {
                let p = {};
                try {
                    p = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $('#pay_tagihan_id').val(p.tagihan_id || '');
                $('#pay_amount').val(p.default_amount || '');
                $('#pay_paid_at').val((new Date()).toISOString().slice(0, 10));
                bsPayModal.show();
            });

            // submit pembayaran
            $payForm.on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData($payForm[0]);
                $('#btnPaySubmit').prop('disabled', true).text('Saving...');
                $.ajax({
                        url: @json(route('super.payments.store')),
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        toastOk(res.message || 'Payment recorded');
                        bsPayModal.hide();
                        $(DT_SEL).DataTable().ajax.reload(null, false);
                    })
                    .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'))
                    .always(() => $('#btnPaySubmit').prop('disabled', false).text('Bayar'));
            });

        })(jQuery);






        $(document).on('change', '#checkAll', function() {
            $('.row-check').prop('checked', this.checked);
        });




        $('#btnPaySelected').on('click', function() {
            let ids = [];
            let total = 0;
            let pelanggan = null;

            $('#payTagihanContainer').html('');

            $('.row-check:checked').each(function() {
                ids.push($(this).val());
                total += parseFloat($(this).data('amount'));
                pelanggan = $(this).data('pelanggan'); // wajib ada
            });

            if (ids.length === 0) {
                toastErr('Pilih minimal 1 tagihan');
                return;
            }

            ids.forEach(id => {
                $('#payTagihanContainer').append(
                    `<input type="hidden" name="tagihan_ids[]" value="${id}">`
                );
            });

            $('#pay_pelanggan').val(pelanggan);
            $('#totalAmount').text(total.toLocaleString('id-ID'));

            new bootstrap.Modal('#payMultiModal').show();
        });





        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
            }
        });




        $('#payMultiForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                    url: "{{ route('super.payments.bulk') }}",
                    method: "POST",
                    data: $(this).serialize(),
                })
                .done(res => {
                    toastOk(res.message);
                    $('#payMultiModal').modal('hide');
                    $('#unpaid-table').DataTable().ajax.reload(null, false);
                })
                .fail(xhr => {
                    toastErr(xhr.responseJSON?.message || 'Gagal bayar');
                });
        });
    </script>
@endpush
