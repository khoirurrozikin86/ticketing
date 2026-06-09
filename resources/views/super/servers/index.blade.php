@extends('layouts.admin')
@section('title', 'Servers')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Servers</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Servers</h6>
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
                        <table id="servers-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>User</th>
                                    <th>Lokasi</th>
                                    <th>No INT</th>
                                    <th>Mikrotik</th>
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
                    <h5 class="modal-title" id="itemModalTitle">New Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">IP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ip" name="ip" required>
                            <div class="invalid-feedback" id="ipErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">User <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user" name="user" required>
                            <div class="invalid-feedback" id="userErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-control" id="password" name="password"
                                placeholder="(kosongkan jika tidak berubah)">
                            <div class="invalid-feedback" id="passwordErr"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                            <div class="invalid-feedback" id="lokasiErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No INT</label>
                            <input type="text" class="form-control" id="no_int" name="no_int">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mikrotik</label>
                            <input type="text" class="form-control" id="mikrotik" name="mikrotik">
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

            const DT_SEL = '#servers-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $ip = $('#ip'),
                $user = $('#user'),
                $password = $('#password'),
                $lokasi = $('#lokasi'),
                $no_int = $('#no_int'),
                $mikrotik = $('#mikrotik'),
                $remark1 = $('#remark1'),
                $remark2 = $('#remark2'),
                $remark3 = $('#remark3');

            const $ipErr = $('#ipErr'),
                $userErr = $('#userErr'),
                $passwordErr = $('#passwordErr'),
                $lokasiErr = $('#lokasiErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$ip, $user, $password, $lokasi].forEach($el => $el.removeClass('is-invalid'));
                $ipErr.text('');
                $userErr.text('');
                $passwordErr.text('');
                $lokasiErr.text('');
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

            // DataTable
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.servers.dt') }}',
                columns: [{
                        data: 'ip',
                        name: 'ip'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'lokasi',
                        name: 'lokasi'
                    },
                    {
                        data: 'no_int',
                        name: 'no_int'
                    },
                    {
                        data: 'mikrotik',
                        name: 'mikrotik'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
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
            }).on('draw.dt', safeFeatherReplace);

            // New
            $('#btnNewItem').on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.servers.store') }}');
                $title.text('New Server');
                bsModal.show();
            });

            // Edit (kelas dari partial: .btn-edit-role)
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Server');
                $ip.val(payload.ip || '');
                $user.val(payload.user || '');
                $password.val(''); // kosongkan
                $lokasi.val(payload.lokasi || '');
                $no_int.val(payload.no_int || '');
                $mikrotik.val(payload.mikrotik || '');
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
                if (mode === 'edit') {
                    fd.append('_method', 'PUT');
                }
                // jika password kosong saat edit → kirim string kosong (controller/action akan mengabaikan)
                if (mode === 'edit' && !$password.val()) {
                    fd.set('password', '');
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
                            if (e.ip) {
                                $ip.addClass('is-invalid');
                                $ipErr.text(e.ip[0]);
                            }
                            if (e.user) {
                                $user.addClass('is-invalid');
                                $userErr.text(e.user[0]);
                            }
                            if (e.password) {
                                $password.addClass('is-invalid');
                                $passwordErr.text(e.password[0]);
                            }
                            if (e.lokasi) {
                                $lokasi.addClass('is-invalid');
                                $lokasiErr.text(e.lokasi[0]);
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
                let name = 'server';
                try {
                    name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').ip) ||
                        'server';
                } catch (e) {}
                if (!window.Swal) {
                    if (confirm(`Delete ${name}?`)) {
                        $.post(url, {
                                _method: 'DELETE'
                            }).done(() => {
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
                    })
                    .then(r => {
                        if (!r.isConfirmed) return;
                        $.post(url, {
                                _method: 'DELETE'
                            }).done(() => {
                                toastOk('Deleted');
                                reloadTable();
                            })
                            .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                    });
            });

            // Export (ikut keyword global DataTables)
            $('#btnExport').on('click', function() {
                const q = $(DT_SEL).DataTable().search() || '';
                const url = @json(route('super.servers.export'));
                window.location = url + '?q=' + encodeURIComponent(q);
            });

            modalEl.addEventListener('hidden.bs.modal', clearErrors);
        })(jQuery);
    </script>
@endpush
