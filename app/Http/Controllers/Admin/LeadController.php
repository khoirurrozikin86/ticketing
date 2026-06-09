<?php
// app/Http/Controllers/Super/LeadController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadStoreRequest;
use App\Http\Requests\Admin\LeadUpdateRequest;
use App\Domain\Leads\Queries\LeadTableQuery;
use App\Domain\Leads\Services\LeadService;
use App\Models\Lead;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index()
    {
        return view('super.leads.index');
    }

    public function datatable(LeadTableQuery $q)
    {
        return DataTables::eloquent($q->builder())
            ->editColumn('message', fn(Lead $l) => e(Str::limit($l->message, 80)))
            ->editColumn('status', function (Lead $l) {
                return match ($l->status) {
                    'contacted' => '<span class="badge bg-info">Contacted</span>',
                    'closed'    => '<span class="badge bg-success">Closed</span>',
                    default     => '<span class="badge bg-secondary">New</span>',
                };
            })
            ->editColumn('updated_at', fn(Lead $l) => optional($l->updated_at)->format('Y-m-d H:i'))
            ->addColumn('actions', function (Lead $l) {
                $actions = [
                    [
                        'type'       => 'edit',
                        'label'      => 'Edit',
                        'icon'       => 'edit-2',
                        'update_url' => route('super.leads.update', $l),
                        'payload'    => [
                            'name'    => $l->name,
                            'email'   => $l->email,
                            'company' => $l->company,
                            'message' => $l->message,
                            'status'  => $l->status,
                        ],
                    ],
                    [
                        'type'     => 'delete',
                        'url'      => route('super.leads.destroy', $l),
                        'label'    => 'Delete',
                        'icon'     => 'trash-2',
                        'confirm'  => "Delete lead {$l->name}?",
                        'disabled' => false,
                    ],
                ];
                return view('admin.partials.table-actions', compact('actions'))->render();
            })
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function store(LeadStoreRequest $req, LeadService $svc)
    {
        $lead = $svc->create($req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Lead created', 'id' => $lead->id], 201);
        }
        return redirect()->route('super.leads.index')->with('success', 'Lead created');
    }

    public function update(LeadUpdateRequest $req, Lead $lead, LeadService $svc)
    {
        $svc->update($lead, $req->sanitized());

        if ($req->ajax() || $req->expectsJson()) {
            return response()->json(['message' => 'Lead updated']);
        }
        return back()->with('success', 'Lead updated');
    }

    public function destroy(Lead $lead, LeadService $svc)
    {
        $svc->delete($lead);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['message' => 'Lead deleted']);
        }
        return redirect()->route('super.leads.index')->with('success', 'Lead deleted');
    }
}
