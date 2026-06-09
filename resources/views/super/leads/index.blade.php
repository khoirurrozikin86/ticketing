{{-- resources/views/super/leads/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Leads')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Sales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Leads</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Leads</h6>
                        <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="leads-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Message</th>
                                    <th>Status</th>
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

{{-- Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="emailErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" name="company">
                        </div>

                        <div class="col-md-9">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us your needs..."
                                required></textarea>
                            <div class="invalid-feedback" id="messageErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="closed">Closed</option>
                            </select>
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

            // Feather guard (untuk icons di partial actions)
            function safeFeatherReplace() {
                if (!window.feather || !feather.icons) return;
                document.querySelectorAll('[data-feather]').forEach(function(el) {
                    const n = el.getAttribute('data-feather') || 'settings';
                    if (!feather.icons[n]) el.setAttribute('data-feather', 'settings');
                });
                try {
                    feather.replace();
                } catch (e) {}
            }

            const DT_SEL = '#leads-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $name = $('#name');
            const $email = $('#email');
            const $company = $('#company');
            const $message = $('#message');
            const $status = $('#status');

            const $nameErr = $('#nameErr'),
                $emailErr = $('#emailErr'),
                $messageErr = $('#messageErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$name, $email, $message].forEach($f => $f.removeClass('is-invalid'));
                $nameErr.text('');
                $emailErr.text('');
                $messageErr.text('');
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
                ajax: '{{ route('super.leads.dt') }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'company',
                        name: 'company',
                        defaultContent: '—'
                    },
                    {
                        data: 'message',
                        name: 'message',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
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
            }).on('draw.dt', function() {
                safeFeatherReplace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $status.val('new');
                $form.data('mode', 'create').data('action', '{{ route('super.leads.store') }}');
                $title.text('New Lead');
                bsModal.show();
            });

            // Edit
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}

                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Lead');

                $name.val(payload.name || '');
                $email.val(payload.email || '');
                $company.val(payload.company || '');
                $message.val(payload.message || '');
                $status.val(payload.status || 'new');

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
                            if (errs.email) {
                                $email.addClass('is-invalid');
                                $emailErr.text(errs.email[0]);
                            }
                            if (errs.message) {
                                $message.addClass('is-invalid');
                                $messageErr.text(errs.message[0]);
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
                const name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').name) ||
                    'this lead';
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
