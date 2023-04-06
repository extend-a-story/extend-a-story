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

namespace Extend_A_Story\HtmlElements;

class InputField extends HtmlElement
{
    private $name;
    private $label;
    private $type;
    private $value;
    private $width;
    private $limit;
    private $threshold;
    private $helpText;

    public function __construct( $name, $label, $type, $value, $width, $limit, $threshold, $helpText )
    {
        $this->name      = $name;
        $this->label     = $label;
        $this->type      = $type;
        $this->value     = $value;
        $this->width     = isset( $width ) ? $width . "ch" : "100%";
        $this->limit     = $limit;
        $this->threshold = isset( $threshold ) ? $threshold : $this->limit;
        $this->helpText  = $helpText;
    }

    public function render()
    {

?>

<div class="inputField">
    <div>
        <label for="<?php echo( htmlentities( $this->name )); ?>">
            <?php echo( htmlentities( $this->label )); ?>:
        </label>

        <span class="helpIcon" title="<?php echo( htmlentities( $this->helpText )); ?>">?</span>
    </div>

    <input type    = "<?php echo( htmlentities( $this->type     )); ?>"
           id      = "<?php echo( htmlentities( $this->name     )); ?>"
           name    = "<?php echo( htmlentities( $this->name     )); ?>"
           value   = "<?php echo( htmlentities( $this->value    )); ?>"
           style   = "width: <?php echo( htmlentities( $this->width )); ?>; box-sizing: border-box;"
           oninput = "updateInputFieldLimit(
                         '<?php echo( htmlentities( $this->name )); ?>',
                          <?php echo( isset( $this->limit     ) ? htmlentities( $this->limit     ) : "null" ); ?>,
                          <?php echo( isset( $this->threshold ) ? htmlentities( $this->threshold ) : "null" ); ?> )">

<?php

        $limitId = $this->name . "-limit";
        $limitClass = "limit-under";
        $limitContent = null;

        if ( isset( $this->limit ))
        {
            $limitRemaining = $this->limit - strlen( $this->value );

            if ( $limitRemaining <= $this->threshold )
            {
                if ( $limitRemaining >= 0 )
                {
                    $limitContent = "Remain: " . $limitRemaining;
                }
                else
                {
                    $limitClass = "limit-over";
                    $limitContent = "Over: " . ( -$limitRemaining );
                }
            }
        }

?>

    <div id="<?php echo( htmlentities( $limitId )); ?>" class="<?php echo( htmlentities( $limitClass )); ?>">
        <?php echo( isset( $limitContent ) ? htmlentities( $limitContent ) : "&nbsp;" ); ?>
    </div>
</div>

<?php

    }
}

?>
