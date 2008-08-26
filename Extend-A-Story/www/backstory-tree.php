<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002 - 2008  Jeffrey J. Weston, Matthew Duhan


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
    $storyName = getStringValue( $error, $fatal, "StoryName" );
    $siteName  = getStringValue( $error, $fatal, "SiteName"  );
    $storyHome = getStringValue( $error, $fatal, "StoryHome" );
    $siteHome  = getStringValue( $error, $fatal, "SiteHome"  );
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Back Story Tree</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Back Story Tree</H1>
<H2>Color Key for Parent Episodes: <FONT COLOR="#008000">First Time Encountered</FONT>, <FONT COLOR="#FF0000">Already Encountered</FONT></H2>
<H2>Bold episodes indicate the primary parent, all others are backlinks.</H2>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>
</CENTER>

<?php

  $curLevel        = 0;
  $curEpisodes     = array( $episode );
  $visitedEpisodes = array( );

  while ( count( $curEpisodes ) > 0 )
  {

?>
<H2>Level: <?php echo( $curLevel ); ?>, Episodes: <?php echo( count( $curEpisodes ) ); ?></H2>
<TABLE BORDER="1" CELLSPACING="0" WIDTH="100%">
  <TR>
    <TH width="50%">Episode Number and Title</TH>
    <TH width="50%">Parents of this Episode</TH>
  <TR>
<?php

    sort( $curEpisodes, SORT_NUMERIC );
    $nextEpisodes = array( );

    for ( $i = 0; $i < count( $curEpisodes ); $i++ )
    {
      array_push( $visitedEpisodes, $curEpisodes[ $i ] );
    }

    for ( $i = 0; $i < count( $curEpisodes ); $i++ )
    {
      $episode = $curEpisodes[ $i ];

      $result = mysql_query( "select Title from Episode where EpisodeID = " . $episode );
      if ( ! $result )
      {
        echo( "Problem retrieving episode from database." );
        exit;
      }

      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        echo( "Problem fetching episode row from database." );
        exit;
      }

      $title  = $row[ 0 ];

      $result = mysql_query( "select SourceEpisodeID, IsBackLink from Link where TargetEpisodeID = " . $episode . " order by SourceEpisodeID" );
      if ( ! $result )
      {
        echo( "Problem retrieving parents from database." );
        exit;
      }

      $children = "";

      for ( $j = 0; $j < mysql_num_rows( $result ); $j++ )
      {
        $row = mysql_fetch_row( $result );
        $target = $row[ 0 ];
        $visited = in_array( $target, $visitedEpisodes );
        $isBackLink = $row[ 1 ];

        if ( $visited )
        {
          $color = "#FF0000";
        }
        else
        {
          $color = "#008000";
          if ( ! in_array( $target, $nextEpisodes ) )
          {
            array_push( $nextEpisodes, $target );
          }
        }

        $child = "<A HREF=\"read.php?episode=" . $target . "\"><FONT COLOR=\"" . $color . "\">" . $target . "</FONT></A>";

        if ( $isBackLink == "N" )
        {
          $child = "<B>" . $child . "</B>";
        }

        if ( $j != 0 )
          $children .= ", ";

        $children .= $child;
      }

?>
  <TR>
    <TD>
<A HREF="backstory-tree.php?episode=<?php echo( $episode ); ?>">View Tree</A> -
<A HREF="read.php?episode=<?php echo( $episode ); ?>"><?php echo( $episode ); ?> : <?php echo( $title ); ?></A>
    </TD>
    <TD><?php echo( $children ); ?></TD>
    <TD><A HREF="read.php?episode=<?php echo( $parent ); ?>"><?php echo( $parent ); ?></A></TD>
  </TR>
<?php

    }

?>
</TABLE>
<?php

    $curEpisodes = $nextEpisodes;
    $curLevel++;
  }

?>

</CENTER>

<?php require( "footer.php" ); ?>

</BODY></HTML>
