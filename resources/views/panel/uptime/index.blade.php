@extends('panel.layouts.master')
@section('wrapper')
    <div class="zo-uptime">
        <style>
            .zo-uptime{background:#f5f7fc;min-height:80vh;padding:30px;color:#243651}.zo-uptime *{box-sizing:border-box}.zo-uptime .wrap{max-width:1120px;margin:auto}.zo-uptime h1{font-size:28px;font-weight:750;margin:12px 0}.zo-uptime h2{font-size:19px;margin:0 0 12px}.zo-uptime .panel{border:1px solid #dfe7f3;border-radius:15px;background:#fff;padding:22px;margin:18px 0}.zo-uptime p,.zo-uptime small{color:#697993;line-height:1.55}.zo-uptime .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px}.zo-uptime .metric{background:#f8faff;border-radius:12px;padding:17px}.zo-uptime .metric strong{display:block;font-size:23px;margin-top:7px}.zo-uptime label{display:block;font-weight:700;margin-bottom:5px;font-size:13px}.zo-uptime select{width:100%;padding:10px;border:1px solid #cbd6e5;border-radius:8px}.zo-uptime .btn{display:inline-block;background:#3569ea;color:white;border:0;border-radius:8px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer}.zo-uptime .btn.secondary{background:#edf2fc;color:#365aab}.zo-uptime .btn.danger{background:#fff0f2;color:#991b1b}.zo-uptime table{width:100%;border-collapse:collapse;font-size:13px}.zo-uptime td,.zo-uptime th{padding:10px;border-bottom:1px solid #edf1f7;text-align:left}.zo-uptime .notice{padding:13px;border-radius:10px;background:#eafaf0;color:#155a35;margin-top:12px}.zo-uptime .notice.error{background:#fff1f2;color:#9f1239}.zo-uptime .status{font-weight:700}.zo-uptime .status.up{color:#16803c}.zo-uptime .status.down{color:#c62828}.zo-uptime a{color:#315ee3}
        </style>
        <div class="wrap">
            <a href="{{ route('panel.uptime.select') }}">← All monitored websites</a>
            <h1>Uptime Monitoring</h1>
            <p>{{ $site->domain }} · HTTPS check for the registered domain, without following redirects</p>
            @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="notice error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
            <div class="panel">
                <h2>Current status</h2>
                <div class="grid">
                    <div class="metric"><small>Availability</small><strong class="status {{ $monitor?->status }}">{{ $monitor && !$monitor->enabled ? 'PAUSED' : strtoupper($monitor?->status ?? 'not configured') }}</strong></div>
                    <div class="metric"><small>Sample success rate · last 24h</small><strong>{{ $uptimePercent !== null ? number_format($uptimePercent,2).'%' : 'No data' }}</strong><small>{{ $total }} checks</small></div>
                    <div class="metric"><small>Last response time</small><strong>{{ $monitor?->last_response_ms !== null ? $monitor->last_response_ms.' ms' : '—' }}</strong></div>
                    <div class="metric"><small>Last HTTP status</small><strong>{{ $monitor?->last_http_status ?? '—' }}</strong></div>
                </div>
                <p style="margin-bottom:0">Last checked: {{ $monitor?->last_checked_at?->format('Y-m-d H:i:s T') ?? 'Never' }} · Two consecutive failures trigger a down incident. Success rate represents recorded samples, not guaranteed continuous availability.</p>
            </div>
            <div class="panel">
                <h2>Monitoring settings</h2>
                <form method="POST" action="{{ route($monitor ? 'panel.websites.uptime.update' : 'panel.websites.uptime.enable', $site->id) }}">
                    @csrf
                    @if($monitor) @method('PATCH') @endif
                    <div class="grid">
                        <div><label>Check interval</label><select name="interval_minutes">@foreach([5,10,15,30,60] as $minutes)<option value="{{ $minutes }}" @selected((int)old('interval_minutes',$monitor?->interval_minutes ?? 5)===$minutes)>Every {{ $minutes }} minutes</option>@endforeach</select></div>
                        <div><label>Expected HTTPS status</label><select name="expected_status">@foreach([200,204,301,302] as $code)<option value="{{ $code }}" @selected((int)old('expected_status',$monitor?->expected_status ?? 200)===$code)>{{ $code }}</option>@endforeach</select></div>
                        @if($monitor)<div><label>Monitoring</label><select name="enabled"><option value="1" @selected($monitor->enabled)>Enabled</option><option value="0" @selected(!$monitor->enabled)>Paused</option></select></div>@endif
                    </div>
                    <button class="btn" type="submit" style="margin-top:16px">{{ $monitor ? 'Save settings' : 'Enable monitoring' }}</button>
                </form>
                @if($monitor && $monitor->enabled)<form method="POST" action="{{ route('panel.websites.uptime.check', $site->id) }}" style="margin-top:12px">@csrf<button class="btn secondary" type="submit">Queue check now</button></form>@endif
                <p>Checks require a running Laravel scheduler and <code>monitoring</code> queue worker. Monitoring does not install agents on your VPS.</p>
            </div>
            <div class="panel">
                <h2>Recent checks</h2>
                <div style="overflow-x:auto"><table><thead><tr><th>Checked at (UTC)</th><th>Result</th><th>HTTP</th><th>Response time</th><th>Reason</th></tr></thead><tbody>
                        @forelse($checks as $check)
                            <tr><td>{{ $check->checked_at?->utc()->format('Y-m-d H:i:s') }}</td><td class="status {{ $check->successful ? 'up' : 'down' }}">{{ $check->successful ? 'UP' : 'FAILED' }}</td><td>{{ $check->http_status ?? '—' }}</td><td>{{ $check->response_ms !== null ? $check->response_ms.' ms' : '—' }}</td><td>{{ $check->reason ?? '—' }}</td></tr>
                        @empty<tr><td colspan="5">No check results yet.</td></tr>@endforelse
                        </tbody></table></div>
            </div>
            <div class="panel">
                <h2>Incidents</h2>
                <div style="overflow-x:auto"><table><thead><tr><th>Started (UTC)</th><th>Resolved (UTC)</th><th>Reason</th></tr></thead><tbody>
                        @forelse($incidents as $incident)
                            <tr><td>{{ $incident->started_at?->utc()->format('Y-m-d H:i:s') }}</td><td>{{ $incident->resolved_at?->utc()->format('Y-m-d H:i:s') ?? 'Ongoing' }}</td><td>{{ $incident->reason ?? 'HTTPS request failed' }}</td></tr>
                        @empty<tr><td colspan="3">No recorded incidents.</td></tr>@endforelse
                        </tbody></table></div>
            </div>
        </div></div>
@endsection
