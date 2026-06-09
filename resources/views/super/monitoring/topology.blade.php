@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <select id="server" class="form-select" style="max-width:360px">
                    <option value="">— pilih server —</option>
                    @foreach ($servers as $s)
                        <option value="{{ $s['id'] }}">{{ $s['label'] }}</option>
                    @endforeach
                </select>
                <button id="btnLoad" class="btn btn-primary btn-sm">Load</button>
            </div>

            <div id="tree"></div>
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
            border-left: 1px solid #e4e4e7;
        }

        ul.topo li {
            margin: .25rem 0;
        }

        .node-up {
            color: #0a7a2a;
        }

        .node-down {
            color: #b3261e;
        }

        .node-label {
            font-weight: 700;
        }

        .node-ip {
            font-size: .9em;
            opacity: .8;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const treeEl = document.getElementById('tree');

        function renderNode(n) {
            const li = document.createElement('li');
            const cls = n.status === 'up' ? 'node-up' : (n.status === 'down' ? 'node-down' : '');
            const latency = (n.latency !== null && n.latency !== undefined) ? ` — ${n.latency} ms` : '';
            li.innerHTML =
                `<div class="${cls}"><div class="node-label">${n.label}</div><div class="node-ip">${n.ip || '-' }${latency}</div></div>`;
            if (n.children && n.children.length) {
                const ul = document.createElement('ul');
                ul.className = 'topo';
                n.children.forEach(ch => ul.appendChild(renderNode(ch)));
                li.appendChild(ul);
            }
            return li;
        }

        async function loadTree() {
            const id = document.getElementById('server').value;
            if (!id) {
                treeEl.innerHTML = '<em>Pilih server…</em>';
                return;
            }
            treeEl.innerHTML = 'Loading…';
            const res = await fetch(`{{ route('super.topology.tree') }}?server_id=${id}`);
            const data = await res.json();
            const ul = document.createElement('ul');
            ul.className = 'topo';
            ul.appendChild(renderNode(data));
            treeEl.innerHTML = '';
            treeEl.appendChild(ul);
        }

        document.getElementById('btnLoad').addEventListener('click', loadTree);
    </script>
@endpush
