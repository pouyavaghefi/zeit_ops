@extends('panel.layouts.master')

@section('wrapper')
    <div class="zo-domains">
        <style>
            .zo-domains { background:#f5f7fc;min-height:calc(100vh - 75px);padding:30px;color:#243651 }
            .zo-domains * { box-sizing:border-box }
            .zo-domains .wrap { max-width:1100px;margin:0 auto }
            .zo-domains .back { color:#4770c2;text-decoration:none;font-size:13px;font-weight:600 }
            .zo-domains .heading { display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin:20px 0 24px }
            .zo-domains h1 { font-size:29px;font-weight:750;margin:0 0 5px }
            .zo-domains h2 { font-size:19px;font-weight:700;margin:0 0 10px }
            .zo-domains .muted { color:#75859f;font-size:13px }
            .zo-domains .panel { background:#fff;border:1px solid #dfe7f3;border-radius:16px;padding:24px;margin-bottom:20px;box-shadow:0 3px 20px rgba(20,50,100,.03) }
            .zo-domains .pill { font-size:12px;padding:5px 10px;border-radius:999px;background:#e8f4eb;color:#166534;display:inline-block }
            .zo-domains .pending { background:#fef3c7;color:#92400e }
            .zo-domains .error-pill { background:#fee2e2;color:#991b1b }
            .zo-domains input[type=text] { padding:11px 12px;border:1px solid #cbd6e5;border-radius:9px;min-width:260px;max-width:100%;font-size:14px }
            .zo-domains .btn { padding:11px 17px;border-radius:9px;background:#3569e9;border:0;color:white;font-size:13px;font-weight:700;cursor:pointer }
            .zo-domains .btn.secondary { background:#eef2ff;color:#2646a4 }
            .zo-domains .btn.danger { background:#fff1f2;color:#a21c35 }
            .zo-domains .btn:disabled { opacity:.45;cursor:not-allowed }
            .zo-domains .actions { display:flex;flex-wrap:wrap;gap:9px;align-items:center;margin-top:12px }
            .zo-domains code { background:#eff4fa;color:#1c3e65;border-radius:5px;padding:4px 6px;overflow-wrap:anywhere }
            .zo-domains .dns { display:grid;grid-template-columns:110px 1fr;gap:7px 14px;font-size:13px;align-items:center;padding:12px;background:#f7f9fd;border:1px solid #e8edf5;border-radius:10px;margin:15px 0 }
            .zo-domains .notice { padding:12px 16px;border-radius:10px;margin:12px 0 20px;background:#e8f4ff;color:#204d77 }
            .zo-domains .danger-notice { background:#fff1f2;color:#a21c35 }
            .zo-domains .domain-row { border-top:1px solid #e9edf4;padding-top:20px;margin-top:20px }
            .zo-domains .domain-top { display:flex;gap:10px;justify-content:space-between;align-items:center;flex-wrap:wrap }
            .zo-domains .grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px }
        </style>
        <div class="wrap">
            <a class="back" href="{{ route('panel.websites.setup', $site->id) }}">← Back to {{ $site->domain }} setup</a>
            <div class="heading">
                <div><h1>SSL &amp; Domains</h1><p class="muted">Manage HTTPS and domain aliases for {{ $site->name ?: $site->domain }}.</p></div>
                <span class="pill">Laravel · LAMP</span>
            </div>

            @if(session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
            @if($errors->any()) <div class="notice danger-notice" role="alert">{{ $errors->first() }}</div> @endif

            <div class="grid">
                <section class="panel">
                    <h2>Primary domain</h2>
                    <div style="font-weight:700;font-size:17px"><a href="https://{{ $site->domain }}" target="_blank" rel="noopener noreferrer">{{ $site->domain }}</a></div>
                    <p class="muted">Set during website registration. Its SSL certificate is handled by the Website Deployment workflow.</p>
                    @if($latestDeployment && $latestDeployment->status === 'completed' && $latestDeployment->stage === 'https-ready')
                        <span class="pill">Last deployment: HTTPS verified</span>
                    @else
                        <span class="pill pending">No recent HTTPS confirmation</span>
                    @endif
                    <p class="muted" style="margin-top:12px">This indicator describes the last deployment, not a live certificate-expiry check.</p>
                </section>
                <section class="panel">
                    <h2>Add another domain</h2>
                    <p class="muted">Point a second domain, such as <code>pouyait.nl</code>, to this same Laravel application.</p>
                    <form method="POST" action="{{ route('panel.websites.domains.store', $site->id) }}">
                        @csrf
                        <label for="alias_domain" style="display:block;font-weight:650;font-size:13px;margin:12px 0 7px">Domain name</label>
                        <input type="text" name="domain" id="alias_domain" placeholder="pouyait.nl" value="{{ old('domain') }}" required autocomplete="off" spellcheck="false">
                        <div class="actions"><button class="btn" type="submit">Add domain</button></div>
                    </form>
                </section>
            </div>

            <section class="panel">
                <h2>Additional domains</h2>
                <p class="muted">Ownership verification protects existing domains from being claimed by another user. DNS records must be created with your DNS provider.</p>
                @forelse($domains as $entry)
                    <div class="domain-row">
                        <div class="domain-top">
                            <div style="font-size:17px;font-weight:700">{{ $entry->domain }}</div>
                            <span class="pill {{ in_array($entry->status, ['failed']) ? 'error-pill' : (in_array($entry->status, ['pending_dns','verified','issuing','removing']) ? 'pending' : '') }}">{{ str_replace('_', ' ', ucfirst($entry->status)) }}</span>
                        </div>
                        <div class="dns">
                            <strong>A record</strong><span><code>{{ $entry->domain }}</code> → <code>{{ $serverIp ?? 'Registered VPS IP' }}</code></span>
                            <strong>TXT record</strong><span><code>_zeitops-verify.{{ $entry->domain }}</code></span>
                            <strong>TXT value</strong><span><code>zeitops-verification={{ $entry->verification_token }}</code></span>
                        </div>
                        @if($entry->certificate_expires_at)
                            <p class="muted">Last recorded certificate expiry: <strong>{{ $entry->certificate_expires_at->format('Y-m-d') }}</strong></p>
                        @endif
                        @if($entry->last_error) <p style="color:#a21c35;font-size:13px">{{ $entry->last_error }}</p> @endif
                        <div class="actions">
                            @if(!$entry->operation && $entry->status !== 'active')
                                <form method="POST" action="{{ route('panel.websites.domains.verify', [$site->id, $entry->id]) }}">
                                    @csrf <button type="submit" class="btn secondary">Verify DNS ownership</button>
                                </form>
                            @endif
                            @if($entry->verified_at && !$entry->operation)
                                <form method="POST" action="{{ route('panel.websites.domains.issue', [$site->id, $entry->id]) }}">
                                    @csrf <button type="submit" class="btn">{{ $entry->status === 'active' ? 'Renew / reissue SSL' : 'Issue SSL certificate' }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('panel.websites.domains.destroy', [$site->id, $entry->id]) }}" onsubmit="return confirm('Remove this domain alias and its alias-specific certificate?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn danger" @if($entry->operation) disabled @endif>Remove domain</button>
                            </form>
                        </div>
                        @if($entry->operation)<p class="muted">Current operation: {{ $entry->operation }}. Refresh this page to see updates.</p>@endif
                    </div>
                @empty
                    <p class="muted">No additional domains yet. Add a domain above to get started.</p>
                @endforelse
            </section>
            <p class="muted">Certificates are issued with Let's Encrypt. Automatic renewal is handled by Certbot on the VPS. ZeitOps verifies issuance but does not yet monitor renewal results continuously.</p>
        </div>
    </div>
@endsection
