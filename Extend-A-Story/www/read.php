<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002  Jeff Weston


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


For information about Extend-A-Story and its authors, please visit the website:
http://www.sir-toby.com/extend-a-story/

*/

  require( "ExtendAStory.php" );

  $error = "";
  $fatal = false;

  $episode = $_GET[ "episode" ];

  $episode = ( int ) $episode;

  if ( $episode == 0 )
    $episode = 1;

  // Connect to the database.
  if ( empty( $error ) )
    connectToDatabase( $error, $fatal );

  if ( empty( $error ) )
    getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );

  if ( empty( $error ) )
  {
    $storyName   = getStringValue( $error, $fatal, "StoryName"   );
    $siteName    = getStringValue( $error, $fatal, "SiteName"    );
    $storyHome   = getStringValue( $error, $fatal, "StoryHome"   );
    $siteHome    = getStringValue( $error, $fatal, "SiteHome"    );
    $isWriteable = getStringValue( $error, $fatal, "IsWriteable" );
  }

  $permissionLevel = 0;

  if ( ( $userID != 0 ) && ( empty( $error ) ) )
  {
    $result = mysql_query( "select PermissionLevel from User where UserID = " . $userID );
    if ( ! $result )
    {
      $error .= "Unable to query user information from database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Unable to fetch user information row from database.<BR>";
        $fatal = true;
      }
      else
      {
        $permissionLevel = $row[ 0 ];
      }
    }
  }

  if ( empty( $error ) )
  {
    $result = mysql_query( "select count( * ) from Link where TargetEpisodeID = " . $episode );
    if ( ! $result )
    {
      $error .= "Problem retrieving link count from the database.<BR>";
      $fatal = true;
    }

    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Problem fetching link count row from the database.<BR>";
      $fatal = true;
    }

    $linkCount = ( int ) $row[ 0 ];

    $result = mysql_query( "select Parent, AuthorSessionID, EditorSessionID, SchemeID, ImageID, " .
                                  "Status, IsLinkable, IsExtendable, AuthorMailto, Title, Text, " .
                                  "AuthorName, AuthorEmail, CreationDate, LockDate, LockKey " .
                           "from Episode where EpisodeID = " . $episode );
    if ( ! $result )
    {
      $error .= "Problem retrieving the episode from the database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {

?>
<HTML><HEAD>
<TITLE>Story Error - Episode <?php echo( $episode ); ?> Not Found</TITLE>
</HEAD><BODY>

<CENTER>

<H1>Story Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Found</H2>
<A HREF="read.php">In the beginning...</A>

</CENTER>

</BODY></HTML>
<?php

        exit;
      }

      $parent          = $row[ 0  ];
      $authorSessionID = $row[ 1  ];
      $editorSessionID = $row[ 2  ];
      $scheme          = $row[ 3  ];
      $image           = $row[ 4  ];
      $status          = $row[ 5  ];
      $isLinkable      = $row[ 6  ];
      $isExtendable    = $row[ 7  ];
      $authorMailto    = $row[ 8  ];
      $title           = $row[ 9  ];
      $text            = $row[ 10 ];
      $authorName      = $row[ 11 ];
      $authorEmail     = $row[ 12 ];
      $creationDate    = $row[ 13 ];
      $lockDate        = $row[ 14 ];
      $lockKey         = $row[ 15 ];

      $title      = htmlentities( $title );
      $text       = htmlentities( $text  );
      $authorName = htmlentities( $authorName );

      $text        = strtr( $text,        getEpisodeBodyTranslationTable(  ) );
      $authorEmail = strtr( $authorEmail, getEmailAddressTranslationTable( ) );

      $lockTime = strtotime( $lockDate );
      $curTime  = time( );
      $diff     = $curTime - $lockTime;
      $minutes  = $diff / 60;
      $minutes  = ( int ) $minutes;
      $timeout  = 60 - $minutes;
    }
  }

  if ( empty( $error ) )
  {
    $result = mysql_query( "select bgcolor, text, link, vlink, alink, background, " .
                           "UncreatedLink, CreatedLink, BackLinkedLink from Scheme where SchemeID = " . $scheme );
    if ( ! $result )
    {
      $error .= "Problem retrieving the scheme from the database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Problem fetching scheme row from the database.<BR>";
        $fatal = true;
      }
      else
      {
        $bgcolorColor   = $row[ 0 ];
        $textColor      = $row[ 1 ];
        $linkColor      = $row[ 2 ];
        $vlinkColor     = $row[ 3 ];
        $alinkColor     = $row[ 4 ];
        $background     = $row[ 5 ];
        $uncreatedLink  = $row[ 6 ];
        $createdLink    = $row[ 7 ];
        $backLinkedLink = $row[ 8 ];

        $body = "<BODY BGCOLOR=\"" . $bgcolorColor . "\" " .
                      "TEXT=\""    . $textColor    . "\" " .
                      "LINK=\""    . $linkColor    . "\" " .
                      "VLINK=\""   . $vlinkColor   . "\" " .
                      "ALINK=\""   . $alinkColor   . "\""  .
                      ( empty( $background ) ? ">" : " BACKGROUND=\"" . $background . "\">" );
      }
    }
  }

  if ( ( empty( $error ) ) && ( $image != 0 ) )
  {
    $result = mysql_query( "select ImageURL from Image where ImageID = " . $image );
    if ( ! $result )
    {
      $error .= "Problem retrieving the image from the database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Problem fetching image row from the database.<BR>";
        $fatal = true;
      }
      else
      {
        $image = $row[ 0 ];
      }
    }
  }

  $canEdit = canEditEpisode( $sessionID, $userID, $episode );

  if ( ( $canEdit ) && ( empty( $error ) ) )
  {
    $result = mysql_query( "select count( * ) from EpisodeEditLog where EpisodeID = " . $episode );
    if ( ! $result )
    {
      $error = "Problem retrieving edit count from database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error = "Problem fetching edit count row from database.<BR>";
        $fatal = true;
      }
      else
      {
        $editCount = $row[ 0 ];
      }
    }
  }

  if ( empty( $error ) )
  {
    $result = mysql_query( "select TargetEpisodeID, IsCreated, IsBackLink, Description from Link where SourceEpisodeID = " .
                           $episode . " order by LinkID" );
    if ( ! $result )
    {
      $error .= "Problem retrieving links from database.<BR>";
      $fatal = true;
    }
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

  if ( ( $isWriteable == "N" ) && ( ( $status == 0 ) || ( $status == 1 ) ) )
  {

?>

<HTML><HEAD>
<TITLE>The End of the Story</TITLE>
</HEAD><BODY>

<CENTER>
<H1>The End of the Story</H1>

<TABLE WIDTH="500">
  <TR>
    <TD>
You have reached the end of the story. This episode has not been created
yet, and episode creation is currently disabled.
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

  }
  else if ( $status == 0 )
  {

?>

<HTML><HEAD>
<TITLE>Creating Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creating Episode <?php echo( $episode ); ?></H1>

<TABLE WIDTH="500">
  <TR>
    <TD>
This episode has not been created yet. If you want, you can create it now.
If you do not wish to create it now, please select cancel.
<P>
<A HREF="create.php?episode=<?php echo( $episode ); ?>&command=Lock">Create this episode!</A>
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Cancel</A> - Do <B>not</B> create this episode.
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

  }
  else if ( $status == 1 )
  {

?>

<HTML><HEAD>
<TITLE>Episode <?php echo( $episode ); ?> is Locked</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Episode <?php echo( $episode ); ?> is Locked</H1>

<TABLE WIDTH="500">
  <TR>
    <TD>
Someone is currently working on this episode. Please wait a few minutes
and try reading it again.
<P>
<?php

    if ( $authorSessionID == $sessionID )
    {

?>
This episode is locked by you. You may manually unlock this episode.
However, if you are working on this episode in another window, unlocking
it will disrupt your work.
<P>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Clear"> - Clear the lock on this episode.
</FORM>
<?php

    }
    else
    {
      if ( $timeout > 0 )
      {

?>
This lock can be manually cleared in <?php echo( $timeout ); ?> <?php echo( $timeout == 1 ? "minute" : "minutes" ); ?>.
(This time will be extended if the author is actively working on the episode.)
<?php

      }
      else
      {

?>
This episode was locked <?php echo( $minutes ); ?> minutes ago and is considered abandoned. You may manually unlock it, if you wish.
<P>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Clear"> - Clear the lock on this episode.
</FORM>
<?php

      }
    }

?>
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>


</CENTER>

</BODY></HTML>

<?php

  }
  else
  {
    $countDate = getStringValue( $error, $fatal, "CountDate" );
    $countValue = getAndIncrementIntValue( $error, $fatal, "CountValue" );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: <?php echo( $title ); ?> [Episode <?php echo( $episode ); ?>]</TITLE>
</HEAD><?php echo( $body ); ?>

<CENTER>
<H1><?php echo( $title ); ?></H1>
<H2><?php echo( $storyName ); ?> - Episode <?php echo( $episode ); ?></H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
<?php echo( $text ); ?>
<?php

    if ( ! empty( $image ) )
    {

?>
<P>
<CENTER>
<IMG SRC="<?php echo( $image ); ?>">
</CENTER>
<?php

    }

?>
<P>
<OL>
<?php

    for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
    {
      $row = mysql_fetch_row( $result );

      $description = $row[ 3 ];
      $description = htmlentities( $description );
      $description = strtr( $description, getOptionTranslationTable( ) );

      if ( $row[ 2 ] == "Y" )
        $image = $backLinkedLink;
      else if ( $row[ 1 ] == "Y" )
        $image = $createdLink;
      else
        $image = $uncreatedLink;

?>
<LI><IMG SRC="<?php echo( $image ); ?>"><A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>"><?php echo( $description ); ?></A></LI>
<?php

    }

?>
</OL>
<?php

    if ( ( $isExtendable == "Y" ) && ( $isWriteable == "Y" ) )
    {

?>
<P>
<A HREF="create.php?episode=<?php echo( $episode ); ?>&command=Extend">Add New Option</A>
<?php

    }

    if ( $episode != 1 )
    {

?>
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
<?php

    }

    if ( $linkCount > 1 )
    {

?>
<P>
<A HREF="link-trace.php?episode=<?php echo( $episode ); ?>">Display All <?php echo( $linkCount ); ?> Links to this Episode</A>
<?php

    }

?>
    </TD>
  </TR>
</TABLE>

<HR>

<?php

    if ( ! empty( $authorName ) )
    {
      if ( ( ( ! empty( $authorEmail ) ) ) && ( $authorMailto == "Y" ) )
      {
        $author = "<A HREF=\"mailto:" . $authorEmail . "\">" . $authorName . "</A>";
      }
      else
      {
        $author = $authorName;
      }
    }
    else
    {
      $author = "";
    }

    if ( ! empty( $author ) )
    {

?>
<ADDRESS><?php echo( $author ); ?></ADDRESS>
<P>
<?php

    }

?>
<?php echo( $creationDate ); ?>
<P>
<?php

    if ( $isLinkable == "Y" )
    {

?>
Linking Enabled
<P>
<?php

    }

    if ( $isExtendable == "Y" )
    {

?>
Extending Enabled
<P>
<?php

    }

?>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>
<P>
<?php echo( $countValue ); ?> episodes viewed since <?php echo( $countDate ); ?>.
<?php

    if ( ( $canEdit ) && ( $isWriteable == "Y" ) )
    {

?>
<P>
<B>You have permission to edit this episode.</B><BR>
<?php

      if ( $status == 3 )
      {
        if ( $editorSessionID == $sessionID )
        {

?>
<B>You already have an edit lock on it.</B><BR>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Clear Edit Lock">
</FORM>
<?php

        }
        else
        {

?>
<B>Someone else is currently editing it.</B>
<?php

          if ( $timeout > 0 )
          {

?>
<B>This lock can be manually cleared in <?php echo( $timeout ); ?> <?php echo( $timeout == 1 ? "minute" : "minutes" ); ?>.</B>
<?php

          }
          else
          {

?>
<B>This episode was locked <?php echo( $minutes ); ?> minutes ago and is considered abandoned.</B><BR>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Clear Edit Lock">
</FORM>
<?php

          }
        }
      }
      else
      {

?>
<A HREF="create.php?episode=<?php echo( $episode ); ?>&command=Edit">Edit</A>
<?php

        if ( $permissionLevel > 1 )
        {
?>
<P>
<B>Advanded Editing Functions</B><BR>
<A HREF="edit.php?episode=<?php echo( $episode ); ?>&command=AddLink">Add a Link</A><BR>
<A HREF="edit.php?episode=<?php echo( $episode ); ?>&command=DeleteLink">Delete a Link</A><BR>
<A HREF="edit.php?episode=<?php echo( $episode ); ?>&command=DeleteEpisode">Delete this Episode</A><BR>
<A HREF="edit.php?episode=<?php echo( $episode ); ?>&command=RevokeAuthor">Revoke Author's Edit Permissions</A><BR>
<?php
        }
      }
    }

    if ( $canEdit )
    {
      if ( $editCount > 0 )
      {

?>
<P>
<B>This episode has been edited <?php echo( $editCount ) ?> times.</B><BR>
<A HREF="list-edits.php?episode=<?php echo( $episode ); ?>">List Edits</A>
<?php

      }
    }

?>

</CENTER>

</BODY></HTML>

<?php

  }

?>
