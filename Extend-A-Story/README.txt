Extend-A-Story is an interactive, extendable, choose your own adventure 
story. It is written in PHP and is designed to work with a MySQL database.

Here are the basic steps needed to get Extend-A-Story working on your 
site with just one story:

1. Setup a database to store the Extend-A-Story data.
2. Run the database creation script, 'ExtendAStory.sql'.
3. Login to the database and change the values in the ExtendAStoryVariable
    table to suite your site. Here are the values you may want to adjust:
     StoryName      - The name of the story.
     SiteName       - The name of your site.
     StoryHome      - URL for the main page of the story.
     SiteHome       - URL for your site.
     ReadEpisodeURL - URL leading to the PHP script that displays
                       episodes: 'read.php'. This is sent out in emails
                       from Extend-A-Story to players letting them know
                       when episodes they have written have been extended.
     AdminEmail     - The email address for the administrator of this
                       story. All email sent from Extend-A-Story is sent
                       from this address. Also, this address reveives
                       email every time an episode is created.
                       Extend-A-Story will send no email if this is not
                       set.
     IsWriteable    - When set to 'Y', the story is open and people can
                       write episodes. When set to 'N', the story is 
                       closed and people cannot write episodes. People can 
                       still read episodes that have been written.
     MaxLinks       - The maximum number of links that can lead off on a
                       particular episode.
4. Take the contents of the www directory and place them in a directory
    accessable to your web server.
5. Modify db.inc to contain the relevant login information for the
    database you created.
6. Create a home page for the story with links pointing to the various
    features of Extend-A-Story that you want people to have access to. You
    may want to delete the PHP scripts for features that you don't want to
    use. The PHP scripts offering various features are as follows:
     read.php       - Read the story. Provide a link to this script to
                       start the reader at the very beginning of the 
                       story.
     statistics.php - Displays a count of the created episodes, empty
                       episodes, and total episodes.
     search.php     - Allows various ways to search for episodes. If you
                       want to remove this functionality, be sure to 
                       delete 'results.php' as well.
     story-tree.php - Provides a listing of which episodes are available 
                       at various levels of depth within the story.
7. Create the first episode. When you first set up the database, no
    episodes exist. Create the first episode to start the story and verify
    that everything is working. If you properly set up your AdminEmail
    setting, you should receive email notifying you of the newly created
    episode.
8. Take the contents of the docs directory and place them in a directory 
    accessable to your web server. These HTML pages are the documentation 
    for the users of Extend-A-Story. Be sure to link to them from your 
    site.

If you want to set up multiple stories, you follow the same directions as 
above, but bear in mind the following:

- Each story will need its own database.
- Each story will need its own seperate directory with a copy of the PHP
   scripts.

Although I've tried my best to ensure that Extend-A-Story is bug free, 
some inconsistencies may still crop up in your database. Some examples of 
problems you may find are:
- A link record has an incorrect 'IsCreated' flag, where the episode it
   points to is created, but the link says its not, or the episode it
   points to is not created. but the link says it is.
- A link record has an incorrect 'IsBackLink' flag, where the target
   episode is an actual child of the source episode, but the link says its
   not, or the target is not a child of the source episode, the the link
   says it is.
- An episode has no links leading to it (an orphaned episode).
- An episode has no links leading from it (a dead end).

I've included several select statements that will identify these problem 
records in 'DBMaintenance.sql'.
