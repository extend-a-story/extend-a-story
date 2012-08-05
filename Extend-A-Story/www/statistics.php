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

    $error = "";
    $fatal = false;

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
        $storyName = getStringValue( $error, $fatal, "StoryName" );
        $siteName  = getStringValue( $error, $fatal, "SiteName"  );
        $storyHome = getStringValue( $error, $fatal, "StoryHome" );
        $siteHome  = getStringValue( $error, $fatal, "SiteHome"  );
    }

    if ( empty( $error ))
    {
        $result = mysql_query( "SELECT COUNT( * ) FROM Episode WHERE Status = 2 OR Status = 3" );

        if ( ! $result )
        {
            $error .= "Problem retrieving created episode count from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $row = mysql_fetch_row( $result );

            if ( ! $row )
            {
                $error .= "Problem fetching created episode count row from the database.<BR>";
                $fatal = true;
            }
            else
            {
                $created = $row[ 0 ];
            }
        }
    }

    if ( empty( $error ))
    {
        $result = mysql_query( "SELECT COUNT( * ) FROM Episode WHERE Status = 0 OR Status = 1" );

        if ( ! $result )
        {
            $error .= "Problem retrieving empty episode count from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $row = mysql_fetch_row( $result );

            if ( ! $row )
            {
                $error .= "Problem fetching empty episode count row from the database.<BR>";
                $fatal = true;
            }
            else
            {
                $empty = $row[ 0 ];
            }
        }
    }

    if ( empty( $error ))
    {
        $result = mysql_query( "SELECT COUNT( * ) FROM Episode" );

        if ( ! $result )
        {
            $error .= "Problem retrieving episode count from the database.<BR>";
            $fatal = true;
        }
        else
        {
            $row = mysql_fetch_row( $result );

            if ( ! $row )
            {
                $error .= "Problem fetching episode count row from the database.<BR>";
                $fatal = true;
            }
            else
            {
                $count = $row[ 0 ];
            }
        }
    }

    if ( ! empty( $error ))
    {
        displayError( $error, $fatal );
    }

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Statistics</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Statistics</H1>
<H2>Created Episodes: <?php echo( $created ); ?></H2>
<H2>Empty Episodes:   <?php echo( $empty   ); ?></H2>
<H2>Total Episodes:   <?php echo( $count   ); ?></H2>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>
