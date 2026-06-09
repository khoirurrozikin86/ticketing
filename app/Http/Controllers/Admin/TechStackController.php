<?php
// app/Http/Controllers/Super/TechStackController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TechStackStoreRequest;
use App\Http\Requests\Admin\TechStackUpdateRequest;

use App\Domain\TechStacks\Queries\TechStackTableQuery;
use App\Domain\TechStacks\Services\TechStackService;
use App\Models\TechStack;
use Yajra\DataTables\Facades\DataTables;

class TechStackController extends Controller
{
    public function index()
    {
        return view('super.techstacks.index');
    }

    public function datatable(TechStackTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('updated_at', fn(TechStack $t) => optional($t->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (TechStack $t) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.tech-stacks.update', $t),
                        'payload'    => ['name' => $t->name, 'order' => $t->order],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.tech-stacks.destroy', $t),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete tech {$t->name}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(TechStackStoreRequest $req, TechStackService $svc)
    {
        $item = $svc->create($req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Tech created', 'id' => $item->id], 201);
        }
        return redirect()->route('super.techstacks.index')->with('success', 'Tech created');
    }

    public function update(TechStackUpdateRequest $req, TechStack $techstack, TechStackService $svc)
    {
        $svc->update($techstack, $req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Tech updated']);
        }
        return back()->with('success', 'Tech updated');
    }

    public function destroy(TechStack $techstack, TechStackService $svc)
    {
        $svc->delete($techstack);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Tech deleted']);
        }
        return redirect()->route('super.techstacks.index')->with('success', 'Tech deleted');
    }
}
