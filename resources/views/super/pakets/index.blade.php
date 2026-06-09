@extends('layouts.admin')
@section('title', 'Paket')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Paket</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Daftar Paket</h6>
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0)" id="btnExport" class="btn btn-outline-secondary btn-sm">Export
                                Excel</a>
                            <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="pakets-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>ID Paket</th>
                                    <th>Nama</th>
                                    <th>Harga</th>
                                    <th>Kecepatan</th>
                                    <th>Durasi</th>
                                    {{-- <th>Updated</th> --}}
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

{{-- Modal Create / Edit --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ID Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="id_paket" name="id_paket" required>
                            <div class="invalid-feedback" id="idPaketErr"></div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                            <div class="invalid-feedback" id="namaErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="harga" name="harga"
                                required>
                            <div class="invalid-feedback" id="hargaErr"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Kecepatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kecepatan" name="kecepatan"
                                placeholder="10Mbps" required>
                            <div class="invalid-feedback" id="kecepatanErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Durasi (hari) <span class="text-danger">*</span></label>
                            <input type="number" min="1" step="1" class="form-control" id="durasi"
                                name="durasi" required>
                            <div class="invalid-feedback" id="durasiErr"></div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Remark 1</label>
                            <input type="text" class="form-control" id="remark1" name="remark1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Remark 2</label>
                            <input type="text" class="form-control" id="remark2" name="remark2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Remark 3</label>
                            <input type="text" class="form-control" id="remark3" name="remark3">
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

@push('vendor-styles')
    <link rel="stylesheet"
        href="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
@endpush

@push('vendor-scripts')
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendor/nobleui/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('scripts')
    <script>
        (function($) {
            'use strict';

            // Feather safety (hindari undefined.toSvg bila ada ikon dari partial)
            function safeFeatherReplace() {
                if (!window.feather || !feather.icons) return;
                document.querySelectorAll('[data-feather]').forEach(function(el) {
                    const name = el.getAttribute('data-feather') || 'settings';
                    if (!feather.icons[name]) el.setAttribute('data-feather', 'settings');
                });
                try {
                    feather.replace();
                } catch (e) {}
            }

            const DT_SEL = '#pakets-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $id_paket = $('#id_paket');
            const $nama = $('#nama');
            const $harga = $('#harga');
            const $kecepatan = $('#kecepatan');
            const $durasi = $('#durasi');
            const $remark1 = $('#remark1');
            const $remark2 = $('#remark2');
            const $remark3 = $('#remark3');

            const $idPaketErr = $('#idPaketErr');
            const $namaErr = $('#namaErr');
            const $hargaErr = $('#hargaErr');
            const $kecepatanErr = $('#kecepatanErr');
            const $durasiErr = $('#durasiErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$id_paket, $nama, $harga, $kecepatan, $durasi]
                .forEach($el => $el.removeClass('is-invalid'));
                $idPaketErr.text('');
                $namaErr.text('');
                $hargaErr.text('');
                $kecepatanErr.text('');
                $durasiErr.text('');
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
                } else {
                    alert(msg || 'Success');
                }
            }

            function toastErr(msg) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg || 'Something went wrong'
                    });
                } else {
                    alert(msg || 'Error');
                }
            }

            function reloadTable() {
                $(DT_SEL).DataTable().ajax.reload(null, false);
            }

            // DataTable
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.pakets.dt') }}',
                columns: [{
                        data: 'id_paket',
                        name: 'id_paket'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'harga',
                        name: 'harga'
                    },
                    {
                        data: 'kecepatan',
                        name: 'kecepatan'
                    },
                    {
                        data: 'durasi',
                        name: 'durasi'
                    },
                    // {
                    //     data: 'updated_at',
                    //     name: 'updated_at'
                    // },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [5, 'desc']
                ]
            }).on('draw.dt', function() {
                safeFeatherReplace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.pakets.store') }}');
                $title.text('New Paket');
                bsModal.show();
            });

            // Edit (pakai .btn-edit-role dari partial)
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Paket');

                $id_paket.val(payload.id_paket || '');
                $nama.val(payload.nama || '');
                $harga.val(payload.harga ?? '');
                $kecepatan.val(payload.kecepatan || '');
                $durasi.val(payload.durasi ?? '');
                $remark1.val(payload.remark1 || '');
                $remark2.val(payload.remark2 || '');
                $remark3.val(payload.remark3 || '');
                bsModal.show();
            });

            // Submit
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();

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
                        bsModal.hide(); // ← auto close
                        reloadTable(); // ← auto refresh
                    })
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errs = xhr.responseJSON.errors;
                            if (errs.id_paket) {
                                $id_paket.addClass('is-invalid');
                                $idPaketErr.text(errs.id_paket[0]);
                            }
                            if (errs.nama) {
                                $nama.addClass('is-invalid');
                                $namaErr.text(errs.nama[0]);
                            }
                            if (errs.harga) {
                                $harga.addClass('is-invalid');
                                $hargaErr.text(errs.harga[0]);
                            }
                            if (errs.kecepatan) {
                                $kecepatan.addClass('is-invalid');
                                $kecepatanErr.text(errs.kecepatan[0]);
                            }
                            if (errs.durasi) {
                                $durasi.addClass('is-invalid');
                                $durasiErr.text(errs.durasi[0]);
                            }
                        } else {
                            toastErr(xhr.responseJSON?.message);
                        }
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete (pakai .btn-delete-role dari partial)
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                let name = 'item ini';
                try {
                    name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').nama) ||
                        'item ini';
                } catch (e) {}

                if (!window.Swal) {
                    if (confirm(`Delete ${name}?`)) {
                        $.post(url, {
                                _method: 'DELETE'
                            })
                            .done(() => {
                                toastOk('Deleted');
                                reloadTable();
                            })
                            .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                    }
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Delete?',
                    text: `Delete ${name}?`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete'
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.post(url, {
                            _method: 'DELETE'
                        })
                        .done(() => {
                            toastOk('Deleted');
                            reloadTable();
                        })
                        .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                });
            });


            $('#btnExport').on('click', function() {
                const table = $(DT_SEL).DataTable();
                const q = table.search() || '';
                const url = @json(route('super.pakets.export'));
                // bawa juga params lain jika perlu (mis. filter kolom)
                window.location = url + '?q=' + encodeURIComponent(q);
            });

            modalEl.addEventListener('hidden.bs.modal', clearErrors);
        })(jQuery);
    </script>
@endpush
