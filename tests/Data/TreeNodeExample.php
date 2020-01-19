<?php
/*-----------------------------------------------------------------------------
*	File:			TreeNodeExample.php
*   Author:         Vincenzo Calabro' <vincenzo@calabrothers.com>
*   Copyright:      Calabrothers Corporation
-----------------------------------------------------------------------------*/

namespace Tests\Data;

class TreeNodeExample {
    public $nX;
    public $szY;
    public function __construct(int $nX, string $szY) {
        $this->nX   = $nX;
        $this->szY  = $szY;
    }

    public function myMultiply(int $nZ) {
        $this->nX *= $nZ;
        $this->szY = implode("+", array_fill(0, $nZ,$this->szY));
    }

    public function __toString():string {
        return "($this->nX|$this->szY)";
    }
}

?>