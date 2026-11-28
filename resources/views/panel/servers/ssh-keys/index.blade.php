@extends('panel.layouts.master')
@section('wrapper')
<div style="padding:28px;max-width:1180px;margin:0 auto">
    <a href="{{ route('panel.servers.ssh-keys.select') }}">&larr; All servers</a>
    <h1 style="margin-top:16px">SSH Keys &amp; Access</h1>
    <p class="text-muted">Server: {{ $server['name'] ?? $server['server_name'] ?? $serverId }} &middot; {{ $server['ip_address'] ?? $server['ip'] ?? '' }}</p>
    <div class="alert alert-info">Access keys are installed only for <strong>zeitops-access</strong>, a separate non-sudo SSH user. ZeitOps management keys and root access are not changed. Only add keys for people you trust to access this VPS.</div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section style="padding:24px;background:#fff;border:1px solid #dfe5f2;border-radius:12px;margin:22px 0">
        <h3>Add an SSH public key</h3>
        <p>Generate a key pair on your own computer, then paste <strong>only the .pub file</strong> here. Never paste a private key.</p>
        <code>ssh-keygen -t ed25519 -C "my-laptop"</code>
        <form method="POST" action="{{ route('panel.servers.ssh-keys.store', ['serverId'=>$serverId]) }}" style="margin-top:20px">
            @csrf
            <div class="form-group" style="margin-bottom:14px"><label for="ssh-label">Key label</label><input id="ssh-label" type="text" name="label" maxlength="100" required class="form-control" value="{{ old('label') }}" placeholder="My laptop"></div>
            <div class="form-group" style="margin-bottom:14px"><label for="ssh-key">Ed25519 public key</label><textarea id="ssh-key" name="public_key" required rows="3" maxlength="500" class="form-control" autocomplete="off" placeholder="ssh-ed25519 AAAAC3... my-laptop"></textarea></div>
            <label style="display:block;margin:16px 0"><input type="checkbox" required name="confirm_access" value="1"> I understand this grants SSH shell access as <code>zeitops-access</code> (without sudo privileges).</label>
            <button type="submit" class="btn btn-primary">Install public key</button>
        </form>
    </section>
    <section style="padding:24px;background:#fff;border:1px solid #dfe5f2;border-radius:12px">
        <h3>Access keys</h3>
        <div style="overflow-x:auto">
            <table class="table table-striped"><thead><tr><th>Label</th><th>Fingerprint</th><th>Status</th><th>Installed</th><th>Action</th></tr></thead><tbody>
            @forelse($keys as $key)
                <tr>
                    <td>{{ $key->label }}</td>
                    <td><code>{{ $key->fingerprint }}</code></td>
                    <td>{{ ucfirst($key->status) }}</td>
                    <td>{{ $key->installed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>@if($key->status !== 'revoked')
                        <form method="POST" action="{{ route('panel.servers.ssh-keys.destroy', ['serverId'=>$serverId,'key'=>$key->id]) }}" onsubmit="return confirm('Revoke SSH access for this key?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Revoke</button>
                        </form>
                        @else <span class="text-muted">Revoked</span> @endif</td>
                </tr>
            @empty <tr><td colspan="5">No SSH access keys registered for this server.</td></tr>
            @endforelse
            </tbody></table>
        </div>
        <p class="text-muted">Connection command after installation: <code>ssh zeitops-access@{{ $server['ip_address'] ?? $server['ip'] ?? 'SERVER_IP' }}</code> (with the matching private key on your computer).</p>
    </section>
</div>
@endsection
