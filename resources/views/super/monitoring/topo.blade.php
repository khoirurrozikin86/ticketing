@extends('layouts.app')

@section('title', 'Monitoring Topology')

@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Monitoring Topology</h4>
            <div class="small text-muted">
                <span class="badge bg-success">UP</span>
                <span class="badge bg-danger">DOWN</span>
                <span class="ms-2">Auto refresh setiap 20s</span>
            </div>
        </div>

        <div id="tree" class="ps-2 border-start"></div>
    </div>
@endsection

@push('styles')
    <style>
        .node {
            margin: 6px 0 6px 18px;
            position: relative;
        }

        .node::before {
            content: '';
            position: absolute;
            left: -18px;
            top: 12px;
            width: 12px;
            height: 1px;
            background: #ccc;
        }

        .node .label {
            font-weight: 600;
        }

        .node .ip {
            display: block;
            font-size: .9rem;
            opacity: .8;
        }

        .dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .dot.up {
            background: #16a34a;
        }

        /* green-600 */
        .dot.down {
            background: #dc2626;
        }

        /* red-600 */
    </style>
@endpush

@push('scripts')
    <script>
        const el = document.getElementById('tree');
        const url = @json(route('monitoring.data', $serverId));

        function renderNode(node) {
            const wrap = document.createElement('div');
            wrap.className = 'node';

            const head = document.createElement('div');
            const dot = document.createElement('span');
            dot.className = 'dot ' + (node.up ? 'up' : 'down');
            head.appendChild(dot);

            const label = document.createElement('span');
            label.className = 'label ' + (node.up ? 'text-success' : 'text-danger');
            label.textContent = node.name;
            head.appendChild(label);

            const ip = document.createElement('span');
            ip.className = 'ip text-success-emphasis';
            ip.textContent = node.ip;
            head.appendChild(document.createElement('br'));
            head.appendChild(ip);

            wrap.appendChild(head);

            if (node.children && node.children.length) {
                const childWrap = document.createElement('div');
                childWrap.className = 'ps-3 border-start';
                node.children.forEach(ch => childWrap.appendChild(renderNode(ch)));
                wrap.appendChild(childWrap);
            }
            return wrap;
        }

        async function load() {
            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                el.innerHTML = '';
                el.appendChild(renderNode(data.tree));
            } catch (e) {
                console.error(e);
            }
        }

        load();
        setInterval(load, 20000);
    </script>
@endpush
