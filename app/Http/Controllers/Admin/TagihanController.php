<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{TagihanStoreRequest, TagihanUpdateRequest, TagihanGenerateRequest};
use App\Domain\Tagihans\Queries\TagihanTableQuery;
use App\Domain\Tagihans\Services\TagihanService;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Bulan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Exports\TagihansExport;
use Maatwebsite\Excel\Facades\Excel;

class TagihanController extends Controller
{
    public function index()
    {
        $bulans = Bulan::orderBy('id_bulan')->get(['id_bulan', 'bulan']);
        $pelanggans = Pelanggan::orderBy('nama')->get(['id_pelanggan', 'nama']);
        return view('super.tagihans.index', compact('bulans', 'pelanggans'));
    }

    public function dt(TagihanTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            // pastikan angka & tanggal tampil rapi
            ->editColumn('jumlah_tagihan', fn($r) => number_format((float)$r->jumlah_tagihan, 2))
            ->editColumn('tgl_bayar', fn($r) => $r->tgl_bayar ? \Illuminate\Support\Carbon::parse($r->tgl_bayar)->format('Y-m-d') : null)
            ->editColumn('updated_at', fn($r) => optional($r->updated_at)->format('Y-m-d H:i'))

            // === ACTIONS ===
            ->addColumn('actions', function ($r) {
                // gunakan id yang PASTI milik tagihans (kita select 'tagihans.id' di query)
                $id = $r->id; // atau $r->tagihan_id kalau kamu alias

                $payload = [
                    'no_tagihan'     => $r->no_tagihan,
                    'id_bulan'       => $r->id_bulan,
                    'tahun'          => $r->tahun,
                    'id_pelanggan'   => $r->id_pelanggan,
                    'jumlah_tagihan' => $r->jumlah_tagihan,
                    'status'         => $r->status,
                    'tgl_bayar'      => $r->tgl_bayar ? \Illuminate\Support\Carbon::parse($r->tgl_bayar)->format('Y-m-d') : null,
                    'remark1'        => $r->remark1,
                    'remark2'        => $r->remark2,
                    'remark3'        => $r->remark3,
                ];

                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Bayar',
                        'icon'       => 'credit-card',
                        'class'      => 'btn-pay-item', // <— pembeda
                        'update_url' => '',             // tidak dipakai
                        'payload'    => [
                            'tagihan_id' => $r->tagihan_id ?? $r->id,
                            'default_amount' => $r->jumlah_tagihan, // boleh diubah user
                            'no_tagihan' => $r->no_tagihan,
                        ],
                    ],
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        // pakai id tagihan
                        'update_url' => route('super.tagihans.update', $id),
                        'payload'    => $payload,
                        // class opsional; partial default ke 'btn-edit-role'
                        // 'class'   => 'btn-edit-role',
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.tagihans.destroy', $id),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus tagihan {$r->no_tagihan}?",
                        'disabled' => false,
                        // 'class'  => 'btn-delete-role',
                    ],
                ];

                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            // penting: agar HTML dropdown tidak di-escape
            ->rawColumns(['actions'])
            // kalau mau full bebas escape:
            // ->escapeColumns([])

            ->toJson();
    }

    public function store(TagihanStoreRequest $req, TagihanService $svc)
    {
        $item = $svc->create($req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Tagihan created', 'id' => $item->id], 201)
            : back()->with('success', 'Tagihan created');
    }

    public function update(TagihanUpdateRequest $req, Tagihan $tagihan, TagihanService $svc)
    {
        $svc->update($tagihan, $req->sanitized());
        return $req->ajax() || $req->expectsJson()
            ? response()->json(['message' => 'Tagihan updated'])
            : back()->with('success', 'Tagihan updated');
    }

    public function destroy(Tagihan $tagihan, TagihanService $svc)
    {
        $svc->delete($tagihan);
        return request()->ajax() || request()->expectsJson()
            ? response()->json(['message' => 'Tagihan deleted'])
            : redirect()->route('super.tagihans.index')->with('success', 'Tagihan deleted');
    }

    public function export(Request $r, TagihanTableQuery $q)
    {
        $builder = $q->builder();
        $kw = (string)$r->query('q', '');
        if ($kw !== '') {
            $like = "%{$kw}%";
            $builder->where(function ($b) use ($like) {
                $b->where('tagihans.no_tagihan', 'like', $like)
                    ->orWhere('pelanggans.nama', 'like', $like)
                    ->orWhere('tagihans.id_pelanggan', 'like', $like)
                    ->orWhere('bulans.bulan', 'like', $like)
                    ->orWhere('tagihans.tahun', 'like', $like)
                    ->orWhere('tagihans.status', 'like', $like);
            });
        }
        $builder->orderByDesc('tagihans.updated_at');

        $filename = 'tagihans_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new TagihansExport($builder), $filename);
    }

    // Generate per-batch / per-user
    public function generate(TagihanGenerateRequest $req, TagihanService $svc)
    {
        $data = $req->sanitized();
        $res = $svc->generateBatch($data['tahun'], $data['id_bulan'], $data['id_pelanggans'], $data['jumlah']);
        return response()->json([
            'message' => "Generated: {$res['created']} created, {$res['skipped']} skipped",
            'created' => $res['created'],
            'skipped' => $res['skipped']
        ]);
    }



    /** Halaman khusus daftar tagihan status=belum */
    public function unpaid()
    {
        return view('super.tagihans.unpaid'); // blade khusus yang sudah kita buat
    }

    /** DataTables untuk tagihan belum lunas */
    public function unpaidDt(TagihanTableQuery $q)
    {
        $builder = $q->builder()->where('tagihans.status', 'belum');

        return DataTables::eloquent($builder)
            ->editColumn('jumlah_tagihan', fn($r) => number_format((float)$r->jumlah_tagihan, 2))
            ->editColumn('tgl_bayar', fn($r) => $r->tgl_bayar?->format('Y-m-d'))
            ->editColumn('updated_at', fn($r) => optional($r->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function ($r) {
                $id = $r->tagihan_id ?? $r->id;
                $actions = [
                    [
                        'type'    => 'edit',
                        'label'   => 'Bayar',
                        'icon'    => 'credit-card',
                        'class'   => 'btn-pay-item', // ditangkap JS di blade
                        'payload' => [
                            'tagihan_id'     => $id,
                            'default_amount' => $r->jumlah_tagihan,
                            'no_tagihan'     => $r->no_tagihan,
                        ],
                        'update_url' => '',
                    ],
                    // Jika mau tampilkan edit/hapus tagihan di halaman ini, boleh tambahkan item di bawah:
                    // [
                    //   'type'=>'edit','label'=>'Edit','icon'=>'edit-2',
                    //   'update_url'=>route('super.tagihans.update',$id),
                    //   'payload'=>[ ... ],
                    // ],
                    // [
                    //   'type'=>'delete','url'=>route('super.tagihans.destroy',$id),
                    //   'label'=>'Delete','icon'=>'trash-2','confirm'=>"Hapus tagihan {$r->no_tagihan}?",
                    // ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /** Export Excel untuk tagihan belum lunas */
    public function unpaidExport(Request $r, TagihanTableQuery $q)
    {
        $builder = $q->builder()
            ->where('tagihans.status', 'belum')
            ->orderByDesc('tagihans.updated_at');

        $filename = 'tagihans_unpaid_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new TagihansExport($builder), $filename);
    }
}
