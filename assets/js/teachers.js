// assets/js/teachers.js — Teacher list filter & search, form helpers

document.addEventListener('DOMContentLoaded', () => {
    // Expertise row management in create/edit forms
    initExpertiseRows();
    initAvailabilitySlots();
});

function initExpertiseRows() {
    const container = document.getElementById('expertise-rows');
    const addBtn = document.getElementById('add-expertise');
    if (!container || !addBtn) return;

    addBtn.addEventListener('click', () => {
        const firstRow = container.querySelector('.expertise-row');
        const row = firstRow.cloneNode(true);
        row.querySelector('select[name="expertise_areas[]"]').selectedIndex = 0;
        row.querySelector('select[name="expertise_levels[]"]').selectedIndex = 0;
        row.querySelector('.remove-row').style.display = '';
        container.appendChild(row);
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.expertise-row').remove();
        }
    });
}

function initAvailabilitySlots() {
    document.querySelectorAll('.avail-day').forEach(dayBlock => {
        const addBtn = dayBlock.querySelector('.add-slot');
        if (!addBtn) return;

        addBtn.addEventListener('click', () => {
            const day = dayBlock.querySelector('.day-slots').dataset.day;
            const firstSlot = dayBlock.querySelector('.slot-row');
            const newSlot = firstSlot.cloneNode(true);
            newSlot.querySelectorAll('input').forEach(i => i.value = '');
            newSlot.querySelector('.remove-slot').style.display = '';
            addBtn.before(newSlot);
        });

        dayBlock.addEventListener('click', (e) => {
            if (e.target.closest('.remove-slot')) {
                const slotRow = e.target.closest('.slot-row');
                const allSlots = dayBlock.querySelectorAll('.slot-row');
                if (allSlots.length > 1) slotRow.remove();
            }
        });
    });
}
