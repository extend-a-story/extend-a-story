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

use \Extend_A_Story\Data\Database;
use \Extend_A_Story\StoryException;

class UpgradeConfirmationPage extends InstallPage
{
    private $databaseVersion;

    public function validate()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton )) return new SelectTaskPage();
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
        $this->databaseVersion = Database::getDatabaseVersion();
    }

    protected function renderMain()
    {

?>

<p>
    Database Version: <?php echo( htmlentities( $this->databaseVersion )); ?>
</p>

<div class="submit">
    <input type="hidden" name="pageName" value="UpgradeConfirmation">
    <input type="submit" name="backButton" value="Back">
</div>

<?php

    }
}

?>
