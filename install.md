# Install Extend-A-Story

Follow these steps to install Extend-A-Story.

## Create Story Database

Create a MySQL database for Extend-A-Story and create a MySQL user that has full access to that database.
The process to do this will vary depending on your hosting environment.
As an example, here is how you would create a database called `StoryDatabase`, a user called `StoryUser`,
with password `StoryPassword`, on a MySQL server that is on the same host as your web server:

```SQL
CREATE DATABASE StoryDatabase;
CREATE USER "StoryUser"@"localhost" IDENTIFIED BY "StoryPassword";
GRANT ALL ON StoryDatabase.* TO "StoryUser"@"localhost";
```

## Create Story Root Directory

Extend-A-Story must be installed in a directory that your web server can access.
We refer to this directory as the `<story-root>` directory.

Take the contents of the `www` directory and place them in the `<story-root>` directory.

## Run Install Process

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

## Create First Episode

You must enable episode creation before you can create the first episode of your story.
Use your web browser to open `admin.php` in the `<story-root>` directory.
Log in using the administrator account you created earlier.
Click `Configure Story Settings`.
Set `Is Writeable` to `Yes` and click `Save`.

Now you can create the first episode in your story.
Use your web browser to open `read.php` in the `<story-root>` directory.
Click the `Create` button to start creating the episode.

## Create Story Home Page

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

## Install Documentation

Take the contents of the `docs` directory and place them in a directory accessible to your web server.
The HTML pages in this directory are documentation for users of Extend-A-Story.
Add a link to this directory from your story home page so that your users can find the documentation.

## Configure Page footer

You may configure a page footer that will be included at the bottom of every page generated by Extend-A-Story.
Place your desired page footer in the footer file:

`<story-root>/include/config/Footer.php`

## Multiple Stories

You may have multiple stories on your website, but you need a separate install of Extend-A-Story for each story.
Follow the above instructions to install Extend-A-Story for each story.
Each story needs its own database and directory on your web server.
