@extends('layouts.admin')
@section('title', 'Pelanggan')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pelanggan</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Daftar Pelanggan</h6>
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
                        <table id="pelanggans-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>ID Pelanggan</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>remark</th>
                                    <th>Paket</th>
                                    <th>Server</th>
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

{{-- Modal Create / Edit --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ID Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="id_pelanggan" name="id_pelanggan" required>
                            <div class="invalid-feedback" id="idPelangganErr"></div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                            <div class="invalid-feedback" id="namaErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <small class="text-muted">(kosongkan saat edit jika tidak
                                    berubah)</small></label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="invalid-feedback" id="passwordErr"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Paket</label>
                            <select class="form-select" id="id_paket" name="id_paket">
                                <option value="">— pilih —</option>
                                @foreach ($pakets as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Server</label>
                            <select class="form-select" id="id_server" name="id_server">
                                <option value="">— pilih —</option>
                                @foreach ($servers as $s)
                                    <option value="{{ $s->id }}">{{ $s->ip }} {{ $s->lokasi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">IP Router</label>
                            <input type="text" class="form-control" id="ip_router" name="ip_router">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Parent Router</label>
                            <input type="text" class="form-control" id="ip_parent_router" name="ip_parent_router">
                        </div>

                        <div class="col-md-12"><label class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                        </div>

                        <div class="col-md-12"><label class="form-label">Remark 1</label>
                            <input type="text" class="form-control" id="remark1" name="remark1">
                        </div>
                        <div class="col-md-6"><label class="form-label">Remark 2</label>
                            <input type="text" class="form-control" id="remark2" name="remark2">
                        </div>
                        <div class="col-md-6"><label class="form-label">Remark 3</label>
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

            function safeFeatherReplace() {
                if (!window.feather || !feather.icons) return;
                document.querySelectorAll('[data-feather]').forEach(el => {
                    const name = el.getAttribute('data-feather') || 'settings';
                    if (!feather.icons[name]) el.setAttribute('data-feather', 'settings');
                });
                try {
                    feather.replace();
                } catch (e) {}
            }

            const DT_SEL = '#pelanggans-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const ids = ['id_pelanggan', 'nama', 'alamat', 'no_hp', 'email', 'password', 'id_paket', 'id_server',
                'ip_router', 'ip_parent_router', 'remark1', 'remark2', 'remark3'
            ];
            const $f = Object.fromEntries(ids.map(id => [id, $('#' + id)]));
            const $err = {
                id_pelanggan: $('#idPelangganErr'),
                nama: $('#namaErr'),
                password: $('#passwordErr')
            };

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$f.id_pelanggan, $f.nama, $f.password].forEach($el => $el.removeClass('is-invalid'));
                $err.id_pelanggan.text('');
                $err.nama.text('');
                $err.password.text('');
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

            function reloadTable() {
                $(DT_SEL).DataTable().ajax.reload(null, false);
            }

            // DataTablee
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.pelanggans.dt') }}',
                columns: [{
                        data: 'id_pelanggan',
                        name: 'pelanggans.id_pelanggan'
                    },
                    {
                        data: 'nama',
                        name: 'pelanggans.nama'
                    },
                    {
                        data: 'alamat',
                        name: 'pelanggans.alamat'
                    },
                    {
                        data: 'remark1',
                        name: 'pelanggans.remark1'
                    },
                    {
                        data: 'paket_nama',
                        name: 'pakets.nama'
                    },
                    {
                        data: 'server_lokasi',
                        name: 'servers.lokasi'
                    },
                    {
                        data: 'updated_at',
                        name: 'pelanggans.updated_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [6, 'desc']
                ]
            }).on('draw.dt', safeFeatherReplace);

            // New
            $('#btnNewItem').on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.pelanggans.store') }}');
                $title.text('New Pelanggan');
                bsModal.show();
            });

            // Edit (kelas partial: .btn-edit-role)
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                $form[0].reset();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Pelanggan');

                Object.entries(payload).forEach(([k, v]) => {
                    if ($f[k]) $f[k].val(v ?? '');
                });
                $f.password.val(''); // selalu kosong saat edit
                bsModal.show();
            });

            // Submit
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();
                const mode = $form.data('mode');
                const action = $form.data('action');
                const fd = new FormData($form[0]);
                if (mode === 'edit') {
                    fd.append('_method', 'PUT');
                    if (!$f.password.val()) {
                        fd.set('password', '');
                    }
                }

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
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const e = xhr.responseJSON.errors;
                            if (e.id_pelanggan) {
                                $f.id_pelanggan.addClass('is-invalid');
                                $err.id_pelanggan.text(e.id_pelanggan[0]);
                            }
                            if (e.nama) {
                                $f.nama.addClass('is-invalid');
                                $err.nama.text(e.nama[0]);
                            }
                            if (e.password) {
                                $f.password.addClass('is-invalid');
                                $err.password.text(e.password[0]);
                            }
                        } else {
                            toastErr(xhr.responseJSON?.message);
                        }
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete (kelas partial: .btn-delete-role)
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                let name = 'pelanggan';
                try {
                    name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').nama) ||
                        'pelanggan';
                } catch (e) {}
                if (!window.Swal) {
                    if (confirm(`Delete ${name}?`)) {
                        $.post(url, {
                            _method: 'DELETE'
                        }).done(() => {
                            toastOk('Deleted');
                            reloadTable();
                        }).fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                    }
                    return;
                }
                Swal.fire({
                        icon: 'warning',
                        title: 'Delete?',
                        text: `Delete ${name}?`,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete'
                    })
                    .then(r => {
                        if (!r.isConfirmed) return;
                        $.post(url, {
                            _method: 'DELETE'
                        }).done(() => {
                            toastOk('Deleted');
                            reloadTable();
                        }).fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                    });
            });

            // Export (ikut keyword global DataTables)
            $('#btnExport').on('click', function() {
                const q = $(DT_SEL).DataTable().search() || '';
                const url = @json(route('super.pelanggans.export'));
                window.location = url + '?q=' + encodeURIComponent(q);
            });

            modalEl.addEventListener('hidden.bs.modal', clearErrors);
        })(jQuery);
    </script>
@endpush
