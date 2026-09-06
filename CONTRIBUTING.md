# Contributing

Contributions are welcome, and this project is meant to be extended.

## Good first contributions

- Adding an example for another backend framework (Django, Rails,
  Express, Spring, Symfony, classic ASP, etc.)
- Improving keyboard or screen-reader accessibility
- Adding automated tests to an existing example
- Improving error handling or edge-case coverage
- Testing the animation in additional browsers and reporting issues
- Improving documentation, especially the integration guide

## Adding a new backend example

1. Create `examples/<your-stack>/`.
2. Implement the same behavior as the existing examples: display records,
   confirm deletion, call a dedicated endpoint, animate the row out only
   after a real success response (safe mode), and follow the response
   contract in the README (`{"success": true|false, "message": "..."}`
   with the matching HTTP status).
3. Use parameterized queries or your framework's ORM — never
   string-concatenated SQL.
4. Check CSRF (or your framework's equivalent) on the delete request.
5. Add a short `examples/<your-stack>/README.md` explaining how to run it
   locally.
6. Update the examples table in the root `README.md`.

## Workflow

1. Fork the project.
2. Create a focused branch (`feature/laravel-example`, not `patch-1`).
3. Keep commits scoped to one logical change.
4. If you're proposing a large architectural change, open an issue first
   so we can discuss it before you invest the work.
5. Open a merge request describing what changed and why.

## Reporting bugs

Open an issue describing:

- Which example is affected
- Steps to reproduce
- What you expected vs. what happened
- Browser/runtime version if relevant

Please don't report security vulnerabilities as public issues — see
[SECURITY.md](SECURITY.md).
