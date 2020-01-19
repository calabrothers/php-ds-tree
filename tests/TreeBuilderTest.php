<?php
/*-----------------------------------------------------------------------------
*	File:			TreeBuilderTest.php
*   Author:         Vincenzo Calabro' <vincenzo@calabrothers.com>
*   Copyright:      Calabrothers Corporation
-----------------------------------------------------------------------------*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Ds\TreeBuilder;
use Tests\Data\TreeNodeExample as Example;


final class TreeBuilderTest extends TestCase
{

    public function testAdvancedFunctionality(): void
    {

        $oTreeC = new TreeBuilder(
            function (int $nX, string $szY) : Example { 
                return new Example($nX, $szY); 
            }
        );
        
        $oTreeC
            ->begin(1, 'one')
                ->begin(2, 'two')
                ->end()
                ->begin(3, 'three')
                    ->begin(4, 'four')
                        ->myMultiply(2)  // This will call the method of Example! :)
                    ->end()
                    ->begin(5, 'five')
                        ->myMultiply(3)
                    ->end()
                ->end()
            ->end();

        $this->assertEquals((string)$oTreeC,
            "┌(1|one)\n".
            "└────(2|two)\n".
            "└────(3|three)\n".
            "    └────(8|four+four)\n".
            "    └────(15|five+five+five)\n"
        );

    }

    public function testStandardFunctionality(): void
    {

        $oTreeC = new TreeBuilder(
            function (int $nNumber) : int { 
                return $nNumber; 
            }
        );
        
        $oTreeC
            ->begin(1)
                ->begin(2)
                ->end()
                ->begin(3)
                    ->begin(4)
                    ->end()
                    ->begin(5)
                    ->end()
                ->end()
            ->end();
        
        //echo $oTreeC;
        
        $this->assertEquals((string)$oTreeC,
            "┌1\n".
            "└────2\n".
            "└────3\n".
            "    └────4\n".
            "    └────5\n"
        );

        $oTreeC = new TreeBuilder(
            function (int $nNumber) : int { 
                return 2 * $nNumber; 
            }
        );
        
        $oTreeC
            ->begin(1)
                ->begin(2)
                ->end()
                ->begin(3)
                    ->begin(4)
                    ->end()
                    ->begin(5)
                    ->end()
                ->end()
            ->end();
        
        $this->assertEquals((string)$oTreeC,
            "┌2\n".
            "└────4\n".
            "└────6\n".
            "    └────8\n".
            "    └────10\n"
        );


        $this->assertTrue($oTreeC->getTip()->isRoot());
        $this->assertTrue($oTreeC->getRoot()->isRoot());


        $oTreeC = new TreeBuilder(
            function (int $nNumber) : int { 
                return 2 * $nNumber; 
            }
        );
        
        $oTreeC
            ->begin(1)
                ->begin(2);

        $this->assertFalse($oTreeC->getTip()->isRoot());
        $this->assertTrue($oTreeC->getRoot()->isRoot());     

        $oTreeC->resetRoot();
        $this->assertTrue($oTreeC->getTip()->isRoot());
        $this->assertTrue($oTreeC->getRoot()->isRoot());

    }

    public function testInvalidUse(): void
    {

        $oTreeC = new TreeBuilder(
            function (int $nNumber) : int { 
                return 2 * $nNumber; 
            }
        );

        // Using an empty tree
        try{ 
            $oTreeC->end();
            $this->assertTrue(false);
        } catch(\Exception $e) {
            $this->assertTrue(true);
        }

        // Invalid number of arguments
        try{ 
            $oTreeC->begin(1, 2, 3);
            $this->assertTrue(false);
        } catch(\Exception $e) {
            $this->assertTrue(true);
        }

    }

}
?>