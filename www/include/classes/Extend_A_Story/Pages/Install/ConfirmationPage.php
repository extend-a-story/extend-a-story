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

class ConfirmationPage extends InstallPage
{
    public function getNextPage()
    {
        if ( isset( $this->backButton )) return new StorySettingsPage();
        throw new StoryException( "Unrecognized navigation from confirmation page." );
    }

    public function validate()
    {
        $result = StorySettingsPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getSubtitle()
    {
        return "Confirmation";
    }

    protected function renderMain()
    {
        $databaseHost           = Util::getStringParamDefault( $_POST, "databaseHost",           "" );
        $databaseUsername       = Util::getStringParamDefault( $_POST, "databaseUsername",       "" );
        $databaseName           = Util::getStringParamDefault( $_POST, "databaseName",           "" );
        $adminLoginName         = Util::getStringParamDefault( $_POST, "adminLoginName",         "" );
        $adminDisplayName       = Util::getStringParamDefault( $_POST, "adminDisplayName",       "" );
        $settingsStoryName      = Util::getStringParamDefault( $_POST, "settingsStoryName",      "" );
        $settingsSiteName       = Util::getStringParamDefault( $_POST, "settingsSiteName",       "" );
        $settingsStoryHome      = Util::getStringParamDefault( $_POST, "settingsStoryHome",      "" );
        $settingsSiteHome       = Util::getStringParamDefault( $_POST, "settingsSiteHome",       "" );
        $settingsReadEpisodeUrl = Util::getStringParamDefault( $_POST, "settingsReadEpisodeUrl", "" );
        $settingsAdminEmail     = Util::getStringParamDefault( $_POST, "settingsAdminEmail",     "" );
        $settingsMaxLinks       = Util::getStringParamDefault( $_POST, "settingsMaxLinks",       "" );
        $settingsMaxEditDays    = Util::getStringParamDefault( $_POST, "settingsMaxEditDays",    "" );

?>

<table>
    <tr>
        <td colspan="2" class="sectionHeader">Database Connection</td>
    </tr>
    <tr>
        <td class="header">Host</td>
        <td><?php echo( htmlentities( $databaseHost )); ?></td>
    </tr>
    <tr>
        <td class="header">Username</td>
        <td><?php echo( htmlentities( $databaseUsername )); ?></td>
    </tr>
    <tr>
        <td class="header">Password</td>
        <td>**********</td>
    </tr>
    <tr>
        <td class="header">Database</td>
        <td><?php echo( htmlentities( $databaseName )); ?></td>
    </tr>
    <tr>
        <td colspan="2"><hr/></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Administrator Account</td>
    </tr>
    <tr>
        <td class="header">Login Name</td>
        <td><?php echo( htmlentities( $adminLoginName )); ?></td>
    </tr>
    <tr>
        <td class="header">Display Name</td>
        <td><?php echo( htmlentities( $adminDisplayName )); ?></td>
    </tr>
    <tr>
        <td class="header">Password</td>
        <td>**********</td>
    </tr>
    <tr>
        <td colspan="2"><hr/></td>
    </tr>
    <tr>
        <td colspan="2" class="sectionHeader">Story Settings</td>
    </tr>
    <tr>
        <td class="header">Story Name</td>
        <td><?php echo( htmlentities( $settingsStoryName )); ?></td>
    </tr>
    <tr>
        <td class="header">Site Name</td>
        <td><?php echo( htmlentities( $settingsSiteName )); ?></td>
    </tr>
    <tr>
        <td class="header">Story Home</td>
        <td><?php echo( htmlentities( $settingsStoryHome )); ?></td>
    </tr>
    <tr>
        <td class="header">Site Home</td>
        <td><?php echo( htmlentities( $settingsSiteHome )); ?></td>
    </tr>
    <tr>
        <td class="header">Read Episode URL</td>
        <td><?php echo( htmlentities( $settingsReadEpisodeUrl )); ?></td>
    </tr>
    <tr>
        <td class="header">Admin Email</td>
        <td><?php echo( htmlentities( $settingsAdminEmail )); ?></td>
    </tr>
    <tr>
        <td class="header">Max Links</td>
        <td><?php echo( htmlentities( $settingsMaxLinks )); ?></td>
    </tr>
    <tr>
        <td class="header">Max Edit Days</td>
        <td><?php echo( htmlentities( $settingsMaxEditDays )); ?></td>
    </tr>
</table>

<?php

        $tableNames = Tables::getTableNames();
        if ( count( $tableNames ) > 0 )
        {
            $tables = UnorderedList::buildFromStringArray( $tableNames );

?>

<div class="dataLossWarning">

    <p>!!! YOU WILL LOSE DATA IF YOU PROCEED !!!</p>

    <p>The following tables will be deleted:</p>

<?php

            $tables->render();

?>

</div>

<?php

        }

?>

<div class="submit">
    <input type="hidden" name="pageName" value="Confirmation" />
    <input type="submit" name="backButton" value="Back" />
</div>

<?php

    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }
}

?>
