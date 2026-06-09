<?php
// app/Http/Controllers/Super/ServiceController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceStoreRequest;
use App\Http\Requests\Admin\ServiceUpdateRequest;

use App\Domain\Services\Queries\ServiceTableQuery;
use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Actions\{
    CreateServiceAction,
    UpdateServiceAction,
    DeleteServiceAction
};

use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index()
    {
        return view('super.services.index');
    }

    public function datatable(ServiceTableQuery $q)
    {
        $builder = $q->builder();

        return DataTables::eloquent($builder)
            ->addColumn('icon', function (Service $s) {
                $icon = (string)($s->icon ?? '');
                if (str_contains($icon, '<svg')) {
                    return $icon;
                }
                if ($icon && Storage::disk('public')->exists($icon)) {
                    $url = asset('storage/' . $icon);
                    return '<img src="' . e($url) . '" alt="icon" width="28" height="28">';
                }
                if ($icon) {
                    return '<i data-feather="' . e($icon) . '"></i>';
                }
                return '<i data-feather="settings"></i>';
            })
            ->editColumn('excerpt', fn(Service $s) => e(str($s->excerpt)->limit(80)))
            ->editColumn('updated_at', fn(Service $s) => optional($s->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Service $s) {
                $pretty = json_encode($s->meta ?? new \stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.services.update', $s),
                        'payload'    => [
                            'name'    => $s->name,
                            'icon'    => $s->icon,
                            'excerpt' => $s->excerpt,
                            'meta'    => $pretty,
                            'order'   => $s->order,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.services.destroy', $s),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete service {$s->name}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['icon', 'actions'])
            ->toJson();
    }

    public function store(ServiceStoreRequest $req, CreateServiceAction $create)
    {
        $dto = ServiceData::fromArray($req->validated());
        $service = $create($dto);

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Service created', 'id' => $service->id], 201);
        }
        return redirect()->route('super.services.index')->with('success', 'Service created');
    }

    public function update(ServiceUpdateRequest $req, Service $service, UpdateServiceAction $update)
    {
        $dto = ServiceData::fromArray($req->validated());
        $update($service, $dto);

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Service updated']);
        }
        return back()->with('success', 'Service updated');
    }

    public function destroy(Service $service, DeleteServiceAction $delete)
    {
        $delete($service);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Service deleted']);
        }
        return redirect()->route('super.services.index')->with('success', 'Service deleted');
    }
}
