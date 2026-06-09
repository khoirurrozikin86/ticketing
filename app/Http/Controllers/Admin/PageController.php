<?php
// app/Http/Controllers/Super/PageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageStoreRequest;
use App\Http\Requests\Admin\PageUpdateRequest;

use App\Domain\Pages\Queries\PageTableQuery;
use App\Domain\Pages\Services\PageService;
use App\Models\Page;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    public function index()
    {
        return view('super.pages.index');
    }

    public function datatable(PageTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn(
                'published',
                fn(Page $p) =>
                $p->published
                    ? '<span class="badge bg-success">Published</span>'
                    : '<span class="badge bg-secondary">Draft</span>'
            )
            ->editColumn('updated_at', fn(Page $p) => optional($p->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Page $p) {
                $pretty = json_encode($p->content ?? new \stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.pages.update', $p),
                        'payload'    => [
                            'title'     => $p->title,
                            'slug'      => $p->slug,
                            'content'   => $pretty,
                            'published' => $p->published,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.pages.destroy', $p),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete page {$p->title}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['published', 'actions'])
            ->toJson();
    }

    public function store(PageStoreRequest $req, PageService $svc)
    {
        $page = $svc->create($req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Page created', 'id' => $page->id], 201);
        }
        return redirect()->route('super.pages.index')->with('success', 'Page created');
    }

    public function update(PageUpdateRequest $req, Page $page, PageService $svc)
    {
        $svc->update($page, $req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Page updated']);
        }
        return back()->with('success', 'Page updated');
    }

    public function destroy(Page $page, PageService $svc)
    {
        $svc->delete($page);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Page deleted']);
        }
        return redirect()->route('super.pages.index')->with('success', 'Page deleted');
    }
}
