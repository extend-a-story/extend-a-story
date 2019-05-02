<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2019 Jeffrey J. Weston <jjweston@gmail.com>


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

$command         = Util::getStringParam(        $_REQUEST, "command"             );
$episode         = Util::getIntParam(           $_REQUEST, "episode"             );
$lockKey         = Util::getIntParamDefault(    $_POST,    "lockKey",         0  );
$commandModifier = Util::getStringParamDefault( $_POST,    "commandModifier", "" );
$extendedLink    = Util::getStringParamDefault( $_POST,    "extendedLink",    "" );
$title           = Util::getStringParamDefault( $_POST,    "title",           "" );
$text            = Util::getStringParamDefault( $_POST,    "text",            "" );
$scheme          = Util::getIntParamDefault(    $_POST,    "scheme",          1  );
$authorName      = Util::getStringParamDefault( $_POST,    "authorName",      "" );
$authorEmail     = Util::getStringParamDefault( $_POST,    "authorEmail",     "" );
$mailto          = Util::getIntParamDefault(    $_POST,    "mailto",          0  );
$notify          = Util::getIntParamDefault(    $_POST,    "notify",          0  );
$linkable        = Util::getIntParamDefault(    $_POST,    "linkable",        0  );
$extendable      = Util::getIntParamDefault(    $_POST,    "extendable",      0  );

$linkCount = 0;

$warning = "";

$createdEpisode = 0;

$command = $commandModifier . $command;

// *** Available Commands ***
//
// Lock          - Lock the episode for creation.
// Preview       - Preview how the episode looks before saving.
// Save          - Save the episode. May do a preview instead if errors are detected.
// Extend        - Create a new episode as a new option from an already written episode.
// ExtendPreview - Preview an extension to an already written episode.
// ExtendSave    - Save an extension to an already written episode.
// Edit          - Lock the episode for editing.
// EditPreview   - Preview how the edited episode looks before saving.
// EditSave      - Save the edits to the episode.

if (( $command != "Lock"          ) &&
    ( $command != "Preview"       ) &&
    ( $command != "Save"          ) &&
    ( $command != "Extend"        ) &&
    ( $command != "ExtendPreview" ) &&
    ( $command != "ExtendSave"    ) &&
    ( $command != "Edit"          ) &&
    ( $command != "EditPreview"   ) &&
    ( $command != "EditSave"      ))
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Command Not Supported</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
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

<?php

    exit;
}

$extending = ( $command == "Extend"        ) ||
             ( $command == "ExtendPreview" ) ||
             ( $command == "ExtendSave"    );

$editing = ( $command == "Edit"        ) ||
           ( $command == "EditPreview" ) ||
           ( $command == "EditSave"    );

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName   = Util::getStringValue( "StoryName"   );
$siteName    = Util::getStringValue( "SiteName"    );
$storyHome   = Util::getStringValue( "StoryHome"   );
$siteHome    = Util::getStringValue( "SiteHome"    );
$adminEmail  = Util::getStringValue( "AdminEmail"  );
$maxLinks    = Util::getIntValue(    "MaxLinks"    );
$countDate   = Util::getStringValue( "CountDate"   );
$countValue  = Util::getIntValue(    "CountValue"  );
$isWriteable = Util::getStringValue( "IsWriteable" );

if ( $isWriteable == "N" )
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode Creation Disabled</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode Creation Disabled</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You are unable to create episodes while episode creation is disabled.
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

if (( $command == "Lock" ) && ( $episode != 1 ))
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT COUNT( * ) " .
              "FROM Link " .
             "WHERE TargetEpisodeID = :episode" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException( "Unable to fetch link count row from the database." );
    }

    if ( $row[ 0 ] == 0 )
    {

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode is an Orphan</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode is an Orphan</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
The episode you are trying to create is an orphan (has no links to it) and cannot be created.
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
}

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT SchemeID, " .
               "SchemeName " .
          "FROM Scheme " .
         "ORDER BY SchemeID" );

$dbStatement->execute();
$schemeList = $dbStatement->fetchAll( PDO::FETCH_NUM );

if ( $command == "Edit" )
{
    $dbStatement = Util::getDbConnection()->prepare(
        "SELECT Parent, "       .
               "SchemeID, "     .
               "Status, "       .
               "IsLinkable, "   .
               "IsExtendable, " .
               "AuthorMailto, " .
               "AuthorNotify, " .
               "Title, "        .
               "Text, "         .
               "AuthorName, "   .
               "AuthorEmail, "  .
               "CreationDate, " .
               "LockKey "       .
          "FROM Episode "       .
         "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException(
                "Problem fetching episode row for editing from the database." );
    }

    $parent          = $row[ 0  ];
    $scheme          = $row[ 1  ];
    $status          = $row[ 2  ];
    $linkable        = $row[ 3  ];
    $extendable      = $row[ 4  ];
    $mailto          = $row[ 5  ];
    $notify          = $row[ 6  ];
    $title           = $row[ 7  ];
    $text            = $row[ 8  ];
    $authorName      = $row[ 9  ];
    $authorEmail     = $row[ 10 ];
    $creationDate    = $row[ 11 ];
    $episodeLockKey  = $row[ 12 ];

    $linkable   = ( $linkable   == "Y" ? 1 : 0 );
    $extendable = ( $extendable == "Y" ? 1 : 0 );
    $mailto     = ( $mailto     == "Y" ? 1 : 0 );
    $notify     = ( $notify     == "Y" ? 1 : 0 );
}
else
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT Parent, "       .
                   "SchemeID, "     .
                   "Status, "       .
                   "IsExtendable, " .
                   "CreationDate, " .
                   "LockKey "       .
              "FROM Episode "       .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException( "Problem fetching episode row from the database." );
    }

    $parent         = $row[ 0 ];
    $schemeID       = $row[ 1 ];
    $status         = $row[ 2 ];
    $isExtendable   = $row[ 3 ];
    $creationDate   = $row[ 4 ];
    $episodeLockKey = $row[ 5 ];

    if (( $command == "Lock" ) || ( $command == "Extend" ))
    {
        $scheme = $schemeID;
    }
}

$canEdit = Util::canEditEpisode( $sessionID, $userID, $episode );

// verify that the selected scheme is in the database
$dbStatement = Util::getDbConnection()->prepare(
    "SELECT COUNT( * ) " .
      "FROM Scheme " .
     "WHERE SchemeID = :scheme" );

$dbStatement->bindParam( ":scheme", $scheme, PDO::PARAM_INT );
$dbStatement->execute();
$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Unable to fetch scheme count row from the database." );
}

if ( $row[ 0 ] == 0 )
{
    throw new StoryException( "The specified scheme does not exist." );
}

if ( $mailto == 1 )
{
    $mailtoChecked = " CHECKED";
}
else
{
    $mailtoChecked = "";
}

if ( $notify == 1 )
{
    $notifyChecked = " CHECKED";
}
else
{
    $notifyChecked = "";
}

if ( $linkable == 1 )
{
    $linkableChecked = " CHECKED";
}
else
{
    $linkableChecked = "";
}

if ( $extendable == 1 )
{
    $extendableChecked = " CHECKED";
}
else
{
    $extendableChecked = "";
}

if (( $command == "Lock" ) &&
    ( $status != 0       ))
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode <?php echo( $episode ); ?> Not Available For Creation</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Available For Creation</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that already exists, or that someone is
currently working on. Wait a few moments and try again.
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if (( $command == "Edit" ) &&
    (( $status != 2     ) ||
     ( !$canEdit        )))
{

?>

<HTML><HEAD>
<TITLE>Edit Error - Episode <?php echo( $episode ); ?> Not Available For Editing</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Available For Editing</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You are trying to edit an episode that someone else is currently editing
or you don't have permission to edit this episode.
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

if ((( $command == "Preview" ) ||
     ( $command == "Save"    )) &&
    ( $status != 1            ))
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode <?php echo( $episode ); ?> Not Locked</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that is not currently locked. You must first
obtain a lock on this episode and then you may proceed with creating it.
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if ((( $command == "EditPreview" ) ||
     ( $command == "EditSave"    )) &&
    ( $status != 3                ))
{

?>

<HTML><HEAD>
<TITLE>Edit Error - Episode <?php echo( $episode ); ?> Not Locked</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that is not currently locked. You must first
obtain a lock on this episode and then you may proceed with editing it.
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

if ((( $command == "Preview"      ) ||
     ( $command == "Save"         )) &&
    ( $lockKey != $episodeLockKey  ))
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode <?php echo( $episode ); ?> Not Locked by You</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked by You</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that is locked, but not by you. If you believe
that this episode is abandoned, first wait for it to time out, obtain a lock
on this episode, and then you may proceed with creating it.
<P>
<A HREF="read.php?episode=<?php echo( $parent ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if ((( $command == "EditPreview"  ) ||
     ( $command == "EditSave"     )) &&
    ( $lockKey != $episodeLockKey  ))
{

?>

<HTML><HEAD>
<TITLE>Edit Error - Episode <?php echo( $episode ); ?> Not Locked by You</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Edit Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Locked by You</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that is locked, but not by you. If you believe
that this episode is abandoned, first wait for it to time out, obtain a lock
on this episode, and then you may proceed with editing it.
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

if ((( $command == "Extend"        ) ||
     ( $command == "ExtendPreview" ) ||
     ( $command == "ExtendSave"    )) &&
    ( $isExtendable != "Y"          ))
{

?>

<HTML><HEAD>
<TITLE>Creation Error - Episode <?php echo( $episode ); ?> Not Extendable</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Extendable</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You are trying to extend an episode that is not extendable.
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

if ( $extending )
{
    if ( empty( $extendedLink ))
    {
        $warning .= "You must enter the description for the new option.<BR>";
    }

    if ( strlen( $extendedLink ) > 255 )
    {
        $warning .= "The description for the new option cannot be longer than 255 characters." .
                    "<BR>";
    }

    if ( Util::maximumWordLength( $extendedLink ) > 30 )
    {
        $warning .= "The description for the new option cannot contain a word with more than " .
                    "30 characters.<BR>";
    }
}

if ( empty( $title ))
{
    $warning .= "You must enter a title.<BR>";
}

if ( empty( $text ))
{
    $warning .= "You must enter the episode description.<BR>";
}

if ( strlen( $title ) > 255 )
{
    $warning .= "The title cannot be longer than 255 characters.<BR>";
}

if ( strlen( $text ) > 65535 )
{
    $warning .= "The episode description cannot be longer than 65535 characters. " .
                "(Current size: " . strlen( $text ) . ")<BR>";
}

if ( strlen( $authorName ) > 255 )
{
    $warning .= "Your signature cannot be longer than 255 characters.<BR>";
}

if ( strlen( $authorEmail ) > 255 )
{
    $warning .= "Your email address cannot be longer than 255 characters.<BR>";
}

if ( Util::maximumWordLength( $title ) > 30 )
{
    $warning .= "The title cannot contain a word with more than 30 characters in it.<BR>";
}

if ( Util::maximumWordLength( $text ) > 30 )
{
    $warning .= "The episode description cannot contain a word with more than 30 characters " .
                "in it.<BR>";
}

if ( Util::maximumWordLength( $authorName ) > 30 )
{
    $warning .= "The author name cannot contain a word with more than 30 characters in it.<BR>";
}

if ( $mailto == 1 )
{
    if ( empty( $authorName ))
    {
        $warning .= "You must sign the episode to turn your signature into a mailto link to " .
                    "your email address.<BR>";
    }

    if ( empty( $authorEmail ))
    {
        $warning .= "You must provide an email address to turn your signature into a mailto " .
                    "link to your email address.<BR>";
    }
}

if ( $notify == 1 )
{
    if ( empty( $authorEmail ))
    {
        $warning .= "You must provide an email address in order to be notified when this " .
                    "episode is extended.<BR>";
    }
}

$linkFound = false;

if ( $editing )
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT LinkID, " .
                   "TargetEpisodeID, " .
                   "IsBacklink, " .
                   "Description " .
              "FROM Link " .
             "WHERE SourceEpisodeID = :episode " .
             "ORDER BY LinkID" );

    $dbStatement->bindParam( ":episode", $episode, PDO::PARAM_INT );
    $dbStatement->execute();
    $rows = $dbStatement->fetchAll( PDO::FETCH_NUM );

    $linkCount = count( $rows );

    for ( $i = 0; $i < $linkCount; $i++ )
    {
        $row = $rows[ $i ];

        $var1 = "linkID"          . $i;
        $var2 = "targetEpisodeID" . $i;
        $var3 = "isBackLink"      . $i;
        $var4 = "option"          . $i;
        $var5 = "backlink"        . $i;

        $$var1 = $row[ 0 ];
        $$var2 = $row[ 1 ];
        $$var3 = $row[ 2 ];

        // if we are previewing or saving, read the option description from the form,
        // otherwise read it from the database
        $$var4 = ((( $command == "EditPreview" ) || ( $command == "EditSave" )) ?
                 Util::getStringParam( $_POST, $var4 ) : $row[ 3 ] );

        // if we are previewing or saving, read the backlinked episode from the form,
        // otherwise read it from the database
        $$var5 = ((( $command == "EditPreview" ) || ( $command == "EditSave" )) ?
                 Util::getIntParamDefault( $_POST, $var5, 0 ) : $$var2 );
    }
}
else
{
    $linkCount = $maxLinks;

    for ( $i = 0; $i < $linkCount; $i++ )
    {
        $var1 = "linkID"          . $i;
        $var2 = "targetEpisodeID" . $i;
        $var3 = "isBackLink"      . $i;
        $var4 = "option"          . $i;
        $var5 = "backlink"        . $i;

        $$var1 = 0;
        $$var2 = 0;
        $$var3 = "N";
        $$var4 = "";
        $$var5 = 0;

        $$var4 = Util::getStringParamDefault( $_POST, $var4, "" );
        $$var5 = Util::getIntParamDefault(    $_POST, $var5, 0  );
    }
}

for ( $i = 0; $i < $linkCount; $i++ )
{
    $var1 = "option"     . $i;
    $var2 = "backlink"   . $i;
    $var3 = "isBackLink" . $i;

    if ( !empty( $$var1 ))
    {
        $linkFound = true;
    }

    if (( $editing ) && ( empty( $$var1 )))
    {
        $warning .= "You must provide a description for option " . ( $i + 1 ) . ".<BR>";
    }

    if ( strlen( $$var1 ) > 255 )
    {
        $warning .= "The description for option " . ( $i + 1 ) .
                    " cannot be longer than 255 characters.<BR>";
    }

    if ( Util::maximumWordLength( $$var1 ) > 30 )
    {
        $warning .= "The description for option " . ( $i + 1 ) .
                    " cannot contain a word with more than 30 characters in it.<BR>";
    }

    if ((( $$var2 != 0 ) && ( !$editing )) || ( $$var3 == "Y" ))
    {
        if (( $editing ) && ( $$var2 == $episode ))
        {
            $warning .= "Option " . ( $i + 1 ) .
                        " is back linked to the same episode you are editing.<BR>";
        }

        if (( empty( $$var1 )) && ( !$editing ))
        {
            $warning .= "Option " . ( $i + 1 ) . " is back linked, but has no description.<BR>";
        }

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT IsLinkable " .
                  "FROM Episode " .
                 "WHERE EpisodeID = :episodeID" );

        $dbStatement->bindParam( ":episodeID", $$var2, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( !$row )
        {
            $warning .= "The back linked episode for option " . ( $i + 1 ) .
                        " doesn't exist.<BR>";
        }
        else
        {
            if ( $row[ 0 ] != "Y" )
            {
                $warning .= "The back linked episode for option " . ( $i + 1 ) .
                            " is not linkable.<BR>";
            }
        }
    }
}

if (( !$linkFound ) && ( !$editing ))
{
    $warning .= "You must enter in at least one option.<BR>";
}

$sameBackLink = false;

for ( $i = 0; $i < $linkCount; $i++ )
{
    $var1 = "backlink" . $i;

    if ( $$var1 != 0 )
    {
        for ( $j = $i + 1; $j < $linkCount; $j++ )
        {
            $var2 = "backlink" . $j;

            if ( $$var1 == $$var2 )
            {
                $sameBackLink = true;
            }
        }
    }
}

if ( $sameBackLink )
{
    $warning .= "More than one option back links to the same episode.<BR>";
}

if ( !empty( $warning ))
{
    if ( $command == "Save" )
    {
        $command = "Preview";
    }

    if ( $command == "ExtendSave" )
    {
        $command = "ExtendPreview";
    }

    if ( $command == "EditSave" )
    {
        $command = "EditPreview";
    }
}

if ( $command == "Lock" )
{
    $titleString = "Creating Episode " . $episode;
    $bodyString = "You have now locked episode " . $episode . " for creation.";
}
else if ( $command == "Preview" )
{
    $titleString = "Previewing Episode " . $episode;
    $bodyString = "You are now previewing episode " . $episode . ".";
}
else if ( $command == "Extend" )
{
    $titleString = "Extending Episode " . $episode;
    $bodyString = "You are now extending episode " . $episode . ".";
}
else if ( $command == "ExtendPreview" )
{
    $titleString = "Previewing Episode " . $episode . " Extension";
    $bodyString = "You are now previewing your extension to episode " . $episode . ".";
}
else if ( $command == "Edit" )
{
    $titleString = "Editing Episode " . $episode;
    $bodyString  = "You have now locked episode " . $episode . " for editing.";
}
else if ( $command == "EditPreview" )
{
    $titleString = "Previewing Edited Episode " . $episode;
    $bodyString = "You are now previewing your changes to episode " . $episode . ".";
}

if ( $command == "Lock" )
{
    $lockDate = date( "n/j/Y g:i:s A" );
    $lockKey = mt_rand();

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET AuthorSessionID = :sessionID, " .
                   "Status          = 1, " .
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

if ( $command == "Preview" )
{
    $lockDate = date( "n/j/Y g:i:s A" );

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET AuthorSessionID = :sessionID, " .
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

if ( $command == "Edit" )
{
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
        throw new StoryException( "Unable to lock the episode for editing." );
    }
}

if ( $command == "EditPreview" )
{
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
        throw new StoryException( "Unable to update the edit lock on the episode." );
    }
}

if (( $command == "Save" ) || ( $command == "ExtendSave" ))
{
    if ( $extending )
    {
        $episodeToUpdate = Util::createEpisode( $episode, $scheme );
        Util::createLink( $episode, $episodeToUpdate, $extendedLink, false );
        $parentToUpdate = $episode;
        $createdEpisode = $episodeToUpdate;
    }
    else
    {
        $episodeToUpdate = $episode;
        $parentToUpdate = $parent;
    }

    $linkableValue   = ( $linkable   == 1 ? "Y" : "N" );
    $extendableValue = ( $extendable == 1 ? "Y" : "N" );
    $mailtoValue     = ( $mailto     == 1 ? "Y" : "N" );
    $notifyValue     = ( $notify     == 1 ? "Y" : "N" );
    $creationDate    = date( "n/j/Y g:i:s A" );

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET AuthorSessionID   = :sessionID, " .
                   "SchemeID          = :scheme, " .
                   "Status            = 2, " .
                   "IsLinkable        = :linkableValue, " .
                   "IsExtendable      = :extendableValue, " .
                   "AuthorMailto      = :mailtoValue, " .
                   "AuthorNotify      = :notifyValue, " .
                   "Title             = :title, " .
                   "Text              = :text, " .
                   "AuthorName        = :authorName, " .
                   "AuthorEmail       = :authorEmail, " .
                   "CreationDate      = :creationDate, " .
                   "LockDate          = '', " .
                   "LockKey           = 0, " .
                   "CreationTimestamp = now() " .
             "WHERE EpisodeID = :episodeToUpdate" );

    $dbStatement->bindParam( ":sessionID",       $sessionID,       PDO::PARAM_INT );
    $dbStatement->bindParam( ":scheme",          $scheme,          PDO::PARAM_INT );
    $dbStatement->bindParam( ":linkableValue",   $linkableValue,   PDO::PARAM_STR );
    $dbStatement->bindParam( ":extendableValue", $extendableValue, PDO::PARAM_STR );
    $dbStatement->bindParam( ":mailtoValue",     $mailtoValue,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":notifyValue",     $notifyValue,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":title",           $title,           PDO::PARAM_STR );
    $dbStatement->bindParam( ":text",            $text,            PDO::PARAM_STR );
    $dbStatement->bindParam( ":authorName",      $authorName,      PDO::PARAM_STR );
    $dbStatement->bindParam( ":authorEmail",     $authorEmail,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":creationDate",    $creationDate,    PDO::PARAM_STR );
    $dbStatement->bindParam( ":episodeToUpdate", $episodeToUpdate, PDO::PARAM_INT );

    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to update the episode record." );
    }

    // if the episode is not the first episode, mark the link leading to it as created
    if ( $episodeToUpdate != 1 )
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Link " .
                   "SET IsCreated = 'Y' " .
                 "WHERE SourceEpisodeID = :parentToUpdate " .
                   "AND TargetEpisodeID = :episodeToUpdate" );

        $dbStatement->bindParam( ":parentToUpdate",  $parentToUpdate,  PDO::PARAM_INT );
        $dbStatement->bindParam( ":episodeToUpdate", $episodeToUpdate, PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to update the link record." );
        }
    }

    for ( $i = 0; $i < $linkCount; $i++ )
    {
        $var1 = "option"   . $i;
        $var2 = "backlink" . $i;

        if ( !empty( $$var1 ))
        {
            if ( $$var2 != 0 )
            {
                Util::createLink( $episodeToUpdate, $$var2, $$var1, true );
            }
            else
            {
                $newEpisode = Util::createEpisode( $episodeToUpdate, $scheme );
                Util::createLink( $episodeToUpdate, $newEpisode, $$var1, false );
            }
        }
    }

    if ( $adminEmail != "-" )
    {
        // send a notification email to the administrator
        Util::extensionNotification( $adminEmail, $parentToUpdate, $episodeToUpdate, $authorName );

        // send a notification email (if applicable) to the author of the parent episode
        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT AuthorNotify, " .
                       "AuthorEmail " .
                  "FROM Episode " .
                 "WHERE EpisodeID = :parentToUpdate" );

        $dbStatement->bindParam( ":parentToUpdate", $parentToUpdate, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( !$row )
        {
            throw new StoryException(
                    "Problem fetching parent episode row from the database." );
        }

        $tempAuthorNotify = $row[ 0 ];
        $tempAuthorEmail  = $row[ 1 ];

        if ( $tempAuthorNotify == "Y" )
        {
            Util::extensionNotification( $tempAuthorEmail, $parentToUpdate,
                                         $episodeToUpdate, $authorName );
        }
    }
}

if ( $command == "EditSave" )
{
    // if the editor is a user, look up their name for the edit log
    if ( $userID != 0 )
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT UserName FROM User WHERE UserID = :userID" );

        $dbStatement->bindParam( ":userID", $userID, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( !$row )
        {
            throw new StoryException( "Unable to fetch user row from the database." );
        }

        $userName = $row[ 0 ];
    }
    else
    {
        $userName = "the author";
    }

    // save the previous episode into the edit log
    Util::createEpisodeEditLog( $episode, "Edited by " . $userName . "." );

    $linkableValue   = ( $linkable   == 1 ? "Y" : "N" );
    $extendableValue = ( $extendable == 1 ? "Y" : "N" );
    $mailtoValue     = ( $mailto     == 1 ? "Y" : "N" );
    $notifyValue     = ( $notify     == 1 ? "Y" : "N" );

    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Episode " .
               "SET EditorSessionID   = :sessionID, " .
                   "SchemeID          = :scheme, " .
                   "Status            = 2, " .
                   "IsLinkable        = :linkableValue, " .
                   "IsExtendable      = :extendableValue, " .
                   "AuthorMailto      = :mailtoValue, " .
                   "AuthorNotify      = :notifyValue, " .
                   "Title             = :title, " .
                   "Text              = :text, " .
                   "AuthorName        = :authorName, " .
                   "AuthorEmail       = :authorEmail, " .
                   "LockDate          = '', " .
                   "LockKey           = 0, " .
                   "CreationTimestamp = now() " .
             "WHERE EpisodeID = :episode" );

    $dbStatement->bindParam( ":sessionID",       $sessionID,       PDO::PARAM_INT );
    $dbStatement->bindParam( ":scheme",          $scheme,          PDO::PARAM_INT );
    $dbStatement->bindParam( ":linkableValue",   $linkableValue,   PDO::PARAM_STR );
    $dbStatement->bindParam( ":extendableValue", $extendableValue, PDO::PARAM_STR );
    $dbStatement->bindParam( ":mailtoValue",     $mailtoValue,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":notifyValue",     $notifyValue,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":title",           $title,           PDO::PARAM_STR );
    $dbStatement->bindParam( ":text",            $text,            PDO::PARAM_STR );
    $dbStatement->bindParam( ":authorName",      $authorName,      PDO::PARAM_STR );
    $dbStatement->bindParam( ":authorEmail",     $authorEmail,     PDO::PARAM_STR );
    $dbStatement->bindParam( ":episode",         $episode,         PDO::PARAM_INT );

    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to update the episode record for editing." );
    }

    for ( $i = 0; $i < $linkCount; $i++ )
    {
        $var1 = "linkID"     . $i;
        $var2 = "isBackLink" . $i;
        $var3 = "option"     . $i;
        $var4 = "backlink"   . $i;

        $dbStatement;

        if ( $$var2 == "Y" )
        {
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE Link " .
                       "SET TargetEpisodeID = :backlink, " .
                           "Description     = :option " .
                     "WHERE LinkID = :linkID" );

            $dbStatement->bindParam( ":backlink", $$var4, PDO::PARAM_INT );
        }
        else
        {
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE Link " .
                       "SET Description = :option " .
                     "WHERE LinkID = :linkID" );
        }

        $dbStatement->bindParam( ":option", $$var3, PDO::PARAM_STR );
        $dbStatement->bindParam( ":linkID", $$var1, PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to update the link record for editing." );
        }
    }
}

if ( $command == "Save" )
{

?>

<HTML><HEAD>
<TITLE>Finished Creating Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Finished Creating Episode <?php echo( $episode ); ?></H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
Episode <?php echo( $episode ); ?> has been created and is ready for
entry. Thank you for your addition to the story.
        </TD>
    </TR>
</TABLE>

<A HREF="read.php?episode=<?php echo( $episode ); ?>">Enter Episode <?php echo( $episode ); ?></A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if ( $command == "ExtendSave" )
{

?>

<HTML><HEAD>
<TITLE>Finished Extending Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Finished Extending Episode <?php echo( $episode ); ?></H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
Episode <?php echo( $createdEpisode ); ?> (extension of episode <?php echo( $episode ); ?>)
has been created and is ready for entry. Thank you for your addition to the story.
        </TD>
    </TR>
</TABLE>

<A HREF="read.php?episode=<?php echo( $createdEpisode ); ?>">Enter Episode <?php echo( $createdEpisode ); ?></A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

if ( $command == "EditSave" )
{

?>

<HTML><HEAD>
<TITLE>Finished Editing Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Finished Editing Episode <?php echo( $episode ); ?></H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
Episode <?php echo( $episode ); ?> has been edited and is ready for
entry. Thank you for your addition to the story.
        </TD>
    </TR>
</TABLE>

<A HREF="read.php?episode=<?php echo( $episode ); ?>">Enter Episode <?php echo( $episode ); ?></A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

?>

<HTML><HEAD>
<TITLE><?php echo( $titleString ); ?></TITLE>
</HEAD><BODY>

<FORM ACTION="create.php" METHOD="post">

<?php

if ( $extending )
{

?>

<INPUT TYPE="hidden" NAME="commandModifier" VALUE="Extend">

<?php

}
else
{
    if ( $editing )
    {

?>

<INPUT TYPE="hidden" NAME="commandModifier" VALUE="Edit">

<?php

    }

?>

<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">

<?php

}

?>

<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">

<CENTER>
<H1><?php echo( $titleString ); ?></H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
<?php echo( $bodyString ); ?>
<P>
<B>Do not press the forwards or backwards buttons on your browser</B>.
Doing so may lock you out of this episode! To consult previous episodes,
please do so in another browser window. If you wish to cancel creating
this episode, please use the cancel button at the bottom of the page. This
will clear the lock you have on the episode and permit someone else to
create it. If you do get locked out of the episode, you should be given
the option to unlock it if you attempt to create it again. If that doesn't
work, locked episodes are unlocked one hour after the lock is placed.
<P>
HTML tags are not allowed in any text field except the episode description
and option descriptions.
<P>
You are limited to the following tags in the episode description:<BR>
&lt;P&gt;, &lt;/P&gt;, &lt;BR&gt;, &lt;HR&gt;, &lt;B&gt;, &lt;/B&gt;, &lt;I&gt;, &lt;/I&gt;.
<P>
You are limited to the following tags in the option descriptions:<BR>
&lt;B&gt;, &lt;/B&gt;, &lt;I&gt;, &lt;/I&gt;.

<?php

if (( $command == "Preview"       ) ||
    ( $command == "ExtendPreview" ) ||
    ( $command == "EditPreview"   ))
{

?>

<P>
Below, your episode is displayed almost exactly as it will appear when saved.
<B>(Your chosen color scheme is not part of this preview.)</B>
Please review your episode and correct any errors before saving.

<?php

}

?>

        </TD>
    </TR>
</TABLE>

</CENTER>

<?php

if (( $command == "Preview"       ) ||
    ( $command == "ExtendPreview" ) ||
    ( $command == "EditPreview"   ))
{
    $displayedExtendedLink = htmlentities( $extendedLink );
    $displayedTitle        = htmlentities( $title        );
    $displayedText         = htmlentities( $text         );
    $displayedAuthorName   = htmlentities( $authorName   );

    $displayedAuthorEmail  = strtr( $authorEmail,           Util::getEmailAddressTranslation() );
    $displayedExtendedLink = strtr( $displayedExtendedLink, Util::getOptionTranslation()       );
    $displayedText         = strtr( $displayedText,         Util::getEpisodeBodyTranslation()  );

?>

<HR SIZE="10">

<CENTER>

<?php

    if ( $extending )
    {

?>

<B>Option Description Leading to this Extension</B><BR>
<?php echo( $displayedExtendedLink ); ?>
<P>

<?php

    }

?>

<H1><?php echo( $displayedTitle ); ?></H1>

<H2>
    <?php echo( $storyName ); ?> - Episode <?php echo( $extending ? "*extension*" : $episode ); ?>
</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
<?php echo( $displayedText ); ?>
<P>
<OL>

<?php

    for ( $i = 0; $i < $linkCount; $i++ )
    {
        $var1 = "option"     . $i;
        $var2 = "backlink"   . $i;
        $var3 = "isBackLink" . $i;

        if ( !empty( $$var1 ))
        {
            $displayedOption = htmlentities( $$var1 );
            $displayedOption = strtr( $displayedOption, Util::getOptionTranslation() );

            if ((( $$var2 != 0 ) && ( !$editing )) || ( $$var3 == "Y" ))
            {
                $image = "blue.gif";
            }
            else
            {
                $image = "red.gif";
            }

?>

<LI>
    <IMG SRC="images/<?php echo( $image ); ?>">
    <A HREF="#"><?php echo( $displayedOption ); ?></A>
</LI>

<?php

        }
    }

?>

</OL>

<?php

    if ( $extendable == 1 )
    {

?>

<P>
<A HREF="#">Add New Option</A>

<?php

    }

    if (( $episode != 1 ) || ( $extending ))
    {

?>

<P>
<A HREF="#">Go Back</A>

<?php

    }

?>

        </TD>
    </TR>
</TABLE>

<HR>

<?php

    if ( !empty( $displayedAuthorName ))
    {
        if ((( !empty( $displayedAuthorEmail ))) && ( $mailto == 1 ))
        {
            $author = "<A HREF=\"mailto:" . $displayedAuthorEmail . "\">" .
                      $displayedAuthorName . "</A>";
        }
        else
        {
            $author = $displayedAuthorName;
        }
    }
    else
    {
        $author = "";
    }

    if ( !empty( $author ))
    {

?>

<ADDRESS><?php echo( $author ); ?></ADDRESS>
<P>

<?php

    }

?>

<?php echo( $editing ? $creationDate : date( "n/j/Y g:i:s A" )); ?>
<P>

<?php

    if ( $linkable == 1 )
    {

?>

Linking Enabled
<P>

<?php

    }

    if ( $extendable == 1 )
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

</CENTER>

<HR SIZE="10">

<?php

}

if (( $command == "Preview"       ) ||
    ( $command == "ExtendPreview" ) ||
    ( $command == "EditPreview"   ))
{
    if ( !empty( $warning ))
    {

?>

<B>Before saving, please correct the following problems:</B><BR>
<?php echo( $warning ); ?>
<P>

<?php

    }
    else
    {

?>

<INPUT TYPE="submit" NAME="command" VALUE="Save">
<P>

<?php

    }
}

if ( $extending )
{

?>

Enter a description for the new option leading to this episode:<BR>
<INPUT TYPE="text" NAME="extendedLink" SIZE="60" MAXLENGTH="255"
       VALUE="<?php echo( htmlentities( $extendedLink )); ?>">
<P>

<?php

}

?>

Enter a title for this episode:<BR>
<INPUT TYPE="text" NAME="title" SIZE="60" MAXLENGTH="255"
       VALUE="<?php echo( htmlentities( $title )); ?>">
<P>
Describe the scenario:<BR>
<TEXTAREA NAME="text" ROWS="10" COLS="75"><?php echo( htmlentities( $text )); ?></TEXTAREA>
<P>
Now, enter the options a reader will have at the end of this episode. Use
only as many options as needed and leave the rest blank.
<P>
For back linked episodes, enter in the episode number you wish to link to
for each option.
<P>
<TABLE>
    <TR>
        <TH>#</TH>
        <TH>Option Text</TH>
        <TH>Back Linked Episode<BR>(advanced option)</TH>
    </TR>

<?php

for ( $i = 0; $i < $linkCount; $i++ )
{
    $var1 = "option"     . $i;
    $var2 = "backlink"   . $i;
    $var3 = "isBackLink" . $i

?>

    <TR>
        <TD ALIGN="right"><?php echo( $i + 1 ); ?></TD>
        <TD>
            <INPUT TYPE="text" NAME="option<?php echo( $i ); ?>" SIZE="60" MAXLENGTH="255"
                   VALUE="<?php echo( htmlentities( $$var1 )); ?>">
        </TD>
        <TD>
            <INPUT TYPE="<?php echo(( $editing && $$var3 == "N" ) ? "hidden" : "text" ); ?>"
                   NAME="backlink<?php echo( $i ); ?>"
                   VALUE="<?php echo( $$var2 == 0 ? "" : $$var2 ); ?>">
        </TD>
  </TR>

<?php

}

?>

</TABLE>
<P>
Select a scheme for this episode:<BR>
<SELECT NAME="scheme">

<?php

for ( $i = 0; $i < count( $schemeList ); $i++ )
{
    $row = $schemeList[ $i ];

    $selected = ( $scheme == $row[ 0 ] ) ? " SELECTED" : "";

?>

<OPTION VALUE="<?php echo( $row[ 0 ] ); ?>"<?php echo( $selected ); ?>>
    <?php echo( $row[ 1 ] );?>
</OPTION>

<?php

}

?>

</SELECT><BR>
<A HREF="scheme-preview.php" TARGET="_blank">Preview available schemes.</A><BR>
(Opens in new window.)
<P>
You may sign this episode, if you wish:<BR>
<INPUT TYPE="text" NAME="authorName" SIZE="60" MAXLENGTH="255"
       VALUE="<?php echo( htmlentities( $authorName )); ?>">
<P>
Some features require your email address:<BR>
<INPUT TYPE="text" NAME="authorEmail" SIZE="60" MAXLENGTH="255"
       VALUE="<?php echo( htmlentities( $authorEmail )); ?>">
<BR>
<INPUT TYPE="checkbox" NAME="mailto" VALUE="1"<?php echo( $mailtoChecked ); ?>>
Turn your signature into a mailto link to your email address.
<BR>
<INPUT TYPE="checkbox" NAME="notify" VALUE="1"<?php echo( $notifyChecked ); ?>>
Receive email when this episode is extended.
<P>
<INPUT TYPE="checkbox" NAME="linkable" VALUE="1"<?php echo( $linkableChecked ); ?>>
Link to this episode later (advanced option).
<BR>
<INPUT TYPE="checkbox" NAME="extendable" VALUE="1"<?php echo( $extendableChecked ); ?>>
Make this episode extendable (advanced option).
<P>
<INPUT TYPE="submit" NAME="command" VALUE="Preview">
</FORM>
<P>

<?php

if ( $extending )
{

?>

<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Cancel</A> - Do <B>not</B> create this episode.

<?php

}
else
{

?>

<P>
<FORM ACTION="clear.php" METHOD="post">
<INPUT TYPE="hidden" NAME="episode" VALUE="<?php echo( $episode ); ?>">
<INPUT TYPE="hidden" NAME="lockKey" VALUE="<?php echo( $lockKey ); ?>">
<INPUT TYPE="submit" VALUE="Cancel"> - Do <B>not</B> create this episode!
</FORM>

<?php

}

?>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
