<x-app-layout>
    <x-slot name="pageTitle">Edit Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Battery Inventory / Edit</x-slot>

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
                'action' => route('battery-inventory.update', $battery),
                'method' => 'PUT',
                'submitLabel' => 'Update',
            ])
        </div>
    </section>
</x-app-layout>
