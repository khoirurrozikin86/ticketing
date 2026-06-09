{{-- resources/views/super/pages/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Pages')

@section('breadcrumb')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Content</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pages</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Pages</h6>
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0)" id="btnAboutShortcut" class="btn btn-outline-secondary btn-sm">Edit
                                “About”</a>
                            <a href="javascript:void(0)" id="btnNewItem" class="btn btn-primary btn-sm">+ New</a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="pages-table" class="table w-100">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Published</th>
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
                    <h5 class="modal-title" id="itemModalTitle">New Page</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback" id="titleErr"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required
                                placeholder="about">
                            <div class="invalid-feedback" id="slugErr"></div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="published" name="published" checked>
                                <label class="form-check-label" for="published">Published</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Content (JSON)</label>
                            <textarea class="form-control font-monospace" id="content" name="content" rows="12"
                                placeholder='{"hero":{"title":"About Us"},"sections":[...]}'></textarea>
                            <div class="invalid-feedback" id="contentErr"></div>
                            <div class="form-text">Masukkan JSON valid (block-based). Contoh sederhana:
                                <code>{"body":"Lorem ipsum ..."}</code>
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

            // Feather guard (optional)
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

            const DT_SEL = '#pages-table';
            const modalEl = document.getElementById('itemModal');
            const bsModal = new bootstrap.Modal(modalEl);
            const $form = $('#itemForm');
            const $btnNew = $('#btnNewItem');
            const $btnSave = $('#btnSaveItem');
            const $titleTxt = $('#itemModalTitle');
            const $btnAbout = $('#btnAboutShortcut');

            const $title = $('#title');
            const $slug = $('#slug');
            const $content = $('#content');
            const $published = $('#published');

            const $titleErr = $('#titleErr'),
                $slugErr = $('#slugErr'),
                $contentErr = $('#contentErr');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            function clearErrors() {
                [$title, $slug, $content].forEach($f => $f.removeClass('is-invalid'));
                $titleErr.text('');
                $slugErr.text('');
                $contentErr.text('');
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

            function isJSON(str) {
                if (!str) return true;
                try {
                    JSON.parse(str);
                    return true;
                } catch (e) {
                    return false;
                }
            }

            // DataTable
            $(DT_SEL).DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('super.pages.dt') }}',
                columns: [{
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'slug',
                        name: 'slug'
                    },
                    {
                        data: 'published',
                        name: 'published',
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
                    [3, 'desc']
                ]
            }).on('draw.dt', function() {
                safeFeatherReplace();
            });

            // New
            $btnNew.on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.pages.store') }}');
                $titleTxt.text('New Page');
                $published.prop('checked', true);
                bsModal.show();
            });

            // Shortcut: Edit/Create “About”
            $btnAbout.on('click', function() {
                clearErrors();
                $form[0].reset();
                $form.data('mode', 'create').data('action', '{{ route('super.pages.store') }}');
                $titleTxt.text('Edit “About”');

                $title.val('About');
                $slug.val('about');
                $published.prop('checked', true);
                $content.val(
                    `{
  "hero": {"title":"About Us","subtitle":"Who we are"},
  "body": "Tuliskan konten tentang perusahaan."
}`
                );
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
                $titleTxt.text('Edit Page');

                $title.val(payload.title || '');
                $slug.val(payload.slug || '');
                $content.val(payload.content || '{}');
                $published.prop('checked', !!payload.published);

                bsModal.show();
            });

            // Submit
            $form.on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const contentStr = String($content.val() || '');
                if (contentStr && !isJSON(contentStr)) {
                    $content.addClass('is-invalid');
                    $contentErr.text('JSON tidak valid');
                    return;
                }

                const mode = $form.data('mode');
                const action = $form.data('action');
                const fd = new FormData($form[0]);
                if (mode === 'edit') fd.append('_method', 'PUT');
                // checkbox -> boolean
                fd.set('published', $('#published').is(':checked') ? 1 : 0);

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
                            if (errs.content) {
                                $content.addClass('is-invalid');
                                $contentErr.text(errs.content[0]);
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
                const name = $(this).data('name') || (JSON.parse($(this).attr('data-payload') || '{}').title) ||
                    'this page';
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
