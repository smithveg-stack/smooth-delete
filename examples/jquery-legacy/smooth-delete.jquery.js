/**
 * SmoothDelete — legacy jQuery version.
 *
 * For older codebases already using jQuery. Demonstrates .done(), .fail(),
 * and .always() explicitly so the safe/optimistic tradeoff is visible in
 * the code, not hidden behind async/await.
 *
 * Markup and timing are identical to assets/smooth-delete.js — see that
 * file's header comment for the full contract.
 */
(function ($) {
    "use strict";

    function animateRowRemoval($row, onDone) {
        var $table = $row.closest("table");

        $row.addClass("delete-row-leaving");

        requestAnimationFrame(function () {
            $row.addClass("delete-row-fading");
        });

        $row.one("transitionend", function () {
            $row.addClass("delete-row-collapsing");

            $row.one("transitionend", function () {
                $row.remove();

                var remaining = $table.find("tbody [data-delete-row]").length;
                if (remaining === 0) {
                    $table.hide();
                    $table.closest("[data-table-wrapper]").find("[data-empty-state]").show();
                }

                if (onDone) {
                    onDone();
                }
            });
        });
    }

    function showStatus(message, variant) {
        var $status = $("#sd-status");
        $status
            .text(message)
            .removeClass("sd-status--error sd-status--warning sd-status--success")
            .addClass(variant ? "sd-status--" + variant : "")
            .prop("hidden", false);
    }

    $(document).on("click", "[data-delete-url]", function () {
        var $button = $(this);

        if ($button.prop("disabled")) {
            return;
        }

        var $row = $button.closest("[data-delete-row]");
        var url = $button.data("delete-url");
        var mode = $button.data("delete-mode") === "optimistic" ? "optimistic" : "safe";

        if (!window.confirm("Delete this record? This cannot be undone.")) {
            return;
        }

        $button.prop("disabled", true);

        $.ajax({
            url: url,
            method: "POST",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") || ""
            }
        })
            .done(function (response) {
                if (response && response.success) {
                    animateRowRemoval($row);
                    showStatus("Record deleted.", "success");
                } else if (mode === "safe") {
                    $button.prop("disabled", false);
                    showStatus((response && response.message) || "Delete failed.", "error");
                }
                // optimistic + server-reported failure is handled in .always()
            })
            .fail(function () {
                if (mode === "safe") {
                    $button.prop("disabled", false);
                    showStatus("The record could not be deleted. Please try again.", "error");
                }
                // optimistic mode falls through to .always()
            })
            .always(function (responseOrJqXHR, textStatus) {
                if (mode !== "optimistic") {
                    return;
                }

                // In optimistic mode the row leaves the screen no matter what,
                // matching an "instant, no confirmation" feel. If we can't
                // prove the delete succeeded, we say so instead of pretending.
                var confirmed =
                    textStatus === "success" &&
                    responseOrJqXHR &&
                    responseOrJqXHR.success === true;

                if ($row.closest("body").length === 0) {
                    return; // already removed via .done()
                }

                animateRowRemoval($row);

                if (!confirmed) {
                    showStatus(
                        "The record was removed from view, but the server did not confirm " +
                            "deletion. It may reappear after a refresh.",
                        "warning"
                    );
                }
            });
    });
})(jQuery);
