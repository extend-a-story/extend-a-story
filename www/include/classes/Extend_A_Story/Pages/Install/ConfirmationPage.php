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
    public static function validate()
    {
        $result = StorySettingsPage::validatePage();
        if ( isset( $result )) return $result;
        return new ConfirmationPage();
    }

    public static function validatePage()
    {
        $result = StorySettingsPage::validatePage();
        if ( isset( $result )) return $result;
        return null;
    }

    private $databaseExists;
    private $databaseVersion;
    private $storyVersion;
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
    private $previousButton;

    protected function getPageTitle() { return ( $this->databaseExists ? "Upgrade" : "Install" ) . " Confirmation"; }

    protected function preRender()
    {
        $version = Version::getVersion();
        $this->databaseVersion = $version->getDatabaseVersion();
        $this->databaseExists  = $version->checkDatabase();
        $this->storyVersion    = $version->getStoryVersion();

        $this->databaseHost     = Util::getStringParam( $_POST, "databaseHost"     );
        $this->databaseUsername = Util::getStringParam( $_POST, "databaseUsername" );
        $this->databaseName     = Util::getStringParam( $_POST, "databaseName"     );

        if (( !$this->databaseExists ) or ( $this->databaseVersion === 1 ))
        {
            $this->adminLoginName   = Util::getStringParam( $_POST, "adminLoginName"   );
            $this->adminDisplayName = Util::getStringParam( $_POST, "adminDisplayName" );

            if ( !$this->databaseExists )
            {
                $this->settingsStoryName      = Util::getStringParam( $_POST, "settingsStoryName"      );
                $this->settingsSiteName       = Util::getStringParam( $_POST, "settingsSiteName"       );
                $this->settingsStoryHome      = Util::getStringParam( $_POST, "settingsStoryHome"      );
                $this->settingsSiteHome       = Util::getStringParam( $_POST, "settingsSiteHome"       );
                $this->settingsReadEpisodeUrl = Util::getStringParam( $_POST, "settingsReadEpisodeUrl" );
                $this->settingsAdminEmail     = Util::getStringParam( $_POST, "settingsAdminEmail"     );
                $this->settingsMaxLinks       = Util::getStringParam( $_POST, "settingsMaxLinks"       );
            }

            $this->settingsMaxEditDays = Util::getStringParam( $_POST, "settingsMaxEditDays" );
        }

        // determine if any of the tables that we are going to add already exist in the database
        $addedTableNames = $this->databaseExists ? $version->getAddedTableNames() : Database::getStoryTableNames();
        $conflictingTableNames = array_intersect( $addedTableNames, Database::getDatabaseTableNames() );
        sort( $conflictingTableNames );
        $this->conflictingTables = empty( $conflictingTableNames ) ? null : UnorderedList::buildFromStringArray( $conflictingTableNames );

        // determine the previous page
        $this->previousButton =
                (( !$this->databaseExists ) || ( $this->databaseVersion === 1 )) ? "storySettingsButton" : "versionConfirmationButton";
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
    <input type="submit" name="<?php echo( htmlentities( $this->previousButton )); ?>" value="Back">
    <input type="submit" name="completedButton" value="<?php echo( $this->databaseExists ? "Upgrade" : "Install" ); ?>">
</div>

<?php

    }
}

?>
