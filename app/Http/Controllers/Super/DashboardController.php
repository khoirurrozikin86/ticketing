<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // app/Http/Controllers/Super/DashboardController.php

    public function index(Request $r)
    {
        $user       = $r->user();

        // Base queries mengikuti scope forUser()
        $tagihanQ    = Tagihan::query()->forUser($user);
        $pembayaranQ = Pembayaran::query()->whereHas('tagihan', fn($q) => $q->forUser($user));

        // ====== 1) DETEKSI PERIODE AKTIF ======
        $today      = now();
        $bulanStr   = $today->format('m');            // "01" .. "12"
        $tahun      = (int) $today->format('Y');
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();

        // Cek apakah ada tagihan di bulan berjalan
        $hasTagihanThisMonth = (clone $tagihanQ)
            ->where('tahun', $tahun)
            ->where('id_bulan', $bulanStr)
            ->exists();

        // Jika kosong, fallback ke periode terakhir yang ada datanya
        if (!$hasTagihanThisMonth) {
            $lastPeriod = (clone $tagihanQ)
                ->select('tahun', 'id_bulan')
                ->orderBy('tahun', 'desc')
                ->orderBy('id_bulan', 'desc') // id_bulan CHAR "01".."12" aman diurut desc
                ->first();

            if ($lastPeriod) {
                $tahun    = (int) $lastPeriod->tahun;
                $bulanStr = $lastPeriod->id_bulan;
                // (jangan ubah $monthStart/$monthEnd untuk "Paid Bulan Ini", biarkan tetap bulan berjalan)
            }
        }

        // ====== 2) METRICS ======
        // kalau mau: distinct user yang bayar bulan ini -> (clone $pembayaranQ)->whereBetween('paid_at', [$monthStart,$monthEnd])->distinct('user_id')->count('user_id');

        // ✅ Total Users
        $totalUsers = User::count();
        if ($totalUsers === 0) {
            // fallback: hitung user yang pernah buat pembayaran (kalau user table kosong)
            $totalUsers = (clone $pembayaranQ)->distinct('user_id')->count('user_id');
        }

        // ✅ Total Pelanggan — kalau user super tampilkan semua,
        // kalau bukan super dan user belum punya server_id, tampilkan 0.
        if ($user->can('tagihans.view-all')) {
            $totalPelanggan = Pelanggan::count();
        } elseif ($user->server_id) {
            $totalPelanggan = Pelanggan::where('id_server', $user->server_id)->count();
        } else {
            $totalPelanggan = 0;
        }


        // Pelanggan: hitung distinct dari tagihan yang terscope (paling konsisten)
        // Total pelanggan (sesuai lokasi)
        if ($user->can('tagihans.view-all')) {
            // super: total semua + breakdown per lokasi
            $totalPelanggan = Pelanggan::count();

            $pelangganByLokasi = Pelanggan::select('id_server', DB::raw('COUNT(*) AS total'))
                ->groupBy('id_server')
                ->get()
                ->map(function ($row) {
                    $srv = Server::find($row->id_server);
                    return (object)[
                        'id'    => $row->id_server,
                        'name'  => $srv->name ?? $srv->nama ?? ('Server #' . $row->id_server),
                        'total' => (int) $row->total,
                    ];
                })
                ->sortByDesc('total')
                ->values();
        } else {
            // user biasa: hanya lokasi (server) miliknya
            $totalPelanggan = $user->server_id
                ? Pelanggan::where('id_server', $user->server_id)->count()
                : 0;

            $pelangganByLokasi = collect([
                (object)[
                    'id'    => $user->server_id,
                    'name'  => optional(Server::find($user->server_id))->name
                        ?? optional(Server::find($user->server_id))->nama
                        ?? 'Lokasi Saya',
                    'total' => (int) $totalPelanggan,
                ]
            ]);
        }

        // masukkan ke $metrics dan kirim $pelangganByLokasi ke view
        $metrics['pelanggans'] = $totalPelanggan;

        // Tagihan periode aktif (bulan berjalan ATAU fallback periode terakhir)
        $tagihanAmountThisPeriod = (float) (clone $tagihanQ)
            ->where('tahun', $tahun)
            ->where('id_bulan', $bulanStr)
            ->sum('jumlah_tagihan');

        // Dibayar Bulan Ini (tetap bulan berjalan, bukan periode fallback)
        $paidAmountThisMonth = (float) (clone $pembayaranQ)
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        // Outstanding total
        $scopedTagihanSub = (clone $tagihanQ)->select('tagihans.id', 'tagihans.jumlah_tagihan');
        $outstandingTotal = (float) DB::query()
            ->fromSub($scopedTagihanSub, 't')
            ->leftJoinSub(
                Pembayaran::select('tagihan_id', DB::raw('SUM(amount) AS total_paid'))->groupBy('tagihan_id'),
                'tp',
                'tp.tagihan_id',
                '=',
                't.id'
            )
            ->selectRaw('COALESCE(SUM(t.jumlah_tagihan - COALESCE(tp.total_paid, 0)), 0) AS ar_total')
            ->value('ar_total');

        // Lunas/Belum untuk periode aktif (biar nyambung dengan kartu "Tagihan Periode Ini")
        $paidCountThisPeriod = (clone $tagihanQ)
            ->where('tahun', $tahun)->where('id_bulan', $bulanStr)
            ->where('status', 'lunas')->count();

        $unpaidCountThisPeriod = (clone $tagihanQ)
            ->where('tahun', $tahun)->where('id_bulan', $bulanStr)
            ->where('status', 'belum')->count();

        $metrics = [
            'users'                   => $totalUsers,
            'pelanggans'              => $totalPelanggan,
            'tagihan_amount_period'   => $tagihanAmountThisPeriod, // note: period aktif (mungkin fallback)
            'paid_amount_month'       => $paidAmountThisMonth,     // bulan berjalan
            'outstanding_total'       => $outstandingTotal,
            'paid_count_period'       => $paidCountThisPeriod,
            'unpaid_count_period'     => $unpaidCountThisPeriod,
            'active_period_label'     => \Carbon\Carbon::createFromFormat('m', $bulanStr)->isoFormat('MMM Y'), // label untuk Blade
            'active_period_bulan'     => $bulanStr,
            'active_period_tahun'     => $tahun,
            'is_fallback_period'      => !$hasTagihanThisMonth,
        ];

        // ====== 3) TREN 6 BULAN (pakai bulan berjalan kebelakang, tetap terscope)
        $trend = collect(range(5, 0))->map(function ($i) use ($tagihanQ, $pembayaranQ) {
            $d        = now()->subMonths($i);
            $bulanStr = $d->format('m');
            $tahun    = (int) $d->format('Y');

            $tagihan = (float) (clone $tagihanQ)
                ->where('tahun', $tahun)->where('id_bulan', $bulanStr)
                ->sum('jumlah_tagihan');

            $paid = (float) (clone $pembayaranQ)
                ->whereBetween('paid_at', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                ->sum('amount');

            return [
                'label'   => $d->format('M Y'),
                'tagihan' => $tagihan,
                'paid'    => $paid,
            ];
        })->values();

        // ====== 4) RECENT PAYMENTS (scope)
        $recentPayments = (clone $pembayaranQ)
            ->with([
                'tagihan:id,no_tagihan,id_pelanggan',
                'tagihan.pelanggan:id_pelanggan,nama',
                'user:id,name',
            ])
            ->latest('paid_at')
            ->limit(8)
            ->get(['id', 'amount', 'method', 'ref_no', 'paid_at', 'tagihan_id', 'user_id']);

        // ====== 5) TOP PIUTANG (scope)
        $topAr = (clone $tagihanQ)
            ->where('status', 'belum')
            ->with(['pelanggan:id_pelanggan,nama'])
            ->withSum('pembayarans as total_paid', 'amount')
            ->get(['id', 'no_tagihan', 'id_pelanggan', 'jumlah_tagihan'])
            ->map(function ($t) {
                $paid = (float) ($t->total_paid ?? 0);
                return (object) [
                    'id'         => $t->id,
                    'no_tagihan' => $t->no_tagihan,
                    'pelanggan'  => $t->pelanggan?->nama,
                    'sisa'       => max(0, (float)$t->jumlah_tagihan - $paid),
                ];
            })
            ->sortByDesc('sisa')
            ->take(5)
            ->values();

        return view('super.dashboard', compact('metrics', 'trend', 'recentPayments', 'topAr', 'pelangganByLokasi'));
    }
}
