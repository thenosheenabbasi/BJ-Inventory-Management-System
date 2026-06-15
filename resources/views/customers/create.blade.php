<x-app-layout>
    <x-slot name="pageTitle">Add Customer</x-slot>
    <x-slot name="pageBreadcrumb">Home / Customers / Add</x-slot>

    <section class="module-page customer-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('customers.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Customers
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('customers._form', [
                'action' => route('customers.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
            ])
        </div>
    </section>
</x-app-layout>
