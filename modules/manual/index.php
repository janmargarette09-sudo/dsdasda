<?php
// modules/manual/index.php — User Guide & Documentation
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAuth();

$pageTitle = 'User Manual';
$extraCss = [];

require __DIR__ . '/../../includes/header.php';
?>

<div class="page-toolbar">
    <h2>User Manual</h2>
</div>

<div class="manual-container">
    <aside class="manual-sidebar">
        <nav class="manual-nav">
            <a href="#overview" class="manual-nav-link active">Overview</a>
            <a href="#teachers" class="manual-nav-link">Managing Teachers</a>
            <a href="#subjects" class="manual-nav-link">Managing Subjects</a>
            <a href="#schedules" class="manual-nav-link">Managing Schedules</a>
            <a href="#assignments" class="manual-nav-link">Load Assignments</a>
            <a href="#reports" class="manual-nav-link">Reports & Export</a>
            <a href="#quickstart" class="manual-nav-link">Quick Start Guide</a>
            <a href="#faq" class="manual-nav-link">FAQ</a>
        </nav>
    </aside>

    <div class="manual-content">
        <!-- Overview -->
        <section id="overview" class="manual-section">
            <h2>Overview</h2>
            <p>TeacherLoad is a web-based system for managing teacher load assignments across academic departments. It helps program chairs and administrators:</p>
            <ul>
                <li>Track teacher availability, expertise, and current load in real time.</li>
                <li>Manage subjects, schedules, and section offerings per semester.</li>
                <li>Automatically match teachers to classes using a rules-based engine.</li>
                <li>Detect scheduling conflicts and overloads before they happen.</li>
                <li>Generate reports and export data for accreditation and HR.</li>
            </ul>
            <p>The system is role-based. <strong>Admin</strong> users can manage all settings and user accounts. <strong>Chair</strong> users can manage teachers, subjects, schedules, assignments, and reports.</p>
        </section>

        <!-- Teachers -->
        <section id="teachers" class="manual-section">
            <h2>Managing Teachers</h2>
            <p>Navigate to <strong>Teachers</strong> in the sidebar to view, add, edit, or import faculty records.</p>
            <h3>Adding a Teacher</h3>
            <ol>
                <li>Click <strong>Add Teacher</strong> on the Teachers page.</li>
                <li>Fill in the required fields: Employee ID, First Name, Last Name.</li>
                <li>Set <strong>Max Units</strong> (default 24) and <strong>Min Units</strong> (default 12) based on employment type.</li>
                <li>Choose <strong>Employment Type</strong>: full-time, part-time, or contractual.</li>
                <li>Assign a <strong>Department</strong> for filtering and reporting.</li>
                <li>Save the record. The teacher will appear in the list with a load indicator.</li>
            </ol>
            <h3>Importing Teachers</h3>
            <p>Use the <strong>Import</strong> button to upload a CSV file. The file should contain columns: <code>employee_id, first_name, last_name, email, department, max_units, employment_type</code>. A template is available in <code>samples/teachers_template.csv</code>.</p>
            <h3>Teacher Load Indicator</h3>
            <p>Each teacher row shows a colored bar representing current load vs. max units:</p>
            <ul>
                <li><span class="badge badge-success">Green</span> — Normal load</li>
                <li><span class="badge badge-warning">Yellow</span> — Near limit (> 85%)</li>
                <li><span class="badge badge-danger">Red</span> — Overloaded (> 100%)</li>
            </ul>
        </section>

        <!-- Subjects -->
        <section id="subjects" class="manual-section">
            <h2>Managing Subjects</h2>
            <p>Navigate to <strong>Subjects</strong> to manage the curriculum catalog.</p>
            <h3>Adding a Subject</h3>
            <ol>
                <li>Click <strong>Add Subject</strong>.</li>
                <li>Enter a unique <strong>Subject Code</strong> and <strong>Name</strong>.</li>
                <li>Set <strong>Units</strong>, <strong>Lecture Hours</strong>, and <strong>Lab Hours</strong>.</li>
                <li>Select the target <strong>Department</strong>, <strong>Semester</strong>, and <strong>Year Level</strong>.</li>
                <li>Save. The subject becomes available for scheduling.</li>
            </ol>
            <h3>Subject Import</h3>
            <p>Upload a CSV with columns: <code>code, name, units, lecture_hours, lab_hours, department, semester, year_level</code>. Use <code>samples/subjects_template.csv</code> as a reference.</p>
        </section>

        <!-- Schedules -->
        <section id="schedules" class="manual-section">
            <h2>Managing Schedules</h2>
            <p>Navigate to <strong>Schedules</strong> to define class time slots.</p>
            <h3>Creating a Schedule</h3>
            <ol>
                <li>Click <strong>Add Schedule</strong>.</li>
                <li>Select the <strong>Subject</strong> from the dropdown.</li>
                <li>Pick the <strong>Day of Week</strong>, <strong>Start Time</strong>, and <strong>End Time</strong>.</li>
                <li>Enter the <strong>Room</strong> and <strong>Section</strong> (e.g., "BSCS-3A").</li>
                <li>The schedule is saved and becomes available for assignment.</li>
            </ol>
            <h3>Schedule Import</h3>
            <p>Bulk upload via CSV: <code>subject_code, day_of_week, start_time, end_time, room, section, school_year, semester</code>. See <code>samples/schedules_template.csv</code>.</p>
            <div class="callout callout-info">
                <strong>Tip:</strong> Ensure the room and time slot do not conflict with existing schedules to avoid assignment errors.
            </div>
        </section>

        <!-- Assignments -->
        <section id="assignments" class="manual-section">
            <h2>Load Assignments</h2>
            <p>Navigate to <strong>Assignments</strong> to assign teachers to schedule slots.</p>
            <h3>Auto-Assignment</h3>
            <ol>
                <li>Click <strong>Auto-Assign</strong> from the Assignments page or Quick Actions.</li>
                <li>The matching engine evaluates teacher expertise, availability, and current load.</li>
                <li>Review the proposed assignments in the preview table.</li>
                <li>Click <strong>Confirm</strong> to finalize. Assignments are created with status <em>pending</em> by default.</li>
            </ol>
            <h3>Manual Override</h3>
            <p>For exceptions or special cases:</p>
            <ol>
                <li>Go to <strong>Assignments → Manual Override</strong>.</li>
                <li>Select the teacher and schedule from the dropdowns.</li>
                <li>Provide a <strong>Rationale</strong> explaining the override.</li>
                <li>Save. The assignment is marked as <code>manual</code> with your notes.</li>
            </ol>
            <h3>Conflict Detection</h3>
            <p>The system automatically flags:</p>
            <ul>
                <li><strong>Time conflicts</strong> — teacher double-booked</li>
                <li><strong>Overload</strong> — exceeds max units</li>
                <li><strong>Room conflicts</strong> — two classes in same room/time</li>
            </ul>
            <p>Resolve conflicts by reassigning or editing schedules.</p>
        </section>

        <!-- Reports -->
        <section id="reports" class="manual-section">
            <h2>Reports & Export</h2>
            <p>Navigate to <strong>Reports</strong> to generate load summaries and export data.</p>
            <h3>Load Report</h3>
            <p>The default report shows every teacher's current load, categorized as:</p>
            <ul>
                <li><span class="badge badge-success">Normal</span> — within min/max range</li>
                <li><span class="badge badge-warning">Underload</span> — below minimum</li>
                <li><span class="badge badge-danger">Overload</span> — exceeds maximum</li>
            </ul>
            <p>Filter by department using the dropdown, then export to <strong>CSV</strong> or <strong>PDF</strong>.</p>
            <h3>Audit Log</h3>
            <p>The <strong>Audit Log</strong> records all major actions (logins, assignments, imports) with timestamps and IP addresses for accountability.</p>
            <div class="callout callout-info">
                <strong>Tip:</strong> Use the CSV export for further analysis in Excel or for submitting to the registrar.
            </div>
        </section>

        <!-- Quick Start -->
        <section id="quickstart" class="manual-section">
            <h2>Quick Start Guide</h2>
            <p>New semester? Follow these steps:</p>
            <ol>
                <li><strong>Update Preferences</strong> — Go to Settings and set the current School Year and Semester.</li>
                <li><strong>Import or Add Teachers</strong> — Ensure all faculty are in the system with correct max units.</li>
                <li><strong>Import or Add Subjects</strong> — Define the curriculum for the semester.</li>
                <li><strong>Create Schedules</strong> — Map out all section time slots.</li>
                <li><strong>Auto-Assign</strong> — Run the matching engine to generate initial assignments.</li>
                <li><strong>Review & Override</strong> — Check overloads and manually adjust as needed.</li>
                <li><strong>Generate Report</strong> — Export the final load report for HR / accreditation.</li>
            </ol>
        </section>

        <!-- FAQ -->
        <section id="faq" class="manual-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-item">
                <h4>Can I change a teacher's max units after assignments are made?</h4>
                <p>Yes. Edit the teacher record; the system will recalculate load and flag any new overloads in the dashboard.</p>
            </div>
            <div class="faq-item">
                <h4>What happens if I delete a schedule?</h4>
                <p>All linked assignments are removed automatically (cascade delete). The affected teachers' loads will decrease accordingly.</p>
            </div>
            <div class="faq-item">
                <h4>Can part-time teachers have different load limits?</h4>
                <p>Yes. Set a custom <strong>Max Units</strong> when creating or editing a teacher. Part-time faculty often use 12 or 15 units.</p>
            </div>
            <div class="faq-item">
                <h4>How do I reset my password?</h4>
                <p>Go to <strong>Settings → Change Password</strong>. If you are an admin, you can also reset passwords for other users in the User Management table.</p>
            </div>
            <div class="faq-item">
                <h4>Who can access the system?</h4>
                <p>Only authenticated users with roles <strong>admin</strong> or <strong>chair</strong>. Inactive accounts cannot log in.</p>
            </div>
        </section>
    </div>
</div>

<style>
.manual-container {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}
.manual-sidebar {
    width: 220px;
    flex-shrink: 0;
    position: sticky;
    top: 1rem;
}
.manual-nav {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 0.75rem;
    box-shadow: var(--shadow-sm);
}
.manual-nav-link {
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-sm);
    color: var(--slate-600);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.15s;
}
.manual-nav-link:hover,
.manual-nav-link.active {
    background: var(--primary-50);
    color: var(--primary-700);
}
.manual-content {
    flex: 1;
    min-width: 0;
}
.manual-section {
    margin-bottom: 3rem;
    scroll-margin-top: 1.5rem;
}
.manual-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--slate-900);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-200);
}
.manual-section h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--slate-800);
    margin: 1.5rem 0 0.5rem;
}
.manual-section p,
.manual-section ul,
.manual-section ol {
    font-size: 0.95rem;
    color: var(--slate-700);
    line-height: 1.7;
}
.manual-section ul,
.manual-section ol {
    margin-left: 1.25rem;
    margin-bottom: 1rem;
}
.manual-section li {
    margin-bottom: 0.35rem;
}
.manual-section code {
    background: var(--slate-100);
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-family: 'Fira Code', monospace;
    font-size: 0.85rem;
    color: var(--primary-700);
}
.callout {
    padding: 1rem 1.25rem;
    border-radius: var(--radius-md);
    margin: 1rem 0;
    font-size: 0.93rem;
}
.callout-info {
    background: var(--info-light);
    border-left: 4px solid var(--info);
    color: var(--slate-800);
}
.faq-item {
    margin-bottom: 1.25rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--border-light);
}
.faq-item:last-child {
    border-bottom: none;
}
.faq-item h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--slate-900);
    margin-bottom: 0.35rem;
}
.faq-item p {
    margin: 0;
    color: var(--slate-600);
}
@media (max-width: 768px) {
    .manual-container {
        flex-direction: column;
    }
    .manual-sidebar {
        width: 100%;
        position: static;
    }
    .manual-nav {
        flex-direction: row;
        flex-wrap: wrap;
    }
}
</style>

<script>
// Highlight active nav link on scroll
const sections = document.querySelectorAll('.manual-section');
const navLinks = document.querySelectorAll('.manual-nav-link');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (scrollY >= sectionTop - 80) current = section.getAttribute('id');
    });
    navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === '#' + current);
    });
});
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

