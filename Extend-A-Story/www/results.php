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

  $method = $_POST[ "method" ];
  $text   = $_POST[ "text"   ];
  $days   = $_POST[ "days"   ];

  $days = ( int ) $days;

  $error = "";
  $fatal = false;

  if ( ( $method != "title"      ) &&
       ( $method != "text"       ) &&
       ( $method != "author"     ) &&
       ( $method != "time"       ) &&
       ( $method != "extendable" ) &&
       ( $method != "linkable"   ) &&
       ( $method != "days"       ) )
  {
    $error .= "The specified search method is not supported.<BR>";
    $fatal = true;
  }

  // Connect to the database.
  if ( empty( $error ) )
    connectToDatabase( $error, $fatal );

  if ( empty( $error ) )
    $sessionID = getSessionID( $error, $fatal );

  if ( empty( $error ) )
  {
    $storyName = getStringValue( $error, $fatal, "StoryName" );
    $siteName  = getStringValue( $error, $fatal, "SiteName"  );
    $storyHome = getStringValue( $error, $fatal, "StoryHome" );
    $siteHome  = getStringValue( $error, $fatal, "SiteHome"  );
  }

  if ( empty( $error ) )
  {
    if ( $method == "title" )
      $whereClause = "Title like '%" . mysql_escape_string( $text ) . "%' and ( Status = 2 or Status = 3 )";

    if ( $method == "text" )
      $whereClause = "Text like '%" . mysql_escape_string( $text ) . "%' and ( Status = 2 or Status = 3 )";

    if ( $method == "author" )
      $whereClause = "AuthorName like '%" . mysql_escape_string( $text ) . "%' and ( Status = 2 or Status = 3 )";

    if ( $method == "time" )
      $whereClause = "CreationDate like '%" . mysql_escape_string( $text ) . "%' and ( Status = 2 or Status = 3 )";

    if ( $method == "extendable" )
      $whereClause = "IsExtendable = 'Y' and ( Status = 2 or Status = 3 )";

    if ( $method == "linkable" )
      $whereClause = "IsLinkable = 'Y' and ( Status = 2 or Status = 3 )";

    if ( $method == "days" )
      $whereClause = "CreationTimestamp > subdate( now( ), interval " . $days . " day ) and ( Status = 2 or Status = 3 )";

    $result = mysql_query( "select EpisodeID, Title from Episode where " . $whereClause );
    if ( ! $result )
    {
      $error .= "Problem retrieving the search results from the database.<BR>";
      $fatal = true;
    }
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Search Results</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Search Results</H1>
</CENTER>

<?php

  for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
  {
    $row = mysql_fetch_row( $result );
    $displayedTitle = htmlentities( $row[ 1 ] );

?>
<A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 0 ] ); ?> - <?php echo( $displayedTitle ); ?></A><BR>
<?php

  }

?>
<P>
<A HREF="search.php">Search Again</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</BODY></HTML>
