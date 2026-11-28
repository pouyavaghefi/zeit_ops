@extends('panel.layouts.master')
@section('wrapper')
    <div class="zo-dns">
        <style>
            .zo-dns{background:#f5f7fc;min-height:80vh;padding:28px;color:#243651}.zo-dns *{box-sizing:border-box}.zo-dns .wrap{max-width:1160px;margin:auto}.zo-dns .panel{border:1px solid #dce5f2;background:#fff;border-radius:15px;padding:24px;margin:18px 0}.zo-dns h1{font-size:28px;font-weight:750}.zo-dns h2{font-size:18px;font-weight:700;margin-bottom:10px}.zo-dns p,.zo-dns small{color:#697993}.zo-dns label{display:block;font-size:13px;font-weight:700;margin:11px 0 5px}.zo-dns input,.zo-dns select{padding:10px;border:1px solid #cbd6e5;border-radius:8px;width:100%;max-width:100%;font-size:14px}.zo-dns .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}.zo-dns .btn{border:0;border-radius:8px;background:#3569ea;color:white;padding:11px 16px;font-weight:700;cursor:pointer;font-size:13px}.zo-dns .secondary{background:#e8effb;color:#27497b}.zo-dns .danger{background:#fff1f2;color:#991b1b}.zo-dns table{width:100%;border-collapse:collapse;font-size:13px}.zo-dns td,.zo-dns th{padding:12px 9px;border-bottom:1px solid #edf1f7;text-align:left;overflow-wrap:anywhere;vertical-align:top}.zo-dns .alert{padding:13px 15px;border-radius:9px;background:#fff1f2;color:#9f1239;margin:14px 0}.zo-dns .ok{background:#eefbf2;color:#166534}.zo-dns .tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.zo-dns .detail{display:none;margin-top:9px;background:#f7f9fd;padding:15px;border-radius:8px}.zo-dns details[open] .detail{display:block}.zo-dns code{word-break:break-all}.zo-dns .hint{font-size:12px;line-height:1.55}.zo-dns input[type=checkbox]{width:auto;margin-right:5px}.zo-dns a{color:#315ee3}
        </style>
        <div class="wrap">
            <a href="{{ route('panel.dns.select') }}">← All websites</a>
            <h1 style="margin:14px 0 4px">DNS Records</h1>
            <p>{{ $site->domain }} · Live Cloudflare DNS management</p>
            @if(session('success'))<div class="alert ok">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
            @if($problem)<div class="alert">{{ $problem }}</div>@endif
            <div class="panel">
                <h2>Connect a DNS zone</h2>
                <p class="hint">ZeitOps can create and edit real DNS records only when a DNS provider is connected. Currently supported: Cloudflare. The domain's authoritative nameservers must use Cloudflare for changes to take effect publicly. A token should be restricted to the selected zone with Zone Read and DNS Edit permissions.</p>
                <form method="POST" action="{{ route('panel.websites.dns.connect', $site->id) }}">@csrf
                    <div class="grid">
                        <div><label>Zone name</label><input name="zone_name" placeholder="pouyait.com" value="{{ old('zone_name', $site->domain) }}" required></div>
                        <div><label>Cloudflare Zone ID</label><input name="zone_id" placeholder="32-character zone ID" value="{{ old('zone_id') }}" required></div>
                        <div><label>API token</label><input type="password" name="api_token" placeholder="Restricted DNS token" required autocomplete="new-password"></div>
                    </div><div style="margin-top:15px"><button type="submit" class="btn">Connect / update zone</button></div>
                </form>
            </div>
            @if($zones->isNotEmpty())
                <div class="panel">
                    <h2>Connected zones</h2>
                    <div class="tools">
                        @foreach($zones as $zone)
                            <a class="btn {{ $selected?->id===$zone->id ? '' : 'secondary' }}" style="text-decoration:none" href="{{ route('panel.websites.dns.index', ['website' => $site->id, 'zone' => $zone->id]) }}">{{ $zone->zone_name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($selected)
                <div class="panel">
                    <div class="tools" style="justify-content:space-between">
                        <h2>Records — {{ $selected->zone_name }}</h2>
                        <form method="POST" action="{{ route('panel.websites.dns.disconnect', [$site->id, $selected->id]) }}" onsubmit="return confirm('Disconnect only? This does NOT delete Cloudflare records.')">@csrf @method('DELETE')<button class="btn danger">Disconnect provider</button></form>
                    </div>
                    <div class="alert" style="background:#fff8e6;color:#885512">Changing A, MX, CNAME or TXT records can interrupt websites, email, and domain verification. Double-check values before saving.</div>
                    <h2>Add DNS record</h2>
                    <form action="{{ route('panel.websites.dns.records.store', [$site->id, $selected->id]) }}" method="POST">@csrf
                        <div class="grid">
                            <div><label>Type</label><select name="type" required>@foreach(['A','AAAA','CNAME','TXT','MX'] as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></div>
                            <div><label>Name</label><input name="name" value="{{ old('name','@') }}" placeholder="@, www, or full name" required></div>
                            <div><label>Content / target</label><input name="content" placeholder="IPv4, hostname, or text" required></div>
                            <div><label>TTL</label><select name="ttl"><option value="1">Auto</option>@foreach([60,120,300,600,1800,3600,7200,14400,28800,43200,86400] as $ttl)<option value="{{ $ttl }}" @selected($ttl===3600)>{{ $ttl }} s</option>@endforeach</select></div>
                            <div><label>MX priority (MX only)</label><input name="priority" type="number" min="0" max="65535" value="10"></div>
                        </div>
                        <label style="font-weight:400"><input name="proxied" type="checkbox" value="1"> Cloudflare proxy (A/AAAA/CNAME only)</label>
                        <div style="margin-top:12px"><button type="submit" class="btn">Create DNS record</button></div>
                    </form>
                </div>
                <div class="panel">
                    <h2>Live DNS records</h2>
                    <div style="overflow-x:auto"><table><thead><tr><th>Type</th><th>Name</th><th>Content</th><th>TTL</th><th>Actions</th></tr></thead><tbody>
                            @forelse($records as $record)
                                <tr><td><strong>{{ $record['type'] ?? '' }}</strong></td><td>{{ $record['name'] ?? '' }}</td><td style="max-width:330px">{{ $record['content'] ?? '' }}</td><td>{{ ($record['ttl'] ?? 1) == 1 ? 'Auto' : ($record['ttl'] ?? '') }}</td><td>
                                        @if(in_array($record['type'] ?? '', ['A','AAAA','CNAME','TXT','MX']))
                                            <details><summary style="cursor:pointer;color:#315ee3">Edit / Delete</summary><div class="detail">
                                                    <form method="POST" action="{{ route('panel.websites.dns.records.update', [$site->id,$selected->id,$record['id']]) }}">@csrf @method('PATCH')
                                                        <input type="hidden" name="type" value="{{ $record['type'] }}">
                                                        <label>Name</label><input name="name" required value="{{ $record['name'] ?? '' }}">
                                                        <label>Content</label><input name="content" required value="{{ $record['content'] ?? '' }}">
                                                        <label>TTL</label><select name="ttl">@foreach([1,60,120,300,600,1800,3600,7200,14400,28800,43200,86400] as $ttl)<option value="{{ $ttl }}" @selected(($record['ttl'] ?? 1)===$ttl)>{{ $ttl === 1 ? 'Auto' : $ttl.'s' }}</option>@endforeach</select>
                                                        @if(($record['type'] ?? '') === 'MX')<label>Priority</label><input name="priority" type="number" value="{{ $record['priority'] ?? 10 }}" min="0" max="65535">@endif
                                                        @if(in_array($record['type'] ?? '', ['A','AAAA','CNAME']))<label><input type="checkbox" name="proxied" value="1" @checked($record['proxied'] ?? false)> Proxy</label>@endif
                                                        <div style="margin:12px 0"><button type="submit" class="btn">Update</button></div></form>
                                                    <form method="POST" action="{{ route('panel.websites.dns.records.destroy', [$site->id,$selected->id,$record['id']]) }}" onsubmit="return confirm('Delete this live DNS record? This may interrupt production services.')">@csrf @method('DELETE')<button class="btn danger" type="submit">Delete record</button></form>
                                                </div></details>
                                        @endif
                                    </td></tr>
                            @empty<tr><td colspan="5">No records returned by Cloudflare for this page.</td></tr>@endforelse
                            </tbody></table></div>
                    <div class="tools" style="margin-top:18px">
                        @if($page > 1)<a href="{{ route('panel.websites.dns.index', ['website'=>$site->id,'zone'=>$selected->id,'page'=>$page-1]) }}">← Previous</a>@endif
                        <span class="hint">Page {{ $page }} of {{ $totalPages }}</span>
                        @if($page < $totalPages)<a href="{{ route('panel.websites.dns.index', ['website'=>$site->id,'zone'=>$selected->id,'page'=>$page+1]) }}">Next →</a>@endif
                    </div>
                </div>
            @else
                <div class="panel"><h2>Connect Cloudflare to start</h2><p>No provider is configured. You can still add DNS records through the authoritative DNS host for your domain. ZeitOps won't pretend to change public records without provider authorization.</p></div>
            @endif
        </div></div>
@endsection
