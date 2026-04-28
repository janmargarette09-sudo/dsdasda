<?php
// includes/functions.php — Utility helpers

/**
 * Sanitize user input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format number of units
 */
function formatUnits($units) {
    return number_format($units, 1) . ' units';
}

/**
 * Format date/time
 */
function formatDate($datetime, $format = 'M j, Y g:i A') {
    if (!$datetime) return '—';
    return date($format, strtotime($datetime));
}

/**
 * Generate pagination links
 */
function paginate($total, $page, $perPage = ITEMS_PER_PAGE) {
    $totalPages = max(1, ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'hasPrev' => $page > 1,
        'hasNext' => $page < $totalPages
    ];
}

/**
 * Flash message helper
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Build query string preserving existing params
 */
function buildQuery(array $params) {
    $current = $_GET;
    foreach ($params as $k => $v) {
        if ($v === null) unset($current[$k]);
        else $current[$k] = $v;
    }
    return '?' . http_build_query($current);
}

