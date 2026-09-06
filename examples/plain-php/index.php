<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

$pdo = smooth_delete_db();
$records = $pdo->query('SELECT id, name FROM records ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$csrfToken = smooth_delete_csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>SmoothDelete — plain PHP example</title>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
<link rel="stylesheet" href="../../assets/smooth-delete.css">
<style>
    body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; }
    table { width: 100%; border-collapse: collapse; }
    td, th { padding: 0.6rem 0.5rem; border-bottom: 1px solid #ddd; text-align: left; }
    button { cursor: pointer; }
</style>
</head>
<body>

<h1>SmoothDelete — plain PHP example</h1>
<p>PDO + SQLite backend, CSRF-protected, safe-mode deletion.</p>

<div data-table-wrapper>
    <table data-table <?= empty($records) ? 'hidden' : '' ?>>
        <thead>
            <tr><th>ID</th><th>Name</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr data-delete-row>
                <td><?= (int) $record['id'] ?></td>
                <td><?= htmlspecialchars($record['name'], ENT_QUOTES) ?></td>
                <td>
                    <button
                        type="button"
                        data-delete-url="delete.php"
                        data-delete-id="<?= (int) $record['id'] ?>"
                        data-delete-mode="safe">
                        Delete
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p data-empty-state <?= empty($records) ? '' : 'hidden' ?> class="sd-empty-state">
        No records left.
    </p>
</div>

<div id="sd-status" class="sd-status" role="status" aria-live="polite" hidden></div>

<script>
    // This example's endpoint expects { id, csrf_token } in the JSON body,
    // so it uses a small adapter instead of assets/smooth-delete.js directly
    // (that shared script assumes the id is baked into the URL).
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-delete-url]');
        if (!button) return;
        if (button.disabled) return;

        if (!confirm('Delete this record? This cannot be undone.')) return;

        button.disabled = true;
        const row = button.closest('[data-delete-row]');
        const statusEl = document.getElementById('sd-status');

        try {
            const response = await fetch(button.dataset.deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id: Number(button.dataset.deleteId),
                    csrf_token: document.querySelector('meta[name="csrf-token"]').content
                })
            });
            const body = await response.json();

            if (response.ok && body.success) {
                animateRowRemoval(row);
                statusEl.textContent = 'Record deleted.';
                statusEl.hidden = false;
            } else {
                button.disabled = false;
                statusEl.textContent = body.message || 'Delete failed.';
                statusEl.className = 'sd-status sd-status--error';
                statusEl.hidden = false;
            }
        } catch (err) {
            button.disabled = false;
            statusEl.textContent = 'Network error. Please try again.';
            statusEl.className = 'sd-status sd-status--error';
            statusEl.hidden = false;
        }
    });

    function animateRowRemoval(row) {
        const table = row.closest('table');
        row.classList.add('delete-row-leaving');
        requestAnimationFrame(() => row.classList.add('delete-row-fading'));
        row.addEventListener('transitionend', () => {
            row.classList.add('delete-row-collapsing');
            row.addEventListener('transitionend', () => {
                row.remove();
                if (table.querySelectorAll('tbody [data-delete-row]').length === 0) {
                    table.hidden = true;
                    document.querySelector('[data-empty-state]').hidden = false;
                }
            }, { once: true });
        }, { once: true });
    }
</script>
</body>
</html>
