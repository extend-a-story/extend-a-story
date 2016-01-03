<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2016 Jeffrey J. Weston


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

use \Exception;
use \PDO;

use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\Util;

class DatabaseConnectionPage extends InstallPage
{
    private $error;

    public static function validate()
    {
        $databaseHost     = Util::getStringParamDefault( $_POST, "databaseHost",     "" );
        $databaseUsername = Util::getStringParamDefault( $_POST, "databaseUsername", "" );
        $databasePassword = Util::getStringParamDefault( $_POST, "databasePassword", "" );
        $databaseName     = Util::getStringParamDefault( $_POST, "databaseName",     "" );

        $errors = array();

        if ( strlen( $databaseHost ) == 0 )
        {
            $errors[] = new RawText( "Host must be set." );
        }

        if ( strlen( $databaseUsername ) == 0 )
        {
            $errors[] = new RawText( "Username must be set." );
        }

        if ( strlen( $databasePassword ) == 0 )
        {
            $errors[] = new RawText( "Password must be set." );
        }

        if ( strlen( $databaseName ) == 0 )
        {
            $errors[] = new RawText( "Database must be set." );
        }

        if ( count( $errors ) == 0 )
        {
            global $dbHost, $dbUser, $dbPassword, $dbDatabase;

            $dbHost     = $databaseHost;
            $dbUser     = $databaseUsername;
            $dbPassword = $databasePassword;
            $dbDatabase = $databaseName;

            try
            {
                $dbConnection = Util::getDbConnection();
            }
            catch ( Exception $e )
            {
                $errors[] = new RawText( "Unable to connect to database: " . $e->getMessage() );
            }
        }

        if ( count( $errors ) == 0 )
        {
            $dbStatement = $dbConnection->prepare( "SHOW TABLES" );
            $dbStatement->execute();
            $tables = $dbStatement->fetchAll( PDO::FETCH_NUM );

            if ( count( $tables ) > 0 )
            {
                $errors[] = new RawText( "Database already contains tables." );
            }
        }

        if ( count( $errors ) > 0 )
        {
            $page = new DatabaseConnectionPage( new UnorderedList( $errors ));
            $page->renderMain();
            return false;
        }

        return true;
    }

    public function __construct( $error = null )
    {
        $this->error = $error;
    }

    protected function renderMain()
    {
        $databaseHost     = Util::getStringParamDefault( $_POST, "databaseHost",     "" );
        $databaseUsername = Util::getStringParamDefault( $_POST, "databaseUsername", "" );
        $databasePassword = Util::getStringParamDefault( $_POST, "databasePassword", "" );
        $databaseName     = Util::getStringParamDefault( $_POST, "databaseName",     "" );

?>

<h2>Database Connection</h2>

<?php

        if ( isset( $this->error ))
        {

?>

<div class="error"><?php echo( $this->error->render() ); ?></div>

<?php

        }

?>

<div class="inputField">
    <div><label for="databaseHost">Host:</label></div>
    <div>
        <span class="inputFieldHelpButton"
              onclick="toggleVisibility( 'databaseHost-help' );">Help</span>
    </div>
    <div id="databaseHost-help" class="inputFieldHelpContents" style="display: none;">
        This is the host name for your database. This is typically "localhost" unless your
        database is running on a different server than your web site. If you are running
        Extend-A-Story in a shared hosting environment, your hosting provider should provide
        you with your database host name.
    </div>
    <input type="text" id="databaseHost" name="databaseHost"
           value="<?php echo( htmlentities( $databaseHost )); ?>" />
</div>

<div class="inputField">
    <div><label for="databaseUsername">Username:</label></div>
    <div>
        <span class="inputFieldHelpButton"
              onclick="toggleVisibility( 'databaseUsername-help' );">Help</span>
    </div>
    <div id="databaseUsername-help" class="inputFieldHelpContents" style="display: none;">
        This is the username that will be used to connect to your database during the
        installation process. This user will need all permissions to your Extend-A-Story
        database.
    </div>
    <input type="text" id="databaseUsername" name="databaseUsername"
           value="<?php echo( htmlentities( $databaseUsername )); ?>" />
</div>

<div class="inputField">
    <div><label for="databasePassword">Password:</label></div>
    <div>
        <span class="inputFieldHelpButton"
              onclick="toggleVisibility( 'databasePassword-help' );">Help</span>
    </div>
    <div id="databasePassword-help" class="inputFieldHelpContents" style="display: none;">
        This is the password that will be used to connect to your database during the
        installation process.
    </div>
    <input type="password" id="databasePassword" name="databasePassword"
           value="<?php echo( htmlentities( $databasePassword )); ?>" />
</div>

<div class="inputField">
    <div><label for="databaseName">Database:</label></div>
    <div>
        <span class="inputFieldHelpButton"
              onclick="toggleVisibility( 'databaseName-help' );">Help</span>
    </div>
    <div id="databaseName-help" class="inputFieldHelpContents" style="display: none;">
        This is the name of your Extend-A-Story database. The tables needed by Extend-A-Story will
        be created in this database.
    </div>
    <input type="text" id="databaseName" name="databaseName"
           value="<?php echo( htmlentities( $databaseName )); ?>" />
</div>

<div class="submit">
    <input type="hidden" name="pageName" value="DatabaseConnection" />
    <input type="submit" name="backButton" value="Back" />
    <input type="submit" name="continueButton" value="Continue" />
</div>

<?php

    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton",
                      "databaseHost", "databaseUsername", "databasePassword", "databaseName" );
    }
}

?>
