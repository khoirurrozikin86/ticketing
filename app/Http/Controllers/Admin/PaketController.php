<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaketStoreRequest;
use App\Http\Requests\Admin\PaketUpdateRequest;
use App\Exports\PaketsExport;
use App\Domain\Pakets\Queries\PaketTableQuery;
use App\Domain\Pakets\Services\PaketService;
use App\Models\Paket;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        // samakan dengan struktur view milik TechStack
        return view('super.pakets.index');
    }

    public function datatable(PaketTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('harga', fn(Paket $p) => number_format($p->harga, 2))
            ->editColumn('updated_at', fn(Paket $p) => optional($p->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Paket $p) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.pakets.update', $p),
                        'payload'    => [
                            'id_paket'  => $p->id_paket,
                            'nama'      => $p->nama,
                            'harga'     => $p->harga,
                            'kecepatan' => $p->kecepatan,
                            'durasi'    => $p->durasi,
                            'remark1'   => $p->remark1,
                            'remark2'   => $p->remark2,
                            'remark3'   => $p->remark3,
                        ],
                    ],
                    [
                        'type'     => 'delete', // ← harus 'delete' agar partial-mu render
                        'url'      => route('super.pakets.destroy', $p),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus paket {$p->nama}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }


    public function export(Request $r, PaketTableQuery $q)
    {
        $builder = $q->builder();

        // Ambil keyword global (sinkron dengan DataTables search)
        $keyword = (string) $r->query('q', '');

        if ($keyword !== '') {
            $builder->where(function ($b) use ($keyword) {
                $kw = "%{$keyword}%";
                $b->where('id_paket', 'like', $kw)
                    ->orWhere('nama', 'like', $kw)
                    ->orWhere('kecepatan', 'like', $kw)
                    ->orWhere('remark1', 'like', $kw)
                    ->orWhere('remark2', 'like', $kw)
                    ->orWhere('remark3', 'like', $kw);
            });
        }

        // (opsional) sorting default
        $builder->orderByDesc('updated_at');

        $filename = 'pakets_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PaketsExport($builder), $filename);
    }



    public function store(PaketStoreRequest $req, PaketService $svc)
    {
        $item = $svc->create($req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Paket created', 'id' => $item->id], 201);
        }
        return redirect()->route('super.pakets.index')->with('success', 'Paket created');
    }

    public function update(PaketUpdateRequest $req, Paket $paket, PaketService $svc)
    {
        $svc->update($paket, $req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Paket updated']);
        }
        return back()->with('success', 'Paket updated');
    }

    public function destroy(Paket $paket, PaketService $svc)
    {
        $svc->delete($paket);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Paket deleted']);
        }
        return redirect()->route('super.pakets.index')->with('success', 'Paket deleted');
    }
}
