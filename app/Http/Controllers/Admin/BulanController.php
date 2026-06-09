<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulanStoreRequest;
use App\Http\Requests\Admin\BulanUpdateRequest;

use App\Domain\Bulans\Queries\BulanTableQuery;
use App\Domain\Bulans\Services\BulanService;
use App\Models\Bulan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Exports\BulansExport;
use Maatwebsite\Excel\Facades\Excel;

class BulanController extends Controller
{
    public function index()
    {
        return view('super.bulans.index');
    }

    public function dt(BulanTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('updated_at', fn(Bulan $b) => optional($b->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Bulan $b) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.bulans.update', $b->getRouteKey()),
                        'payload'    => ['id_bulan' => $b->id_bulan, 'bulan' => $b->bulan],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.bulans.destroy', $b->getRouteKey()),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus bulan {$b->bulan}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(BulanStoreRequest $req, BulanService $svc)
    {
        $item = $svc->create($req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Bulan created', 'id' => $item->id_bulan], 201)
            : back()->with('success', 'Bulan created');
    }

    public function update(BulanUpdateRequest $req, Bulan $bulan, BulanService $svc)
    {
        $svc->update($bulan, $req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Bulan updated'])
            : back()->with('success', 'Bulan updated');
    }

    public function destroy(Bulan $bulan, BulanService $svc)
    {
        $svc->delete($bulan);
        return request()->ajax() || request()->expectsJson()
            ? response()->json(['message' => 'Bulan deleted'])
            : redirect()->route('super.bulans.index')->with('success', 'Bulan deleted');
    }

    public function export(Request $r, BulanTableQuery $q)
    {
        $builder = $q->builder();
        $kw = (string) $r->query('q', '');
        if ($kw !== '') {
            $builder->where(function ($b) use ($kw) {
                $like = "%{$kw}%";
                $b->where('id_bulan', 'like', $like)
                    ->orWhere('bulan', 'like', $like);
            });
        }
        $builder->orderByDesc('updated_at');

        $filename = 'bulans_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new BulansExport($builder), $filename);
    }
}
