<x-app-layout>
    <x-slot name="pageTitle">Add Repair Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Repair Battery / Add</x-slot>

    <section class="module-page customer-entry-page repair-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('repair-jobs.index') }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Repair Battery
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('repair-jobs._form', [
                'action' => route('repair-jobs.store'),
                'method' => 'POST',
                'submitLabel' => 'Save',
            ])
        </div>
    </section>
</x-app-layout>
