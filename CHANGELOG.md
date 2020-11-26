# Release Notes for Gatsby Helper

## Unreleased

### Added
- Added the correct header to trigger Gatsby Cloud previews correctly. ([#5](https://github.com/craftcms/gatsby-helper/issues/5))

### Changed
- Renamed the `previewServerUrl` setting to `webhookTarget`.

### Fixed
- Fixed a bug where queries that would not be defined by the GraphQL schema were exposed to Gatsby, anyway.
- Fixed a bug where it was impossible to not provide server port for the preview server url. ([#4](https://github.com/craftcms/gatsby-helper/issues/4))

## 1.0.0-beta.1 - 2020-11-03

### Added
- Initial release
