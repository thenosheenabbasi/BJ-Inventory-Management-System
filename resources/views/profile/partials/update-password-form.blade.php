<section class="settings-panel">
    <div class="settings-panel-header">
        <div>
            <span class="settings-eyebrow">Security</span>
            <h2>Change Password</h2>
            <p>Use your current password and choose a new secure password for future logins.</p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="settings-form">
        @csrf
        @method('put')

        <div class="settings-form-grid settings-password-grid">
            <div>
                <label for="update_password_current_password" class="form-label">Current Password</label>
                <div class="input-shell password-toggle-shell @if($errors->updatePassword->has('current_password')) is-invalid @endif">
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @foreach ($errors->updatePassword->get('current_password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>

            <div>
                <label for="update_password_password" class="form-label">New Password</label>
                <div class="input-shell password-toggle-shell @if($errors->updatePassword->has('password')) is-invalid @endif">
                    <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @foreach ($errors->updatePassword->get('password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>

            <div>
                <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-shell password-toggle-shell">
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-actions settings-actions-right">
            @if (session('status') === 'password-updated')
                <span class="settings-saved">Password updated successfully.</span>
            @endif
            <button type="submit" class="btn btn-brand">Update Password</button>
        </div>
    </form>
</section>
