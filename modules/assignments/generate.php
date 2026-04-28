<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/MatchEngine.php';
require_once __DIR__ . '/../../models/Assignment.php';
requireAuth();

$pageTitle = 'Auto-Match Assignments';
$extraCss = ['/assets/css/components.css'];

$currentSem = $_SESSION['current_semester'] ?? '1st';
$currentSY = $_SESSION['current_school_year'] ?? '2024-2025';

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="margin-bottom: 1rem; background: #f0fdf4;">
    <div style="font-size: 0.875rem; color: #166534;">
        <strong>Current View:</strong> <?= htmlspecialchars($currentSY) ?> — <?= htmlspecialchars($currentSem) ?> Semester
        <a href="index.php" style="margin-left: 1rem; color: #166534; text-decoration: underline;">Change</a>
    </div>
</div>

<div class="card">
    <h2>⚡ Auto-Match Engine</h2>
    <p>The algorithm will match teachers to unassigned schedules based on:</p>
    <ol>
        <li><strong>Expertise</strong> — Subject area specialization</li>
        <li><strong>Availability</strong> — Preferred time slots</li>
        <li><strong>Load Balance</strong> — Respects max units per teacher</li>
    </ol>
    
    <div class="progress-container" id="progress-container" style="display:none;">
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
        <p id="progress-text">Analyzing...</p>
    </div>
    
    <div id="match-results"></div>
    
    <div class="form-actions">
        <button id="run-match-btn" class="btn btn-primary" onclick="runMatch()">Run Auto-Match</button>
        <button id="save-matches-btn" class="btn btn-success" style="display:none;" onclick="saveMatches()">Save Assignments</button>
        <a href="index.php" class="btn btn-outline">Back to Assignments</a>
    </div>
</div>

<script>
let currentMatches = [];

function runMatch() {
    document.getElementById('progress-container').style.display = 'block';
    document.getElementById('run-match-btn').disabled = true;
    
    apiRequest('<?= BASE_URL ?>/api/match_engine.php', { method: 'POST', body: JSON.stringify({}) })
        .then(data => {
            currentMatches = data.matches || [];
            document.getElementById('progress-fill').style.width = '100%';
            document.getElementById('progress-text').textContent = 'Found ' + currentMatches.length + ' potential matches';
            
            const container = document.getElementById('match-results');
            if (currentMatches.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No unassigned schedules found or all teachers are at capacity.</div>';
                return;
            }
            
            let html = '<table class="data-table"><thead><tr><th>Schedule ID</th><th>Teacher ID</th><th>Score</th><th>Rationale</th></tr></thead><tbody>';
            currentMatches.forEach(m => {
                html += '<tr><td>' + m.schedule_id + '</td><td>' + m.teacher_id + '</td><td>' + m.score + '</td><td>' + m.rationale + '</td></tr>';
            });
            html += '</tbody></table>';
            container.innerHTML = html;
            document.getElementById('save-matches-btn').style.display = 'inline-flex';
        })
        .catch(err => {
            document.getElementById('progress-text').textContent = 'Error: ' + err.message;
            console.error(err);
        });
}

function saveMatches() {
    if (!confirm('Save ' + currentMatches.length + ' auto-matched assignments?')) return;
    
    apiRequest('<?= BASE_URL ?>/api/match_engine.php', { method: 'POST', body: JSON.stringify({save: true}) })
        .then(data => {
            showToast('success', 'Saved ' + data.saved + ' assignments');
            setTimeout(() => location.href = 'index.php', 1500);
        })
        .catch(err => {
            showToast('error', 'Error saving: ' + err.message);
        });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
