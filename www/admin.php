<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2025 Jeffrey J. Weston <jjweston@gmail.com>


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

$storyName      = Util::getStringValue( "StoryName"      );
$siteName       = Util::getStringValue( "SiteName"       );
$storyHome      = Util::getStringValue( "StoryHome"      );
$siteHome       = Util::getStringValue( "SiteHome"       );
$readEpisodeURL = Util::getStringValue( "ReadEpisodeURL" );
$adminEmail     = Util::getStringValue( "AdminEmail"     );
$isWriteable    = Util::getStringValue( "IsWriteable"    );
$maxLinks       = Util::getIntValue(    "MaxLinks"       );
$maxEditDays    = Util::getIntValue(    "MaxEditDays"    );

$message = "";

$command = Util::getStringParamDefault( $_REQUEST, "command", "" );

if (( $command != ""                   ) &&
    ( $command != "addUser"            ) &&
    ( $command != "addUserSave"        ) &&
    ( $command != "changePassword"     ) &&
    ( $command != "changePasswordSave" ) &&
    ( $command != "deleteUser"         ) &&
    ( $command != "deleteUserSave"     ) &&
    ( $command != "editUser"           ) &&
    ( $command != "editUserSave"       ) &&
    ( $command != "configure"          ) &&
    ( $command != "configureSave"      ) &&
    ( $command != "listDeadEnds"       ) &&
    ( $command != "listOrphans"        ) &&
    ( $command != "listRecentEdits"    ) &&
    ( $command != "login"              ) &&
    ( $command != "logout"             ))
{
    $message = "Invalid Command";
    $command = "";
}

if ( $command == "login" )
{
    $loginName = Util::getStringParam( $_POST, "loginName" );
    $password  = Util::getStringParam( $_POST, "password"  );

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT UserID " .
              "FROM User " .
             "WHERE LoginName = :loginName " .
               "AND Password = SHA2( :password, 256 )" );

    $dbStatement->bindParam( ":loginName", $loginName, PDO::PARAM_STR );
    $dbStatement->bindParam( ":password",  $password,  PDO::PARAM_STR );

    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        $message = "Invalid login name and/or password.";
    }
    else
    {
        $message = "Successfully logged in.";
        $userID = $row[ 0 ];

        $dbStatement = Util::getDbConnection()->prepare(
                "UPDATE Session " .
                   "SET UserID = :userID " .
                 "WHERE SessionID = :sessionID" );

        $dbStatement->bindParam( ":userID",    $userID,    PDO::PARAM_INT );
        $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to update session record." );
        }
    }
}

if ( $command == "logout" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            "UPDATE Session " .
               "SET UserID = 0 " .
             "WHERE SessionID = :sessionID" );

    $dbStatement->bindParam( ":sessionID", $sessionID, PDO::PARAM_INT );
    $dbStatement->execute();

    if ( $dbStatement->rowCount() != 1 )
    {
        throw new StoryException( "Unable to update session record." );
    }

    $message = "Successfully logged out.";
    $userID = 0;
}

if ( $userID == 0 )
{
    if (( $command != ""                   ) &&
        ( $command != "login"              ) &&
        ( $command != "logout"             ))
    {
        $message = "Invalid Command";
        $command = "";
    }

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Administration</TITLE>
</HEAD><BODY>

<CENTER>

<H1><?php echo( $storyName ); ?>: Administration</H1>

<H2>Please log in.</H2>

<?php

    if ( !empty( $message ))
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>

<?php

    exit;
}

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT PermissionLevel, " .
               "UserName " .
          "FROM User " .
         "WHERE UserID = :userID" );

$dbStatement->bindParam( ":userID", $userID, PDO::PARAM_INT );
$dbStatement->execute();

$row = $dbStatement->fetch( PDO::FETCH_NUM );

if ( !$row )
{
    throw new StoryException( "Unable to fetch user information row from database." );
}

$permissionLevel = $row[ 0 ];
$userName        = $row[ 1 ];

if ((( $permissionLevel < 2           ) &&
     (( $command == "listOrphans"    ) ||
      ( $command == "listDeadEnds"   ))) ||
    (( $permissionLevel < 3           ) &&
     (( $command == "configureSave"  ) ||
      ( $command == "configure"      ))) ||
    (( $permissionLevel < 4           ) &&
     (( $command == "addUserSave"    ) ||
      ( $command == "addUser"        ) ||
      ( $command == "editUserSave"   ) ||
      ( $command == "editUser"       ) ||
      ( $command == "deleteUser"     ) ||
      ( $command == "deleteUserSave" ))))
{
    $message = "You don't have permission to perform this operation.";
    $command = "";
}

if ( $command == "changePasswordSave" )
{
    $curPassword  = Util::getStringParam( $_POST, "curPassword"  );
    $newPassword1 = Util::getStringParam( $_POST, "newPassword1" );
    $newPassword2 = Util::getStringParam( $_POST, "newPassword2" );

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT COUNT( * ) " .
              "FROM User " .
             "WHERE UserID = :userID " .
               "AND Password = SHA2( :curPassword, 256 )" );

    $dbStatement->bindParam( ":userID",      $userID,      PDO::PARAM_INT );
    $dbStatement->bindParam( ":curPassword", $curPassword, PDO::PARAM_STR );

    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException( "Unable to fetch user count row from database." );
    }

    if ( $row[ 0 ] != 1 )
    {
        $message = "Error: Old password is incorrect.";
    }
    else
    {
        if (( empty( $newPassword1 )) && ( empty( $newPassword2 )))
        {
            $message = "Error: You must enter a new password.";
        }
        else if ( $newPassword1 != $newPassword2 )
        {
            $message = "Error: New passwords do not match.";
        }
        else
        {
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE User " .
                       "SET Password = SHA2( :newPassword1, 256 ) " .
                     "WHERE UserID = :userID" );

            $dbStatement->bindParam( ":newPassword1", $newPassword1, PDO::PARAM_STR );
            $dbStatement->bindParam( ":userID",       $userID,       PDO::PARAM_INT );

            $dbStatement->execute();

            if ( $dbStatement->rowCount() != 1 )
            {
                throw new StoryException( "Unable to update user record." );
            }

            $message = "Password successfully changed.";
        }
    }
}

if ( $command == "configureSave" )
{
    $newStoryName      = Util::getStringParam( $_POST, "newStoryName"      );
    $newSiteName       = Util::getStringParam( $_POST, "newSiteName"       );
    $newStoryHome      = Util::getStringParam( $_POST, "newStoryHome"      );
    $newSiteHome       = Util::getStringParam( $_POST, "newSiteHome"       );
    $newReadEpisodeURL = Util::getStringParam( $_POST, "newReadEpisodeURL" );
    $newAdminEmail     = Util::getStringParam( $_POST, "newAdminEmail"     );
    $newIsWriteable    = Util::getIntParam(    $_POST, "newIsWriteable"    );
    $newMaxLinks       = Util::getIntParam(    $_POST, "newMaxLinks"       );
    $newMaxEditDays    = Util::getIntParam(    $_POST, "newMaxEditDays"    );

    if ( empty( $newStoryName ))
    {
        $message .= "You must enter the story name.<BR>";
    }

    if ( empty( $newSiteName ))
    {
        $message .= "You must enter the site name.<BR>";
    }

    if ( empty( $newStoryHome ))
    {
        $message .= "You must enter the story home.<BR>";
    }

    if ( empty( $newSiteHome ))
    {
        $message .= "You must enter the site home.<BR>";
    }

    if ( empty( $newReadEpisodeURL ))
    {
        $message .= "You must enter the read episode URL.<BR>";
    }

    if ( empty( $newAdminEmail ))
    {
        $message .= "You must enter the admin email.<BR>";
    }

    if ( strlen( $newStoryName ) > 255 )
    {
        $message .= "The story name cannot be longer than 255 characters.<BR>";
    }

    if ( strlen( $newSiteName ) > 255 )
    {
        $message .= "The site name cannot be longer than 255 characters.<BR>";
    }

    if ( strlen( $newStoryHome ) > 255 )
    {
        $message .= "The story home cannot be longer than 255 characters.<BR>";
    }

    if ( strlen( $newSiteHome ) > 255 )
    {
        $message .= "The site home cannot be longer than 255 characters.<BR>";
    }

    if ( strlen( $newReadEpisodeURL ) > 255 )
    {
        $message .= "The read episode URL cannot be longer than 255 characters.<BR>";
    }

    if ( strlen( $newAdminEmail ) > 255 )
    {
        $message .= "The admin email cannot be longer than 255 characters.<BR>";
    }

    if (( $newIsWriteable != 0 ) && ( $newIsWriteable != 1 ))
    {
        $message .= "Your chosen 'is writeable' setting is not recognized.<BR>";
    }

    if ( $newMaxLinks <= 0 )
    {
        $message .= "Max links must be a positive number.<BR>";
    }

    if ( $newMaxEditDays <= 0 )
    {
        $message .= "Max edit days must be a positive number.<BR>";
    }

    if ( empty( $message ))
    {
        Util::setStringValue( "StoryName",      $newStoryName                       );
        Util::setStringValue( "SiteName",       $newSiteName                        );
        Util::setStringValue( "StoryHome",      $newStoryHome                       );
        Util::setStringValue( "SiteHome",       $newSiteHome                        );
        Util::setStringValue( "ReadEpisodeURL", $newReadEpisodeURL                  );
        Util::setStringValue( "AdminEmail",     $newAdminEmail                      );
        Util::setStringValue( "IsWriteable",    ( $newIsWriteable == 1 ? "Y" : "N" ));
        Util::setIntValue(    "MaxLinks",       $newMaxLinks                        );
        Util::setIntValue(    "MaxEditDays",    $newMaxEditDays                     );

        $storyName      = $newStoryName;
        $siteName       = $newSiteName;
        $storyHome      = $newStoryHome;
        $siteHome       = $newSiteHome;
        $readEpisodeURL = $newReadEpisodeURL;
        $adminEmail     = $newAdminEmail;
        $isWriteable    = ( $newIsWriteable == 1 ? "Y" : "N" );
        $maxLinks       = $newMaxLinks;
        $maxEditDays    = $newMaxEditDays;

        $message = "Configuration Saved";
    }
    else
    {
        $message = "Problems saving configuration:<P>" . $message;
    }
}

if ( $command == "addUserSave" )
{
    $newLoginName       = Util::getStringParam( $_POST, "newLoginName"       );
    $newUserName        = Util::getStringParam( $_POST, "newUserName"        );
    $newPermissionLevel = Util::getIntParam(    $_POST, "newPermissionLevel" );
    $newPassword1       = Util::getStringParam( $_POST, "newPassword1"       );
    $newPassword2       = Util::getStringParam( $_POST, "newPassword2"       );

    if ( empty( $newLoginName ))
    {
        $message .= "You must enter the login name.<BR>";
    }

    if ( empty( $newUserName ))
    {
        $message .= "You must enter the user name.<BR>";
    }

    if (( empty( $newPassword1 )) && ( empty( $newPassword2 )))
    {
        $message .= "You must enter a password.<BR>";
    }

    if ( strlen( $newLoginName ) > 255 )
    {
        $message .= "The login name cannot exceed 255 characters.<BR>";
    }

    if ( strlen( $newUserName ) > 255 )
    {
        $message .= "The user name cannot exceed 255 characters.<BR>";
    }

    if ( $newPassword1 != $newPassword2 )
    {
        $message .= "The passwords do not match.<BR>";
    }

    if (( $newPermissionLevel != 1 ) &&
        ( $newPermissionLevel != 2 ) &&
        ( $newPermissionLevel != 3 ) &&
        ( $newPermissionLevel != 4 ))
    {
        $message .= "Your chosen 'permission level' setting is not recognized.<BR>";
    }

    $count = -1;

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT COUNT( * ) " .
              "FROM User " .
             "WHERE LoginName = :newLoginName" );

    $dbStatement->bindParam( ":newLoginName", $newLoginName, PDO::PARAM_STR );
    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException(
                "Unable to fetch existing login name count row from database." );
    }

    $count = $row[ 0 ];

    if ( $count != 0 )
    {
        $message .= "The login name you selected is already in use.<BR>";
    }

    if ( empty( $message ))
    {
        Util::createUser( $newPermissionLevel, $newLoginName, $newPassword1, $newUserName );

        $message = "User Added";
    }
    else
    {
        $message = "Problems adding user:<P>" . $message;
    }
}

if (( $command == "editUser"     ) ||
    ( $command == "editUserSave" ) ||
    ( $command == "deleteUser"   ))
{
    $editedUserID = Util::getIntParam( $_POST, "userID" );

    if ( $editedUserID == 0 )
    {
        if ( $command == "deleteUser" )
        {
            $message = "You must select a user to delete.";
        }
        else
        {
            $message = "You must select a user to edit.";
        }

        $command = "";
    }
    else if (( $command == "deleteUser" ) && ( $editedUserID == $userID ))
    {
        $message = "You cannot delete yourself.";
        $command = "";
    }
    else
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT PermissionLevel, " .
                       "LoginName, " .
                       "UserName " .
                  "FROM User " .
                 "WHERE UserID = :editedUserID" );

        $dbStatement->bindParam( ":editedUserID", $editedUserID, PDO::PARAM_INT );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( !$row )
        {
            $message = "The specified user does not exist.";
            $command = "";
        }
        else
        {
            $oldPermissionLevel = $row[ 0 ];
            $oldLoginName       = $row[ 1 ];
            $oldUserName        = $row[ 2 ];
        }
    }
}

if ( $command == "editUserSave" )
{
    $newLoginName       = Util::getStringParam(     $_POST, "newLoginName"          );
    $newUserName        = Util::getStringParam(     $_POST, "newUserName"           );
    $newPermissionLevel = Util::getIntParam(        $_POST, "newPermissionLevel"    );
    $setNewPassword     = Util::getIntParamDefault( $_POST, "setNewPassword",     0 );
    $newPassword1       = Util::getStringParam(     $_POST, "newPassword1"          );
    $newPassword2       = Util::getStringParam(     $_POST, "newPassword2"          );

    if ( empty( $newLoginName ))
    {
        $message .= "You must enter the login name.<BR>";
    }

    if ( empty( $newUserName ))
    {
        $message .= "You must enter the user name.<BR>";
    }

    if ( strlen( $newLoginName ) > 255 )
    {
        $message .= "The login name cannot exceed 255 characters.<BR>";
    }

    if ( strlen( $newUserName ) > 255 )
    {
        $message .= "The user name cannot exceed 255 characters.<BR>";
    }

    if ( $setNewPassword == 1 )
    {
        if (( empty( $newPassword1 )) && ( empty( $newPassword2 )))
        {
            $message .= "You must enter a password when setting a new password.<BR>";
        }

        if ( $newPassword1 != $newPassword2 )
        {
            $message .= "The passwords do not match.<BR>";
        }
    }

    if ( $userID == $editedUserID )
    {
        if ( $oldPermissionLevel != $newPermissionLevel )
        {
            $message .= "You cannot change your own permission level.<BR>";
        }

        if ( $setNewPassword == 1 )
        {
            $message .= "You cannot change your own password here. Use the Change Password " .
                        "function instead.<BR>";
        }
    }

    if (( $newPermissionLevel != 1 ) &&
        ( $newPermissionLevel != 2 ) &&
        ( $newPermissionLevel != 3 ) &&
        ( $newPermissionLevel != 4 ))
    {
        $message .= "Your chosen 'permission level' setting is not recognized.<BR>";
    }

    if ( $oldLoginName != $newLoginName )
    {
        $count = -1;

        $dbStatement = Util::getDbConnection()->prepare(
                "SELECT COUNT( * ) " .
                  "FROM User " .
                 "WHERE LoginName = :newLoginName" );

        $dbStatement->bindParam( ":newLoginName", $newLoginName, PDO::PARAM_STR );
        $dbStatement->execute();
        $row = $dbStatement->fetch( PDO::FETCH_NUM );

        if ( !$row )
        {
            throw new StoryException(
                    "Unable to fetch existing login name count row from database." );
        }

        $count = $row[ 0 ];

        if ( $count != 0 )
        {
            $message .= "The login name you selected is already in use.<BR>";
        }
    }

    if ( empty( $message ))
    {
        $dbStatement;

        if ( $setNewPassword == 1 )
        {
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE User " .
                       "SET PermissionLevel = :newPermissionLevel, " .
                           "LoginName       = :newLoginName, " .
                           "Password        = SHA2( :newPassword1, 256 ), " .
                           "UserName        = :newUserName " .
                     "WHERE UserID = :editedUserID" );

            $dbStatement->bindParam( ":newPassword1", $newPassword1, PDO::PARAM_STR );
        }
        else
        {
            $dbStatement = Util::getDbConnection()->prepare(
                    "UPDATE User " .
                       "SET PermissionLevel = :newPermissionLevel, " .
                           "LoginName       = :newLoginName, " .
                           "UserName        = :newUserName " .
                     "WHERE UserID = :editedUserID" );
        }

        $dbStatement->bindParam( ":newPermissionLevel", $newPermissionLevel, PDO::PARAM_INT );
        $dbStatement->bindParam( ":newLoginName",       $newLoginName,       PDO::PARAM_STR );
        $dbStatement->bindParam( ":newUserName",        $newUserName,        PDO::PARAM_STR );
        $dbStatement->bindParam( ":editedUserID",       $editedUserID,       PDO::PARAM_INT );

        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to update user." );
        }

        if ( $userID == $editedUserID )
        {
            $userName = $newUserName;
        }

        $message = "User Edited";
    }
    else
    {
        $message = "Problems editing user:<P>" . $message;
    }
}

if ( $command == "deleteUserSave" )
{
    $deletedUserID = Util::getIntParam( $_POST, "userID" );

    if ( $deletedUserID == 0 )
    {
        $message = "You must select a user to delete.";
    }
    else if ( $deletedUserID == $userID )
    {
        $message = "You cannot delete yourself.";
    }
    else
    {
        $dbStatement = Util::getDbConnection()->prepare(
                "DELETE " .
                  "FROM User " .
                 "WHERE UserID = :deletedUserID" );

        $dbStatement->bindParam( ":deletedUserID", $deletedUserID, PDO::PARAM_INT );
        $dbStatement->execute();

        if ( $dbStatement->rowCount() != 1 )
        {
            throw new StoryException( "Unable to delete user." );
        }

        $message = "User Deleted";
    }
}

if ( $command == "listOrphans" )
{
    $dbStatement = Util::getDbConnection()->prepare(
                      "SELECT Episode.EpisodeID, " .
                             "Episode.Parent, " .
                             "Episode.Status, " .
                             "COUNT( * )" .
                        "FROM Link " .
            "RIGHT OUTER JOIN Episode " .
                          "ON Link.IsBackLink = 'N' " .
                         "AND Link.TargetEpisodeID = Episode.EpisodeID " .
             "LEFT OUTER JOIN EpisodeEditLog " .
                          "ON Episode.EpisodeID = EpisodeEditLog.EpisodeID " .
                       "WHERE Link.LinkID IS NULL " .
                         "AND Episode.EpisodeID != 1 " .
                       "GROUP BY Episode.EpisodeID " .
                       "ORDER BY Episode.EpisodeID" );

    $dbStatement->execute();
    $orphans = $dbStatement->fetchAll( PDO::FETCH_NUM );
}

if ( $command == "listDeadEnds" )
{
    $dbStatement = Util::getDbConnection()->prepare(
                      "SELECT Episode.EpisodeID " .
                        "FROM Link " .
            "RIGHT OUTER JOIN Episode " .
                          "ON Link.SourceEpisodeID = Episode.EpisodeID " .
                       "WHERE Link.LinkID IS NULL " .
                         "AND ( Episode.Status = 2 OR Episode.Status = 3 ) " .
                       "ORDER BY Episode.EpisodeID" );

    $dbStatement->execute();
    $deadEnds = $dbStatement->fetchAll( PDO::FETCH_NUM );
}

if ( $command == "listRecentEdits" )
{
    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT MAX( EpisodeEditLogID ) FROM EpisodeEditLog" );

    $dbStatement->execute();
    $row = $dbStatement->fetch( PDO::FETCH_NUM );

    if ( !$row )
    {
        throw new StoryException(
                "Unable to fetch the max EpisodeEditLogID record from database." );
    }

    $maxEpisodeEditLogID = (int) $row[ 0 ];

    $start = Util::getIntParamDefault( $_GET, "start", 0 );

    if (( $start < 1 ) || ( $start > $maxEpisodeEditLogID ))
    {
        $start = $maxEpisodeEditLogID;
    }

    $dbStatement = Util::getDbConnection()->prepare(
            "SELECT EpisodeEditLogID, " .
                   "EpisodeID, " .
                   "EditDate, " .
                   "EditLogEntry " .
              "FROM EpisodeEditLog " .
             "WHERE EpisodeEditLogID <= :start " .
             "ORDER BY EpisodeEditLogID DESC " .
             "LIMIT 20" );

    $dbStatement->bindParam( ":start", $start, PDO::PARAM_INT );
    $dbStatement->execute();
    $edits = $dbStatement->fetchAll( PDO::FETCH_NUM );
}

$dbStatement = Util::getDbConnection()->prepare(
        "SELECT UserID, " .
               "LoginName " .
          "FROM User " .
         "ORDER BY LoginName" );

$dbStatement->execute();
$users = $dbStatement->fetchAll( PDO::FETCH_NUM );

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

    for ( $i = 0; $i < count( $orphans ); $i++ )
    {
        $row = $orphans[ $i ];

        $edits = (( $row[ 3 ] > 1 ) ?
                 "<A HREF=\"list-edits.php?episode=" .
                         $row[ 0 ] . "\">Yes - " . $row[ 3 ] . " Times</A>" :
                 "No" );

?>

    <TR>
        <TD>
            <A HREF="read.php?episode=<?php echo( $row[ 0 ] ); ?>">
                <?php echo( $row[ 0 ] ); ?>
            </A>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <A HREF="read.php?episode=<?php echo( $row[ 1 ] ); ?>">
                <?php echo( $row[ 1 ] ); ?>
            </A>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <?php echo((( $row[ 2 ] == 2 ) || ( $row[ 2 ] == 3 )) ? "Yes" : "No" ); ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <?php echo( $edits ); ?>
        </TD>
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

    for ( $i = 0; $i < count( $deadEnds ); $i++ )
    {
        $row = $deadEnds[ $i ];

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

    for ( $i = 0; $i < count( $edits ); $i++ )
    {
        $row = $edits[ $i ];

?>

    <TR>
        <TD>
            <A HREF="view-edit.php?episodeEditLogID=<?php echo( $row[ 0 ] );?>">
                <?php echo( $row[ 0 ] );?>
            </A>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <A HREF="list-edits.php?episode=<?php echo( $row[ 1 ] );?>">
                <?php echo( $row[ 1 ] );?>
            </A>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <?php echo( $row[ 2 ] );?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </TD>
        <TD>
            <?php echo( $row[ 3 ] );?>
        </TD>
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
<A HREF="admin.php?command=listRecentEdits&start=<?php echo( $start - 20 ); ?>">
    Previous 20 Edits
</A>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newStoryName"
                   VALUE="<?php echo( $storyName ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Site Name:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newSiteName"
                   VALUE="<?php echo( $siteName ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Story Home:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newStoryHome"
                   VALUE="<?php echo( $storyHome ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Site Home:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newSiteHome"
                   VALUE="<?php echo( $siteHome ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Read Episode URL:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newReadEpisodeURL"
                   VALUE="<?php echo( $readEpisodeURL ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Admin Email:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newAdminEmail"
                   VALUE="<?php echo( $adminEmail ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Is Writeable:</TD>
        <TD>
            <SELECT NAME="newIsWriteable">
                <OPTION VALUE="1">
                    Yes
                </OPTION>
                <OPTION VALUE="0"<?php echo( $isWriteable == "N" ? " SELECTED" : "" ); ?>>
                    No
                </OPTION>
            </SELECT>
        </TD>
    </TR>
    <TR>
        <TD>Max Links:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newMaxLinks"
                   VALUE="<?php echo( $maxLinks ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Max Edit Days:</TD>
        <TD>
            <INPUT TYPE="text" SIZE="60" MAXLENGTH="255" NAME="newMaxEditDays"
                   VALUE="<?php echo( $maxEditDays ); ?>">
        </TD>
    </TR>
</TABLE>

<INPUT TYPE="submit" VALUE="Save">
</FORM>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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
        <TD><INPUT TYPE="text" MAXLENGTH="255" NAME="newLoginName"></TD>
    </TR>
    <TR>
        <TD>User Name:</TD>
        <TD><INPUT TYPE="text" MAXLENGTH="255" NAME="newUserName"></TD>
    </TR>
    <TR>
        <TD>Permission Level:</TD>
        <TD>
            <SELECT NAME="newPermissionLevel">
                <OPTION VALUE="1">Moderator</OPTION>
                <OPTION VALUE="2">Super Moderator</OPTION>
                <OPTION VALUE="3">Manager</OPTION>
                <OPTION VALUE="4">Administrator</OPTION>
            </SELECT>
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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
<INPUT TYPE="hidden" NAME="command" VALUE="editUserSave">
<INPUT TYPE="hidden" NAME="userID" VALUE="<?php echo( $editedUserID ); ?>">

<TABLE>
    <TR>
        <TD>Login Name:</TD>
        <TD>
            <INPUT TYPE="text" MAXLENGTH="255" NAME="newLoginName"
                   VALUE="<?php echo( $oldLoginName ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>User Name:</TD>
        <TD>
            <INPUT TYPE="text" MAXLENGTH="255" NAME="newUserName"
                   VALUE="<?php echo( $oldUserName ); ?>">
        </TD>
    </TR>
    <TR>
        <TD>Permission Level:</TD>
        <TD>
            <SELECT NAME="newPermissionLevel">
                <OPTION VALUE="1"<?php echo( $oldPermissionLevel == 1 ? " SELECTED" : "" ); ?>>
                    Moderator
                </OPTION>
                <OPTION VALUE="2"<?php echo( $oldPermissionLevel == 2 ? " SELECTED" : "" ); ?>>
                    Super Moderator
                </OPTION>
                <OPTION VALUE="3"<?php echo( $oldPermissionLevel == 3 ? " SELECTED" : "" ); ?>>
                    Manager
                </OPTION>
                <OPTION VALUE="4"<?php echo( $oldPermissionLevel == 4 ? " SELECTED" : "" ); ?>>
                    Administrator
                </OPTION>
            </SELECT>
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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
<INPUT TYPE="hidden" NAME="command" VALUE="deleteUserSave">
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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

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

<H2>Welcome: <?php echo( htmlentities( $userName )); ?></H2>

<?php

if ( !empty( $message ))
{

?>

<H3><FONT COLOR="#FF0000"><?php echo( $message ); ?></FONT></H3>

<?php

}

?>

<H3>Select the operation you want to perform:</H3>

<H4>

<A HREF="admin.php?command=listRecentEdits">List Recent Edits</A><BR>
<FORM METHOD="get" ACTION="list-edits.php">
View edits for episode: <INPUT TYPE="text" NAME="episode"> <INPUT TYPE="submit" VALUE="Go">
</FORM>
<P>

<?php

if ( $permissionLevel >= 2 )
{

?>

<A HREF="admin.php?command=listOrphans">List Orphaned Episodes</A><BR>
<A HREF="admin.php?command=listDeadEnds">List Dead Ends</A>
<P>

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

    for ( $i = 0; $i < count( $users ); $i++ )
    {
        $row = $users[ $i ];

?>

<OPTION VALUE="<?php echo( $row[ 0 ] ); ?>"><?php echo( $row[ 1 ] ); ?></OPTION>

<?php

    }

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

    for ( $i = 0; $i < count( $users ); $i++ )
    {
        $row = $users[ $i ];

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

<?php require( __DIR__ . "/include/config/Footer.php" ); ?>

</BODY></HTML>
