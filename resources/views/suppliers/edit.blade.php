<x-app-layout>
    <x-slot name="pageTitle">Edit Supplier</x-slot>
    <x-slot name="pageBreadcrumb">Home / Suppliers / Edit</x-slot>

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
                'action' => route('suppliers.update', $supplier),
                'method' => 'PUT',
                'submitLabel' => 'Update',
            ])
        </div>
    </section>
</x-app-layout>
