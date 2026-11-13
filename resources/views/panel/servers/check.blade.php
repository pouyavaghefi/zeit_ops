@extends('panel.layouts.master')

@section('wrapper')
    <div class="dashboard-content">
        <div class="container">
            <h2>Server Check</h2>

            <p><strong>IP:</strong> {{ $ip }}</p>

            {{-- Ping Status --}}
            <p><strong>Ping:</strong>
                @if($pingStatus)
                    ✅ Reachable
                @else
                    ❌ Unreachable
                @endif
            </p>
            @if(!empty($pingOutput))
                <pre>{{ implode("\n", $pingOutput) }}</pre>
            @endif

            {{-- SSH Status --}}
            <p><strong>SSH:</strong>
                @if($sshStatus['ok'])
                    ✅ OK
                @else
                    ❌ Failed
                @endif
            </p>
            <p><strong>Message:</strong> {{ $sshStatus['message'] }}</p>

            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
{{--            @if($sshStatus['ok'])--}}
            <a href="/panel/servers/register/{{ $ip }}" class="btn btn-success mt-3">Register</a>
{{--            @endif--}}
        </div>
    </div>
@endsection
