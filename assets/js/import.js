// assets/js/import.js — File upload, parse preview table

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('import-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('import-btn');
        const resultDiv = document.getElementById('import-result');
        const type = form.dataset.type;

        const formData = new FormData(form);
        formData.append('type', type);

        btn.disabled = true;
        btn.textContent = 'Uploading...';

        try {
            // Use relative path since import pages are 2 levels deep (modules/*/import.php)
            const apiPath = window.location.pathname.includes('/modules/') 
                ? '../../api/import.php' 
                : '/api/import.php';
            const res = await fetch(apiPath, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                resultDiv.innerHTML = `<div class="alert alert-success">Imported ${data.imported} records successfully.</div>`;
                form.reset();
            } else if (data.errors && data.errors.length > 0) {
                const errList = data.errors.map(e => `<li>${e}</li>`).join('');
                resultDiv.innerHTML = `<div class="alert alert-error"><strong>Validation Errors:</strong><ul>${errList}</ul></div>`;
                if (data.preview) showPreview(data.preview);
            } else {
                resultDiv.innerHTML = `<div class="alert alert-error">${data.error || 'Import failed'}</div>`;
            }
        } catch (err) {
            resultDiv.innerHTML = `<div class="alert alert-error">Upload error: ${err.message}</div>`;
        } finally {
            btn.disabled = false;
            btn.textContent = 'Import';
        }
    });

    const fileInput = form.querySelector('input[type="file"]');
    fileInput?.addEventListener('change', () => {
        document.getElementById('preview-area').style.display = 'none';
    });
});

function showPreview(rows) {
    const container = document.getElementById('preview-area');
    const thead = document.querySelector('#preview-table thead');
    const tbody = document.querySelector('#preview-table tbody');
    if (!container || !thead || !tbody || rows.length === 0) return;

    const headers = Object.keys(rows[0]);
    thead.innerHTML = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
    tbody.innerHTML = rows.map(r => '<tr>' + headers.map(h => `<td>${r[h] ?? ''}</td>`).join('') + '</tr>').join('');
    container.style.display = 'block';
}
