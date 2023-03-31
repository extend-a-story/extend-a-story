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

use \Extend_A_Story\HtmlElements\RawText;
use \Extend_A_Story\HtmlElements\UnorderedList;
use \Extend_A_Story\StoryException;
use \Extend_A_Story\Util;

class AuthorizationPage extends InstallPage
{
    public static function validatePage()
    {
        $installTokenPost   = Util::getStringParamDefault( $_POST,   "installToken", null );
        $installTokenCookie = Util::getStringParamDefault( $_COOKIE, "installToken", null );
        $installTokenLocal  = isset( $installTokenPost ) ? $installTokenPost : $installTokenCookie;

        // force the user to configure the install token if the installation token is not set or does not match
        global $configInstallToken;
        if (( !isset( $configInstallToken )) or ( $configInstallToken !== $installTokenLocal ))
        {
            $error = null;

            $pageName = Util::getStringParamDefault( $_POST, "pageName", null );
            if ( $pageName === "Authorization" )
            {
                $message = "You must verify that you are the owner of this site.";
                $error = new UnorderedList( [ new RawText( $message ) ] );
            }

            return new AuthorizationPage( $error );
        }

        // allow installation to proceed
        return null;
    }

    public function __construct( $error = null )
    {
        parent::__construct( $error );
    }

    protected function getNextPage()
    {
        if ( isset( $this->continueButton )) return new StartPage();
        throw new StoryException( "Unrecognized navigation from authorization page." );
    }

    protected function getSubtitle()
    {
        return "Authorization Required";
    }

    protected function getFields()
    {
        return array( "pageName", "backButton", "continueButton" );
    }

    protected function renderMain()
    {

?>

<p>
    You are attempting to install Extend-A-Story. You cannot proceed until you have verified that you are the owner of
    this site by updating your configuration file. This is the location of your configuration file:
</p>

<pre>
<?php echo( htmlentities( realpath( __DIR__ . "/../../../../config/Configuration.php" ))); ?>
</pre>

<p>
    Find the line that begins with <code>$configInstallToken</code> and change it to read as follows:
</p>

<pre>
$configInstallToken = "<?php echo( htmlentities( $this->installToken )); ?>";
</pre>

<p>
    Once this is done, click the <em>Continue</em> button to proceed.
</p>

<div class="submit">
    <input type="hidden" name="pageName" value="Authorization">
    <input type="submit" name="continueButton" value="Continue">
</div>

<?php

    }
}

?>
