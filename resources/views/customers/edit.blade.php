<x-app-layout>
    <x-slot name="pageTitle">Edit Customer</x-slot>
    <x-slot name="pageBreadcrumb">Home / Customers / Edit</x-slot>

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
                'action' => route('customers.update', $customer),
                'method' => 'PUT',
                'submitLabel' => 'Update',
            ])
        </div>
    </section>
</x-app-layout>
