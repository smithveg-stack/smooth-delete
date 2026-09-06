# Security Policy

## Reporting a Vulnerability

Please do **not** open a public issue for a security vulnerability.

Instead, open a [confidential issue](https://gitlab.com/smv-k8x-2741/smooth-delete/-/issues/new?issue%5Bconfidential%5D=true)
on GitLab — confidential issues are only visible to project maintainers,
not the public.

Include:

- A description of the vulnerability
- Steps to reproduce it
- Which example(s) are affected
- The potential impact

You should receive an acknowledgement within a few days. Once a fix is
available, we'll coordinate on disclosure timing with you.

## Scope

This project is a set of educational reference examples. Each backend
example is meant to demonstrate a safe pattern (parameterized queries,
CSRF checks, authorization checks) but has not undergone a formal
third-party security audit. Review and adapt any example before using it
in a production system handling real user data.
