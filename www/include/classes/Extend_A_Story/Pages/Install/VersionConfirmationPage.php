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

use \Extend_A_Story\StoryException;
use \Extend_A_Story\Upgrade\Version;
use \Extend_A_Story\Util;

class VersionConfirmationPage extends InstallPage
{
    public static function validatePage()
    {
        $result = VersionConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;

        $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
        $version = Version::getVersion();
        if ( $databaseVersion !== $version->getDatabaseVersion() ) return new VersionConfirmationPage();

        return null;
    }

    private static function validatePreviousPage()
    {
        $result = SelectTaskPage::validatePage();
        if ( isset( $result )) return $result;
        return null;
    }

    private $databaseVersion;
    private $databaseExists;
    private $storyVersion;

    public function validate()
    {
        $result = VersionConfirmationPage::validatePreviousPage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton )) return new SelectTaskPage();

        if ( isset( $this->continueButton ))
        {
            $databaseVersion = Util::getIntParam( $_POST, "databaseVersion" );
            if ( $databaseVersion === 1 ) return new AdminAccountPage();
            if (( $databaseVersion > 1 ) and ( $databaseVersion < 4 )) return new ConfirmationPage();
            else throw new StoryException( "Unrecognized database version." );
        }

        throw new StoryException( "Unrecognized navigation from version confirmation page." );
    }

    protected function getSubtitle()
    {
        return "Version Confirmation";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "databaseVersion" );
    }

    protected function preRender()
    {
        $version = Version::getVersion();
        $this->databaseVersion = $version->getDatabaseVersion();
        $this->databaseExists  = $version->checkDatabase();
        $this->storyVersion    = $version->getStoryVersion();
    }

    protected function renderMain()
    {

?>

<p>

<?php

        if ( !$this->databaseExists )
        {

?>

An Extend-A-Story database was not found. Check your database connection settings. If you are installing Extend-A-Story
into an empty database, go back and select <em>Install New Database</em> instead.

<?php

        }
        else
        {
            if ( $this->databaseVersion === 4 )
            {

?>

Your Extend-A-Story database is already the latest version. There is no need to upgrade.

<?php

            }
            else
            {

?>

You are upgrading from version <?php echo( htmlentities( $this->storyVersion )); ?> of Extend-A-Story.

<?php

            }
        }

?>

</p>

<div class="submit">
    <input type="hidden" name="pageName" value="VersionConfirmation">
    <input type="hidden" name="databaseVersion" value="<?php echo( htmlentities( $this->databaseVersion )); ?>">
    <input type="submit" name="backButton" value="Back">

<?php

        if (( $this->databaseExists ) and ( $this->databaseVersion !== 4 ))
        {

?>

    <input type="submit" name="continueButton" value="Continue">

<?php

        }

?>

</div>

<?php

    }
}

?>
