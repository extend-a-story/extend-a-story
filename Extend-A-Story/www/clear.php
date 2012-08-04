<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002 - 2012  Jeffrey J. Weston, Matthew Duhan


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

    $episode = $_POST[ "episode" ];
    $lockKey = $_POST[ "lockKey" ];

    $error = "";
    $fatal = false;

    $episode = (int) $episode;

    if ( $episode == 0 )
    {
        $episode = 1;
    }

    // connect to the database
    if ( empty( $error ))
    {
        connectToDatabase( $error, $fatal );
    }

    if ( empty( $error ))
    {
        getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );
    }

    if ( empty( $error ))
    {
        $isWriteable = getStringValue( $error, $fatal, "IsWriteable" );
    }

    if ( $isWriteable == "N" )
    {

?>

<HTML><HEAD>
<TITLE>Clear Lock Error - Episode Creation Disabled</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Clear Lock Error</H1>
<H2>Episode Creation Disabled</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You are unable to clear locks while episode creation is disabled.
<P>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>

<?php

        exit;
    }

    if ( empty( $error ))
    {
        $result = mysql_query( "SELECT Parent, " .
                                      "Status, " .
                                      "LockKey " .
                                 "FROM Episode " .
                                "WHERE EpisodeID = " . $episode );

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
                $error .= "Problem fetching episode row from the database.<BR>";
                $fatal = true;
            }
            else
            {
                $parent         = $row[ 0 ];
                $status         = $row[ 1 ];
                $episodeLockKey = $row[ 2 ];
            }
        }
    }

    if ( $lockKey != $episodeLockKey )
    {

?>

<HTML><HEAD>
<TITLE>Clear Lock Error - Wrong Key to Unlock Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1>Clear Lock Error</H1>
<H2>Wrong Key to Unlock Episode <?php echo( $episode ); ?></H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You are trying to unlock an episode, but you don't have the correct key to
unlock it. Please wait for the episode to time out. You will be given the
correct key to unlock it at that time.
<P>
<A HREF="read.php?episode=<?php echo(( $status == 1 ) ? $parent : $episode ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>

<?php

        exit;
    }

    if (( $status != 1 ) && ( $status != 3 ))
    {

?>

<HTML><HEAD>
<TITLE>Clearing Error - Episode <?php echo( $episode ); ?> Not Available For Clearing</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Creation Error</H1>
<H2>Episode <?php echo( $episode ); ?> Not Available For Clearing</H2>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have specified an episode that is not locked.
<P>
<A HREF="read.php?episode=<?php echo(( $status == 1 ) ? $parent : $episode ); ?>">Go Back</A>.
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>

<?php

        exit;
    }

    if ( empty( $error ))
    {
        $sessionColumn = ( $status == 1 ) ? "AuthorSessionID" : "EditorSessionID";
        $statusValue = ( $status == 1 ) ? 0 : 2;

        $result = mysql_query( "UPDATE Episode " .
                                  "SET " . $sessionColumn . " = 0, " .
                                      "Status = " . $statusValue . ", " .
                                      "LockDate = '-', " .
                                      "LockKey = 0 " .
                                "WHERE EpisodeID = " . $episode );

        if ( ! $result )
        {
            $error .= "Unable to unlock the episode record.<BR>";
            $fatal = true;
        }
    }

    if ( ! empty( $error ))
    {
        displayError( $error, $fatal );
    }

?>

<HTML><HEAD>
<TITLE>Cleared Episode <?php echo( $episode ); ?> Lock</TITLE>
</HEAD><BODY>

<CENTER>
<H1>Cleared Episode <?php echo( $episode ); ?> Lock</H1>

<TABLE WIDTH="500">
    <TR>
        <TD>
You have now cleared the lock on episode <?php echo( $episode ); ?>.
It is now ready to be <?php echo(( $status == 1 ) ? "created" : "edited" ); ?> again.
<P>
<A HREF="read.php?episode=<?php echo(( $status == 1 ) ? $parent : $episode ); ?>">Go Back</A>
        </TD>
    </TR>
</TABLE>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>
