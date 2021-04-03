# Extend-A-Story

Extend-A-Story is an interactive, extendable, choose-your-own-adventure story.
It is written in PHP and uses a MySQL database.

## Under Development

This is a development version of Extend-A-Story that may not be stable or function at all.
Download a stable version of Extend-A-Story from our releases page:

[Extend-A-Story Releases](https://github.com/extend-a-story/extend-a-story/releases)

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

Use your web browser to open `install.php` in the `<story-root>` directory.
For example, if your website is `https://example.com/` and
`<story-root>` is a directory called `story` in the root directory of your website,
this is the URL you would use: `https://example.com/story/install.php`

The install page will guide you through the install process.
You will be asked to make changes to your Extend-A-Story configuration file.
This file is located at: `<story-root>/include/config/Configuration.php`.
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

## Multiple Stories

Follow the same instructions as above to set up multiple stories on your website,
but note that each story will need its own database and directory.

## Database Maintenance

Although I've tried my best to ensure that Extend-A-Story is bug free,
some inconsistencies may still crop up in your database.
Some examples of problems you may find are:

- A link record has an incorrect 'IsCreated' flag, where the episode it points to is created,
but the link says it's not, or the episode it points to is not created, but the link says it is.
- A link record has an incorrect 'IsBackLink' flag, where the target episode is an actual child of the source episode,
but the link says it's not, or the target is not a child of the source episode, but the link says it is.

I've included SQL statements that will identify these problems in: [sql/DBMaintenance.sql](sql/DBMaintenance.sql)
