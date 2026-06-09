@extends('layouts.admin')
@section('title', 'Bulan')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bulan</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Daftar Bulan</h6>
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
                        <table id="bulans-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>ID Bulan</th>
                                    <th>Bulan</th>
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
                    <h5 class="modal-title" id="itemModalTitle">New Bulan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">ID Bulan <small class="text-muted">(2 char, ex: 01..12)</small>
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="id_bulan" name="id_bulan" maxlength="2"
                                required>
                            <div class="invalid-feedback" id="idBulanErr"></div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Bulan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bulan" name="bulan" required>
                            <div class="invalid-feedback" id="bulanErr"></div>
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
                if (!window.feather || !feather.icons) return;
                document.querySelectorAll('[data-feather]').forEach(el => {
                    const name = el.getAttribute('data-feather') || 'settings';
                    if (!feather.icons[name]) el.setAttribute('data-feather', 'settings');
                });
                try {
                    feather.replace();
                } catch (e) {}
            }

            const DT_SEL = '#bulans-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnSave = $('#btnSaveItem');

            const $id_bulan = $('#id_bulan');
            const $bulan = $('#bulan');
            const $idBulanErr = $('#idBulanErr');
            const $bulanErr = $('#bulanErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$id_bulan, $bulan].forEach($el => $el.removeClass('is-invalid'));
                $idBulanErr.text('');
                $bulanErr.text('');
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
                ajax: '{{ route('super.bulans.dt') }}',
                columns: [{
                        data: 'id_bulan',
                        name: 'id_bulan'
                    },
                    {
                        data: 'bulan',
                        name: 'bulan'
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
                    [2, 'desc']
                ]
            }).on('draw.dt', safeFeatherReplace);

            // New
            $('#btnNewItem').on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.bulans.store') }}');
                $('#itemModalTitle').text('New Bulan');
                bsModal.show();
            });

            // Edit
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                $form[0].reset();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $('#itemModalTitle').text('Edit Bulan');

                $id_bulan.val(payload.id_bulan || '');
                $bulan.val(payload.bulan || '');
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
                            if (e.id_bulan) {
                                $id_bulan.addClass('is-invalid');
                                $idBulanErr.text(e.id_bulan[0]);
                            }
                            if (e.bulan) {
                                $bulan.addClass('is-invalid');
                                $bulanErr.text(e.bulan[0]);
                            }
                        } else {
                            toastErr(xhr.responseJSON?.message);
                        }
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                let name = 'bulan';
                try {
                    name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').bulan) ||
                        'bulan';
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

            // Export
            $('#btnExport').on('click', function() {
                const q = $(DT_SEL).DataTable().search() || '';
                const url = @json(route('super.bulans.export'));
                window.location = url + '?q=' + encodeURIComponent(q);
            });

            modalEl.addEventListener('hidden.bs.modal', clearErrors);
        })(jQuery);
    </script>
@endpush
