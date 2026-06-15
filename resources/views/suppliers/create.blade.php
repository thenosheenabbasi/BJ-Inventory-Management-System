<x-app-layout>
    <x-slot name="pageTitle">Add Supplier</x-slot>
    <x-slot name="pageBreadcrumb">Home / Suppliers / Add</x-slot>

    <section class="module-page customer-entry-page supplier-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Suppliers
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('suppliers._form', [
                'action' => route('suppliers.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
            ])
        </div>
    </section>
</x-app-layout>
