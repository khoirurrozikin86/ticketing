<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentStoreRequest;
use App\Domain\Pembayarans\Services\PaymentService;
use App\Domain\Pembayarans\Queries\{PaymentTableQuery, PaymentLookupQuery};
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;


class PaymentController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        return view('super.payments.index', compact('users'));
    }

    public function dt(Request $r, PaymentTableQuery $q)
    {
        $filters = [
            'date_from' => $r->get('date_from'),
            'date_to'   => $r->get('date_to'),
            'user_id'   => $r->get('user_id'),
            'kw'        => $r->get('kw'),
        ];

        $builder = $q->applyFilters($q->builder(), $filters);

        return DataTables::eloquent($builder)
            ->editColumn('paid_at', fn($r) => optional($r->paid_at)->format('Y-m-d'))
            ->editColumn('amount',  fn($r) => number_format((float)$r->amount, 2))
            ->addColumn('actions', function ($r) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => '', // (opsional: bila mau edit payment)
                        'payload'    => [
                            'id'         => $r->id,
                            'tagihan_id' => $r->tagihan_id,
                            'amount'     => $r->amount,
                            'paid_at'    => optional($r->paid_at)->format('Y-m-d'),
                            'method'     => $r->method,
                            'ref_no'     => $r->ref_no,
                            'note'       => $r->note,
                        ],
                        'class' => 'btn-edit-payment', // siapkan handler jika perlu
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.payments.destroy', $r->id),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Hapus pembayaran #{$r->id}?",
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /** Ringkasan angka mengikuti filter yang sama */
    public function summary(Request $r, PaymentTableQuery $q)
    {
        $filters = [
            'date_from' => $r->get('date_from'),
            'date_to'   => $r->get('date_to'),
            'user_id'   => $r->get('user_id'),
            'kw'        => $r->get('kw'),
        ];
        $builder = $q->applyFilters($q->builder(), $filters);

        // clone tanpa limit/offset
        $sum   = (clone $builder)->sum('pembayarans.amount');
        $count = (clone $builder)->count('pembayarans.id');
        $avg   = $count ? ($sum / $count) : 0;

        return response()->json([
            'total_amount' => round($sum, 2),
            'tx_count'     => (int)$count,
            'avg_amount'   => round($avg, 2),
        ]);
    }

    public function store(PaymentStoreRequest $req, PaymentService $svc)
    {
        try {
            $svc->create($req->sanitized());
            return response()->json(['message' => 'Payment recorded']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    public function destroy(Pembayaran $payment, PaymentService $svc)
    {
        $svc->delete($payment);
        return response()->json(['message' => 'Payment deleted']);
    }

    public function export(Request $r, PaymentTableQuery $q)
    {
        $filters = [
            'date_from' => $r->get('date_from'),
            'date_to'   => $r->get('date_to'),
            'user_id'   => $r->get('user_id'),
            'kw'        => $r->get('kw'),
        ];
        $builder = $q->applyFilters($q->builder(), $filters)->orderByDesc('pembayarans.paid_at');
        $filename = 'payments_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PaymentsExport($builder), $filename);
    }




    // ====== LOOKUP (card) —> SATU controller yang sama ======
    public function lookup()
    {
        return view('super.payments.lookup'); // Blade yg sudah kamu punya
    }

    public function find(Request $r, PaymentLookupQuery $q)
    {
        $kw = trim((string)$r->query('q', ''));
        if ($kw === '') {
            return response()->json([
                'html' => view('super.payments.partials._cards', ['items' => collect()])->render()
            ]);
        }

        $pelanggans = $q->pelangganBuilder($kw)->get();
        $ids = $pelanggans->pluck('id_pelanggan')->all();
        $tagihansByPel = $q->tagihansByPelanggan($ids);

        $items = $pelanggans->map(function ($p) use ($tagihansByPel) {
            $tags = ($tagihansByPel[$p->id_pelanggan] ?? collect());
            $display = $tags->take(6);
            $out_count = $tags->where('status', 'belum')->count();
            $out_sum   = (float) $tags->where('status', 'belum')->sum('jumlah_tagihan');

            return (object)[
                'id_pelanggan'   => $p->id_pelanggan,
                'nama'           => $p->nama,
                'server_ip'      => $p->server_ip,
                'server_lokasi'  => $p->server_lokasi,
                'server_mikrotik' => $p->server_mikrotik,
                'alamat'         => $p->alamat,
                'no_hp'          => $p->no_hp,
                'email'          => $p->email,
                'tagihans'       => $display,
                'out_count'      => $out_count,
                'out_sum'        => $out_sum,
            ];
        });

        return response()->json([
            'html' => view('super.payments.partials._cards', compact('items'))->render()
        ]);
    }


    /**
     * 🔥 MULTIPLE PAYMENT
     * Checklist → Bayar Bersamaan
     */
    public function payMultiple(Request $request, PaymentService $svc)
    {
        $data = $request->validate([
            'tagihan_ids'   => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihans,id',
            'method'        => 'required|in:cash,transfer,qris',
            'paid_at'       => 'nullable|date',
            'note'          => 'nullable|string',
        ]);

        try {
            $payment = $svc->payMultiple($data);

            return response()->json([
                'message' => 'Pembayaran kolektif berhasil',
                'data' => [
                    'payment_id' => $payment->id,
                    'total'      => $payment->amount,
                ]
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
