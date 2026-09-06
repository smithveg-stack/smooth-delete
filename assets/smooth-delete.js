/**
 * SmoothDelete — shared frontend behavior (no framework, no jQuery).
 *
 * Two deletion modes:
 *   - Safe mode (default): row is removed only after the server confirms
 *     { success: true }. On failure the button is re-enabled and an error
 *     is shown. The database and the UI never disagree.
 *   - Optimistic mode: row is removed as soon as the request settles,
 *     even on failure/network error, matching a jQuery `.always()` pattern.
 *     Use this only when instant feedback matters more than the small
 *     chance the UI and database briefly disagree, and always warn the
 *     user when that happens.
 *
 * Markup contract:
 *   <tr data-delete-row>
 *     <td>...</td>
 *     <td>
 *       <button type="button" data-delete-url="/records/42" data-delete-mode="safe">
 *         Delete
 *       </button>
 *     </td>
 *   </tr>
 *   <div id="sd-status" class="sd-status" role="status" aria-live="polite" hidden></div>
 */

(function () {
    "use strict";

    var TIMING = {
        // must match assets/smooth-delete.css transition durations
        fadeMs: 200,
        collapseMs: 200
    };

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : "";
    }

    function showStatus(message, variant) {
        var el = document.getElementById("sd-status");
        if (!el) {
            return;
        }
        el.textContent = message;
        el.hidden = false;
        el.className = "sd-status" + (variant ? " sd-status--" + variant : "");
    }

    function maybeShowEmptyState(table) {
        if (!table) {
            return;
        }
        var remaining = table.querySelectorAll("tbody [data-delete-row]").length;
        var emptyState = table.parentElement
            ? table.parentElement.querySelector("[data-empty-state]")
            : null;

        if (remaining === 0) {
            table.hidden = true;
            if (emptyState) {
                emptyState.hidden = false;
            }
        }
    }

    function animateRowRemoval(row, onDone) {
        var table = row.closest("table");

        row.classList.add("delete-row-leaving");

        requestAnimationFrame(function () {
            row.classList.add("delete-row-fading");
        });

        row.addEventListener(
            "transitionend",
            function () {
                row.classList.add("delete-row-collapsing");

                row.addEventListener(
                    "transitionend",
                    function () {
                        row.remove();
                        maybeShowEmptyState(table);
                        if (onDone) {
                            onDone();
                        }
                    },
                    { once: true }
                );
            },
            { once: true }
        );
    }

    async function deleteRow(button) {
        if (button.disabled) {
            return;
        }

        var row = button.closest("[data-delete-row]");
        var url = button.dataset.deleteUrl;
        var mode = button.dataset.deleteMode === "optimistic" ? "optimistic" : "safe";

        if (!row || !url) {
            return;
        }

        if (!window.confirm("Delete this record? This cannot be undone.")) {
            return;
        }

        button.disabled = true;

        var response = null;
        var networkError = null;

        try {
            response = await fetch(url, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": getCsrfToken()
                }
            });
        } catch (err) {
            networkError = err;
        }

        var succeeded = false;
        var serverMessage = "";

        if (response) {
            try {
                var body = await response.json();
                succeeded = response.ok && body && body.success === true;
                serverMessage = body && body.message ? body.message : "";
            } catch (parseError) {
                succeeded = false;
            }
        }

        if (succeeded) {
            animateRowRemoval(row);
            showStatus("Record deleted.", "success");
            return;
        }

        if (mode === "optimistic") {
            // Row disappears regardless, but we are honest that the
            // deletion was not confirmed by the server.
            animateRowRemoval(row);
            showStatus(
                "The record was removed from view, but the server did not confirm deletion. " +
                    "It may reappear after a refresh.",
                "warning"
            );
            return;
        }

        // Safe mode failure: keep the row, re-enable the button.
        button.disabled = false;
        showStatus(
            serverMessage || "The record could not be deleted. Please try again.",
            "error"
        );

        if (networkError) {
            console.error("SmoothDelete network error:", networkError);
        }
    }

    document.addEventListener("click", function (event) {
        var button = event.target.closest("[data-delete-url]");
        if (button) {
            deleteRow(button);
        }
    });
})();
