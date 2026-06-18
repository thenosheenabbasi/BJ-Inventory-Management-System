<form method="POST" action="{{ $action }}" class="customer-form customer-entry-form user-management-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="customer-form-body">
        @if ($errors->any())
            <div class="form-validation-summary" role="alert">
                Please review the highlighted fields.
            </div>
        @endif

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>Account Information</h2>
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="name" class="form-label">Full Name <span class="required">*</span></label>
                    <div class="input-shell @error('name') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 22a8 8 0 0 1 16 0"/></svg>
                        </span>
                        <input id="name" type="text" name="name" value="{{ old('name', $managedUser->name) }}" class="form-control" placeholder="Enter full name" maxlength="255" required>
                    </div>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                    <div class="input-shell @error('email') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email', $managedUser->email) }}" class="form-control" placeholder="Enter email address" maxlength="255" required>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="role" class="form-label">Role <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('role') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><circle cx="12" cy="10" r="2"/><path d="M8.5 16a4 4 0 0 1 7 0"/></svg>
                        </span>
                        <select id="role" name="role" class="form-control form-select" required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $managedUser->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="status" class="form-label">Account Status <span class="required">*</span></label>
                    <div class="input-shell select-shell @error('status') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                        </span>
                        <select id="status" name="status" class="form-control form-select" required>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $managedUser->status ?: 'active') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="customer-form-section">
            <div class="section-title-row">
                <h2>{{ $managedUser->exists ? 'Change Password' : 'Login Password' }}</h2>
                @if ($managedUser->exists)
                    <span class="user-form-optional">Leave blank to keep the current password.</span>
                @endif
            </div>

            <div class="customer-form-grid">
                <div class="form-field">
                    <label for="password" class="form-label">Password @unless($managedUser->exists)<span class="required">*</span>@endunless</label>
                    <div class="input-shell password-toggle-shell @error('password') is-invalid @enderror">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input id="password" type="password" name="password" class="form-control" placeholder="Minimum 8 characters" @required(! $managedUser->exists)>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false"></button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label for="password_confirmation" class="form-label">Confirm Password @unless($managedUser->exists)<span class="required">*</span>@endunless</label>
                    <div class="input-shell password-toggle-shell">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        </span>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" @required(! $managedUser->exists)>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false"></button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="customer-form-footer">
        <div class="form-actions">
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-brand save-customer-btn">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
