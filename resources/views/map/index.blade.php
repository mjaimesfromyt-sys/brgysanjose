@extends('layouts.app')
@section('title', 'Barangay GIS Map')

@section('content')
<!-- Leaflet.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    /* Compact Page Spacing - Gisaka ang mapa */
    .gis-wrapper {
        margin-top: -15px;
    }
    #barangayMap {
        height: 580px;
        width: 100%;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        z-index: 1;
        background-color: #f1f5f9;
        cursor: crosshair;
    }
    .map-filter-btn {
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 50px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
    }
    .map-filter-btn.active, .map-filter-btn:hover {
        background-color: #1b5e20;
        color: #ffffff;
        border-color: #1b5e20;
    }
    .purok-sidebar-item {
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid #e2e8f0;
    }
    .purok-sidebar-item:hover {
        background-color: #e8f5e9 !important;
        border-color: #a5d6a7 !important;
        transform: translateX(3px);
    }
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
    }
    .leaflet-div-icon {
        background: transparent !important;
        border: none !important;
    }
    .coord-display {
        background: rgba(15, 23, 42, 0.85);
        color: #fff;
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 10.5px;
        font-family: ui-monospace, monospace;
    }
</style>

<div class="container gis-wrapper py-1 pb-4">
    <!-- Compact Header (Title + Filters sa usa ka row) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
        <div>
            <h2 class="h5 fw-bold mb-0" style="color: #0f172a;">Interactive Barangay GIS Map</h2>
            <p class="text-muted small mb-0" style="font-size: 11px;">Barangay San Jose, Talibon, Bohol &bull; Geographic &amp; Emergency Map</p>
        </div>

        <!-- Filter Controls -->
        <div class="d-flex flex-wrap gap-1" id="filterContainer">
            <button class="map-filter-btn active" onclick="filterMarkers('all', this)">All Pins</button>
            <button class="map-filter-btn" onclick="filterMarkers('purok', this)">📍 Puroks</button>
            <button class="map-filter-btn" onclick="filterMarkers('evacuation', this)">🚨 Evacuation Sites</button>
            <button class="map-filter-btn" onclick="filterMarkers('community', this)">🏛️ Facilities</button>
        </div>
    </div>

    <!-- Slim & Compact Stats Cards -->
    <div class="row g-2 mb-2">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-2 px-3 rounded-3" style="background-color: #f0fdf4; border-left: 3.5px solid #166534 !important;">
                <div class="text-muted fw-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">TOTAL PUROKS</div>
                <div class="h5 fw-bold mb-0 text-success">7 Puroks</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-2 px-3 rounded-3" style="background-color: #eff6ff; border-left: 3.5px solid #1d4ed8 !important;">
                <div class="text-muted fw-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">REGISTERED RESIDENTS</div>
                <div class="h5 fw-bold mb-0 text-primary">{{ $totalResidents }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-2 px-3 rounded-3" style="background-color: #fffbeb; border-left: 3.5px solid #d97706 !important;">
                <div class="text-muted fw-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">EVACUATION SITES</div>
                <div class="h5 fw-bold mb-0 text-warning">2 Centers</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-2 px-3 rounded-3" style="background-color: #fdf2f8; border-left: 3.5px solid #db2777 !important;">
                <div class="text-muted fw-bold" style="font-size: 9.5px; letter-spacing: 0.5px;">HEALTH FACILITIES</div>
                <div class="h5 fw-bold mb-0 text-danger">2 Facilities</div>
            </div>
        </div>
    </div>

    <!-- Map & Sidebar Layout (Taas ug dako ang viewable area) -->
    <div class="row g-2">
        <div class="col-lg-9">
            <div id="barangayMap"></div>
            <div class="mt-1 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 10.5px;">💡 Click anywhere on map to inspect exact coordinates.</span>
                <span id="clickCoord" class="coord-display">Click map to inspect</span>
            </div>
        </div>

        <!-- Sidebar Purok Directory -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-2.5 h-100" style="border: 1px solid #e2e8f0; background: #ffffff;">
                <h6 class="fw-bold mb-1 pb-1 border-bottom text-uppercase" style="font-size: 11.5px; color: #1e293b;">
                    Purok Directory (Locate)
                </h6>
                <p class="text-muted mb-2" style="font-size: 10.5px;">Pislita aron mo-focus ang mapa.</p>

                <div class="d-flex flex-column gap-1.5" style="max-height: 500px; overflow-y: auto;">
                    @foreach ($purokCounts as $purokName => $count)
                        <div class="purok-sidebar-item p-2 rounded-2 d-flex justify-content-between align-items-center" 
                             style="background-color: #f8fafc; font-size: 12px;"
                             onclick="locatePurok('{{ $purokName }}')">
                            <span class="fw-semibold text-dark">📍 {{ $purokName }}</span>
                            <span class="badge bg-success rounded-pill px-2 py-0.5" style="font-size: 10px;">{{ $count }} Residents</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet.js Library -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Exact Center Coordinates para makita tanan apil ang hospital ug tanang puroks
    var centerLat = 10.1342;
    var centerLng = 124.3165;

    var map = L.map('barangayMap').setView([centerLat, centerLng], 16);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap &bull; Brgy San Jose, Talibon'
    }).addTo(map);

    setTimeout(function () {
        map.invalidateSize();
    }, 250);

    function makeBadgeIcon(emoji, bg) {
        return L.divIcon({
            className: 'brgy-custom-pin',
            html: '<div style="background:' + bg + ';color:#ffffff;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 3px 8px rgba(0,0,0,0.3);border:2px solid #ffffff;">' + emoji + '</div>',
            iconSize: new L.Point(30, 30),
            iconAnchor: new L.Point(15, 15),
            popupAnchor: new L.Point(0, -16)
        });
    }

    var greenIcon  = makeBadgeIcon('📍', '#15803d');
    var blueIcon   = makeBadgeIcon('🏛️', '#1d4ed8');
    var orangeIcon = makeBadgeIcon('🏥', '#ea580c');
    var redIcon    = makeBadgeIcon('🚨', '#dc2626');

    var locations = [
        {
            type: 'community',
            name: 'Garcia Memorial Provincial Hospital',
            lat: 10.1312,
            lng: 124.3188,
            icon: orangeIcon,
            desc: 'Pres. Carlos P. Garcia Memorial Provincial Hospital.',
            hours: '24/7 Emergency &amp; Inpatient Services'
        },
        {
            type: 'community',
            name: 'Barangay San Jose Hall',
            lat: 10.13665,
            lng: 124.31306,
            icon: blueIcon,
            desc: 'Main Administrative Hall, Barangay Captain &amp; Secretary Office.',
            hours: 'Mon - Fri, 8:00 AM - 5:00 PM'
        },
        {
            type: 'community',
            name: 'San Jose Barangay Health Station',
            lat: 10.1352,
            lng: 124.3163,
            icon: orangeIcon,
            desc: 'Primary Healthcare &amp; First Aid Desk.',
            hours: 'Mon - Fri, 8:00 AM - 4:00 PM'
        },
        {
            type: 'evacuation',
            name: 'San Jose Multi-Purpose Covered Court',
            lat: 10.13672,
            lng: 124.31331,
            icon: redIcon,
            desc: 'Primary Designated Evacuation Center &amp; Sports Complex.',
            contact: 'Tanod Emergency Hotline: 24/7'
        },
        { type: 'purok', name: 'Purok 1', lat: 10.1362, lng: 124.3148, icon: greenIcon, residents: '{{ $purokCounts["Purok 1"] }}' },
        { type: 'purok', name: 'Purok 2', lat: 10.1368, lng: 124.3175, icon: greenIcon, residents: '{{ $purokCounts["Purok 2"] }}' },
        { type: 'purok', name: 'Purok 3', lat: 10.1345, lng: 124.3182, icon: greenIcon, residents: '{{ $purokCounts["Purok 3"] }}' },
        { type: 'purok', name: 'Purok 4', lat: 10.1328, lng: 124.3170, icon: greenIcon, residents: '{{ $purokCounts["Purok 4"] }}' },
        { type: 'purok', name: 'Purok 5', lat: 10.1320, lng: 124.3145, icon: greenIcon, residents: '{{ $purokCounts["Purok 5"] }}' },
        { type: 'purok', name: 'Purok 6', lat: 10.1335, lng: 124.3128, icon: greenIcon, residents: '{{ $purokCounts["Purok 6"] }}' },
        { type: 'purok', name: 'Purok 7', lat: 10.1355, lng: 124.3195, icon: greenIcon, residents: '{{ $purokCounts["Purok 7"] }}' },
    ];

    var allMarkers = [];
    window.purokMarkersMap = {};

    locations.forEach(function (loc) {
        var popupContent = '<div style="font-size: 12.5px;">' +
            '<h6 class="fw-bold mb-1" style="color: #166534; font-size: 13.5px;">' + loc.name + '</h6>' +
            (loc.desc ? '<p class="text-muted small mb-1">' + loc.desc + '</p>' : '') +
            (loc.residents ? '<p class="mb-1"><span class="badge bg-success">' + loc.residents + ' Registered Residents</span></p>' : '') +
            (loc.hours ? '<div class="small text-muted"><strong>Hours:</strong> ' + loc.hours + '</div>' : '') +
            (loc.contact ? '<div class="small text-danger"><strong>Emergency:</strong> ' + loc.contact + '</div>' : '') +
            '<div class="mt-1.5 pt-1 border-top small text-muted">Barangay San Jose, Talibon</div>' +
        '</div>';

        var marker = L.marker([loc.lat, loc.lng], { icon: loc.icon })
                      .bindPopup(popupContent, { className: 'custom-popup' });

        marker.category = loc.type;
        marker.addTo(map);
        allMarkers.push(marker);

        if (loc.type === 'purok') {
            window.purokMarkersMap[loc.name] = marker;
        }
    });

    window.filterMarkers = function (category, btnElement) {
        document.querySelectorAll('.map-filter-btn').forEach(function (b) {
            b.classList.remove('active');
        });
        if (btnElement) btnElement.classList.add('active');

        allMarkers.forEach(function (marker) {
            if (category === 'all' || marker.category === category) {
                map.addLayer(marker);
            } else {
                map.removeLayer(marker);
            }
        });
    };

    window.locatePurok = function (purokName) {
        var target = window.purokMarkersMap[purokName];
        if (target) {
            map.flyTo(target.getLatLng(), 17, { duration: 1.2 });
            setTimeout(function () {
                target.openPopup();
            }, 1250);
        }
    };

    map.on('click', function(e) {
        var lat = e.latlng.lat.toFixed(5);
        var lng = e.latlng.lng.toFixed(5);
        document.getElementById('clickCoord').innerText = 'Selected: ' + lat + ', ' + lng;
    });
});
</script>
@endsection