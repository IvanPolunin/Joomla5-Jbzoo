# Contributing

This repository is focused on experimental JBZoo compatibility work for Joomla 5/6 and PHP 8.x.

## Scope

Good contributions include:

- Reproducible compatibility reports
- Small PHP 8.x fixes
- Joomla 5/6 API modernization notes
- Installation and migration documentation
- Safe patches that can be proposed upstream

Avoid unrelated rewrites, formatting-only changes, or large refactors without an issue first.

## Reporting compatibility issues

Please include:

- Joomla version
- PHP version
- Database version
- JBZoo/Zoo version or commit
- Installation or upgrade path
- Exact error message or stack trace
- Steps to reproduce

## Patch guidelines

- Keep changes small and reviewable
- Prefer backward-compatible fixes where possible
- Explain the Joomla/PHP compatibility reason
- Add migration notes when behavior changes
- Link the related issue in the pull request

## Testing notes

At minimum, describe what was checked manually. When possible, test administrator installation, frontend rendering, and common JBZoo workflows on the target Joomla/PHP version.
