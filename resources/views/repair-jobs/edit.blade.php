<x-app-layout>
    <x-slot name="pageTitle">Edit Repair Battery</x-slot>
    <x-slot name="pageBreadcrumb">Home / Repair Battery / Edit</x-slot>

    <section class="module-page customer-entry-page repair-entry-page">
        <div class="module-header customer-entry-header">
            <div></div>
            <a href="{{ route('repair-jobs.show', $repairJob) }}" class="btn btn-light back-btn">
                <span>&larr;</span>
                Back to Repair Battery Details
            </a>
        </div>

        <div class="form-card customer-entry-card">
            @include('repair-jobs._form', [
                'action' => route('repair-jobs.update', $repairJob),
                'method' => 'PUT',
                'submitLabel' => 'Update',
            ])
        </div>
    </section>
</x-app-layout>
