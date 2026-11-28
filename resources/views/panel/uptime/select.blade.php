@extends('panel.layouts.master')
@section('wrapper')
    <div style="background:#f5f7fc;min-height:80vh;padding:32px;color:#243651">
        <div style="max-width:1080px;margin:auto">
            <h1 style="font-size:28px;font-weight:750;margin-bottom:8px">Uptime Monitoring</h1>
            <p style="color:#718096;margin-bottom:22px">Monitor the HTTPS availability of your registered websites. Select a website to configure checks and view its history.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
                @forelse($websites as $website)
                    @php($monitor = $monitors->get($website->id))
                    <a href="{{ route('panel.websites.uptime.index', $website->id) }}" style="text-decoration:none;color:#243651;background:white;border:1px solid #dfe7f3;padding:22px;border-radius:14px;display:block">
                        <div style="font-weight:750;font-size:19px">{{ $website->domain }}</div>
                        <div style="font-size:13px;color:#76859b;margin-top:8px">{{ $monitor && $monitor->enabled ? strtoupper($monitor->status) : 'Monitoring not enabled' }}</div>
                        <div style="color:#3868ec;font-size:13px;margin-top:16px;font-weight:700">View uptime &amp; incidents →</div>
                    </a>
                @empty
                    <p>No registered websites yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
