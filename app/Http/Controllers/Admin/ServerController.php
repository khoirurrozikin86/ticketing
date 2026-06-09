<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServerStoreRequest;
use App\Http\Requests\Admin\ServerUpdateRequest;

use App\Domain\Servers\Queries\ServerTableQuery;
use App\Domain\Servers\Services\ServerService;
use App\Models\Server;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ServersExport;

class ServerController extends Controller
{
    public function index()
    {
        return view('super.servers.index');
    }

    public function dt(ServerTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('updated_at', fn(Server $s) => optional($s->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Server $s) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.servers.update', $s),
                        'payload'    => [
                            'ip'       => $s->ip,
                            'user'     => $s->user,
                            'password' => '', // tidak pernah kirim password asli
                            'lokasi'   => $s->lokasi,
                            'no_int'   => $s->no_int,
                            'mikrotik' => $s->mikrotik,
                            'remark1'  => $s->remark1,
                            'remark2'  => $s->remark2,
                            'remark3'  => $s->remark3,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.servers.destroy', $s),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus server {$s->ip}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(ServerStoreRequest $req, ServerService $svc)
    {
        $item = $svc->create($req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Server created', 'id' => $item->id], 201)
            : back()->with('success', 'Server created');
    }

    public function update(ServerUpdateRequest $req, Server $server, ServerService $svc)
    {
        $svc->update($server, $req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Server updated'])
            : back()->with('success', 'Server updated');
    }

    public function destroy(Server $server, ServerService $svc)
    {
        $svc->delete($server);
        return request()->ajax() || request()->expectsJson()
            ? response()->json(['message' => 'Server deleted'])
            : redirect()->route('super.servers.index')->with('success', 'Server deleted');
    }

    public function export(Request $r, ServerTableQuery $q)
    {
        $builder = $q->builder();
        $keyword = (string) $r->query('q', '');
        if ($keyword !== '') {
            $kw = "%{$keyword}%";
            $builder->where(function ($b) use ($kw) {
                $b->where('ip', 'like', $kw)
                    ->orWhere('user', 'like', $kw)
                    ->orWhere('lokasi', 'like', $kw)
                    ->orWhere('no_int', 'like', $kw)
                    ->orWhere('mikrotik', 'like', $kw)
                    ->orWhere('remark1', 'like', $kw)
                    ->orWhere('remark2', 'like', $kw)
                    ->orWhere('remark3', 'like', $kw);
            });
        }
        $builder->orderByDesc('updated_at');

        $filename = 'servers_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ServersExport($builder), $filename);
    }
}
