@extends('layouts.admin')
@section('title', 'Pembayaran')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
            <li class="breadcrumb-item active">Pembayaran</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                        <div>
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control form-control-sm" id="f_date_from">
                        </div>
                        <div>
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control form-control-sm" id="f_date_to">
                        </div>
                        <div>
                            <label class="form-label">User</label>
                            <select id="f_user_id" class="form-select form-select-sm">
                                <option value="">— All —</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label">Keyword</label>
                            <input type="text" class="form-control form-control-sm" id="f_kw"
                                placeholder="no tagihan / pelanggan / method / ref">
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm" id="btnFilter">Apply</button>
                            <button class="btn btn-outline-secondary btn-sm" id="btnReset">Reset</button>
                        </div>
                        <div class="ms-auto">
                            <a href="javascript:void(0)" id="btnExport" class="btn btn-outline-secondary btn-sm">Export
                                Excel</a>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div
                                class="card-stats bg-gradient-primary text-white shadow-sm p-3 rounded-3 position-relative overflow-hidden">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                        <i data-feather="shopping-bag"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold text-white-50">Total Transaksi</div>
                                        <div id="sum_tx" class="fs-5 fw-bold mb-0">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div
                                class="card-stats bg-gradient-success text-white shadow-sm p-3 rounded-3 position-relative overflow-hidden">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                        <i data-feather="dollar-sign"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold text-white-50">Total Amount</div>
                                        <div id="sum_amount" class="fs-5 fw-bold mb-0">0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div
                                class="card-stats bg-gradient-warning text-white shadow-sm p-3 rounded-3 position-relative overflow-hidden">
                                <div class="d-flex align-items-center">
                                    <div class="icon-wrapper bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                        <i data-feather="activity"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold text-white-50">Avg Amount</div>
                                        <div id="avg_amount" class="fs-5 fw-bold mb-0">0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table id="payments-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paid At</th>
                                    <th>Amount</th>
                                    <th>No Tagihan</th>
                                    <th>ID Pelanggan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Server</th>
                                    <th>Method</th>
                                    <th>Ref No</th>
                                    <th>User</th>
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

{{-- @push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <style>
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
@endpush --}}

@push('scripts')
    <script>
        (function($) {
            'use strict';
            const DT_SEL = '#payments-table';

            function safeFeatherReplace() {
                if (window.feather && feather.icons) {
                    try {
                        feather.replace();
                    } catch (e) {}
                }
            }

            function gatherFilters() {
                return {
                    date_from: $('#f_date_from').val() || '',
                    date_to: $('#f_date_to').val() || '',
                    user_id: $('#f_user_id').val() || '',
                    kw: $('#f_kw').val() || '',
                };
            }

            function formatRupiah(value) {
                if (value === null || value === undefined || isNaN(value)) return 'Rp 0';
                return 'Rp ' + parseFloat(value)
                    .toFixed(2) // dua desimal
                    .replace('.', ',') // ganti titik jadi koma untuk desimal
                    .replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // tambahkan titik setiap 3 digit
            }

            function loadSummary() {
                const f = gatherFilters();
                $.get(@json(route('super.payments.summary')), f)
                    .done(res => {
                        $('#sum_tx').text(res.tx_count ?? 0);
                        $('#sum_amount').text(formatRupiah(res.total_amount ?? 0));
                        $('#avg_amount').text(formatRupiah(res.avg_amount ?? 0));
                    });
            }


            const dt = $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('super.payments.dt')),
                    data: function(d) {
                        const f = gatherFilters();
                        Object.assign(d, f);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'pembayarans.id'
                    },
                    {
                        data: 'paid_at',
                        name: 'pembayarans.paid_at'
                    },
                    {
                        data: 'amount',
                        name: 'pembayarans.amount'
                    },
                    {
                        data: 'no_tagihan',
                        name: 'tagihans.no_tagihan'
                    },
                    {
                        data: 'id_pelanggan',
                        name: 'tagihans.id_pelanggan'
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
                        data: 'method',
                        name: 'pembayarans.method'
                    },
                    {
                        data: 'ref_no',
                        name: 'pembayarans.ref_no'
                    },
                    {
                        data: 'user_name',
                        name: 'users.name'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'desc']
                ]
            }).on('draw.dt', safeFeatherReplace);

            $('#btnFilter').on('click', function() {
                dt.ajax.reload();
                loadSummary();
            });
            $('#btnReset').on('click', function() {
                $('#f_date_from').val('');
                $('#f_date_to').val('');
                $('#f_user_id').val('');
                $('#f_kw').val('');
                dt.ajax.reload();
                loadSummary();
            });

            $('#btnExport').on('click', function() {
                const f = gatherFilters();
                const q = $.param(f);
                window.location = @json(route('super.payments.export')) + '?' + q;
            });

            // delete payment (pakai partial class default)
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                const token = $('meta[name="csrf-token"]').attr('content');

                const ask = window.Swal ? Swal.fire({
                    icon: 'warning',
                    title: 'Delete?',
                    showCancelButton: true
                }).then(r => r.isConfirmed) : Promise.resolve(confirm('Delete?'));
                ask.then(ok => {
                    if (!ok) return;
                    $.post(url, {
                        _method: 'DELETE',
                        _token: token
                    }).done(() => {
                        if (window.Swal) {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Deleted',
                                timer: 1600,
                                showConfirmButton: false
                            });
                        }
                        dt.ajax.reload(null, false);
                        loadSummary();
                    });
                });
            });

            // initial load summary
            loadSummary();
        })(jQuery);
    </script>
@endpush
