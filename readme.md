# Extend-A-Story

Extend-A-Story is an interactive, extendable, choose-your-own-adventure story.
It is written in PHP and uses a MySQL database.

## Under Development

**This version of Extend-A-Story is under development and may not be stable or work correctly.**

Download a stable version of Extend-A-Story from our tags page:

[Extend-A-Story Tags](https://github.com/extend-a-story/extend-a-story/tags)

## Installation

Follow these steps to install Extend-A-Story.

### Create Database

Create a MySQL database for Extend-A-Story and create a MySQL user that has full access to that database.
The process to do this will vary depending on your hosting environment.
As an example, here is how you would create a database called `StoryDatabase`, a user called `StoryUser`,
with password `StoryPassword`, on a MySQL server that is on the same host as your web server:

```SQL
CREATE DATABASE StoryDatabase;
CREATE USER "StoryUser"@"localhost" IDENTIFIED BY "StoryPassword";
GRANT ALL ON StoryDatabase.* TO "StoryUser"@"localhost";
```

### Install Extend-A-Story

Take the contents of the `www` directory and place them in a directory accessible to your web server.
We will refer to this directory as `<story-root>`.

Use your web browser to open the install page, `install.php`, in the `<story-root>` directory.
For example, if your website is `https://example.com/` and
`<story-root>` is a directory called `story` in the root directory of your website,
you would use this URL:

`https://example.com/story/install.php`

The install page will guide you through the install process.
You will be asked to make changes to your Extend-A-Story configuration file.
This file is located at:

`<story-root>/include/config/Configuration.php`

You will need to know the connection settings for the database you created above.
You will also be prompted to create an administrator account for your story and configure your story settings.

### Create First Episode

You must enable episode creation before you can create the first episode of your story.
Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using the administrator account you created earlier.
Click `Configure Story Settings`.
Set `Is Writeable` to `Yes` and click `Save`.

Now you can create the first episode in your story.
Use your web browser to open `read.php` in the `<story-root>` directory.
Click the `Create` button to start creating the episode.

### Create Story Home Page

Create a home page for your story with links pointing to
the various features of Extend-A-Story that you want people to have access to.
You may want to delete the PHP scripts for features that you don't want to use.
The PHP scripts offering various features are as follows:

- `read.php` : Read the story.
Provide a link to this script to start the reader at the very beginning of the story.
- `statistics.php` : Displays a count of the created episodes, empty episodes, and total episodes.
- `search.php` : Allows various ways to search for episodes.
If you want to remove this functionality, be sure to delete `results.php` as well.
- `story-tree.php` : Provides a listing of which episodes are available at various levels of depth within the story.
- `admin.php` : Grants access to the administration and moderator functionality.
- `list-locked.php` : Displays all episodes that are currently locked for creation.

### Install Documentation

Take the contents of the `docs` directory and place them in a directory accessible to your web server.
The HTML pages in this directory are documentation for users of Extend-A-Story.
Add a link to this directory from your story home page so that your users can find the documentation.

## Upgrading

Follow these steps to upgrade from a previous version of Extend-A-Story to this version.

### Disable Episode Creation

Consider disabling episode creation before upgrading to prevent changes to your database during the process.
How to do this depends on your current version of Extend-A-Story.

#### Versions 2.1.0 and Later

Versions 2.1.0 and later have a web interface for disabling episode creation.

Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using an account with the `Manager` or `Administrator` permission level.
Click `Configure Story Settings`.
Set `Is Writeable` to `No` and click `Save`.

#### Versions Before 2.1.0

Versions before 2.1.0 do not have a web interface for disabling episode creation.
You must make this change directly in your database instead.

Connect to your database and execute the following statement:

```SQL
UPDATE ExtendAStoryVariable SET StringValue = "N" WHERE VariableName = "IsWriteable";
```

### Backups

We recommend making a backup of your database and `<story-root>` directory before upgrading.
If anything goes wrong with the upgrade you can use these backups to restore your current version.

### Database Connection Settings

Make a note of your current database connection settings in your Extend-A-Story configuration file:

`<story-root>/db.php`

The location and content of the Extend-A-Story configuration file is different in this version.
The install process will guide you through setting up your configuration file for this version.

### Upgrade Extend-A-Story

Replace the contents of your `<story-root>` directory with the contents of the `www` directory for this version.

Use your web browser to open the install page, `install.php`, in the `<story-root>` directory.
The install page will guide you through the upgrade process.
You will need the database connection settings that we referenced above.

After you specify your database connection settings you will be prompted for which task you wish to perform.
Be sure to select `Upgrade Existing Database`, otherwise all data in your database will be deleted.

You may be asked for additional information to complete the upgrade
depending on which version of Extend-A-Story you are upgrading from.

### Enable Episode Creation

If you disabled episode creation before starting the upgrade
be sure to enable it again once the upgrade is complete.

Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using an account with the `Manager` or `Administrator` permission level.
Click `Configure Story Settings`.
Set `Is Writeable` to `Yes` and click `Save`.

## Multiple Stories

Follow the same instructions as above to set up multiple stories on your website,
but note that each story will need its own database and directory.

## Release Notes

[Extend-A-Story Release Notes](release-notes.md)

## Database Maintenance

Although I've tried my best to ensure that Extend-A-Story is bug free,
some inconsistencies may still crop up in your database.
Some examples of problems you may find are:

- A link record has an incorrect 'IsCreated' flag, where the episode it points to is created,
but the link says it's not, or the episode it points to is not created, but the link says it is.
- A link record has an incorrect 'IsBackLink' flag, where the target episode is an actual child of the source episode,
but the link says it's not, or the target is not a child of the source episode, but the link says it is.

I've included SQL statements that will identify these problems in: [sql/DBMaintenance.sql](sql/DBMaintenance.sql)
