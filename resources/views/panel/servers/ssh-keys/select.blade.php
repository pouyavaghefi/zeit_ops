@extends('panel.layouts.master')
@section('wrapper')
<div style="padding:28px;max-width:1100px;margin:0 auto">
    <h1>SSH Keys &amp; Access</h1>
    <p class="text-muted">Manage limited SSH access to your registered servers. Select a server to continue.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:24px">
        @forelse($servers as $server)
            <div style="background:white;border:1px solid #dfe5f2;padding:22px;border-radius:12px">
                <h4>{{ $server['name'] }}</h4>
                <p>{{ $server['ip'] }}</p>
                <p>Status: {{ $server['status'] }}</p>
                <a class="btn btn-primary" href="{{ route('panel.servers.ssh-keys.index', ['serverId'=>$server['id']]) }}">Manage access</a>
            </div>
        @empty
            <div class="alert alert-info">No registered servers found.</div>
        @endforelse
    </div>
</div>
@endsection
