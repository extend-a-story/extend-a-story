<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2018 Jeffrey J. Weston <jjweston@gmail.com>


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

use \Extend_A_Story\Data\Tables;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class InstallConfirmationPage extends InstallPage
{
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

    public function validate()
    {
        $result = StorySettingsPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton     )) return new StorySettingsPage();
        if ( isset( $this->continueButton )) return new InstallCompletedPage();
        throw new StoryException( "Unrecognized navigation from install confirmation page." );
    }

    protected function getSubtitle()
    {
        return "Install Confirmation";
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
        $this->tables                 = null;

        $tableNames = Tables::getConflictingTableNames();
        if ( count( $tableNames ) > 0 )
        {
            $this->tables = UnorderedList::buildFromStringArray( $tableNames );
        }
    }

    protected function renderMain()
    {

?>

<p>
    Verify that your settings are correct. Once you are ready to proceed, click the <em>Install</em> button.
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
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Story Settings</td>
    </tr>
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
    <tr>
        <td class="header">Max Edit Days</td>
        <td><?php echo( htmlentities( $this->settingsMaxEditDays )); ?></td>
    </tr>
</table>

<?php

        if ( isset( $this->tables ))
        {

?>

<div class="dataLossWarning">

    <p>!!! YOU WILL LOSE DATA IF YOU PROCEED !!!</p>

    <p>The following tables will be deleted:</p>

<?php

            $this->tables->render();

?>

</div>

<?php

        }

?>

<div class="submit">
    <input type="hidden" name="pageName" value="InstallConfirmation">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton" value="Install">
</div>

<?php

    }
}

?>
