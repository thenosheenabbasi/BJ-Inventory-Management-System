<x-app-layout>
    <x-slot name="pageTitle">Edit Sale Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Sale Battery / Edit</x-slot>

    <section class="module-page customer-entry-page sales-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('sales.show', $sale) }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Sale Battery Details
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('sales._form', [
                'action' => route('sales.update', $sale),
                'method' => 'PUT',
                'submitLabel' => 'Update Sale',
            ])
        </div>
    </section>
</x-app-layout>
