<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PelangganStoreRequest;
use App\Http\Requests\Admin\PelangganUpdateRequest;

use App\Domain\Pelanggans\Queries\PelangganTableQuery;
use App\Domain\Pelanggans\Services\PelangganService;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Server;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Exports\PelanggansExport;
use Maatwebsite\Excel\Facades\Excel;

class PelangganController extends Controller
{
    public function index()
    {
        // untuk opsi select paket & server di modal
        $pakets  = Paket::orderBy('nama')->get(['id', 'nama']);
        $servers = Server::orderBy('ip')->get(['id', 'ip', 'lokasi']);
        return view('super.pelanggans.index', compact('pakets', 'servers'));
    }

    public function dt(PelangganTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('updated_at', fn($r) => optional($r->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function ($r) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.pelanggans.update', $r->id),
                        'payload'    => [
                            'id_pelanggan'    => $r->id_pelanggan,
                            'nama'            => $r->nama,
                            'alamat'          => $r->alamat,
                            'no_hp'           => $r->no_hp,
                            'email'           => $r->email,
                            'password'        => '', // tidak pernah kirim password
                            'id_paket'        => $r->id_paket,
                            'ip_router'       => $r->ip_router,
                            'ip_parent_router' => $r->ip_parent_router,
                            'remark1'         => $r->remark1,
                            'remark2'         => $r->remark2,
                            'remark3'         => $r->remark3,
                            'id_server'       => $r->id_server,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.pelanggans.destroy', $r->id),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus pelanggan {$r->nama}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(PelangganStoreRequest $req, PelangganService $svc)
    {
        $item = $svc->create($req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Pelanggan created', 'id' => $item->id], 201)
            : back()->with('success', 'Pelanggan created');
    }

    public function update(PelangganUpdateRequest $req, Pelanggan $pelanggan, PelangganService $svc)
    {
        $svc->update($pelanggan, $req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Pelanggan updated'])
            : back()->with('success', 'Pelanggan updated');
    }

    public function destroy(Pelanggan $pelanggan, PelangganService $svc)
    {
        $svc->delete($pelanggan);
        return request()->ajax() || request()->expectsJson()
            ? response()->json(['message' => 'Pelanggan deleted'])
            : redirect()->route('super.pelanggans.index')->with('success', 'Pelanggan deleted');
    }

    public function export(Request $r, PelangganTableQuery $q)
    {
        $builder = $q->builder();
        $kw = (string) $r->query('q', '');
        if ($kw !== '') {
            $builder->where(function ($b) use ($kw) {
                $like = "%{$kw}%";
                $b->where('pelanggans.id_pelanggan', 'like', $like)
                    ->orWhere('pelanggans.nama', 'like', $like)
                    ->orWhere('pelanggans.no_hp', 'like', $like)
                    ->orWhere('pelanggans.email', 'like', $like)
                    ->orWhere('pakets.nama', 'like', $like)
                    ->orWhere('servers.ip', 'like', $like);
            });
        }
        $builder->orderByDesc('pelanggans.updated_at');

        $filename = 'pelanggans_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PelanggansExport($builder), $filename);
    }
}
