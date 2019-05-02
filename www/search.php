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

use \Extend_A_Story\Util;

Util::getSessionAndUserIDs( $sessionID, $userID );

$storyName = Util::getStringValue( "StoryName" );
$siteName  = Util::getStringValue( "SiteName"  );
$storyHome = Util::getStringValue( "StoryHome" );
$siteHome  = Util::getStringValue( "SiteHome"  );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Search</TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Search</H1>

<TABLE WIDTH="500">
<TR><TD>

This is a simple search engine. Type the text you wish to search for below.
The search engine will print out any episodes that match that literal string
anywhere in the selected field. Example : A title search of "Win" will match
"You Win!", "The Winner Is...", or "It is Windy" in the title field.
<P>
The search is <B>NOT</B> case sensitive.

</TD></TR>
</TABLE>

</CENTER>

<FORM ACTION="results.php" METHOD="post">
Select Search Method
<SELECT NAME="method">
<OPTION VALUE="title">Episode Title</OPTION>
<OPTION VALUE="text">Episode Body</OPTION>
<OPTION VALUE="author">Author Name</OPTION>
<OPTION VALUE="time">Creation Time</OPTION>
</SELECT><BR>
Search Text :
<INPUT TYPE="TEXT" NAME="text" SIZE="60" MAXLENGTH="255"><BR>
<INPUT TYPE="SUBMIT" VALUE="Search">
</FORM>
<P>
<FORM ACTION="results.php" METHOD="post">
<INPUT TYPE="HIDDEN" NAME="method" VALUE="extendable">
List Extendable Episodes
<INPUT TYPE="SUBMIT" VALUE="Search">
</FORM>
<P>
<FORM ACTION="results.php" METHOD="post">
<INPUT TYPE="HIDDEN" NAME="method" VALUE="linkable">
List Linkable Episodes
<INPUT TYPE="SUBMIT" VALUE="Search">
</FORM>
<P>
<FORM ACTION="results.php" METHOD="post">
<INPUT TYPE="HIDDEN" NAME="method" VALUE="days">
Search for episodes created or edited within the last
<INPUT TYPE="TEXT" NAME="days" SIZE="2" MAXLENGTH="2">
days.
<INPUT TYPE="SUBMIT" VALUE="Search">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
