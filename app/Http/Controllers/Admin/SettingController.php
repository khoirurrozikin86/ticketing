<?php

// app/Http/Controllers/Admin/SettingController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingStoreRequest;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Domain\Settings\Queries\SettingTableQuery;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SettingController extends Controller
{
    public function index()
    {
        return view('super.settings.index');
    }

    public function datatable(SettingTableQuery $q)
    {
        $builder = $q->builder();

        return DataTables::eloquent($builder)
            ->addColumn('value_preview', function (Setting $s) {
                $val = $s->value;
                // pastikan array
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    $val = is_array($decoded) ? $decoded : [];
                }
                // ringkas: ambil beberapa kunci & potong
                $pairs = collect($val ?: [])->take(4)->map(function ($v, $k) {
                    $v = is_scalar($v) ? (string)$v : json_encode($v);
                    $v = mb_strimwidth($v, 0, 40, '…');
                    return "{$k}: {$v}";
                })->implode(' • ');

                return e($pairs ?: '—');
            })
            ->editColumn('updated_at', fn(Setting $s) => optional($s->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Setting $s) {
                // siapkan payload untuk modal (pretty JSON)
                $pretty = json_encode($s->value ?? new \stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                // coba deteksi "logo" jika ada di value
                $logoUrl = null;
                if (is_array($s->value) && !empty($s->value['logo']) && Storage::disk('public')->exists($s->value['logo'])) {
                    $logoUrl = asset('storage/' . $s->value['logo']);
                }

                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.settings.update', $s),
                        'payload'    => [
                            'key'       => $s->key,
                            'value'     => $pretty,
                            'logo_url'  => $logoUrl,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.settings.destroy', $s),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete setting {$s->key}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(SettingStoreRequest $req)
    {
        $data = $req->validated();

        // decode JSON value
        $value = [];
        if (!empty($data['value'])) {
            $decoded = json_decode($data['value'], true);
            if (!is_array($decoded)) {
                return response()->json(['message' => 'Invalid JSON in value'], 422);
            }
            $value = $decoded;
        }

        // handle upload logo -> simpan ke value['logo']
        if ($req->hasFile('logo')) {
            $path = $req->file('logo')->store('settings', 'public');
            $value['logo'] = $path;
        }

        $setting = Setting::create([
            'key'   => $data['key'],
            'value' => $value,
        ]);

        return $req->ajax()
            ? response()->json(['message' => 'Setting created', 'id' => $setting->id], 201)
            : redirect()->route('admin.settings.index')->with('success', 'Setting created');
    }

    public function update(SettingUpdateRequest $req, Setting $setting)
    {
        $data = $req->validated();

        $value = $setting->value ?? [];
        if (!empty($data['value'])) {
            $decoded = json_decode($data['value'], true);
            if (!is_array($decoded)) {
                return response()->json(['message' => 'Invalid JSON in value'], 422);
            }
            $value = $decoded;
        }

        if ($req->hasFile('logo')) {
            // hapus lama kalau ada
            if (!empty($value['logo']) && Storage::disk('public')->exists($value['logo'])) {
                Storage::disk('public')->delete($value['logo']);
            }
            $value['logo'] = $req->file('logo')->store('settings', 'public');
        }

        $setting->update([
            'key'   => $data['key'],
            'value' => $value,
        ]);

        return $req->ajax()
            ? response()->json(['message' => 'Setting updated'])
            : back()->with('success', 'Setting updated');
    }

    public function destroy(Setting $setting)
    {
        // opsional hapus logo
        if (is_array($setting->value) && !empty($setting->value['logo']) && Storage::disk('public')->exists($setting->value['logo'])) {
            Storage::disk('public')->delete($setting->value['logo']);
        }
        $setting->delete();

        return request()->ajax()
            ? response()->json(['message' => 'Setting deleted'])
            : redirect()->route('Admin.settings.index')->with('success', 'Setting deleted');
    }
}
