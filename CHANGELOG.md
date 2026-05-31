# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `YandexSearchService` now accepts the Folder ID and API key as optional
  constructor arguments — the recommended way to configure the client.
- New canonical `YandexSearchAPI\xml\Misspelling` class (correctly spelled).
- Tests for multiple result groups, multi-passage snippets, and responses
  without a `<results>` block.

### Changed
- **BREAKING:** minimum PHP version raised from 8.0 to **8.1**.
- **BREAKING:** `YandexSearchService::__construct()` is now type-hinted against
  `GuzzleHttp\ClientInterface` instead of the concrete `GuzzleHttp\Client`.
  Passing a `GuzzleHttp\Client` keeps working.
- `xml\Response::getResults()` now returns `null` instead of fatally erroring
  when the response has no `<results>` element; the parser yields an empty
  result set in that case.
- Malformed XML responses no longer emit PHP warnings; they are reported solely
  via `SearchException` (internal libxml errors are used).
- Dev tooling upgraded: PHPUnit `^10.5 || ^11.5`, PHPStan `^2.0`. CI now runs the
  test suite against a PHP `8.1`–`8.4` matrix.

### Deprecated
- `YandexSearchService::setApiId()` / `setApiKey()` — pass the credentials to the
  constructor instead.
- `YandexSearchAPI\xml\Mispelling` — use `YandexSearchAPI\xml\Misspelling`
  instead. The old (misspelled) class remains as a subclass alias and will be
  removed in a future major release.

### Fixed
- `Pagination::getPagesCount()` no longer throws `DivisionByZeroError` when the
  total or page size is unset; it returns `0` instead.
- `Result::getDomain()` now throws `SearchException` for hostless URLs (e.g.
  `/path/only`) instead of a `TypeError`.
- `xml\Passage::getText()` now trims surrounding whitespace, so multi-passage
  snippets are joined cleanly.
