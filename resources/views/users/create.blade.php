<x-app-layout>
    <x-slot name="pageTitle">Add User</x-slot>
    <x-slot name="pageBreadcrumb">Home / Users & Roles / Add User</x-slot>

    <section class="module-page user-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('users.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Users
            </a>
        </div>

        <div class="form-card user-entry-card">
            @include('users._form', [
                'action' => route('users.store'),
                'method' => 'POST',
                'submitLabel' => 'Create User',
            ])
        </div>
    </section>
</x-app-layout>
