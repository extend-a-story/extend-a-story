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

  $command = $_REQUEST[ "command" ];

  $error = "";
  $fatal = false;

  // Connect to the database.
  if ( empty( $error ) )
    connectToDatabase( $error, $fatal );

  if ( empty( $error ) )
    getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );

  if ( empty( $error ) )
  {
    $storyName      = getStringValue( $error, $fatal, "StoryName"      );
    $siteName       = getStringValue( $error, $fatal, "SiteName"       );
    $storyHome      = getStringValue( $error, $fatal, "StoryHome"      );
    $siteHome       = getStringValue( $error, $fatal, "SiteHome"       );
    $readEpisodeURL = getStringValue( $error, $fatal, "ReadEpisodeURL" );
    $adminEmail     = getStringValue( $error, $fatal, "AdminEmail"     );
    $isWriteable    = getStringValue( $error, $fatal, "IsWriteable"    );
    $maxLinks       = getIntValue   ( $error, $fatal, "MaxLinks"       );
    $maxEditDays    = getIntValue   ( $error, $fatal, "MaxEditDays"    );
  }

  $message = "";

  if ( ( $command == "login" ) && ( empty( $error ) ) )
  {
    $loginName = $_POST[ "loginName" ];
    $password  = $_POST[ "password"  ];

    prepareParam( $loginName );
    prepareParam( $password  );

    $result = mysql_query( "select UserID from User where LoginName = '" . mysql_escape_string( $loginName ) . "' " .
                           "and Password = password( '" . $password . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to query user table in database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $message = "Invalid login name and/or password.";
      }
      else
      {
        $message = "Successfully logged in.";
        $userID = $row[ 0 ];

        $result = mysql_query( "update Session set UserID = " . $userID . " where SessionID = " . $sessionID );
        if ( ! $result )
        {
          $error .= "Unable to update session record.<BR>";
          $fatal = true;
        }
      }
    }
  }

  if ( ( $command == "logout" ) && ( empty( $error ) ) )
  {
    $result = mysql_query( "update Session set UserID = 0 where SessionID = " . $sessionID );
    if ( ! $result )
    {
      $error .= "Unable to update session record.<BR>";
      $fatal = true;
    }
    else
    {
      $message = "Successfully logged out.";
      $userID = 0;
    }
  }

  if ( ( $userID == 0 ) && empty( $error ) )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Please log in.</H2>

<?php

    if ( ! empty( $message ) )
    {

?>
<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>
<?php

    }

?>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command" VALUE="login">

<TABLE>
  <TR>
    <TD>Login Name:</TD>
    <TD><INPUT TYPE="text" NAME="loginName"></TD>
  </TR>
  <TR>
    <TD>Password:</TD>
    <TD><INPUT TYPE="password" NAME="password"></TD>
  </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Login">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( empty( $error ) )
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

  if ( ( $permissionLevel < 2 ) &&
       ( ( $command == "listOrphans"  ) ||
         ( $command == "listDeadEnds" ) ) )
    $command = "";

  if ( ( $permissionLevel < 3 ) &&
       ( ( $command == "configureSave" ) ||
         ( $command == "configure"     ) ) )
    $command = "";

  if ( ( $permissionLevel < 4 ) &&
       ( ( $command == "addUserSave"    ) ||
         ( $command == "addUser"        ) ||
         ( $command == "editUserSave"   ) ||
         ( $command == "editUser"       ) ||
         ( $command == "deleteUser"     ) ||
         ( $command == "deleteUserSave" ) ) )
    $command = "";

  if ( ( $command == "changePasswordSave" ) && ( empty( $error ) ) )
  {
    $curPassword  = $_POST[ "curPassword"  ];
    $newPassword1 = $_POST[ "newPassword1" ];
    $newPassword2 = $_POST[ "newPassword2" ];

    prepareParam( $curPassword  );
    prepareParam( $newPassword1 );
    prepareParam( $newPassword2 );

    $result = mysql_query( "select count( * ) from User where UserID = " . $userID . " " .
                           "and Password = password( '" . $curPassword . "' )" );
    if ( ! $result )
    {
      $error .= "Unable to query user record from database.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Unable to fetch user count row from database.<BR>";
        $fatal = true;
      }
      else
      {
        if ( $row[ 0 ] != 1 )
        {
          $message = "Error: Old password is incorrect.";
        }
        else
        {
          if ( $newPassword1 != $newPassword2 )
          {
            $message = "Error: New passwords do not match.";
          }
          else
          {
            $result = mysql_query( "update User set Password = password( '" . $newPassword1 . "' ) where UserID = " . $userID );
            if ( ! $result )
            {
              $error .= "Unable to update user record.<BR>";
              $fatal = true;
            }
            else
            {
              $message = "Password successfully changed.";
            }
          }
        }
      }
    }
  }

  if ( ( $command == "configureSave" ) && ( empty( $error ) ) )
  {
    $newStoryName      = $_POST[ "newStoryName"      ];
    $newSiteName       = $_POST[ "newSiteName"       ];
    $newStoryHome      = $_POST[ "newStoryHome"      ];
    $newSiteHome       = $_POST[ "newSiteHome"       ];
    $newReadEpisodeURL = $_POST[ "newReadEpisodeURL" ];
    $newAdminEmail     = $_POST[ "newAdminEmail"     ];
    $newIsWriteable    = $_POST[ "newIsWriteable"    ];
    $newMaxLinks       = $_POST[ "newMaxLinks"       ];
    $newMaxEditDays    = $_POST[ "newMaxEditDays"    ];

    $newMaxLinks    = ( int ) $newMaxLinks;
    $newMaxEditDays = ( int ) $newMaxEditDays;

    if ( empty( $newStoryName ) )
      $message .= "You must enter the story name.<BR>";

    if ( empty( $newSiteName ) )
      $message .= "You must enter the site name.<BR>";

    if ( empty( $newStoryHome ) )
      $message .= "You must enter the story home.<BR>";

    if ( empty( $newSiteHome ) )
      $message .= "You must enter the site home.<BR>";

    if ( empty( $newReadEpisodeURL ) )
      $message .= "You must enter the read episode URL.<BR>";

    if ( empty( $newAdminEmail ) )
      $message .= "You must enter the admin email.<BR>";

    if ( strlen( $newStoryName ) > 255 )
      $message .= "The story name cannot be longer than 255 characters.<BR>";

    if ( strlen( $newSiteName ) > 255 )
      $message .= "The site name cannot be longer than 255 characters.<BR>";

    if ( strlen( $newStoryHome ) > 255 )
      $message .= "The story home cannot be longer than 255 characters.<BR>";

    if ( strlen( $newSiteHome ) > 255 )
      $message .= "The site home cannot be longer than 255 characters.<BR>";

    if ( strlen( $newReadEpisodeURL ) > 255 )
      $message .= "The read episode URL cannot be longer than 255 characters.<BR>";

    if ( ( $newIsWriteable != 0 ) && ( $newIsWriteable != 1 ) )
      $message .= "Your chosen 'is writeable' setting is not recognized.<BR>";

    if ( $newMaxLinks <= 0 )
      $message .= "Max links must be a positive number.<BR>";

    if ( $newMaxEditDays <= 0 )
      $message .= "Max edit days must be a positive number.<BR>";

    if ( empty( $message ) )
    {
      setStringValue( $error, $fatal, "StoryName",      $newStoryName                        );
      setStringValue( $error, $fatal, "SiteName",       $newSiteName                         );
      setStringValue( $error, $fatal, "StoryHome",      $newStoryHome                        );
      setStringValue( $error, $fatal, "SiteHome",       $newSiteHome                         );
      setStringValue( $error, $fatal, "ReadEpisodeURL", $newReadEpisodeURL                   );
      setStringValue( $error, $fatal, "AdminEmail",     $newAdminEmail                       );
      setStringValue( $error, $fatal, "IsWriteable",    ( $newIsWriteable == 1 ? "Y" : "N" ) );
      setIntValue   ( $error, $fatal, "MaxLinks",       $newMaxLinks                         );
      setIntValue   ( $error, $fatal, "MaxEditDays",    $newMaxEditDays                      );

      $message = "Configuration Saved";
    }
    else
    {
      $message = "Problems saving configuration:<P>" . $message;
    }
  }

  if ( ( $command == "addUserSave" ) && ( empty( $error ) ) )
  {
    $newLoginName       = $_POST[ "newLoginName"       ];
    $newUserName        = $_POST[ "newUserName"        ];
    $newPermissionLevel = $_POST[ "newPermissionLevel" ];
    $newPassword1       = $_POST[ "newPassword1"       ];
    $newPassword2       = $_POST[ "newPassword2"       ];

    if ( empty( $newLoginName ) )
      $message .= "You must enter the login name.<BR>";

    if ( empty( $newUserName ) )
      $message .= "You must enter the user name.<BR>";

    if ( empty( $newPassword1 ) )
      $message .= "You must enter a password.<BR>";

    if ( strlen( $newLoginName ) > 255 )
      $message .= "The login name cannot exceed 255 characters.<BR>";

    if ( strlen( $newUserName ) > 255 )
      $message .= "The user name cannot exceed 255 characters.<BR>";

    if ( $newPassword1 != $newPassword2 )
      $message .= "The passwords do not match.<BR>";

    if ( ( $newPermissionLevel != 1 ) &&
         ( $newPermissionLevel != 2 ) &&
         ( $newPermissionLevel != 3 ) &&
         ( $newPermissionLevel != 4 ) )
      $message .= "Your chosen 'permission level' setting is not recognized.<BR>";

    $count = -1;

    $result = mysql_query( "select count( * ) from User where LoginName = '" . mysql_escape_string( $newLoginName ) . "'" );
    if ( ! $result )
    {
      $error .= "Unable to query database for existing login name.<BR>";
      $fatal = true;
    }
    else
    {
      $row = mysql_fetch_row( $result );
      if ( ! $row )
      {
        $error .= "Unable to fetch existing login name count row from database.<BR>";
        $fatal = true;
      }
      else
      {
        $count = $row[ 0 ];
      }
    }

    if ( $count != 0 )
      $message .= "The login name you selected is already in use.<BR>";

    if ( empty( $message ) )
    {
      createUser( $error, $fatal, $newPermissionLevel, $newLoginName, $newPassword1, $newUserName );

      $message = "User Added";
    }
    else
    {
      $message = "Problems adding user:<P>" . $message;
    }
  }

  if ( ( $command == "editUser" ) || ( $command == "editUserSave" ) || ( $command == "deleteUser" ) )
  {
    $editedUserID = $_POST[ "userID" ];

    if ( $editedUserID == 0 )
    {
      if ( $command == "deleteUser" )
        $message = "You must select an user to delete.";
      else
        $message = "You must select an user to edit.";

      $command = "";
    }
    else if ( ( $command == "deleteUser" ) && ( $editedUserID == $userID ) )
    {
      $message = "You cannot delete yourself.";
      $command = "";
    }
    else
    {
      $result = mysql_query( "select PermissionLevel, LoginName, UserName from User where UserID = " . $editedUserID );
      if ( ! $result )
      {
        $error .= "Unable to query user for editing from database.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $error .= "Unable to fetch user row for editing from databsae.<BR>";
          $fatal = true;
        }
        else
        {
          $oldPermissionLevel = $row[ 0 ];
          $oldLoginName       = $row[ 1 ];
          $oldUserName        = $row[ 2 ];
        }
      }
    }
  }

  if ( ( $command == "editUserSave" ) && ( empty( $error ) ) )
  {
    $newLoginName       = $_POST[ "newLoginName"       ];
    $newUserName        = $_POST[ "newUserName"        ];
    $newPermissionLevel = $_POST[ "newPermissionLevel" ];
    $setNewPassword     = $_POST[ "setNewPassword"     ];
    $newPassword1       = $_POST[ "newPassword1"       ];
    $newPassword2       = $_POST[ "newPassword2"       ];

    if ( empty( $newLoginName ) )
      $message .= "You must enter the login name.<BR>";

    if ( empty( $newUserName ) )
      $message .= "You must enter the user name.<BR>";

    if ( strlen( $newLoginName ) > 255 )
      $message .= "The login name cannot exceed 255 characters.<BR>";

    if ( strlen( $newUserName ) > 255 )
      $message .= "The user name cannot exceed 255 characters.<BR>";

    if ( $setNewPassword == 1 )
    {
      if ( empty( $newPassword1 ) )
        $message .= "You must enter a password when setting a new password.<BR>";

      if ( $newPassword1 != $newPassword2 )
        $message .= "The passwords do not match.<BR>";
    }

    if ( $userID == $editedUserID )
    {
      if ( $oldPermissionLevel != $newPermissionLevel )
        $message .= "You cannot change your own permission level.<BR>";

      if ( $setNewPassword == 1 )
        $message .= "You cannot change your own password here. Use the Change Password function instead.<BR>";
    }

    if ( ( $newPermissionLevel != 1 ) &&
         ( $newPermissionLevel != 2 ) &&
         ( $newPermissionLevel != 3 ) &&
         ( $newPermissionLevel != 4 ) )
      $message .= "Your chosen 'permission level' setting is not recognized.<BR>";

    if ( $oldLoginName != $newLoginName )
    {
      $count = -1;

      $result = mysql_query( "select count( * ) from User where LoginName = '" . mysql_escape_string( $newLoginName ) . "'" );
      if ( ! $result )
      {
        $error .= "Unable to query database for existing login name.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $error .= "Unable to fetch existing login name count row from database.<BR>";
          $fatal = true;
        }
        else
        {
          $count = $row[ 0 ];
        }
      }

      if ( $count != 0 )
        $message .= "The login name you selected is already in use.<BR>";
    }

    if ( empty( $message ) )
    {
      if ( $setNewPassword == 1 )
      {
        $sql = "update User set PermissionLevel = "            . $newPermissionLevel                  .  ", "   .
                               "LoginName       = '"           . mysql_escape_string( $newLoginName ) . "', "   .
                               "Password        = password( '" . mysql_escape_string( $newPassword1 ) . "' ), " .
                               "UserName        = '"           . mysql_escape_string( $newUserName  ) . "' "    .
                         "where UserID          = "            . $editedUserID;
      }
      else
      {
        $sql = "update User set PermissionLevel = "  . $newPermissionLevel                  .  ", " .
                               "LoginName       = '" . mysql_escape_string( $newLoginName ) . "', " .
                               "UserName        = '" . mysql_escape_string( $newUserName  ) . "' "  .
                         "where UserID          = "            . $editedUserID;
      }

      $result = mysql_query( $sql );
      if ( ! $result )
      {
        $error .= "Unable to update user record.<BR>";
        $fatal = true;
      }
      else
      {
        $message = "User Edited";
      }
    }
    else
    {
      $message = "Problems editing user:<P>" . $message;
    }
  }

  if ( ( $command == "deleteUserSave" ) && ( empty( $error ) ) )
  {
    $deletedUserID = $_POST[ "userID" ];

    if ( $deletedUserID == 0 )
    {
      $message = "You must select an user to delete.";
    }
    else if ( $deletedUserID == $userID )
    {
      $message = "You cannot delete yourself.";
    }
    else
    {
      $result = mysql_query( "delete from User where UserID = " . $deletedUserID );
      if ( ! $result )
      {
        $error .= "Problem deleting user from the database.<BR>";
        $fatal = true;
      }
      else
      {
        $message = "User Deleted";
      }
    }
  }

  if ( ( $command == "listOrphans" ) && ( empty( $error ) ) )
  {
    $orphans = mysql_query( "select Episode.EpisodeID, Episode.Parent, Episode.Status, count( * )" .
                            "from Link right outer join Episode on Link.IsBackLink = 'N' and " .
                                                                  "Link.TargetEpisodeID = Episode.EpisodeID " .
                            "left outer join EpisodeEditLog on Episode.EpisodeID = EpisodeEditLog.EpisodeID " .
                            "where Link.LinkID is null and Episode.EpisodeID != 1 " .
                            "group by Episode.EpisodeID " .
                            "order by Episode.EpisodeID" );

    if ( ! $orphans )
    {
      $error .= "Unable to query list of orphans from the database.<BR>";
      $fatal = true;
    }
  }

  if ( ( $command == "listDeadEnds" ) && ( empty( $error ) ) )
  {
    $deadEnds = mysql_query( "select Episode.EpisodeID " .
                             "from Link right outer join Episode on Link.SourceEpisodeID = Episode.EpisodeID " .
                             "where Link.LinkID is null and ( Episode.Status = 2 or Episode.Status = 3 ) " .
                             "order by Episode.EpisodeID" );

    if ( ! $deadEnds )
    {
      $error .= "Unable to query list of dead ends from the database.<BR>";
      $fatal = true;
    }
  }

  if ( $command == "listRecentEdits" )
  {
    if ( empty( $error ) )
    {
      $result = mysql_query( "select max( EpisodeEditLogID ) from EpisodeEditLog" );
      if ( ! $result )
      {
        $error .= "Unable to query the max EpisodeEditLogID from database.<BR>";
        $fatal = true;
      }
      else
      {
        $row = mysql_fetch_row( $result );
        if ( ! $row )
        {
          $error .= "Unable to retrieve the max EpisodeEditLogID record from database.<BR>";
          $fatal = true;
        }
        else
        {
          $maxEpisodeEditLogID = ( int ) $row[ 0 ];
        }
      }
    }

    $start = $_REQUEST[ "start" ];
    $start = ( int ) $start;

    if ( ( $start < 1 ) || ( $start > $maxEpisodeEditLogID ) )
      $start = $maxEpisodeEditLogID;

    $edits = mysql_query( "select EpisodeEditLogID, EpisodeID, EditDate, EditLogEntry from EpisodeEditLog " .
                          "where EpisodeEditLogID <= " . $start . " order by EpisodeEditLogID desc limit 20" );
    if ( ! $edits )
    {
      $error .= "Unable to query list of recent edits from the database.<BR>";
      $fatal = true;
    }
  }

  if ( empty( $error ) )
  {
    $users = mysql_query( "select UserID, LoginName from User order by UserID" );
    if ( ! $users )
    {
      $error .= "Unable to query user list from database.<BR>";
      $fatal = true;
    }
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

  if ( $command == "listOrphans" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>List Orphans</H2>

<TABLE>
  <TR>
    <TH>Episode&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Parent&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Is Created?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Edited?</TH>
  </TR>
<?php

    for ( $i = 0; $i < mysql_num_rows( $orphans ); $i++ )
    {
      $row = mysql_fetch_row( $orphans );

      $edits = ( ( $row[ 3 ] > 1 )
             ?
               "<A HREF=\"list-edits.php?episode=" .
                 $row[ 0 ] . "\">Yes - " .
                 $row[ 3 ] . " Times</A>"
             :
               "No" );

?>
  <TR>
    <TD><A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 0 ] ); ?></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><A HREF="read.php?episode=<?php echo( $row[ 1 ] ); ?>"><?php echo( $row[ 1 ] ); ?></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><?php echo( ( ( $row[ 2 ] == 2 ) || ( $row[ 2 ] == 3 ) ) ? "Yes" : "No" ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><?php echo( $edits ); ?></TD>
  </TR>
<?php

    }

?>
</TABLE>
<P>
<A HREF="admin.php">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "listDeadEnds" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>List Dead Ends</H2>

<TABLE>
  <TR>
    <TD>
<?php

    for ( $i = 0; $i < mysql_num_rows( $deadEnds ); $i++ )
    {
      $row = mysql_fetch_row( $deadEnds );

?>
<A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>">Episode <?php echo( $row[ 0 ] ); ?></A><BR>
<?php

    }

?>
    </TD>
  </TR>
</TABLE>
<P>
<A HREF="admin.php">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "listRecentEdits" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>List Recent Edits</H2>

<TABLE>
  <TR>
    <TH>Edit&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Episode&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TH>
    <TH>Log Entry</TH>
  </TR>
<?php

    for ( $i = 0; $i < mysql_num_rows( $edits ); $i++ )
    {
      $row = mysql_fetch_row( $edits );

?>
  <TR>
    <TD><A HREF="view-edit.php?episodeEditLogID=<?php echo( $row[ 0 ] );?>"><?php echo( $row[ 0 ] );?></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><A HREF="list-edits.php?episode=<?php echo( $row[ 1 ] );?>"><?php echo( $row[ 1 ] );?></A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><?php echo( $row[ 2 ] );?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
    <TD><?php echo( $row[ 3 ] );?></TD>
  </TR>
<?php

    }

?>
</TABLE>
<?php

    if ( $start > 20 )
    {

?>
<P>
<A HREF="admin.php?command=listRecentEdits&start=<?php echo( $start - 20 ); ?>">Previous 20 Edits</A>
<?php

    }

?>
<P>
<A HREF="admin.php">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "changePassword" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Changing password for: <?php echo( $userName ); ?></H2>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command" VALUE="changePasswordSave">

<TABLE>
  <TR>
    <TD>Current Password:</TD>
    <TD><INPUT TYPE="password" NAME="curPassword"></TD>
  </TR>
  <TR>
    <TD>New Password:</TD>
    <TD><INPUT TYPE="password" NAME="newPassword1"></TD>
  </TR>
  <TR>
    <TD>New Password (Again):</TD>
    <TD><INPUT TYPE="password" NAME="newPassword2"></TD>
  </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Change Password">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "configure" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Configuring Story Settings</H2>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command" VALUE="configureSave">

<TABLE>
  <TR>
    <TD>Story Name:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newStoryName" VALUE="<?php echo( $storyName ); ?>"></TD>
  </TR>
  <TR>
    <TD>Site Name:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newSiteName" VALUE="<?php echo( $siteName ); ?>"></TD>
  </TR>
  <TR>
    <TD>Story Home:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newStoryHome" VALUE="<?php echo( $storyHome ); ?>"></TD>
  </TR>
  <TR>
    <TD>Site Home:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newSiteHome" VALUE="<?php echo( $siteHome ); ?>"></TD>
  </TR>
  <TR>
    <TD>Read Episode URL:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newReadEpisodeURL" VALUE="<?php echo( $readEpisodeURL ); ?>"></TD>
  </TR>
  <TR>
    <TD>Admin Email:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newAdminEmail" VALUE="<?php echo( $adminEmail ); ?>"></TD>
  </TR>
  <TR>
    <TD>Is Writeable:</TD>
    <TD>
      <SELECT NAME="newIsWriteable">
        <OPTION VALUE="1">Yes</OPTION>
        <OPTION VALUE="0"<?php echo( $isWriteable == "N" ? " SELECTED" : "" ); ?>>No</OPTION>
      </SELECT>
    </TD>
  </TR>
  <TR>
    <TD>Max Links:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newMaxLinks" VALUE="<?php echo( $maxLinks ); ?>"></TD>
  </TR>
  <TR>
    <TD>Max Edit Days:</TD>
    <TD><INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newMaxEditDays" VALUE="<?php echo( $maxEditDays ); ?>"></TD>
  </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Save">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "addUser" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Add New User</H2>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command" VALUE="addUserSave">

<TABLE>
  <TR>
    <TD>Login Name:</TD>
    <TD><INPUT TYPE="text" NAME="newLoginName"></TD>
  </TR>
  <TR>
    <TD>User Name:</TD>
    <TD><INPUT TYPE="text" NAME="newUserName"></TD>
  </TR>
  <TR>
    <TD>Permission Level:</TD>
    <TD>
      <SELECT NAME="newPermissionLevel">
        <OPTION VALUE="1">Moderator</OPTION>
        <OPTION VALUE="2">Super Moderator</OPTION>
        <OPTION VALUE="3">Manager</OPTION>
        <OPTION VALUE="4">Administrator</OPTION>
    </TD>
  </TR>
  <TR>
    <TD>Password:</TD>
    <TD><INPUT TYPE="password" NAME="newPassword1"></TD>
  </TR>
  <TR>
    <TD>Password (Again):</TD>
    <TD><INPUT TYPE="password" NAME="newPassword2"></TD>
  </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Add User">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "editUser" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Edit User</H2>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command"      VALUE="editUserSave">
<INPUT TYPE="hidden" NAME="userID" VALUE="<?php echo( $editedUserID ); ?>">

<TABLE>
  <TR>
    <TD>Login Name:</TD>
    <TD><INPUT TYPE="text" NAME="newLoginName" VALUE="<?php echo( $oldLoginName ); ?>"></TD>
  </TR>
  <TR>
    <TD>User Name:</TD>
    <TD><INPUT TYPE="text" NAME="newUserName" VALUE="<?php echo( $oldUserName ); ?>"></TD>
  </TR>
  <TR>
    <TD>Permission Level:</TD>
    <TD>
      <SELECT NAME="newPermissionLevel">
        <OPTION VALUE="1"<?php echo( $oldPermissionLevel == 1 ? " SELECTED" : "" ); ?>>Moderator</OPTION>
        <OPTION VALUE="2"<?php echo( $oldPermissionLevel == 2 ? " SELECTED" : "" ); ?>>Super Moderator</OPTION>
        <OPTION VALUE="3"<?php echo( $oldPermissionLevel == 3 ? " SELECTED" : "" ); ?>>Manager</OPTION>
        <OPTION VALUE="4"<?php echo( $oldPermissionLevel == 4 ? " SELECTED" : "" ); ?>>Administrator</OPTION>
    </TD>
  </TR>
  <TR>
    <TD>Set new password?</TD>
    <TD><INPUT TYPE="checkbox" NAME="setNewPassword" VALUE="1"></TD>
  </TR>
  <TR>
    <TD>Password:</TD>
    <TD><INPUT TYPE="password" NAME="newPassword1"></TD>
  </TR>
  <TR>
    <TD>Password (Again):</TD>
    <TD><INPUT TYPE="password" NAME="newPassword2"></TD>
  </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Save">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

  if ( $command == "deleteUser" )
  {

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Delete User</H2>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="hidden" NAME="command"      VALUE="deleteUserSave">
<INPUT TYPE="hidden" NAME="userID" VALUE="<?php echo( $editedUserID ); ?>">

<H3>Are you sure you wish to delete <?php echo( $oldLoginName ); ?>?</H3>

<INPUT TYPE="submit" VALUE="Yes">
</FORM>

<FORM ACTION="admin.php" METHOD="post">
<INPUT TYPE="submit" VALUE="No">
</FORM>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
<?php

    exit;
  }

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Welcome: <?php echo( htmlentities( $userName ) ); ?></H2>

<?php

  if ( ! empty( $message ) )
  {

?>
<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>
<?php

  }

?>

<H3>Select the operation you want to perform:</H3>

<H4>
<?php

  if ( $permissionLevel >= 2 )
  {

?>
<A HREF="admin.php?command=listOrphans">List Orphaned Episodes</A><BR>
<A HREF="admin.php?command=listDeadEnds">List Dead Ends</A><BR>
<A HREF="admin.php?command=listRecentEdits">List Recent Edits</A><P>
<?php

  }

  if ( $permissionLevel >= 3 )
  {

?>
<A HREF="admin.php?command=configure">Configure Story Settings</A><P>
<?php

  }

  if ( $permissionLevel >= 4 )
  {

?>
<A HREF="admin.php?command=addUser">Add User</A>

<FORM ACTION="admin.php" METHOD="POST">
<INPUT TYPE="hidden" NAME="command" VALUE="editUser">
Edit User -
<SELECT NAME="userID">
<OPTION VALUE="0">Select One</OPTION>
<?php

    for ( $i = 0; $i < mysql_num_rows( $users ); $i++ )
    {
      $row = mysql_fetch_row( $users );

?>
<OPTION VALUE="<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 1 ] ); ?></OPTION>
<?php

    }

    mysql_data_seek( $users, 0 );

?>
</SELECT>
<INPUT TYPE="submit" VALUE="Go">
</FORM>

<FORM ACTION="admin.php" METHOD="POST">
<INPUT TYPE="hidden" NAME="command" VALUE="deleteUser">
Delete User -
<SELECT NAME="userID">
<OPTION VALUE="0">Select One</OPTION>
<?php

    for ( $i = 0; $i < mysql_num_rows( $users ); $i++ )
    {
      $row = mysql_fetch_row( $users );

?>
<OPTION VALUE="<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 1 ] ); ?></OPTION>
<?php

    }

?>
</SELECT>
<INPUT TYPE="submit" VALUE="Go">
</FORM><P>

<?php

  }

?>
<A HREF="admin.php?command=changePassword">Change Password</A><BR>
<A HREF="admin.php?command=logout">Logout</A>
</H4>

<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
