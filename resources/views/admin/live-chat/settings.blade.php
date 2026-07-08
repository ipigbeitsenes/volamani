@extends('layouts.admin')

@section('title', 'Live Chat Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.live-chat.index') }}">Live Chat</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Live Chat Settings</h4>
        <p class="text-muted mb-0 small">Control the widget, greeting, and the offline auto-reply.</p>
    </div>
    <a href="{{ route('admin.live-chat.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to inbox
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.live-chat.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" class="form-check-input" role="switch" id="chat_enabled" name="chat_enabled" value="1"
                               {{ $settings['chat_enabled'] ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="chat_enabled">Show the live chat widget on the site</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Chat team name</label>
                        <input type="text" name="chat_team_name" class="form-control" maxlength="80" required
                               value="{{ old('chat_team_name', $settings['chat_team_name']) }}">
                        <div class="form-text">Shown in the widget header and against agent replies.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Greeting bubble</label>
                        <input type="text" name="chat_greeting" class="form-control" maxlength="255" required
                               value="{{ old('chat_greeting', $settings['chat_greeting']) }}">
                        <div class="form-text">The pop-up nudge that appears a few seconds after a page loads.</div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Welcome message</label>
                        <textarea name="chat_welcome" class="form-control" rows="2" maxlength="500" required>{{ old('chat_welcome', $settings['chat_welcome']) }}</textarea>
                        <div class="form-text">First message shown inside the chat panel.</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-robot me-1"></i> Offline auto-reply</h6>
                    <span class="small text-muted">Sent automatically when no agent answers in time.</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Reply after (seconds)</label>
                            <input type="number" name="chat_bot_delay" class="form-control" min="0" max="3600" required
                                   value="{{ old('chat_bot_delay', $settings['chat_bot_delay']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fallback support email</label>
                            <input type="email" name="chat_support_email" class="form-control" maxlength="120" required
                                   value="{{ old('chat_support_email', $settings['chat_support_email']) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Auto-reply message</label>
                            <textarea name="chat_offline_message" class="form-control" rows="3" maxlength="500" required>{{ old('chat_offline_message', $settings['chat_offline_message']) }}</textarea>
                            <div class="form-text">Use <code>:email</code> where the support email should appear.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
