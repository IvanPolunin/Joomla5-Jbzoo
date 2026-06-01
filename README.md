# Joomla5-Jbzoo

Experimental modernization and compatibility work for JBZoo CCK on Joomla 5/6 and PHP 8.x.

This repository is used to test legacy JBZoo code, identify compatibility issues, prepare migration notes, and develop fixes that may be contributed back to the upstream JBZoo CCK project.

## Project status

This is an active experimental workspace, not an official JBZoo release. The current focus is compatibility research, small safe patches, and migration documentation for modern Joomla and PHP environments.

## Current focus

- Joomla 5 installation and administrator workflow checks
- Joomla 6 compatibility review
- PHP 8.2, 8.3 and 8.4 compatibility audit
- Legacy Joomla API modernization notes
- Migration notes for existing JBZoo websites
- Safe upstream-ready patches

## Compatibility targets

| Area | Target |
| --- | --- |
| CMS | Joomla 5, Joomla 6 |
| PHP | PHP 8.2, PHP 8.3, PHP 8.4 |
| Extension | JBZoo CCK legacy codebase |
| Status | Experimental compatibility work |

## Work tracking

Open issues are used as the public roadmap:

- [Joomla 5/6 compatibility roadmap](https://github.com/IvanPolunin/Joomla5-Jbzoo/issues/1)
- [PHP 8.x compatibility audit](https://github.com/IvanPolunin/Joomla5-Jbzoo/issues/2)
- [Legacy Joomla API modernization](https://github.com/IvanPolunin/Joomla5-Jbzoo/issues/3)
- [Migration notes for existing JBZoo websites](https://github.com/IvanPolunin/Joomla5-Jbzoo/issues/4)

## Goals

- Test JBZoo compatibility with Joomla 5 and Joomla 6
- Identify deprecated Joomla APIs
- Fix PHP 8.x compatibility issues
- Improve installation and migration documentation
- Prepare safe patches for upstream contribution
- Review legacy PHP code for maintainability and security concerns

## Contributing

Small, focused compatibility reports and patches are preferred. See [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## Upstream project

Upstream repository:

https://github.com/JBZoo-CCK/JBZoo

JBZoo is a long-running open-source Joomla CCK extension used by legacy and active Joomla/Zoo websites.
