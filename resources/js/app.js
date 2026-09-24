import "bootstrap";

// --- Admin residents: edit rejection reason inline ---------------------------
function editRejectionReason(userId) {
    const card = event.target.closest('.resident-card');
    if (!card) return;

    // If there's already an edit form, remove it and show the reason
    const existing = card.querySelector('.reason-edit-form');
    if (existing) {
        existing.remove();
        const display = card.querySelector('.reason-display');
        if (display) display.style.display = 'block';
        return;
    }

    // Hide the read-only reason and show the edit form
    const readOnly = card.querySelector('.rejection-reason');
    if (readOnly) readOnly.style.display = 'none';

    const display = card.querySelector('.reason-display');
    if (display) display.style.display = 'none';

    const actions = card.querySelector('.resident-actions');
    if (!actions) return;

    const form = document.createElement('form');
    form.className = 'd-inline reason-edit-form';
    form.method = 'POST';
    form.action = '/admin/residents/' + userId + '/reconsider';

    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="d-flex align-items-center gap-2">
            <select name="resident_type" class="form-select form-select-sm" style="max-width:130px" aria-label="Resident type">
                <option value="resident" {{ old('resident_type', 'resident') === 'resident' ? 'selected' : '' }}>Resident</option>
                <option value="non_resident" {{ old('resident_type', 'non_resident') === 'non_resident' ? 'selected' : '' }}>Non-resident</option>
            </select>
            <button type="submit" class="btn btn-sm btn-success">✓ Re-verify</button>
            <button type="button" class="btn btn-sm btn-outline-danger opn-edit" onclick="editRejectionReason(${userId})">✏️ Cancel</button>
        </div>
    `;
    actions.appendChild(form);
}

// --- ID photo lightbox -------------------------------------------------------
const idViewer = document.getElementById('idViewer');
const idViewerImg = document.getElementById('idViewerImg');
const idViewerName = document.getElementById('idViewerName');
const idViewerType = document.getElementById('idViewerType');
const idViewerNum = document.getElementById('idViewerNum');
const idViewerPurok = document.getElementById('idViewerPurok');
const idViewerOpen = document.getElementById('idViewerOpen');
const idViewerClose = document.getElementById('idViewerClose');

function openIdViewer(url, name, type, num, purok) {
    if (!url) return;
    idViewerImg.src = url;
    idViewerName.textContent = name || '—';
    idViewerType.textContent = type || '—';
    idViewerNum.textContent = num || '—';
    idViewerPurok.textContent = purok || '—';
    idViewerOpen.href = url;
    idViewer.classList.remove('d-none');
    document.body.style.overflow = 'hidden';
    idViewerImg.classList.remove('d-none');
}

function closeIdViewer() {
    idViewer.classList.add('d-none');
    document.body.style.overflow = '';
    idViewerImg.classList.add('d-none');
}

if (idViewer) {
    idViewerClose.addEventListener('click', closeIdViewer);
    idViewer.addEventListener('click', function (e) {
        if (e.target === idViewer) closeIdViewer();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeIdViewer();
    });
}
