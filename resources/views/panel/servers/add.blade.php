@extends('panel.layouts.master')

@section('wrapper')
    <div class="dashboard-content">
        <div class="container">
            <h2>Add New Server</h2>

            <form method="POST" action="{{ route('panel.servers.store') }}">
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
                    <input type="text" name="ip" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" name="port" class="form-control" required value="22">
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control" required value="">
                </div>

                <button type="submit" class="btn btn-primary">Generate Command</button>
            </form>

            @isset($command)
                <hr>
                <h4>Generated Command:</h4>
                <pre>{{ $command }}</pre>
                <a href="/panel/servers/check/{{ $ip }}" class="btn btn-default">Check Access</a>
            @endisset
        </div>
    </div>
@endsection
