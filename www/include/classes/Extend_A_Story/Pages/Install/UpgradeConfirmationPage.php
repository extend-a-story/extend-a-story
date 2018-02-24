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

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Upgrade\Version;
use \Extend_A_Story\Util;

class UpgradeConfirmationPage extends InstallPage
{
    public static function validatePage()
    {
        $result = UpgradeConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return null;
    }

    private static function validatePreviousPage()
    {
        $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
        if ( $databaseVersion === 1 )
        {
            $result = AdminAccountPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 ))
        {
            $result = VersionConfirmationPage::validatePage();
            if ( isset( $result )) return $result;
        }
        else throw new StoryException( "Unrecognized database version." );

        return null;
    }

    private $databaseVersion;
    private $storyVersion;
    private $adminLoginName;
    private $adminDisplayName;

    public function validate()
    {
        $result = UpgradeConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton ))
        {
            $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
            if ( $databaseVersion === 1 ) return new AdminAccountPage();
            if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 )) return new VersionConfirmationPage();
            else throw new StoryException( "Unrecognized database version." );
        }

        if ( isset( $this->continueButton )) return new CompletedPage();

        throw new StoryException( "Unrecognized navigation from upgrade confirmation page." );
    }

    protected function getSubtitle()
    {
        return "Upgrade Confirmation";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }

    protected function preRender()
    {
        $version = Version::getVersion();
        $this->databaseVersion = $version->getDatabaseVersion();
        $this->storyVersion    = $version->getStoryVersion();

        if ( $this->databaseVersion === 1 )
        {
            $this->adminLoginName   = Util::getStringParamDefault( $_POST, "adminLoginName",   "" );
            $this->adminDisplayName = Util::getStringParamDefault( $_POST, "adminDisplayName", "" );
        }
    }

    protected function renderMain()
    {

?>

<p>
    We have gathered all of the information we need to upgrade your Extend-A-Story database. Verify that your
    information is correct. Once you are ready to proceed, click the <em>Upgrade</em> button.
</p>

<table>
    <tr>
        <td colspan="2" class="sectionHeader">Version</td>
    </tr>
    <tr>
        <td class="header">Current Version</td>
        <td><?php echo( htmlentities( $this->storyVersion )); ?></td>
    </tr>

<?php

        if ( $this->databaseVersion === 1 )
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

?>

</table>

<div class="dataLossWarning">
    <p>We strongly suggest performing a backup before you proceed.</p>
</div>

<div class="submit">
    <input type="hidden" name="pageName" value="UpgradeConfirmation">
    <input type="submit" name="backButton" value="Back">
    <input type="submit" name="continueButton" value="Upgrade">
</div>

<?php

    }
}

?>
