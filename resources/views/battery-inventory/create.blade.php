<x-app-layout>
    <x-slot name="pageTitle">Add Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Battery Inventory / Add</x-slot>

    <section class="module-page customer-entry-page inventory-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('battery-inventory.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Inventory
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('battery-inventory._form', [
                'action' => route('battery-inventory.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
            ])
        </div>
    </section>
</x-app-layout>
