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

class ConfirmationPage extends InstallPage
{
    public function validate()
    {
        $result = DatabaseConnectionPage::validatePage();

        if ( isset( $result ))
        {
            return $result;
        }

        return $this;
    }

    protected function renderMain()
    {

?>

<h2>Confirmation</h2>

<table>
    <tr>
        <th>Field Name</th>
        <th>Value</th>
    </tr>

<?php

$keys = array_keys( $_POST );

for ( $i = 0; $i < count( $keys ); $i++ )
{
    $key = $keys[ $i ];
    $value = $_POST[ $key ];

?>

    <tr>
        <td><?php echo( htmlentities( $key )); ?></td>
        <td><?php echo( htmlentities( $value )); ?></td>
    </tr>

<?php

}

?>

</table>

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
