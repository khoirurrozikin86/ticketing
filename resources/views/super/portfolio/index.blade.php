@extends('layouts.admin')
@section('title', 'Portfolio')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Content</a></li>
            <li class="breadcrumb-item active" aria-current="page">Portfolio</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Portfolio</h6>
                        <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="portfolio-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Thumb</th>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Tags</th>
                                    <th>Order</th>
                                    <th>Created</th>
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
                    <h5 class="modal-title" id="itemModalTitle">New Portfolio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback" id="titleErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required>
                            <div class="invalid-feedback" id="slugErr"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Summary</label>
                            <textarea class="form-control" id="summary" name="summary" rows="4" placeholder="Short summary..."></textarea>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                placeholder="CMS, POS">
                            <div class="form-text">Akan dikirim sebagai JSON array ke server</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input type="number" class="form-control" id="order" name="order" value="0"
                                min="0" step="1">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Thumbnail</span>
                                <small class="text-muted">.jpg/.png</small>
                            </label>
                            <input type="file" class="form-control" id="thumb" name="thumb" accept="image/*">
                            <div
                                class="ratio ratio-16x9 mt-2 border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden">
                                <img id="thumbPreview" src="" alt="Preview"
                                    class="w-100 h-100 object-fit-cover d-none">
                                <span id="thumbEmpty" class="text-muted small">No image</span>
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

            const DT_SEL = '#portfolio-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $titleTxt = $('#itemModalTitle');

            const $title = $('#title');
            const $slug = $('#slug');
            const $summary = $('#summary');
            const $tags = $('#tags');
            const $order = $('#order');
            const $thumb = $('#thumb');
            const $thumbPreview = $('#thumbPreview');
            const $thumbEmpty = $('#thumbEmpty');

            const $titleErr = $('#titleErr'),
                $slugErr = $('#slugErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$title, $slug].forEach($f => $f.removeClass('is-invalid'));
                $titleErr.text('');
                $slugErr.text('');
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

            // Auto-slug: generate saat mengetik title (bisa diedit)
            $title.on('input', function() {
                if ($slug.data('dirty')) return; // kalau user sudah edit slug, jangan auto overwrite
                const s = String($(this).val() || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                $slug.val(s);
            });
            $slug.on('input', () => $slug.data('dirty', true));

            // Thumbnail preview
            $thumb.on('change', function() {
                const [file] = this.files || [];
                if (!file) {
                    $thumbPreview.addClass('d-none');
                    $thumbEmpty.removeClass('d-none');
                    return;
                }
                const url = URL.createObjectURL(file);
                $thumbPreview.attr('src', url).removeClass('d-none');
                $thumbEmpty.addClass('d-none');
            });

            // DataTable (serverSide)
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.portfolios.dt') }}',
                columns: [{ // thumb: server sudah render <img> atau fallback div


                        data: 'thumb',
                        name: 'thumb',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'slug',
                        name: 'slug'
                    },
                    { // tags: server bisa kirim HTML badges, atau array/str
                        data: 'tags',
                        name: 'tags',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'order',
                        name: 'order'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
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
                if (window.feather) feather.replace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $slug.data('dirty', false);
                $thumbPreview.addClass('d-none').attr('src', '');
                $thumbEmpty.removeClass('d-none');
                $form.data('mode', 'create').data('action', '{{ route('super.portfolios.store') }}');
                $titleTxt.text('New Portfolio');
                bsModal.show();
            });

            // Edit (expects .btn-edit-role with data-payload)
            $(document).on('click', '.btn-edit-role', function() {
                clearErrors();
                let payload = {};
                try {
                    payload = JSON.parse($(this).attr('data-payload') || '{}');
                } catch (e) {}

                $form.data('mode', 'edit').data('action', $(this).data('update-url'));
                $titleTxt.text('Edit Portfolio');

                $title.val(payload.title ?? $(this).data('title') ?? '');
                $slug.val(payload.slug ?? $(this).data('slug') ?? '').data('dirty', true);
                $summary.val(payload.summary ?? $(this).data('summary') ?? '');
                // tags bisa string "A,B" atau array
                const tagsStr = Array.isArray(payload.tags) ? payload.tags.join(', ') : (payload.tags ?? '');
                $tags.val(tagsStr);
                $order.val(payload.order ?? $(this).data('order') ?? 0);

                const thumbUrl = payload.thumbnail_url ?? $(this).data('thumbnail-url') ?? '';
                if (thumbUrl) {
                    $thumbPreview.attr('src', thumbUrl).removeClass('d-none');
                    $thumbEmpty.addClass('d-none');
                } else {
                    $thumbPreview.addClass('d-none').attr('src', '');
                    $thumbEmpty.removeClass('d-none');
                }

                bsModal.show();
            });

            // Submit (Create/Update) — FormData + tags -> JSON
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const mode = $form.data('mode');
                const action = $form.data('action');

                const fd = new FormData($form[0]);
                if (mode === 'edit') fd.append('_method', 'PUT');

                // Normalisasi TAGS: "CMS, POS" -> ["CMS","POS"]
                const rawTags = String(fd.get('tags') || '');
                const tagsArr = rawTags.split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
                fd.set('tags', JSON.stringify(tagsArr)); // kirim sebagai JSON string

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
                            if (errs.title) {
                                $title.addClass('is-invalid');
                                $titleErr.text(errs.title[0]);
                            }
                            if (errs.slug) {
                                $slug.addClass('is-invalid');
                                $slugErr.text(errs.slug[0]);
                            }
                            return;
                        }
                        toastErr(xhr.responseJSON?.message);
                    })
                    .always(() => $btnSave.prop('disabled', false).text('Save'));
            });

            // Delete (expects .btn-delete-role seperti Users)
            $(document).on('click', '.btn-delete-role', function() {
                const url = $(this).data('url');
                const name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').title) ||
                    'this item';
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

            modalEl.addEventListener('hidden.bs.modal', clearErrors);

        })(jQuery);
    </script>
@endpush
