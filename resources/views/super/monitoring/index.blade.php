@extends('layouts.admin')

@section('title', 'Monitoring Pelanggan')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <select id="server" class="form-select" style="max-width:380px">
            <option value="">— pilih server —</option>
            @foreach ($servers as $s)
                {{-- $servers = array dari GetServerOptions: [{id,label}] --}}
                <option value="{{ $s['id'] }}">{{ $s['label'] }}</option>s
            @endforeach
        </select>
        <button id="btnLoad" class="btn btn-primary">Load</button>
    </div>

    <div id="serverInfo" class="mb-3" style="display:none">
        <div class="alert alert-secondary py-2 mb-0">
            <strong>Server:</strong> <span id="srv-ip"></span>
            <span class="text-muted"> · </span>
            <strong>Lokasi:</strong> <span id="srv-lok"></span>
            <span class="text-muted"> · </span>
            <strong>User:</strong> <span id="srv-user"></span>
        </div>
    </div>

    <ul class="nav nav-tabs" id="tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-tree" data-bs-toggle="tab" data-bs-target="#pane-tree" type="button"
                role="tab">Topology Tree</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-table" data-bs-toggle="tab" data-bs-target="#pane-table" type="button"
                role="tab">Table</button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom p-3">
        <div class="tab-pane fade show active" id="pane-tree" role="tabpanel" aria-labelledby="tab-tree">
            <div id="treeWrap"></div>
        </div>
        <div class="tab-pane fade" id="pane-table" role="tabpanel" aria-labelledby="tab-table">
            <div class="table-responsive">
                <table id="dt" class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Nama</th>
                            <th>IP</th>
                            <th>Parent</th>
                            <th>Latency</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        ul.topo,
        ul.topo ul {
            list-style: none;
            margin-left: 1rem;
            padding-left: .75rem;
            border-left: 1px solid #e5e7eb;
        }

        ul.topo li {
            margin: .25rem 0;
        }

        .node-up {
            color: #0a7a2a;
        }

        /* hijau */
        .node-down {
            color: #b3261e;
        }

        /* merah */
        .node-label {
            font-weight: 600;
        }

        .node-ip {
            font-size: .9em;
            opacity: .8;
        }

        .badge-dot {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .5rem;
            border-radius: 999px;
            font-weight: 600;
        }

        .badge-dot::before {
            content: "";
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            display: inline-block;
        }

        .badge-up {
            background: #e8f5e9;
            color: #0a7a2a;
        }

        .badge-up::before {
            background: #16a34a;
        }

        .badge-down {
            background: #fdecea;
            color: #b3261e;
        }

        .badge-down::before {
            background: #ef4444;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const $tree = document.getElementById('treeWrap');
        const $dtBody = document.querySelector('#dt tbody');
        const $srvBox = document.getElementById('serverInfo');
        const $srvIp = document.getElementById('srv-ip');
        const $srvLok = document.getElementById('srv-lok');
        const $srvUser = document.getElementById('srv-user');

        function badge(status) {
            if (status === 'green') return '<span class="badge-dot badge-up">UP</span>';
            return '<span class="badge-dot badge-down">DOWN</span>';
        }

        function renderNode(n) {
            const li = document.createElement('li');
            const cls = n.status === 'green' ? 'node-up' : (n.status === 'red' ? 'node-down' : '');
            const lat = (n.latency !== null && n.latency !== undefined) ? ` — ${n.latency} ms` : '';
            li.innerHTML = `
    <div class="${cls}">
      <div class="node-label">${badge(n.status)} ${n.name}</div>
      <div class="node-ip">${n.ip || '-'}${lat}</div>
    </div>
  `;
            if (n.children && n.children.length) {
                const ul = document.createElement('ul');
                ul.className = 'topo';
                n.children.forEach(ch => ul.appendChild(renderNode(ch)));
                li.appendChild(ul);
            }
            return li;
        }

        function listToTree(list) {
            const norm = s => (s || '').trim();

            // clone nodes + PID unik + children
            const nodes = list.map((n, idx) => ({
                ...n,
                pid: n.pid ?? idx, // fallback kalau belum ada
                ip: norm(n.ip),
                parent: norm(n.parent),
                children: []
            }));

            const roots = [];

            for (const node of nodes) {
                // cegah self-loop: jika parent == ip sendiri, anggap tidak punya parent
                if (node.parent && node.parent === node.ip) {
                    node.parent = '';
                }

                const parent = node.parent ?
                    nodes.find(x => x.ip === node.parent && x.pid !== node.pid) // hindari diri sendiri
                    :
                    null;

                if (parent) {
                    parent.children.push(node);
                } else {
                    roots.push(node);
                }
            }
            return roots;
        }

        async function loadData() {
            const id = document.getElementById('server').value;
            if (!id) {
                $tree.innerHTML = '<em>Pilih server dulu…</em>';
                $dtBody.innerHTML = '';
                $srvBox.style.display = 'none';
                return;
            }
            $tree.innerHTML = 'Loading…';
            $dtBody.innerHTML = '<tr><td colspan="5">Loading…</td></tr>';

            try {
                // route name harus sesuai: super.monitoring.json => /super/monitoring/{serverId}
                const res = await fetch(`{{ route('super.monitoring.json', ['serverId' => 0]) }}`.replace('/0', '/' +
                    id));
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(text || 'HTTP ' + res.status);
                }
                const data = await res.json();

                // header server
                if (data.server) {
                    $srvIp.textContent = data.server.ip || '-';
                    $srvLok.textContent = data.server.lokasi || '-';
                    $srvUser.textContent = data.server.user || '-';
                    $srvBox.style.display = '';
                } else {
                    $srvBox.style.display = 'none';
                }

                const list = data.routerStatus || [];

                // TABEL
                $dtBody.innerHTML = '';
                if (list.length === 0) {
                    $dtBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No data</td></tr>';
                } else {
                    for (const r of list) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
          <td>${badge(r.status)}</td>
          <td>${r.name || '-'}</td>
          <td>${r.ip || '-'}</td>
          <td>${r.parent || '-'}</td>
          <td>${r.latency != null ? (r.latency + ' ms') : '-'}</td>
        `;
                        $dtBody.appendChild(tr);
                    }
                }

                // TREE
                const roots = listToTree(list);
                const rootUL = document.createElement('ul');
                rootUL.className = 'topo';
                if (roots.length === 0) {
                    rootUL.innerHTML = '<li class="text-muted">No nodes</li>';
                } else {
                    // kalau mau server sebagai root visual:
                    const serverRoot = {
                        name: 'SERVER ' + ($srvIp.textContent || ''),
                        ip: data.server?.ip || null,
                        status: 'green', // asumsi server reachable
                        latency: null,
                        children: roots
                    };
                    rootUL.appendChild(renderNode(serverRoot));
                }
                $tree.innerHTML = '';
                $tree.appendChild(rootUL);

            } catch (e) {
                console.error(e);
                $tree.innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${e.message ?? e}</div>`;
                $dtBody.innerHTML = '';
            }
        }

        document.getElementById('btnLoad').addEventListener('click', loadData);
    </script>
@endpush
