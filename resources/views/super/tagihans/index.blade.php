@extends('layouts.admin')
@section('title', 'Tagihan')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
            <li class="breadcrumb-item active">Tagihan</li>
        </ol>
    </nav>
@endsection

@push('styles')
    <style>
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745, #4cd964);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #dc3545, #ff7675);
        }

        .badge.shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush



@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Daftar Tagihan</h6>
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0)" id="btnExport" class="btn btn-outline-secondary btn-sm">Export
                                Excel</a>
                            <a href="javascript:void(0)" id="btnGenerate"
                                class="btn btn-outline-primary btn-sm">Generate</a>
                            <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tagihans-table" class="table w-100">
                            <thead>
                                <tr>
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

{{-- Modal Create/Edit --}}
@include('super.tagihans.partials.modal-form')

{{-- Modal Generate Batch --}}
@include('super.tagihans.partials.modal-generate', ['bulans' => $bulans, 'pelanggans' => $pelanggans])

@include('super.tagihans.partials.modal-pay')

{{-- Modal View Payments --}}

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
@endpush
@push('vendor-scripts')
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('scripts')
    <script>
        (function($) {
            'use strict';

            function safeFeatherReplace() {
                if (window.feather && feather.icons) {
                    try {
                        feather.replace();
                    } catch (e) {}
                }
            }

            const DT_SEL = '#tagihans-table';
            const modalEl = document.getElementById('itemModal');
            const modalGenEl = document.getElementById('genModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const bsGenModal = new bootstrap.Modal(modalGenEl);

            const $form = $('#itemForm');
            const $btnSave = $('#btnSaveItem');
            const $formGen = $('#genForm');
            const $btnGenSubmit = $('#btnGenSubmit');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

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

            function reloadTable() {
                $(DT_SEL).DataTable().ajax.reload(null, false);
            }

            // DataTable
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.tagihans.dt') }}',
                columns: [{
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


            // New
            $('#btnNewItem').on('click', function() {
                $('#itemForm')[0].reset();
                $('#itemModalTitle').text('New Tagihan');
                $('#itemForm').data('mode', 'create').data('action', '{{ route('super.tagihans.store') }}');
                bsModal.show();
            });

            // Edit
            $(document).on('click', '.btn-edit-role', function() {
                $('#itemForm')[0].reset();
                let p = {};
                try {
                    p = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $('#itemModalTitle').text('Edit Tagihan');
                $('#itemForm').data('mode', 'edit').data('action', $(this).data('update-url'));

                // fill fields by name
                for (const k in p) {
                    const $f = $('#itemForm [name="' + k + '"]');
                    if ($f.length) $f.val(p[k] ?? '');
                }
                bsModal.show();
            });

            // Submit Create/Update
            $form.on('submit', function(e) {
                e.preventDefault();
                const mode = $form.data('mode');
                const action = $form.data('action');
                const fd = new FormData($form[0]);
                if (mode === 'edit') fd.append('_method', 'PUT');
                $btnSave.prop('disabled', true).text('Saving...');
                $.ajax({
                        url: action,
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        toastOk(res.message || (mode === 'edit' ? 'Updated' : 'Created'));
                        bsModal.hide();
                        reloadTable();
                    })
                    .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'))
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                if (!window.Swal) {
                    if (!confirm('Delete?')) return;
                }
                const ask = window.Swal ? Swal.fire({
                    icon: 'warning',
                    title: 'Delete?',
                    showCancelButton: true
                }).then(r => r.isConfirmed) : Promise.resolve(true);
                ask.then(ok => {
                    if (!ok) return;
                    $.post(url, {
                            _method: 'DELETE'
                        }).done(() => {
                            toastOk('Deleted');
                            reloadTable();
                        })
                        .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                });
            });

            // Export
            $('#btnExport').on('click', function() {
                const q = $(DT_SEL).DataTable().search() || '';
                window.location = @json(route('super.tagihans.export')) + '?q=' + encodeURIComponent(q);
            });

            // Generate Batch
            $('#btnGenerate').on('click', () => bsGenModal.show());
            $formGen.on('submit', function(e) {
                e.preventDefault();
                const fd = new FormData($formGen[0]);
                $btnGenSubmit.prop('disabled', true).text('Generating...');
                $.ajax({
                        url: @json(route('super.tagihans.generate')),
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        toastOk(res.message || 'Generated');
                        bsGenModal.hide();
                        reloadTable();
                    })
                    .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'))
                    .always(() => $btnGenSubmit.prop('disabled', false).text('Generate'));
            });





            // --- PAY: open modal ---
            const payModalEl = document.getElementById('payModal');
            const bsPayModal = new bootstrap.Modal(payModalEl);
            const $payForm = $('#payForm');
            const $btnPaySubmit = $('#btnPaySubmit');

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

            // --- PAY: submit ---
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
                        toastOk(res.message || 'Payment recorded');
                        bsPayModal.hide();
                        reloadTable();
                    })
                    .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'))
                    .always(() => $btnPaySubmit.prop('disabled', false).text('Bayar'));
            });





        })(jQuery);
    </script>
@endpush
