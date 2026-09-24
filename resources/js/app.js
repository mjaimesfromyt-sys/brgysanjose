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
