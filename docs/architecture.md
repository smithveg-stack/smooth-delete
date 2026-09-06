# Architecture

## Why animate a wrapper's height, not the `<tr>` itself

Animating `height` or `transform: scaleY()` directly on a `<tr>` behaves
inconsistently across browsers because table rows aren't standard block
boxes. This library animates:

1. `opacity` + `transform: scale()` on the row (safe on `<tr>` in all
   current evergreen browsers), then
2. the row's cell `padding` down to zero + `transform: scaleY(0)` on the
   row, which collapses the visible height without relying on the browser
   animating a `<tr>`'s intrinsic height directly.

If you need pixel-perfect height animation for a very tall row, wrap each
cell's content in a `<div>` and animate that div's `max-height` instead —
documented as an option here rather than the default, since it adds
markup for a case most tables don't hit.

## Two-stage timeline

1. `delete-row-leaving` is added (arms the CSS transition).
2. Next animation frame: `delete-row-fading` is added (opacity → 0,
   scale → 0.96). The one-frame delay is required — setting both the
   start and end state in the same synchronous tick gives the browser
   nothing to interpolate between, so it jumps instead of animating.
3. On `transitionend`: `delete-row-collapsing` is added (padding → 0,
   scaleY → 0).
4. On the second `transitionend`: the row is removed from the DOM, and
   the container checks whether the table is now empty.

## Safe mode vs. optimistic mode

See the root [README](../README.md#safe-mode-vs-optimistic-mode). The
short version: safe mode is the default because a UI that silently
disagrees with the database is worse than a UI that's briefly not
instant. Optimistic mode exists for the `.always()`-style pattern
explicitly requested for the app this library was extracted from, and
it always tells the user when a removal wasn't actually confirmed.
