# Release Notes for Gatsby Helper

## Unreleased

### Fixed
- Fixed a bug that could lead to a Gatsby content sourcing failure. ([#13](https://github.com/craftcms/gatsby-helper/issues/13))

## 1.0.1 - 2020-03-30

### Changed
- Source node types registered on `craft\gatsbyhelper\events\RegisterSourceNodeTypesEvent::$types` should now be indexed by the type’s interface name.

### Fixed
- Fixed a bug that could lead to a Gatsby content sourcing failure. ([#9](https://github.com/craftcms/gatsby-helper/issues/9))
- Fixed a bug where the “Build Webhook URL” said it could be set to an alias or environment variable, but didn’t actually support them. ([#10](https://github.com/craftcms/gatsby-helper/pull/10))

## 1.0.0 - 2020-03-16

### Added
- Added a new “Build Webhook URL” setting that triggers Gatsby site builds on element save, if set. ([#7](https://github.com/craftcms/gatsby-helper/issues/7))

### Fixed
- Fixed a Javascript error that would occur when saving an entry without previewing it. ([#6](https://github.com/craftcms/gatsby-helper/issues/6))

## 1.0.0-beta.2 - 2020-12-01

> {note} You will need to ensure that your “Preview Webhook URL” setting is set correctly after updating.

### Added
- Added the correct header to trigger Gatsby Cloud previews correctly. ([#5](https://github.com/craftcms/gatsby-helper/issues/5))
- Added the “Preview Webhook URL” setting.

### Removed
- Removed the “Preview Server URL” setting.

### Fixed
- Fixed a bug where queries that would not be defined by the GraphQL schema were exposed to Gatsby, anyway.
- Fixed a bug where it was impossible to not provide server port for the preview server url. ([#4](https://github.com/craftcms/gatsby-helper/issues/4))

## 1.0.0-beta.1 - 2020-11-03

### Added
- Initial release
