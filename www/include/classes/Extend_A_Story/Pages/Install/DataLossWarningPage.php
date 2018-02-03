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
use \Extend_A_Story\HtmlElements\Checkbox;
use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class DataLossWarningPage extends InstallPage
{
    public static function validatePage()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;

        $tableNames = Tables::getConflictingTableNames();
        if ( count( $tableNames ) > 0 )
        {
            $allowDataLoss = Util::getStringParamDefault( $_POST, "allowDataLoss", null );
            if ( !isset( $allowDataLoss ))
            {
                $error = null;

                $pageName = Util::getStringParamDefault( $_POST, "pageName", null );
                if ( $pageName === "DataLossWarning" )
                {
                    $message = "You must either confirm that you are okay with losing data or go back and specify a " .
                               "different database.";
                    $error = new UnorderedList( [ new RawText( $message ) ] );
                }

                return new DataLossWarningPage( $error );
            }
        }

        return null;
    }

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    public function validate()
    {
        $result = DatabaseConnectionPage::validatePage();
        if ( isset( $result )) return $result;
        return $this;
    }

    protected function getNextPage()
    {
        if ( isset( $this->backButton     )) return new DatabaseConnectionPage();
        if ( isset( $this->continueButton )) return new AdminAccountPage();
        throw new StoryException( "Unrecognized navigation from data loss warning page." );
    }

    protected function getSubtitle()
    {
        return "Data Loss Warning";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "allowDataLoss" );
    }

    protected function preRender()
    {
        $allowDataLoss = Util::getStringParamDefault( $_POST, "allowDataLoss", null );

        $this->allowDataLossCheckbox =
                new Checkbox( "allowDataLoss", "Allow Data Loss", "true", isset( $allowDataLoss ),
                              "Clicking this checkbox will allow you to proceed at the expense of losing data in " .
                              "the database you specified. Do not click this checkbox unless you are okay with " .
                              "losing data." );

        $this->tables = null;
        $tableNames = Tables::getConflictingTableNames();
        if ( count( $tableNames ) > 0 )
        {
            $this->tables = UnorderedList::buildFromStringArray( $tableNames );
        }
    }

    protected function renderMain()
    {
        if ( isset( $this->tables ))
        {

?>

<p>
    The database you specified already contains the following tables:
</p>

<?php

            $this->tables->render();

?>

<p>
    These tables will be deleted if you proceed with the installation. If you do not wish to lose data, please go back
    and specify a different database. If you wish to proceed, click the checkbox below to confirm that you are okay with
    losing data. We strongly suggest performing a backup before you proceed.
</p>

<?php

        }

        $this->allowDataLossCheckbox->render();

?>

<div class="submit">
    <input type="hidden" name="pageName" value="DataLossWarning" />
    <input type="submit" name="backButton" value="Back" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }

    private $allowDataLossCheckbox;
}

?>
