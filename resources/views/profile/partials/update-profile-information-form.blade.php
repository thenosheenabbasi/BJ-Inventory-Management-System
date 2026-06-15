<section class="settings-panel">
    <div class="settings-panel-header">
        <div>
            <span class="settings-eyebrow">Account</span>
            <h2>Profile Information</h2>
            <p>Update your display name. Email address is fixed for this dashboard account.</p>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="settings-form">
        @csrf
        @method('patch')

        <div class="settings-form-grid">
            <div>
                <label for="name" class="form-label">Name</label>
                <div class="input-shell @error('name') is-invalid @enderror">
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                </div>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <div class="input-shell is-readonly">
                    <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
                </div>
                <div class="settings-muted-note">Email cannot be changed by admin or any user.</div>
            </div>
        </div>

        <div class="settings-actions settings-actions-right">
            @if (session('status') === 'profile-updated')
                <span class="settings-saved">Saved successfully.</span>
            @endif
            <button type="submit" class="btn btn-brand">Save Profile</button>
        </div>
    </form>
</section>
