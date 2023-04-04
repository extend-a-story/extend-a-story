<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2023 Jeffrey J. Weston <jjweston@gmail.com>


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
        if ( !Util::getBoolParam( $_POST, "databaseExists" ))
        {
            $result = StorySettingsPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else
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

        return null;
    }

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
    private $conflictingTables;
    private $databaseExists;
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
        if ( isset( $this->continueButton )) return new CompletedPage();

        if ( isset( $this->backButton ))
        {
            if ( !Util::getBoolParam( $_POST, "databaseExists" )) return new StorySettingsPage();
            else
            {
                $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
                if ( $databaseVersion === 1 ) return new StorySettingsPage();
                if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 )) return new VersionConfirmationPage();
                else throw new StoryException( "Unrecognized database version." );
            }
        }

        throw new StoryException( "Unrecognized navigation from confirmation page." );
    }

    protected function getSubtitle()
    {
        return $this->databaseExists ? "Upgrade Confirmation" : "Install Confirmation";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }

    protected function preRender()
    {
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

        $version = Version::getVersion();
        $this->databaseVersion = $version->getDatabaseVersion();
        $this->databaseExists  = $version->checkDatabase();
        $this->storyVersion    = $version->getStoryVersion();

        // determine if any of the tables that we are going to add already exist in the database
        $addedTableNames = $this->databaseExists ? $version->getAddedTableNames() : Database::getStoryTableNames();
        $conflictingTableNames = array_intersect( $addedTableNames, Database::getDatabaseTableNames() );
        sort( $conflictingTableNames );
        $this->conflictingTables =
                empty( $conflictingTableNames ) ? null : UnorderedList::buildFromStringArray( $conflictingTableNames );
    }

    protected function renderMain()
    {

?>

<p>
    We have gathered all of the information we need to
    <?php echo( $this->databaseExists ? "upgrade" : "install" ); ?> your Extend-A-Story database.
    Verify that your information is correct.
    Once you are ready to proceed, click the
    <em><?php echo( $this->databaseExists ? "Upgrade" : "Install" ); ?></em> button.
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

        if (( !$this->databaseExists ) or ( $this->databaseVersion === 1 ))
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

        if (( !$this->databaseExists ) or ( $this->databaseVersion === 1 ))
        {

?>

    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Story Settings</td>
    </tr>

<?php

            if ( !$this->databaseExists )
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

        if ( $this->databaseExists )
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

        if (( $this->databaseExists ) or ( isset( $this->conflictingTables )))
        {

?>

<div class="dataLossWarning">

<?php

            if ( isset( $this->conflictingTables ))
            {

?>

    <p>Data in the following tables will be deleted if you proceed:</p>

<?php

                $this->conflictingTables->render();
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
    <input type="submit" name="continueButton" value="<?php echo( $this->databaseExists ? "Upgrade" : "Install" ); ?>">
</div>

<?php

    }
}

?>
