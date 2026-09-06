<?php
declare(strict_types=1);

/**
 * Dedicated AJAX delete endpoint.
 *
 * Contract:
 *   POST /delete.php  { "id": 42, "csrf_token": "..." }
 *   -> 200 { "success": true }
 *   -> 400 { "success": false, "message": "..." }   invalid id
 *   -> 403 { "success": false, "message": "..." }   bad/missing CSRF token
 *   -> 404 { "success": false, "message": "..." }   record does not exist
 *   -> 500 { "success": false, "message": "..." }   database error
 */

require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true) ?? [];

$submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf_token'] ?? null);

if (!smooth_delete_csrf_valid($submittedToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$id = filter_var($payload['id'] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid record id.']);
    exit;
}

try {
    $pdo = smooth_delete_db();

    $exists = $pdo->prepare('SELECT 1 FROM records WHERE id = :id');
    $exists->execute(['id' => $id]);

    if ($exists->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Record not found.']);
        exit;
    }

    // NOTE: a real app must also verify the current user is authorized to
    // delete this specific record, not just that they are logged in.

    $delete = $pdo->prepare('DELETE FROM records WHERE id = :id');
    $delete->execute(['id' => $id]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
