<x-app-layout>
    <x-slot name="pageTitle">Account Settings</x-slot>
    <x-slot name="pageBreadcrumb">Home / Account Settings</x-slot>

    <div class="settings-page">
        <div class="settings-backbar">
            <a href="{{ route('dashboard') }}" class="btn btn-light back-btn">Back to Dashboard</a>
        </div>

        <div class="settings-grid settings-grid-single">
            <div class="settings-main">
                @include('profile.partials.update-profile-information-form')

                <div id="change-password">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
