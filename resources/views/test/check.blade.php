@extends('test.layouts.app')

@section('content')
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
    </div>
@endsection
