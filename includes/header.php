<?php
// includes/header.php — Shared HTML head, nav, sidebar
if (!isset($pageTitle)) $pageTitle = 'Teacher Load System';

$currentModule = basename(dirname($_SERVER['PHP_SELF']));
$navItems = [
    'dashboard' => ['icon' => 'grid-3x3', 'label' => 'Dashboard'],
    'teachers' => ['icon' => 'users', 'label' => 'Teachers'],
    'subjects' => ['icon' => 'book-open', 'label' => 'Subjects'],
    'schedules' => ['icon' => 'calendar-days', 'label' => 'Schedules'],
    'assignments' => ['icon' => 'zap', 'label' => 'Assignments'],
    'reports' => ['icon' => 'file-text', 'label' => 'Reports'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — TeacherLoad</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="<?= BASE_URL . htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php $flash = getFlash(); if ($flash): ?>
        <div class="flash flash-<?= $flash['type'] ?>" id="flash-msg">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <?php if ($flash['type'] === 'success'): ?>
                    <polyline points="20 6 9 17 4 12"></polyline>
                <?php else: ?>
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                <?php endif; ?>
            </svg>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/>
                    </svg>
                </div>
                <span class="brand-text">TeacherLoad</span>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">Main Menu</div>
                <?php foreach ($navItems as $module => $item): ?>
                    <a href="<?= BASE_URL ?>/modules/<?= $module ?>/" class="nav-link <?= $currentModule === $module ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <?php if ($item['icon'] === 'grid-3x3'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            <?php elseif ($item['icon'] === 'users'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <?php elseif ($item['icon'] === 'book-open'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <?php elseif ($item['icon'] === 'calendar-days'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php elseif ($item['icon'] === 'zap'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            <?php elseif ($item['icon'] === 'file-text'): ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <?php endif; ?>
                        </span>
                        <?= $item['label'] ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['user_data']['full_name'] ?? 'User') ?></div>
                    <div class="user-role"><?= ucfirst(htmlspecialchars($_SESSION['user_data']['role'] ?? 'Chair')) ?></div>
                </div>
                <a href="<?= BASE_URL ?>/modules/auth/logout.php" class="btn btn-sm btn-outline" style="width: 100%; border-color: rgba(255,255,255,0.15); color: rgba(255,255,255,0.7);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.375rem;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
                <div class="topbar-actions">
                    <span class="badge badge-secondary" style="display: flex; align-items: center; gap: 0.375rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        SY <?= htmlspecialchars($_SESSION['current_school_year'] ?? '2024-2025') ?>
                    </span>
                    <span class="badge badge-primary" style="display: flex; align-items: center; gap: 0.375rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?= htmlspecialchars($_SESSION['current_semester'] ?? '1st') ?> Sem
                    </span>
                </div>
            </header>
            <div class="content-wrapper">

