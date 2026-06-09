@extends('layouts.admin')
@section('title', 'Tech Stack')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Content</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tech Stack</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Tech Stack</h6>
                        <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="tech-stacks-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Order</th>
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
                    <h5 class="modal-title" id="itemModalTitle">New Tech</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input type="number" class="form-control" id="order" name="order" value="0"
                                min="0" step="1">
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

            const DT_SEL = '#tech-stacks-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $name = $('#name');
            const $order = $('#order');
            const $nameErr = $('#nameErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                $name.removeClass('is-invalid');
                $nameErr.text('');
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

            // DataTable
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.tech-stacks.dt') }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'order',
                        name: 'order'
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
                safeFeatherReplace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.tech-stacks.store') }}');
                $title.text('New Tech');
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
                $title.text('Edit Tech');

                $name.val(payload.name || '');
                $order.val(payload.order ?? 0);
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
                        bsModal.hide();
                        reloadTable();
                    })
                    .fail(xhr => {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errs = xhr.responseJSON.errors;
                            if (errs.name) {
                                $name.addClass('is-invalid');
                                $nameErr.text(errs.name[0]);
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
                let name = 'this item';
                try {
                    name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').name) ||
                        'this item';
                } catch (e) {}
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
