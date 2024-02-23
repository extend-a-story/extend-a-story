<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2024 Jeffrey J. Weston <jjweston@gmail.com>


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

namespace Extend_A_Story\HtmlElements;

class Checkbox extends HtmlElement
{
    private $name;
    private $label;
    private $value;
    private $checked;
    private $helpText;

    public function __construct( $name, $label, $value, $checked, $helpText )
    {
        $this->name     = $name;
        $this->label    = $label;
        $this->value    = $value;
        $this->checked  = $checked;
        $this->helpText = $helpText;
    }

    public function render()
    {

?>

<div class="inputField">
    <div>
        <input type="checkbox"
               id="<?php echo( htmlentities( $this->name )); ?>"
               name="<?php echo( htmlentities( $this->name )); ?>"
               value="<?php echo( htmlentities( $this->value )); ?>"
               <?php echo( $this->checked ? "checked" : "" ); ?>>
        <label for="<?php echo( htmlentities( $this->name )); ?>">
            <?php echo( htmlentities( $this->label )); ?>
        </label>
        <span class="helpIcon" title="<?php echo( htmlentities( $this->helpText )); ?>">?</span>
    </div>
</div>

<?php

    }
}

?>
