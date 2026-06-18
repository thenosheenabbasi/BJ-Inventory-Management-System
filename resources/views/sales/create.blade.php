<x-app-layout>
    <x-slot name="pageTitle">Create Sale Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Sale Battery / Create</x-slot>

    <section class="module-page customer-entry-page sales-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('sales.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Sale Battery
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('sales._form', [
                'action' => route('sales.store'),
                'method' => 'POST',
                'submitLabel' => 'Create Sale Battery',
            ])
        </div>
    </section>
</x-app-layout>
