<section class="settings-panel settings-panel-danger">
    <div class="settings-panel-header">
        <div>
            <span class="settings-eyebrow">Danger Zone</span>
            <h2>Delete Account</h2>
            <p>Deleting this account is permanent. Enter your password only when you are sure you want to continue.</p>
        </div>
    </div>

    <form method="post" action="{{ route('profile.destroy') }}" class="settings-form" onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
        @csrf
        @method('delete')

        <div class="settings-form-grid settings-form-grid-single">
            <div>
                <label for="delete_account_password" class="form-label">Password</label>
                <div class="input-shell password-toggle-shell @if($errors->userDeletion->has('password')) is-invalid @endif">
                    <input id="delete_account_password" name="password" type="password" class="form-control" placeholder="Enter password to confirm">
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @foreach ($errors->userDeletion->get('password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </div>
    </form>
</section>
