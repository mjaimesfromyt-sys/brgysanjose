{{-- Shared sub-nav for the reports section --}}
<div class="d-flex gap-2 flex-wrap mb-4 d-print-none">
    <a href="{{ route('reports.requests') }}"
       class="btn btn-sm {{ request()->routeIs('reports.requests') ? 'btn-primary' : 'btn-outline-secondary' }}">
        Document Requests
    </a>
    <a href="{{ route('reports.bookings') }}"
       class="btn btn-sm {{ request()->routeIs('reports.bookings') ? 'btn-primary' : 'btn-outline-secondary' }}">
        Facility Bookings
    </a>
    <a href="{{ route('reports.rentals') }}"
       class="btn btn-sm {{ request()->routeIs('reports.rentals') ? 'btn-primary' : 'btn-outline-secondary' }}">
        Equipment Rentals
    </a>
</div>
