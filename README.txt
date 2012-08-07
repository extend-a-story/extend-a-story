Extend-A-Story is an interactive, extendable, choose your own adventure
story. It is written in PHP and is designed to work with a MySQL
database.


Here are the basic steps needed to get Extend-A-Story working on your
site with just one story:

- Set up a MySQL database to store the Extend-A-Story data.

- In the new database, run the database creation script
  'ExtendAStory.sql'.

- Take the contents of the 'www' directory and place them in a directory
  accessible to your web server.

- Modify db.php to contain the relevant login information for the
  database you created.

- Point your browser to '<story root>/admin.php' and login as the
  administrator:
  - Login Name : admin
  - Password   : change-me

- !!! CHANGE THE PASSWORD OF THE ADMIN ACCOUNT !!!

- Click on the 'Configure Story Settings' link and change the story
  configuration to suit your site:

  - Story Name       : The name of the story.

  - Site Name        : The name of your site.

  - Story Home       : URL for the home page of the story.

  - Site Home        : URL for your site.

  - Read Episode URL : URL leading to the PHP script that displays
                       episodes: 'read.php'. This is sent out in emails
                       from Extend-A-Story to authors letting them know
                       when episodes they have written have been
                       extended.

  - Admin Email      : The email address for the administrator of this
                       story. All email sent from Extend-A-Story is sent
                       from this address. Also, this address receives
                       email every time an episode is created.
                       Extend-A-Story will send no email if this is not
                       set.

  - Is Writeable     : When set to 'Yes', the story is open and people
                       can write episodes. When set to 'No', the story
                       is closed and people cannot write episodes.
                       People can still read episodes that have been
                       written.

  - Max Links        : The maximum number of links that can lead off
                       from a particular episode.

  - Max Edit Days    : The maximum number of days that an author will be
                       allowed to edit an episode after they have
                       created it. Moderators are not affected by this.

- Create a home page for the story with links pointing to the various
  features of Extend-A-Story that you want people to have access to. You
  may want to delete the PHP scripts for features that you don't want to
  use. The PHP scripts offering various features are as follows:

  - read.php        : Read the story. Provide a link to this script to
                      start the reader at the very beginning of the
                      story.

  - statistics.php  : Displays a count of the created episodes, empty
                      episodes, and total episodes.

  - search.php      : Allows various ways to search for episodes. If you
                      want to remove this functionality, be sure to
                      delete 'results.php' as well.

  - story-tree.php  : Provides a listing of which episodes are available
                      at various levels of depth within the story.

  - admin.php       : Grants access to the administration and moderator
                      functionality.

  - list-locked.php : Displays all episodes that are currently locked
                      for creation.

- Create the first episode. When you first set up the database, no
  episodes exist. Create the first episode to start the story and verify
  that everything is working. If you properly set up your AdminEmail
  setting, you should receive email notifying you of the newly created
  episode.

- Take the contents of the 'docs' directory and place them in a
  directory accessible to your web server. These HTML pages are the
  documentation for the users of Extend-A-Story. Be sure to link to them
  from your site.


If you want to set up multiple stories, you follow the same directions
as above, but bear in mind the following:

- Each story will need its own database.

- Each story will need its own separate directory with a copy of the PHP
  scripts.


Although I've tried my best to ensure that Extend-A-Story is bug free,
some inconsistencies may still crop up in your database. Some examples
of problems you may find are:

- A link record has an incorrect 'IsCreated' flag, where the episode it
  points to is created, but the link says it's not, or the episode it
  points to is not created, but the link says it is.

- A link record has an incorrect 'IsBackLink' flag, where the target
  episode is an actual child of the source episode, but the link says
  it's not, or the target is not a child of the source episode, but the
  link says it is.

I've included select statements that will identify these problems in
'DBMaintenance.sql'.
