<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2018 Jeffrey J. Weston <jjweston@gmail.com>


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

require(  __DIR__ . "/include/Extend-A-Story.php" );

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

Util::getSessionAndUserIDs( $sessionID, $userID );

$isWriteable = Util::getStringValue( "IsWriteable" );

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

$command = Util::getStringParam(     $_REQUEST, "command"    );
$episode = Util::getIntParam(        $_REQUEST, "episode"    );
$lockKey = Util::getIntParamDefault( $_REQUEST, "lockKey", 0 );

$permissionLevel = 0;

if ( $userID != 0 )
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT PermissionLevel, " .
                   "UserName " .
              "FROM User " .
             "WHERE UserID = :userID" );

    $dbStatement->bindParam( ":userID", $userID, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( ! $row )
    {
        throw new StoryException( "Unable to fetch user information row from database." );
    }

    $permissionLevel = $row[ 0 ];
    $userName        = $row[ 1 ];
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

$status = 0;

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT SchemeID, " .
               "Status, " .
               "LockKey " .
          "FROM Episode " .
         "WHERE EpisodeID = :episode" );

$dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( ! $row )
{
    throw new StoryException( "Problem fetching episode row from database." );
}

$scheme         = $row[ 0 ];
$status         = $row[ 1 ];
$episodeLockKey = $row[ 2 ];

if (( $command == "AddLink"       ) ||
    ( $command == "DeleteLink"    ) ||
    ( $command == "DeleteEpisode" ) ||
    ( $command == "RevokeAuthor"  ))
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

        exit;
    }

    $lockDate = date( "n/j/Y g:i:s A" );
    $lockKey = mt_rand();

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET EditorSessionID = :sessionID, " .
                   "Status          = 3, " .
                   "LockDate        = :lockDate, " .
                   "LockKey         = :lockKey " .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
    $dbStatement->bindParam( ":lockDate",  $lockDate,  PDO::PARAM_STR );
    $dbStatement->bindParam( ":lockKey",   $lockKey,   PDO::PARAM_INT );
    $dbStatement->bindParam( ":episode",   $episode,   PDO::PARAM_INT );

    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to lock the episode." );
    }
}

if (( $command == "AddLinkSave"            ) ||
    ( $command == "DeleteSelectedLink"     ) ||
    ( $command == "DeleteSelectedLinkSave" ) ||
    ( $command == "DeleteEpisodeSave"      ) ||
    ( $command == "RevokeAuthorSave"       ))
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

        exit;
    }

    $lockDate = date( "n/j/Y g:i:s A" );

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET EditorSessionID = :sessionID, " .
                   "LockDate        = :lockDate " .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
    $dbStatement->bindParam( ":lockDate",  $lockDate,  PDO::PARAM_STR );
    $dbStatement->bindParam( ":episode",   $episode,   PDO::PARAM_INT );

    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to update the lock on the episode." );
    }
}

$message = "";

$linkDescription = "";
$linkEpisode     = 0;

if ( $command == "AddLinkSave" )
{
    $linkDescription = Util::getStringParam(     $_POST, "description"      );
    $linkEpisode     = Util::getIntParamDefault( $_POST, "linkedEpisode", 0 );

    if ( empty( $linkDescription ))
    {
        $message .= "You must enter the link description.<BR>";
    }

    if ( strlen( $linkDescription ) > 255 )
    {
        $message .= "The link description cannot be longer then 255 characters.<BR>";
    }

    if ( Util::maximumWordLength( $linkDescription ) > 30 )
    {
        $message .=
                "The link description cannot contain a word with more than 30 characters.<BR>";
    }

    if ( $linkEpisode != 0 )
    {
        if ( $linkEpisode == $episode )
        {
            $message .= "The link cannot link back to the same episode you are editing.<BR>";
        }

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT COUNT( * ) " .
                  "FROM Link " .
                 "WHERE SourceEpisodeID = :episode " .
                   "AND TargetEpisodeID = :linkEpisode" );

        $dbStatement->bindParam( ":episode",     $episode,     PDO::PARAM_INT );
        $dbStatement->bindParam( ":linkEpisode", $linkEpisode, PDO::PARAM_INT );

        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( ! $row )
        {
            throw new StoryException( "Problem fetching link count row from the database." );
        }

        if ( $row[ 0 ] != 0 )
        {
            $message .= "There is already a back link from this episode that leads to the " .
                        "specified episode.<BR>";
        }

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT IsLinkable " .
                  "FROM Episode " .
                 "WHERE EpisodeID = :linkEpisode" );

        $dbStatement->bindParam( ":linkEpisode", $linkEpisode, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

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

    if ( empty( $message ))
    {
        Util::createEpisodeEditLog( $episode, "New link added by " . $userName . "." );

        if ( $linkEpisode == 0 )
        {
            $newEpisode = Util::createEpisode( $episode, $scheme );
            Util::createLink( $episode, $newEpisode, $linkDescription, false );
        }
        else
        {
            Util::createLink( $episode, $linkEpisode, $linkDescription, true );
        }

        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Episode " .
                   "SET EditorSessionID   = :sessionID, " .
                       "Status            = 2, " .
                       "LockDate          = '', " .
                       "LockKey           = 0, " .
                       "CreationTimestamp = now() " .
                 "WHERE EpisodeID = :episode" );

        $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
        $dbStatement->bindParam( ":episode",   $episode,   PDO::PARAM_INT );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to unlock the episode record." );
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

$linkID = 0;

if (( $command == "DeleteSelectedLink"     ) ||
    ( $command == "DeleteSelectedLinkSave" ))
{
    $linkID = Util::getIntParam( $_REQUEST, "linkID" );

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT SourceEpisodeID, " .
                   "IsCreated, " .
                   "IsBackLink, " .
                   "Description " .
              "FROM Link " .
             "WHERE LinkID = :linkID" );

    $dbStatement->bindParam( ":linkID", $linkID, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( ! $row )
    {
        throw new StoryException( "Unable to fetch link row from database." );
    }

    if ( $row[ 0 ] != $episode )
    {
        $message .= "The specified link does not belong to this episode.<BR>";
    }

    if (( $row[ 1 ] == "Y" ) && ( $row[ 2 ] == "N" ))
    {
        $message .= "The destination of this link has been created.<BR>";
    }

    if ( empty( $message ))
    {
        $description = $row[ 3 ];
        $description = htmlentities( $description );
        $description = strtr( $description, Util::getOptionTranslation() );
    }
    else
    {
        $message = "Problem deleting link:<P>" . $message;
        $command = "DeleteLink";
    }
}

if ( $command == "DeleteSelectedLinkSave" )
{
    Util::createEpisodeEditLog( $episode, "Link deleted by " . $userName . "." );

    $dbStatement = Util::getDbConnection()->prepare( "DELETE FROM Link WHERE LinkID = :linkID" );
    $dbStatement->bindParam( ":linkID", $linkID, PDO::PARAM_INT );
    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to delete link from database." );
    }

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET EditorSessionID   = :sessionID, " .
                   "Status            = 2, " .
                   "LockDate          = '', " .
                   "LockKey           = 0, " .
                   "CreationTimestamp = now() " .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
    $dbStatement->bindParam( ":episode",   $episode,   PDO::PARAM_INT );
    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to unlock the episode record." );
    }

    $message = "Link Deleted";
    $command = "Done";
}

if ( $command == "DeleteLink" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT LinkID, " .
                   "IsBackLink, " .
                   "Description " .
              "FROM Link " .
             "WHERE SourceEpisodeID = :episode " .
               "AND ( IsCreated = 'N' OR IsBackLink = 'Y' ) " .
             "ORDER BY LinkID" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $links = $dbStatement->fetchAll( PDO::FETCH_NUM );
}

if (( $command == "DeleteEpisode" ) || ( $command == "DeleteEpisodeSave" ))
{
    $linkCount = 0;
    $backlinkCount = 0;

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT COUNT( * ) " .
              "FROM Link " .
             "WHERE SourceEpisodeID = :episode" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( ! $row )
    {
        throw new StoryException( "Problem fetching link count row from the database." );
    }

    $linkCount = $row[ 0 ];

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT SourceEpisodeID " .
              "FROM Link " .
             "WHERE TargetEpisodeID = :episode " .
               "AND IsBackLink = 'Y' " .
          "ORDER BY SourceEpisodeID" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $backlinks = $dbStatement->fetchAll( PDO::FETCH_NUM );

    $backlinkCount = count( $backlinks );
    $canDeleteEpisode = ( $linkCount == 0 ) && ( $backlinkCount == 0 );
}

if ( $command == "DeleteEpisodeSave" )
{
    if ( $canDeleteEpisode )
    {
        Util::createEpisodeEditLog( $episode, "Episode deleted by " . $userName . "." );

        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Episode " .
                   "SET AuthorSessionID   = 0, "   .
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
                       "CreationTimestamp = NULL " .
                 "WHERE EpisodeID = :episode" );

        $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Problem deleting episode from database." );
        }

        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Link " .
                   "SET IsCreated = 'N' " .
                 "WHERE TargetEpisodeID = :episode" );

        $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Problem resetting link IsCreated status." );
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
    Util::createEpisodeEditLog(
            $episode, "Author's edit permission revoked by " . $userName . "." );

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET AuthorSessionID   = 0, " .
                   "EditorSessionID   = :sessionID, " .
                   "Status            = 2, " .
                   "LockDate          = '', " .
                   "LockKey           = 0, " .
                   "CreationTimestamp = now() " .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
    $dbStatement->bindParam( ":episode",   $episode,   PDO::PARAM_INT );
    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to update the episode record." );
    }

    $message = "Author's Edit Permission Revoked";
    $command = "Done";
}

if ( $command == "Done" )
{

?>

<HTML><HEAD>
<TITLE>Edit Completed</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Completed</H1>

<?php

if ( ! empty( $message ))
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

    if ( ! empty( $message ))
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
<INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="description"
       VALUE="<?php echo( $linkDescription ); ?>">
<P>
Linked Episode:<BR>
(Fill this in to get a back link. Leave it blank for a normal link.)<BR>
<INPUT TYPE="text" NAME="linkedEpisode"
       VALUE="<?php echo( $linkEpisode == 0 ? "" : $linkEpisode ); ?>">
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

    if ( ! empty( $message ))
    {

?>

<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>

<?php

    }

?>

<TABLE WIDTH="500">
    <TR>
        <TD>
You may only delete links that are back links, or that lead to episodes that have not been created
yet.
<P>

<?php

    if ( count( $links ) > 0 )
    {

?>

Select a link to delete:
<P>
<OL>

<?php

        for ( $i = 0; $i < count( $links ); $i++ )
        {
            $row = $links[ $i ];

            $description = $row[ 2 ];
            $description = htmlentities( $description );
            $description = strtr( $description, Util::getOptionTranslation() );

            if ( $row[ 1 ] == "Y" )
            {
                $image = "images/blue.gif";
            }
            else
            {
                $image = "images/red.gif";
            }

            $url = "edit.php?episode=" . $episode . "&command=DeleteSelectedLink&lockKey=" .
                   $lockKey . "&linkID=" . $row[ 0 ];

?>

<LI>
    <IMG SRC="<?php echo( $image ); ?>">
    <A HREF="<?php echo( $url ); ?>">
        <?php echo( $description ); ?>
    </A>
</LI>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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
You may only delete an episode that has no links leading from it and no back links leading to it.

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
This episode has back links leading to it from the following episodes that must be deleted first:
<UL>

<?php

        for ( $i = 0; $i < $backlinkCount; $i++ )
        {
            $row = $backlinks[ $i ];

?>

<LI>
    <A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>">Episode <?php echo( $row[ 0 ] ); ?></A>
</LI>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if ( $command == "RevokeAuthor" )
{

?>

<HTML><HEAD>
<TITLE>
    Advanced Edit - Revoke Author's Edit Permissions for Episode <?php echo( $episode ); ?>
</TITLE>
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
