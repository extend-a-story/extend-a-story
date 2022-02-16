# Extend-A-Story Release Notes

This document contains the release notes for all Extend-A-Story versions since 2.0.0.

## 3.0.0

**This version of Extend-A-Story is under development and may not be stable or work correctly.**

- Add web interface for installing or upgrading Extend-A-Story.

## 2.2.1

- Replace calls to `mysql` library with calls to `mysqli` library.
- Specify the `latin1` character set for all database tables.
- Fix error message typo: `longer then` to `longer than`.

## 2.2.0

- Add `xmlns` attribute to the `html` element in the documentation web pages.
- Add ability to include a common footer at the bottom of every page.
- Fix bug: Cannot Log In Using Admin Interface
- Fix bug: Glitch with Hyphenated Words
- Fix many PHP warnings.
- Update all database tables to use `AUTO_INCREMENT`.

## 2.1.1

- Make the documentation HTML files XTHML 1.1 compliant.
- Fix bug: Old Reference to `db.inc` in the Readme File
- Fix bug: Fatal Error with Single Quote in Password in Admin Interface
- Fix bug: Problems with Single Quotes when Configuring Story Settings
- Fix bug: Problem when Creating a User with a Password with a Single Quote
- Fix bug: Fatal Error when Changing a Password with a Single Quote
- Fix bug: Quotes Not Handled Properly when Editing a User

## 2.1.0

- Add administration features:
    - Add and edit administrator and moderator users.
    - Configure story settings.
    - View potential database problems.
    - Super moderators can delete episodes and links.
    - Moderators can edit any episode.
    - Keep a log of all editing done to each episode.
- Add ability to view the story tree from any starting episode.
- Add the ability to view the back story tree of an episode.
This maps out all the episodes you can reach from an episode by following all back links that link to it.
- Include the author name in extension notification email messages.
- Obfuscate email addresses that are displayed publicly.
- Require the use of a form button to create episodes.
- Order search results by the episode number.
- Display the author name in search results.
- Display result number in search results.

## 2.0.1

- Fix incorrect behavior when editing episodes with options with quotes in their description.
- Show the correct creation date when editing episodes.

## 2.0.0

- Rewrite Extend-A-Story in PHP.
- Release Extend-A-Story under version 2 of the GNU General Public License.
