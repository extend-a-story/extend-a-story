<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2021 Jeffrey J. Weston <jjweston@gmail.com>


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

namespace Extend_A_Story\Pages\Install;

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Upgrade\Version;
use \Extend_A_Story\Util;

class ConfirmationPage extends InstallPage
{
    public static function validatePage()
    {
        $result = ConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return null;
    }

    private static function validatePreviousPage()
    {
        $task = Util::getStringParam( $_POST, "task" );
        if ( $task === "install" )
        {
            $result = StorySettingsPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else if ( $task === "upgrade" )
        {
            $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
            if ( $databaseVersion === 1 )
            {
                $result = StorySettingsPage::validatePage();
                if ( isset( $result )) return $result;
            }
            else if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 ))
            {
                $result = VersionConfirmationPage::validatePage();
                if ( isset( $result )) return $result;
            }
            else throw new StoryException( "Unrecognized database version." );
        }
        else throw new StoryException( "Unrecognized task." );

        return null;
    }

    private $task;
    private $databaseHost;
    private $databaseUsername;
    private $databaseName;
    private $adminLoginName;
    private $adminDisplayName;
    private $settingsStoryName;
    private $settingsSiteName;
    private $settingsStoryHome;
    private $settingsSiteHome;
    private $settingsReadEpisodeUrl;
    private $settingsAdminEmail;
    private $settingsMaxLinks;
    private $settingsMaxEditDays;
    private $tables;
    private $databaseVersion;
    private $storyVersion;

    public function validate()
    {
        $result = ConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        $task = Util::getStringParam( $_POST, "task" );
        if ( $task === "install" )
        {
            if ( isset( $this->backButton     )) return new StorySettingsPage();
            if ( isset( $this->continueButton )) return new CompletedPage();
        }
        else if ( $task === "upgrade" )
        {
            if ( isset( $this->backButton ))
            {
                $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
                if ( $databaseVersion === 1 ) return new StorySettingsPage();
                if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 )) return new VersionConfirmationPage();
                else throw new StoryException( "Unrecognized database version." );
            }

            if ( isset( $this->continueButton )) return new CompletedPage();
        }
        else throw new StoryException( "Unrecognized task." );

        throw new StoryException( "Unrecognized navigation from confirmation page." );
    }

    protected function getSubtitle()
    {
        $task = Util::getStringParam( $_POST, "task" );
        switch ( $task )
        {
            case "install" : return "Install Confirmation";
            case "upgrade" : return "Upgrade Confirmation";
            default : throw new StoryException( "Unrecognized task." );
        }
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }

    protected function preRender()
    {
        $this->task                   = Util::getStringParamDefault( $_POST, "task",                   "" );
        $this->databaseHost           = Util::getStringParamDefault( $_POST, "databaseHost",           "" );
        $this->databaseUsername       = Util::getStringParamDefault( $_POST, "databaseUsername",       "" );
        $this->databaseName           = Util::getStringParamDefault( $_POST, "databaseName",           "" );
        $this->adminLoginName         = Util::getStringParamDefault( $_POST, "adminLoginName",         "" );
        $this->adminDisplayName       = Util::getStringParamDefault( $_POST, "adminDisplayName",       "" );
        $this->settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $this->settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $this->settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $this->settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $this->settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $this->settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $this->settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $this->settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );
        $this->tables                 = null;
        $this->databaseVersion        = null;
        $this->storyVersion           = null;

        if ( $this->task === "install" )
        {
            $tableNames = Database::getConflictingTableNames();
            if ( count( $tableNames ) > 0 ) $this->tables = UnorderedList::buildFromStringArray( $tableNames );
        }
        else if ( $this->task === "upgrade" )
        {
            $version = Version::getVersion();
            $this->databaseVersion = $version->getDatabaseVersion();
            $this->storyVersion    = $version->getStoryVersion();
        }
        else throw new StoryException( "Unrecognized task." );
    }

    protected function renderMain()
    {

?>

<p>
    We have gathered all of the information we need to <?php echo( htmlentities( $this->task )); ?> your Extend-A-Story
    database. Verify that your information is correct. Once you are ready to proceed, click the
    <em><?php echo( $this->task === "install" ? "Install" : "Upgrade" ); ?></em> button.
</p>

<table>
    <tr>
        <td colspan="2" class="sectionHeader">Database Connection</td>
    </tr>
    <tr>
        <td class="header">Host</td>
        <td><?php echo( htmlentities( $this->databaseHost )); ?></td>
    </tr>
    <tr>
        <td class="header">Username</td>
        <td><?php echo( htmlentities( $this->databaseUsername )); ?></td>
    </tr>
    <tr>
        <td class="header">Password</td>
        <td>**********</td>
    </tr>
    <tr>
        <td class="header">Database</td>
        <td><?php echo( htmlentities( $this->databaseName )); ?></td>
    </tr>

<?php

        if (( $this->task === "install" ) or
            (( $this->task === "upgrade" ) and
             ( $this->databaseVersion === 1 )))
        {
?>

    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Administrator Account</td>
    </tr>
    <tr>
        <td class="header">Login Name</td>
        <td><?php echo( htmlentities( $this->adminLoginName )); ?></td>
    </tr>
    <tr>
        <td class="header">Display Name</td>
        <td><?php echo( htmlentities( $this->adminDisplayName )); ?></td>
    </tr>
    <tr>
        <td class="header">Password</td>
        <td>**********</td>
    </tr>

<?php

        }

        if (( $this->task === "install" ) or
            (( $this->task === "upgrade" ) and
             ( $this->databaseVersion === 1 )))
        {

?>

    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Story Settings</td>
    </tr>

<?php

            if ( $this->task === "install" )
            {

?>

    <tr>
        <td class="header">Story Name</td>
        <td><?php echo( htmlentities( $this->settingsStoryName )); ?></td>
    </tr>
    <tr>
        <td class="header">Site Name</td>
        <td><?php echo( htmlentities( $this->settingsSiteName )); ?></td>
    </tr>
    <tr>
        <td class="header">Story Home</td>
        <td><?php echo( htmlentities( $this->settingsStoryHome )); ?></td>
    </tr>
    <tr>
        <td class="header">Site Home</td>
        <td><?php echo( htmlentities( $this->settingsSiteHome )); ?></td>
    </tr>
    <tr>
        <td class="header">Read Episode URL</td>
        <td><?php echo( htmlentities( $this->settingsReadEpisodeUrl )); ?></td>
    </tr>
    <tr>
        <td class="header">Admin Email</td>
        <td><?php echo( htmlentities( $this->settingsAdminEmail )); ?></td>
    </tr>
    <tr>
        <td class="header">Max Links</td>
        <td><?php echo( htmlentities( $this->settingsMaxLinks )); ?></td>
    </tr>

<?php

            }

?>

    <tr>
        <td class="header">Max Edit Days</td>
        <td><?php echo( htmlentities( $this->settingsMaxEditDays )); ?></td>
    </tr>

<?php

        }

        if ( $this->task === "upgrade" )
        {

?>

    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Version</td>
    </tr>
    <tr>
        <td class="header">Current Version</td>
        <td><?php echo( htmlentities( $this->storyVersion )); ?></td>
    </tr>

<?php

        }

?>

</table>

<?php

        if (( $this->task === "upgrade" ) or ( isset( $this->tables )))
        {

?>

<div class="dataLossWarning">

<?php

            if ( isset( $this->tables ))
            {

?>

    <p>!!! YOU WILL LOSE DATA IF YOU PROCEED !!!</p>

    <p>The following tables will be deleted:</p>

<?php

                $this->tables->render();
            }

?>

    <p>We strongly suggest performing a backup before you proceed.</p>

</div>

<?php

        }

?>

<div class="submit">
    <input type="hidden" name="pageName" value="Confirmation">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton"
           value="<?php echo( $this->task === "install" ? "Install" : "Upgrade" ); ?>">
</div>

<?php

    }
}

?>
