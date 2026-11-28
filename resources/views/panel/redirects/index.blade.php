@extends('panel.layouts.master')
@section('wrapper')
<div class="zo-redirects">
<style>
.zo-redirects{padding:30px;background:#f5f7fc;min-height:80vh;color:#243651}.zo-redirects *{box-sizing:border-box}.zo-redirects .wrap{max-width:1120px;margin:auto}.zo-redirects h1{font-size:28px;font-weight:750;margin:12px 0}.zo-redirects h2{font-size:19px;font-weight:720;margin-bottom:9px}.zo-redirects .panel{border:1px solid #dfe7f3;border-radius:15px;background:white;padding:23px;margin:18px 0}.zo-redirects p,.zo-redirects small{color:#697993;line-height:1.55}.zo-redirects label{display:block;font-size:13px;font-weight:700;margin:12px 0 5px}.zo-redirects input,.zo-redirects select{width:100%;padding:11px;border:1px solid #cbd6e5;border-radius:8px;font-size:14px}.zo-redirects input[type=checkbox]{width:auto;margin:0 6px 0 0}.zo-redirects .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}.zo-redirects .btn{display:inline-block;background:#3569ea;color:white;border:0;border-radius:8px;padding:11px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none}.zo-redirects .btn.secondary{background:#edf2fc;color:#365aab}.zo-redirects .btn.danger{background:#fff0f2;color:#991b1b}.zo-redirects table{width:100%;border-collapse:collapse;font-size:13px}.zo-redirects td,.zo-redirects th{padding:12px 9px;border-bottom:1px solid #edf1f7;text-align:left;overflow-wrap:anywhere;vertical-align:top}.zo-redirects .message{padding:13px 15px;border-radius:10px;background:#ecfdf3;color:#166534;margin:15px 0}.zo-redirects .message.error{background:#fff1f2;color:#9f1239}.zo-redirects a{color:#315ee3}
</style>
<div class="wrap">
<a href="{{ route('panel.redirects.select') }}">← All websites</a>
<h1>Redirects &amp; Subdomains</h1>
<p>{{ $site->domain }} · Website-level routing controls</p>
@if(session('success'))<div class="message">{{ session('success') }}</div>@endif
@if($errors->any())<div class="message error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="panel">
<h2>Subdomains &amp; domain aliases</h2>
<p>Subdomains use the existing SSL &amp; Domains verification workflow. Add a subdomain, set its DNS A record to your VPS, publish the ownership TXT record, then verify and issue SSL. Registered, active subdomains can be used as redirect sources.</p>
<a class="btn" href="{{ route('panel.websites.domains.index', $site->id) }}">Manage subdomains &amp; SSL</a>
<div style="margin-top:17px"><strong>Available source hostnames</strong><ul style="padding-left:20px;margin-top:9px">@foreach($hosts as $host)<li><code>{{ $host }}</code></li>@endforeach</ul></div>
</div>
<div class="panel">
<h2>New redirect</h2>
<p>Redirects are applied through Cloudflare Single Redirect Rules, not by modifying Apache. The chosen source hostname must be proxied (orange cloud) through the connected Cloudflare zone. API tokens need Rulesets Edit permission.</p>
@if($zones->isEmpty())
<div class="message error">No Cloudflare DNS zone connected. <a href="{{ route('panel.websites.dns.index', $site->id) }}">Connect a DNS zone</a> first. Local records alone cannot redirect public traffic.</div>
@else
<form action="{{ route('panel.websites.redirects.store', $site->id) }}" method="POST">@csrf
<div class="grid">
<div><label>Cloudflare zone</label><select name="website_dns_zone_id" required>@foreach($zones as $zone)<option value="{{ $zone->id }}" @selected(old('website_dns_zone_id') == $zone->id)>{{ $zone->zone_name }}</option>@endforeach</select></div>
<div><label>Source hostname</label><select name="source_host" required>@foreach($hosts as $host)<option value="{{ $host }}" @selected(old('source_host') === $host)>{{ $host }}</option>@endforeach</select></div>
<div><label>Exact source path</label><input name="source_path" required value="{{ old('source_path', '/old-page') }}" placeholder="/old-page" maxlength="512"></div>
<div><label>Destination URL</label><input type="url" name="destination_url" required maxlength="2048" value="{{ old('destination_url') }}" placeholder="https://example.com/new-page"></div>
<div><label>HTTP status</label><select name="http_status">@foreach([302 => '302 — Temporary (recommended for testing)', 301 => '301 — Permanent', 307 => '307 — Temporary (preserve method)', 308 => '308 — Permanent (preserve method)'] as $status=>$text)<option value="{{ $status }}" @selected((int)old('http_status',302)===$status)>{{ $text }}</option>@endforeach</select></div>
</div>
<label style="font-weight:400"><input type="checkbox" name="preserve_query_string" value="1" @checked(old('preserve_query_string',true))>Preserve query string</label>
<div style="margin-top:15px"><button type="submit" class="btn">Create live redirect</button></div>
</form>
@endif
</div>
<div class="panel">
<h2>Managed redirects</h2>
<p>Only rules created by this ZeitOps feature appear here. External changes in Cloudflare require reconciliation before editing or deleting.</p>
<div style="overflow-x:auto"><table><thead><tr><th>Source</th><th>Destination</th><th>Status</th><th>Provider</th><th>Action</th></tr></thead><tbody>
@forelse($redirects as $redirect)
<tr>
<td><code>{{ $redirect->source_host }}{{ $redirect->source_path }}</code></td>
<td><a href="{{ $redirect->destination_url }}" rel="noopener noreferrer" target="_blank">{{ $redirect->destination_url }}</a></td>
<td>{{ $redirect->http_status }}</td>
<td>Cloudflare — {{ $redirect->zone?->zone_name ?? 'Zone missing' }}</td>
<td><form method="POST" action="{{ route('panel.websites.redirects.destroy', [$site->id,$redirect->id]) }}" onsubmit="return confirm('Remove this live Cloudflare redirect?')">@csrf @method('DELETE')<button class="btn danger" type="submit">Remove</button></form></td>
</tr>
@empty<tr><td colspan="5">No redirects created yet.</td></tr>@endforelse
</tbody></table></div>
</div>
</div>
</div>
@endsection
