<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002 - 2004  Extend-A-Story Development Team


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

  $episode = $_REQUEST[ "episode" ];
  $command = $_REQUEST[ "command" ];
  $lockKey = $_REQUEST[ "lockKey" ];

  $episode = ( int ) $episode;

  if ( $episode == 0 )
    $episode = 1;

  $error = "";
  $fatal = false;

  // Connect to the database.
  if ( empty( $error ) )
    connectToDatabase( $error, $fatal );

  if ( empty( $error ) )
    getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );

  if ( empty( $error ) )
  {
    $isWriteable = getStringValue( $error, $fatal, "IsWriteable" );
  }

  if ( $isWriteable == "N" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Episode Creation Disabled</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Episode Creation Disabled</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You are unable to perform advanced edit functions while episode creation is disabled.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  $permissionLevel = 0;

  if ( ( $userID != 0 ) && ( empty( $error ) ) )
  {
    $result = mysql_query( "select PermissionLevel, UserName from User where UserID = " . $userID );
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
        $userName        = $row[ 1 ];
      }
    }
  }

  if ( $permissionLevel < 2 )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Insufficient Permissions</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Insufficient Permissions</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You do not have permission to use the advanced editing features.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  $status = 0;

  $result = mysql_query( "select SchemeID, Status, LockKey from Episode where EpisodeID = " . $episode );
  if ( ! $result )
  {
    $error .= "Problem querying episode from database.<BR>";
    $fatal = true;
  }
  else
  {
    $row = mysql_fetch_row( $result );
    if ( ! $row )
    {
      $error .= "Problem fetching episode row from database.<BR>";
      $fatal = true;
    }
    else
    {
      $scheme         = $row[ 0 ];
      $status         = $row[ 1 ];
      $episodeLockKey = $row[ 2 ];
    }
  }

  if ( ( $command == "AddLink"       ) ||
       ( $command == "DeleteLink"    ) ||
       ( $command == "DeleteEpisode" ) ||
       ( $command == "RevokeAuthor"  ) )
  {
    if ( $status != 2 )
    {

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Episode <?php echo( $episode ); ?> Not Available For Editing</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Available For Editing</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You are trying to edit an episode that someone else is currently editing.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

      exit;
    }

    if ( empty( $error ) )
    {
      $lockKey = mt_rand( );

      $result = mysql_query( "update Episode set EditorSessionID = "  . $sessionID              .  ", " .
                                                "Status          = 3, " .
                                                "LockDate        = '" . date( "n/j/Y g:i:s A" ) . "', " .
                                                "LockKey         = "  . $lockKey                .   " " .
                                          "where EpisodeID       = "  . $episode );
      if ( ! $result )
      {
        $error .= "Problem updating episode record in database.<BR>";
        $fatal = true;
      }
    }
  }

  if ( ( $command == "AddLinkSave"            ) ||
       ( $command == "DeleteSelectedLink"     ) ||
       ( $command == "DeleteSelectedLinkSave" ) ||
       ( $command == "DeleteEpisodeSave"      ) ||
       ( $command == "RevokeAuthorSave"       ) )
  {
    if ( $status != 3 )
    {

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Episode <?php echo( $episode ); ?> Not Locked</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You are trying to edit an episode that has not been locked.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

      exit;
    }

    if ( $lockKey != $episodeLockKey )
    {

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Episode <?php echo( $episode ); ?> Not Locked by You</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked by You</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You are trying to edit an episode that has been locked, but not by you.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

      exit;
    }

    if ( empty( $error ) )
    {
      $result = mysql_query( "update Episode set EditorSessionID = "  . $sessionID              . ", " .
                                                "LockDate        = '" . date( "n/j/Y g:i:s A" ) . "' " .
                                          "where EpisodeID = " . $episode );
      if ( ! $result )
      {
        $error .= "Unable to update the edit lock on the episode.<BR>";
        $fatal = true;
      }
    }
  }

  $message = "";

  if ( $command == "AddLinkSave" )
  {
    $linkDescription = $_POST[ "description"   ];
    $linkEpisode     = $_POST[ "linkedEpisode" ];

    prepareParam( $linkDescription );

    $linkEpisode = ( int ) $linkEpisode;

    if ( empty( $linkDescription ) )
      $message .= "You must enter the link description.<BR>";

    if ( strlen( $linkDescription ) > 255 )
      $message .= "The link description cannot be longer then 255 characters.<BR>";

    if ( $linkEpisode != 0 )
    {
      $result = mysql_query( "select count( * ) from Link where SourceEpisodeID = " . $episode . " " .
                             "and TargetEpisodeID = " . $linkEpisode );
      if ( ! $result )
      {
        $error .= "Problem retrieving link count from the database.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $error .= "Problem fetching link count row from the database.<BR>";
          $fatal = true;
        }
        else
        {
          if ( $row[ 0 ] != 0 )
          {
            $message .= "There is already a backlink from this episode that leads to the specified episode.<BR>";
          }
        }
      }

      $result = mysql_query( "select IsLinkable from Episode where EpisodeID = " . $linkEpisode );
      if ( ! $result )
      {
        $error .= "Problem retrieving an episode from the database to determine if it is linkable.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $message .= "The back linked episode doesn't exist.<BR>";
        }
        else
        {
          if ( $row[ 0 ] != "Y" )
          {
            $message .= "The back linked episode is not linkable.<BR>";
          }
        }
      }
    }

    if ( empty( $message ) )
    {
      if ( empty( $error ) )
        createEpisodeEditLog( $error, $fatal, $episode, "New link added by " . $userName . "." );

      if ( $linkEpisode == 0 )
      {
        if ( empty( $error ) )
          $newEpisode = createEpisode( $error, $fatal, $episode, $scheme );

        if ( empty( $error ) )
          createLink( $error, $fatal, $episode, $newEpisode, $linkDescription, false );
      }
      else
      {
        if ( empty( $error ) )
          createLink( $error, $fatal, $episode, $linkEpisode, $linkDescription, true );
      }

      if ( empty( $error ) )
      {
        $result = mysql_query( "update Episode set EditorSessionID   = "  . $sessionID .  ", " .
                                                  "Status            = 2, "  .
                                                  "LockDate          = '', " .
                                                  "LockKey           = 0, "  .
                                                  "CreationTimestamp = now( ) " .
                                            "where EpisodeID         = " . $episode );
        if ( ! $result )
        {
          $error .= "Unable to unlock the episode record.<BR>";
          $fatal = true;
        }
      }

      $message = "Link Added";
      $command = "Done";
    }
    else
    {
      $message = "Problem adding link:<P>" . $message;
      $command = "AddLink";
    }
  }

  if ( ( $command == "DeleteSelectedLink"     ) ||
       ( $command == "DeleteSelectedLinkSave" ) )
  {
    $linkID = $_REQUEST[ "linkID" ];

    $result = mysql_query( "select SourceEpisodeID, IsCreated, IsBackLink, Description from Link where LinkID = " . $linkID );
    if ( ! $result )
    {
      $error .= "Problem querying databae for link information.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Unable to fetch link row from database.<BR>";
        $fatal = true;
      }
      else
      {
        if ( $row[ 0 ] != $episode )
        {
          $message .= "The specified link does not belong to this episode.<BR>";
        }

        if ( ( $row[ 1 ] == "Y" ) && ( $row[ 2 ] == "N" ) )
        {
          $message .= "The destination of this link has been created.<BR>";
        }
      }
    }

    if ( empty( $message ) )
    {
      $description = $row[ 3 ];
      $description = htmlentities( $description );
      $description = strtr( $description, getOptionTranslationTable( ) );
    }
    else
    {
      $message = "Problem deleting link:<P>" . $message;
      $command = "DeleteLink";
    }
  }

  if ( $command == "DeleteSelectedLinkSave" )
  {
    if ( empty( $error ) )
      createEpisodeEditLog( $error, $fatal, $episode, "Link deleted by " . $userName . "." );

    if ( empty( $error ) )
    {
      $result = mysql_query( "delete from Link where LinkID = " . $linkID );
      if ( ! $result )
      {
        $error .= "Unable to delete link from database.<BR>";
        $fatal = true;
      }
    }

    if ( empty( $error ) )
    {
      $result = mysql_query( "update Episode set EditorSessionID   = "  . $sessionID .  ", " .
                                                "Status            = 2, "  .
                                                "LockDate          = '', " .
                                                "LockKey           = 0, "  .
                                                "CreationTimestamp = now( ) " .
                                          "where EpisodeID         = " . $episode );
      if ( ! $result )
      {
        $error .= "Unable to unlock the episode record.<BR>";
        $fatal = true;
      }
    }

    $message = "Link Deleted";
    $command = "Done";
  }

  if ( $command == "DeleteLink" )
  {
    if ( empty( $error ) )
    {
      $links = mysql_query( "select LinkID, IsBackLink, Description from Link " .
                            "where SourceEpisodeID = " . $episode . " " .
                            "and ( IsCreated = 'N' or IsBackLink = 'Y' ) order by LinkID" );
      if ( ! $links )
      {
        $error .= "Problem retrieving links from database.<BR>";
        $fatal = true;
      }
    }
  }

  if ( ( $command == "DeleteEpisode" ) || ( $command == "DeleteEpisodeSave" ) )
  {
    $linkCount = 0;
    $backlinkCount = 0;

    if ( empty( $error ) )
    {
      $result = mysql_query( "select count( * ) from Link where SourceEpisodeID = " . $episode );
      if ( ! $result )
      {
        $error .= "Problem querying link count from the database.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $error .= "Problem fetching link count row from the database.<BR>";
          $fatal = true;
        }
        else
        {
          $linkCount = $row[ 0 ];
        }
      }
    }

    if ( empty( $error ) )
    {
      $backlinks = mysql_query( "select SourceEpisodeID from Link " .
                                 "where TargetEpisodeID = " . $episode . " " .
                                   "and IsBackLink = 'Y' " .
                              "order by SourceEpisodeID" );
      if ( ! $backlinks )
      {
        $error .= "Problem querying database for backlinks to this episode.<BR>";
        $fatal = true;
      }
      else
      {
        $backlinkCount = mysql_num_rows( $backlinks );
      }
    }
    $canDeleteEpisode = ( $linkCount == 0 ) && ( $backlinkCount == 0 );
  }

  if ( $command == "DeleteEpisodeSave" )
  {
    if ( $canDeleteEpisode )
    {
      if ( empty( $error ) )
        createEpisodeEditLog( $error, $fatal, $episode, "Episode deleted by " . $userName . "." );

      if ( empty( $error ) )
      {
        $result = mysql_query( "update Episode set AuthorSessionID   = 0, "   .
                                                  "EditorSessionID   = 0, "   .
                                                  "ImageID           = 0, "   .
                                                  "Status            = 0, "   .
                                                  "IsLinkable        = 'N', " .
                                                  "IsExtendable      = 'N', " .
                                                  "AuthorMailto      = 'N', " .
                                                  "AuthorNotify      = 'N', " .
                                                  "Title             = '-', " .
                                                  "Text              = '-', " .
                                                  "AuthorName        = '-', " .
                                                  "AuthorEmail       = '-', " .
                                                  "CreationDate      = '-', " .
                                                  "LockDate          = '-', " .
                                                  "LockKey           = 0, "   .
                                                  "CreationTimestamp = null " .
                                            "where EpisodeID         = "  . $episode );
        if ( ! $result )
        {
          $error .= "Problem deleting episode from database.<BR>";
          $fatal = true;
        }
      }

      if ( ! $error )
      {
        $result = mysql_query( "update Link set IsCreated = 'N' where TargetEpisodeID = " . $episode );
        if ( ! $result )
        {
          $error .= "Problem resetting link IsCreated status.<BR>";
          $fatal = true;
        }
      }

      $command = "Done";
      $message = "Episode Deleted";
    }
    else
    {
      $command = "DeleteEpisode";
    }
  }

  if ( $command == "RevokeAuthorSave" )
  {
    if ( empty( $error ) )
      createEpisodeEditLog( $error, $fatal, $episode, "Author's edit permission revoked by " . $userName . "." );

    if ( empty( $error ) )
    {
      $result = mysql_query( "update Episode set AuthorSessionID   = 0, " .
                                                "EditorSessionID   = " . $sessionID .  ", " .
                                                "Status            = 2, "  .
                                                "LockDate          = '', " .
                                                "LockKey           = 0, "  .
                                                "CreationTimestamp = now( ) " .
                                          "where EpisodeID         = " . $episode );
      if ( ! $result )
      {
        $error .= "Unable to update the episode record.<BR>";
        $fatal = true;
      }
    }

    $message = "Author's Edit Permission Revoked";
    $command = "Done";
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

  if ( $command == "Done" )
  {

?>

<HTML><HEAD>
<TITLE>Edit Completed</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Completed</H1>

<?php

  if ( ! empty( $message ) )
  {

?>
<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>
<?php

  }

?>

<TABLE WIDTH="500">
  <TR>
    <TD>
The edit operation you requested has been completed.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  if ( $command == "AddLink" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit - Add Link to Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit</H1>
<H2>Add Link to Episode <?php echo( $episode ); ?></H2>

<?php

    if ( ! empty( $message ) )
    {

?>
<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>
<?php

    }

?>

<FORM ACTION="edit.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command" VALUE="AddLinkSave">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">

Link Description:<BR>
<INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="description" VALUE="<?php echo( $linkDescription ); ?>">
<P>
Linked Episode:<BR>
(Fill this in to get a backlink. Leave it blank for a normal link.)<BR>
<INPUT TYPE="text" NAME="linkedEpisode" VALUE="<?php echo( $linkEpisode == 0 ? "" : $linkEpisode ); ?>">
<P>
<INPUT TYPE="submit" VALUE="Save">
</FORM>
<P>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel">
</FORM>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  if ( $command == "DeleteSelectedLink" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit - Delete Link from Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit</H1>
<H2>Delete Link from Episode <?php echo( $episode ); ?></H2>
<H3>Are you sure you want to delete the following link?</H3>
<H4><?php echo( $description ); ?></H4>

<FORM ACTION="edit.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="command" VALUE="DeleteSelectedLinkSave">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="hidden" NAME="linkID"  VALUE="<?php echo( $linkID ); ?>">
<INPUT TYPE="submit" VALUE="Yes">
</FORM>

<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel">
</FORM>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  if ( $command == "DeleteLink" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit - Delete Link from Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit</H1>
<H2>Delete Link from Episode <?php echo( $episode ); ?></H2>

<?php

    if ( ! empty( $message ) )
    {

?>
<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>
<?php

    }

?>

<TABLE WIDTH="500">
  <TR>
    <TD>
You may only delete links that are backlinks, or that lead to episodes that have not been created yet.
<P>
<?php

    if ( mysql_num_rows( $links ) > 0 )
    {

?>
Select a link to delete:
<P>
<OL>
<?php

      for ( $i = 0; $i < mysql_num_rows( $links ); $i++ )
      {
        $row = mysql_fetch_row( $links );

        $description = $row[ 2 ];
        $description = htmlentities( $description );
        $description = strtr( $description, getOptionTranslationTable( ) );

        if ( $row[ 1 ] == "Y" )
          $image = "images/blue.gif";
        else
          $image = "images/red.gif";

?>
<LI><IMG SRC="<?php echo( $image ); ?>"><A HREF="edit.php?episode=<?php echo( $episode ); ?>&command=DeleteSelectedLink&lockKey=<?php echo( $lockKey ); ?>&linkID=<?php echo( $row[ 0 ] );?>"><?php echo( $description ); ?></A></LI>
<?php

      }

?>
</OL>
<?php

    }
    else
    {

?>
There are no links from this episode that you can delete.
<?php

    }

?>
    </TD>
  </TR>
</TABLE>

<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel">
</FORM>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  if ( $command == "DeleteEpisode" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit - Delete Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit</H1>
<H2>Delete Episode <?php echo( $episode ); ?></H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
You may only delete an episode that has no links leading from it and no backlinks leading to it.
<?php

    if ( $linkCount > 0 )
    {

?>
<P>
This episode has links leading from it that must be deleted first.
<?php

    }

    if ( $backlinkCount > 0 )
    {

?>
<P>
This episode has backlinks leading to it from the following episodes that must be deleted first:
<UL>
<?php

      for ( $i = 0; $i < $backlinkCount; $i++ )
      {
        $row = mysql_fetch_row( $backlinks );

?>
<LI><A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>">Episode <?php echo( $row[ 0 ] ); ?></A></LI>
<?php

      }

?>
</UL>
<?php

    }

    if ( $canDeleteEpisode )
    {

?>
<P>
This episode can be deleted. Are you sure you want to delete it?
<?php

    }

?>
    </TD>
  </TR>
</TABLE>
<?php

    if ( $canDeleteEpisode )
    {

?>
<FORM ACTION="edit.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="command" VALUE="DeleteEpisodeSave">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Yes">
</FORM>
<?php

    }

?>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel">
</FORM>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

  if ( $command == "RevokeAuthor" )
  {

?>

<HTML><HEAD>
<TITLE>Advanced Edit - Revoke Author's Edit Permissions for Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit</H1>
<H2>Revoke Author's Edit Permissions for Episode <?php echo( $episode ); ?></H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
Revoking the author's edit permissions for this episode will prevent the
author from being able to edit the episode afterwords. Are you sure?
    </TD>
  </TR>
</TABLE>

<FORM ACTION="edit.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="command" VALUE="RevokeAuthorSave">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Yes">
</FORM>

<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel">
</FORM>

</CENTER>

</BODY></HTML>

<?php

    exit;
  }

?>

<HTML><HEAD>
<TITLE>Advanced Edit Error - Command Not Supported</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Advanced Edit Error</H1>
<H2>Command Not Supported</H2>

<TABLE WIDTH="500">
  <TR>
    <TD>
The command you selected is not supported.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
    </TD>
  </TR>
</TABLE>

</CENTER>

</BODY></HTML>
