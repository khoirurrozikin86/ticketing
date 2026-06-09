{{-- resources/views/super/services/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Services')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Content</a></li>
            <li class="breadcrumb-item active" aria-current="page">Services</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Services</h6>
                        <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="services-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Excerpt</th>
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

{{-- Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="itemForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameErr"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Icon</span> <small class="text-muted">lucide/feather name atau path</small>
                            </label>
                            <input type="text" class="form-control" id="icon" name="icon"
                                placeholder="server">
                            <div class="form-text">Contoh: <code>server</code> (Feather) atau
                                <code>settings/icon.svg</code>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Order</label>
                            <input type="number" class="form-control" id="order" name="order" value="0"
                                min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Short description..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Meta (JSON)</label>
                            <textarea class="form-control font-monospace" id="meta" name="meta" rows="8"
                                placeholder='{"note":"additional info"}'></textarea>
                            <div class="invalid-feedback" id="metaErr"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Icon Preview</label>
                            <div class="border rounded p-3 d-flex align-items-center gap-3">
                                <div id="iconPreviewBox" class="d-flex align-items-center justify-content-center"
                                    style="width:42px;height:42px;">
                                    <i data-feather="settings"></i>
                                </div>
                                <small class="text-muted">Jika diisi nama Feather (contoh: <code>server</code>), preview
                                    akan muncul. Untuk path file atau SVG inline, preview ada saat tersimpan & dirender
                                    dari server.</small>
                            </div>
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

            // ===== Feather: safe replace (hindari undefined.toSvg) =====
            const FEATHER_FALLBACK = 'settings';

            function isFeatherReady() {
                return !!(window.feather && feather.icons);
            }

            function sanitizeIconName(raw) {
                if (!raw) return FEATHER_FALLBACK;
                // hanya alnum dan dash
                const name = String(raw).trim().toLowerCase().replace(/[^a-z0-9-]/g, '');
                if (!name) return FEATHER_FALLBACK;
                if (!isFeatherReady()) return name; // belum bisa cek ketersediaan
                return feather.icons[name] ? name : FEATHER_FALLBACK;
            }

            function safeFeatherReplace(scope) {
                if (!isFeatherReady()) return;
                const root = scope ? (scope instanceof $ ? scope[0] : scope) : document;
                root.querySelectorAll('[data-feather]').forEach(function(el) {
                    const valid = sanitizeIconName(el.getAttribute('data-feather'));
                    el.setAttribute('data-feather', valid);
                });
                try {
                    feather.replace();
                } catch (e) {
                    /* noop */
                }
            }

            // ====== VARS ======
            const DT_SEL = '#services-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $title = $('#itemModalTitle');

            const $name = $('#name');
            const $icon = $('#icon');
            const $excerpt = $('#excerpt');
            const $meta = $('#meta');
            const $order = $('#order');

            const $nameErr = $('#nameErr'),
                $metaErr = $('#metaErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$name, $meta].forEach($f => $f.removeClass('is-invalid'));
                $nameErr.text('');
                $metaErr.text('');
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

            // ===== Preview Feather di modal (aman) =====
            const $iconPreviewBox = $('#iconPreviewBox');

            function refreshIconPreview() {
                const raw = ($icon.val() || '');
                // kalau user isi path file/inline svg, preview pakai fallback feather
                const isFeatherName = raw && !raw.includes('/') && !raw.includes('<');
                const name = isFeatherName ? sanitizeIconName(raw) : FEATHER_FALLBACK;
                $iconPreviewBox.html(`<i data-feather="${name}"></i>`);
                safeFeatherReplace($iconPreviewBox[0]);
            }
            $icon.on('input', refreshIconPreview);

            // ===== DataTable =====
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.services.dt') }}',
                columns: [{
                        data: 'icon',
                        name: 'icon',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'excerpt',
                        name: 'excerpt'
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
                    [4, 'desc']
                ]
            }).on('draw.dt', function() {
                // Normalisasi ikon dari server (baik <i data-feather="..."> maupun yang salah)
                safeFeatherReplace(document);
            });

            // ===== New =====
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.services.store') }}');
                $title.text('New Service');
                refreshIconPreview();
                bsModal.show();
            });

            // ===== Edit =====
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}
                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $title.text('Edit Service');

                $name.val(payload.name || '');
                $icon.val(payload.icon || '');
                $excerpt.val(payload.excerpt || '');
                $meta.val(payload.meta || '{}');
                $order.val(payload.order ?? 0);

                refreshIconPreview();
                bsModal.show();
            });

            // ===== Validate JSON =====
            function isJson(str) {
                if (!str) return true;
                try {
                    JSON.parse(str);
                    return true;
                } catch (e) {
                    return false;
                }
            }

            // ===== Submit =====
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const metaStr = String($meta.val() || '');
                if (metaStr && !isJson(metaStr)) {
                    $meta.addClass('is-invalid');
                    $metaErr.text('JSON tidak valid');
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
                            if (errs.name) {
                                $name.addClass('is-invalid');
                                $nameErr.text(errs.name[0]);
                            }
                            if (errs.meta) {
                                $meta.addClass('is-invalid');
                                $metaErr.text(errs.meta[0]);
                            }
                        } else {
                            toastErr(xhr.responseJSON?.message);
                        }
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // ===== Delete =====
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

            // ===== Cleanup =====
            modalEl.addEventListener('hidden.bs.modal', clearErrors);

            // Initial pass (kalau sudah ada ikon di DOM)
            safeFeatherReplace(document);

        })(jQuery);
    </script>
@endpush
