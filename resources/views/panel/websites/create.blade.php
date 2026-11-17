
@extends('panel.layouts.master')

@section('wrapper')

    <div class="dashboard-content p-4">

        <h2 class="mb-3">Add New Website</h2>

        <p class="text-muted">
            Configure a new website on your connected ZeitOps server.
        </p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="POST"
                      action="{{ route('panel.websites.store') }}">

                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Target Server</label>

                        <select name="server_id"
                                class="form-select"
                                required>
                            <option value="">Select Server</option>

                            @foreach($servers as $server)
                                <option value="{{ $server['id'] }}"
                                    @selected(old('server_id') === $server['id'])>
                                    {{ $server['name'] }} — {{ $server['ip'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Website Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Domain</label>
                        <input type="text"
                               name="domain"
                               class="form-control"
                               placeholder="app.example.com"
                               value="{{ old('domain') }}"
                               required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Stack</label>
                            <select name="stack" class="form-select">
                                <option value="lamp">LAMP</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Framework</label>
                            <select name="framework" class="form-select">
                                <option value="laravel">Laravel</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PHP Version</label>
                            <select name="php_version" class="form-select">
                                <option value="8.3">PHP 8.3</option>
                                <option value="8.4">PHP 8.4</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Git Repository URL</label>
                        <input type="text"
                               name="repository_url"
                               class="form-control"
                               value="{{ old('repository_url') }}"
                               placeholder="https://github.com/user/repo.git">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Git Branch</label>
                        <input type="text"
                               name="repository_branch"
                               class="form-control"
                               value="{{ old('repository_branch', 'main') }}"
                               required>
                    </div>

                    <button type="submit"
                            class="btn btn-primary"
                        @disabled($servers->isEmpty())>
                        <i class="fas fa-plus-circle me-2"></i>
                        Create Website
                    </button>

                </form>

            </div>
        </div>

    </div>

@endsection
