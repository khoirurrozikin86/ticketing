<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioStoreRequest;
use App\Http\Requests\Admin\PortfolioUpdateRequest;

use App\Domain\Portfolio\DTOs\PortfolioData;
use App\Domain\Portfolio\Actions\CreatePortfolioAction;
use App\Domain\Portfolio\Actions\UpdatePortfolioAction;
use App\Domain\Portfolio\Actions\DeletePortfolioAction;
use Yajra\DataTables\Facades\DataTables;

use App\Domain\Portfolio\Queries\PortfolioQuery; // <- builder-only (mirip UserTableQuery)
use App\Support\DataTables\Responder;

use App\Models\PortfolioItem;

class PortfolioController extends Controller
{
    public function index()
    {
        // View index disamakan dengan Users (area super)
        return view('super.portfolio.index');
    }

    /**
     * DataTables server-side — mengikuti pola UserController@datatable
     */


    // ...

    public function datatable(\App\Domain\Portfolio\Queries\PortfolioQuery $q)
    {
        $builder = $q->builder(); // builder-only: select id,title,slug,summary,thumb_path,tags,order,created_at

        return DataTables::eloquent($builder)
            ->addColumn('thumb', function (PortfolioItem $p) {
                if (!empty($p->thumb_path)) {
                    $filePath = storage_path('app/public/' . $p->thumb_path);
                    if (is_file($filePath)) {
                        $url = asset('storage/' . $p->thumb_path);
                        return '<img src="' . e($url) . '" class="rounded object-cover" width="80" height="60" alt="thumb">';
                    }
                }
                // placeholder aman
                return '<div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:80px;height:60px;">
                        <i data-feather="image" class="text-muted"></i>
                    </div>';
            })
            ->editColumn('tags', function (PortfolioItem $p) {
                // dukung kolom json (array) atau string json
                $tags = $p->tags;
                if (is_string($tags)) {
                    $decoded = json_decode($tags, true);
                    $tags = is_array($decoded) ? $decoded : [];
                }
                if (!is_array($tags) || empty($tags)) return '-';

                return collect($tags)->map(
                    fn($t) =>
                    '<span class="badge bg-light text-dark">' . e($t) . '</span>'
                )->implode(' ');
            })
            ->editColumn(
                'created_at',
                fn(PortfolioItem $p) =>
                optional($p->created_at)->format('Y-m-d H:i')
            )
            ->addColumn('actions', function (PortfolioItem $p) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.portfolios.update', $p),
                        'payload'    => [
                            'title'         => $p->title,
                            'slug'          => $p->slug,
                            'summary'       => $p->summary,
                            'tags'          => is_array($p->tags) ? implode(', ', $p->tags) : (string)($p->tags ?? ''),
                            'order'         => $p->order,
                            'thumbnail_url' => $p->thumb_path ? asset('storage/' . $p->thumb_path) : null,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.portfolios.destroy', $p),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete portfolio {$p->title}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['thumb', 'tags', 'actions'])
            ->toJson();
    }


    public function create()
    {
        // opsional (kalau pakai modal tidak wajib)
        $item = new PortfolioItem(['order' => 0]);
        return view('super.portfolio.form', compact('item'));
    }

    public function store(PortfolioStoreRequest $req, CreatePortfolioAction $create)
    {
        // siapkan DTO; sertakan file 'thumb' bila ada
        $data = $req->validated();
        $dto  = PortfolioData::fromRequest($data + ['thumb' => $req->file('thumb')]);

        $item = $create->handle($dto);

        if ($req->expectsJson() || $req->ajax()) {
            return response()->json(['message' => 'Portfolio created', 'id' => $item->id], 201);
        }
        return redirect()->route('super.portfolio.index')->with('success', 'Portfolio created');
    }

    public function edit(PortfolioItem $portfolio)
    {
        // opsional (kalau pakai modal tidak wajib)
        $item = $portfolio;
        return view('super.portfolio.form', compact('item'));
    }

    public function update(PortfolioUpdateRequest $req, PortfolioItem $portfolio, UpdatePortfolioAction $update)
    {
        $data = $req->validated();
        $dto  = PortfolioData::fromRequest($data + ['thumb' => $req->file('thumb')]);

        $update->handle($portfolio, $dto);

        if ($req->expectsJson() || $req->ajax()) {
            return response()->json(['message' => 'Portfolio updated']);
        }
        return back()->with('success', 'Portfolio updated');
    }

    public function destroy(PortfolioItem $portfolio, DeletePortfolioAction $delete)
    {
        $delete->handle($portfolio);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['message' => 'Portfolio deleted']);
        }
        return redirect()->route('super.portfolio.index')->with('success', 'Portfolio deleted');
    }
}
