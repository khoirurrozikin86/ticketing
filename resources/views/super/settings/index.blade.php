{{-- resources/views/super/settings/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Settings')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">System</a></li>
            <li class="breadcrumb-item active" aria-current="page">Settings</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Settings</h6>
                        <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="settings-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Value</th>
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
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="itemForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="key" name="key" required>
                            <div class="invalid-feedback" id="keyErr"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Logo (opsional)</span><small class="text-muted">.jpg/.png</small>
                            </label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <div
                                class="ratio ratio-16x9 mt-2 border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden">
                                <img id="logoPreview" src="" alt="Logo preview"
                                    class="w-100 h-100 object-fit-cover d-none">
                                <span id="logoEmpty" class="text-muted small">No image</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Value (JSON)</label>
                            <textarea class="form-control font-monospace" id="value" name="value" rows="10"
                                placeholder='{"name":"OZNET Systems","email":"..."}'></textarea>
                            <div class="form-text">Masukkan JSON valid. Jika upload logo, field <code>logo</code> akan
                                diisi otomatis.</div>
                            <div class="invalid-feedback" id="valueErr"></div>
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

            const DT_SEL = '#settings-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $key = $('#key');
            const $value = $('#value');
            const $logo = $('#logo');
            const $logoPreview = $('#logoPreview');
            const $logoEmpty = $('#logoEmpty');

            const $keyErr = $('#keyErr'),
                $valueErr = $('#valueErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$key, $value].forEach($f => $f.removeClass('is-invalid'));
                $keyErr.text('');
                $valueErr.text('');
            }

            function toastOk(msg) {
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false,
                    title: msg || 'Success'
                });
            }

            function toastErr(msg) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg || 'Something went wrong'
                });
            }

            function reloadTable() {
                $(DT_SEL).DataTable().ajax.reload(null, false);
            }

            // preview logo
            $logo.on('change', function() {
                const [file] = this.files || [];
                if (!file) {
                    $logoPreview.addClass('d-none');
                    $logoEmpty.removeClass('d-none');
                    return;
                }
                const url = URL.createObjectURL(file);
                $logoPreview.attr('src', url).removeClass('d-none');
                $logoEmpty.addClass('d-none');
            });

            // DT
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.settings.dt') }}',
                columns: [{
                        data: 'key',
                        name: 'key'
                    },
                    {
                        data: 'value_preview',
                        name: 'value'
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
            }).on('draw.dt', function() {
                if (window.feather) feather.replace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $logoPreview.addClass('d-none').attr('src', '');
                $logoEmpty.removeClass('d-none');
                $form.data('mode', 'create').data('action', '{{ route('super.settings.store') }}');
                $title.text('New Setting');
                bsModal.show();
            });

            // Edit
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Setting');

                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}

                $key.val(payload.key || '');
                $value.val(payload.value || '{}');

                const logoUrl = payload.logo_url || '';
                if (logoUrl) {
                    $logoPreview.attr('src', logoUrl).removeClass('d-none');
                    $logoEmpty.addClass('d-none');
                } else {
                    $logoPreview.addClass('d-none').attr('src', '');
                    $logoEmpty.removeClass('d-none');
                }

                bsModal.show();
            });

            // Client-side JSON validate
            function assertJSON(str) {
                if (!str) return true;
                try {
                    JSON.parse(str);
                    return true;
                } catch (e) {
                    return false;
                }
            }

            // Submit
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                // validasi JSON cepat
                const val = String($value.val() || '');
                if (val && !assertJSON(val)) {
                    $value.addClass('is-invalid');
                    $valueErr.text('JSON tidak valid');
                    return;
                }

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
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errs = xhr.responseJSON.errors;
                            if (errs.key) {
                                $key.addClass('is-invalid');
                                $keyErr.text(errs.key[0]);
                            }
                            if (errs.value) {
                                $value.addClass('is-invalid');
                                $valueErr.text(errs.value[0]);
                            }
                            return;
                        }
                        toastErr(xhr.responseJSON?.message);
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                const name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').key) ||
                    'this item';
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
                            })
                            .done(() => {
                                toastOk('Deleted');
                                reloadTable();
                            })
                            .fail(xhr => toastErr(xhr.responseJSON?.message || 'Failed'));
                    });
            });

            modalEl.addEventListener('hidden.bs.modal', clearErrors);
        })(jQuery);
    </script>
@endpush
