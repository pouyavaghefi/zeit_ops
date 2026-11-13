@extends('test.layouts.app')

@section('content')
    <div class="container">
        <h2>Add New Server</h2>

        <form method="POST" action="{{ route('servers.store') }}">
            @csrf

            <div class="mb-3">
                <label for="type" class="form-label">Server Type</label>
                <select name="type" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="1">Web Full (nginx, php, memcached, redis, nodejs, database)</option>
                    <option value="2">Web NoDB (nginx, nodejs, php)</option>
                    <option value="3">Email (wildduck)</option>
                    <option value="4">Worker (nodejs, php)</option>
                    <option value="5">Database</option>
                    <option value="6">Cache (memcached, redis)</option>
                    <option value="7">Load Balancer (nginx)</option>
                    <option value="8">Meilisearch (meilisearch, nginx)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">IP Server</label>
                <input type="text" name="ip" class="form-control" required value="{{ request('ip') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Port</label>
                <input type="number" name="port" class="form-control" required value="{{ request('port', 22) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required value="{{ request('username') }}">
            </div>

{{--            <div class="mb-3">--}}
{{--                <label class="form-label">Password</label>--}}
{{--                <input type="text" name="password" class="form-control" required value="{{ request('password') }}">--}}
{{--            </div>--}}

            <button type="submit" class="btn btn-primary">Generate Command</button>
        </form>

        @isset($command)
            <hr>
            <h4>Generated Command:</h4>
            <pre>{{ $command }}</pre>
            <button type="button" id="check-access" class="btn btn-primary">
                Check Access
            </button>

            <div id="check-result" class="mt-3"></div>

            <script>
                document.getElementById('check-access').addEventListener('click', async () => {
                    const button = document.getElementById('check-access');
                    const result = document.getElementById('check-result');

                    button.disabled = true;
                    result.className = 'mt-3';
                    result.textContent = 'Checking server access...';

                    try {
                        const response = await fetch(
                            @json(route('servers.check', ['serverId' => $serverId])),
                            {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Enrollment-Token': @json($enrollmentToken)
                                },
                                credentials: 'same-origin'
                            }
                        );

                        const data = await response.json();

                        result.className = response.ok && data.success
                            ? 'alert alert-success mt-3'
                            : 'alert alert-danger mt-3';

                        result.textContent = data.message || 'Connection check failed.';

                    } catch (error) {
                        result.className = 'alert alert-danger mt-3';
                        result.textContent = 'Unable to check server access.';
                    } finally {
                        button.disabled = false;
                    }
                });
            </script>
        @endisset
    </div>
@endsection
