<?php
/*-----------------------------------------------------------------------------
*	File:			TreeNodeTest.php
*   Author:         Vincenzo Calabro' <vincenzo@calabrothers.com>
*   Copyright:      Calabrothers Corporation
-----------------------------------------------------------------------------*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Ds\TreeNode;
use Tests\Data\TreeNodeExample as Example;


final class TreeNodeTest extends TestCase
{

    public function testReadmeFunctionality(): void
    {
        // Let build some tree...
        /*
               A(1)
              /    \    
            B(2)   C(3)
                   /   \
                 D(4)  E(5)  

        */
        $oA = new TreeNode(1);
        $oB = new TreeNode(2);
        $oC = new TreeNode(3);
        $oD = new TreeNode(4);
        $oE = new TreeNode(5);

        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        // Compute 2 x sum(Descendants(A))
        $aValue = TreeNode::mapSet(
            $oA->getDescendants(), 
            function ($oValue) {
                return 2 * $oValue;
            }
        );
        $this->assertEquals($aValue->sum(), 28);

        // Get all even nodes
        $aEven = TreeNode::filterSet(
            $oA->getNodes(),
            function ($oValue) {
                return $oValue % 2 == 0;
            }
        );
        $this->assertEquals($aEven->count(),2);

        //echo "(".$aEven[0]->getValue().",".$aEven[1]->getValue().")\n";

        // echo $aValue->sum()."\n"; // 28
    
    }

    public function testLeavesFunctionality(): void
    {
        // Let build some tree...
        /*
                1
              /  \    
             2    3
                /   \
               4     5  

        */
        $oA = new TreeNode(1);
        $oB = new TreeNode(2);
        $oC = new TreeNode(3);
        $oD = new TreeNode(4);
        $oE = new TreeNode(5);
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        $this->assertEquals($oD->getParent(),$oC);
        $this->assertEquals($oE->getParent(),$oC);
        $this->assertEquals($oC->getParent(),$oA);
        $this->assertEquals($oB->getParent(),$oA);

        $aLeavesA = $oA->getLeaves();
        $this->assertEquals($aLeavesA->count(), 3);
        $aValue = TreeNode::mapSet($aLeavesA, 
            function ($oValue) {
                return $oValue;
            }
        );
        $this->assertEquals($aValue->sum(), 11);

        $aLeavesC = $oC->getLeaves();
        $this->assertEquals($aLeavesC->count(), 2);
        $aValue = TreeNode::mapSet($aLeavesC, 
            function ($oValue) {
                return $oValue;
            }
        );
        $this->assertEquals($aValue->sum(), 9);
    }

    public function testPrintFunctionality(): void
    {
        // Let build some tree...
        /*
                1
              /  \    
             2    3
                /   \
               4     5  

        */
        $oA = new TreeNode(1);
        $oB = new TreeNode(2);
        $oC = new TreeNode(3);
        $oD = new TreeNode(4);
        $oE = new TreeNode(5);
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        $this->assertEquals($oD->getParent(),$oC);
        $this->assertEquals($oE->getParent(),$oC);
        $this->assertEquals($oC->getParent(),$oA);
        $this->assertEquals($oB->getParent(),$oA);
        $this->assertEquals((string)$oA,
            "┌1\n".
            "└────2\n".
            "└────3\n".
            "    └────4\n".
            "    └────5\n"
        );

    }

    public function testRemoveFunctionality(): void
    {
        // Let build some tree...
        /*
                1
              /  \    
             2    3
                /   \
               4     5  

        */
        $oA = new TreeNode(1);
        $oB = new TreeNode(2);
        $oC = new TreeNode(3);
        $oD = new TreeNode(4);
        $oE = new TreeNode(5);
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        $this->assertEquals($oA->getValue(), 1);
        $this->assertEquals($oB->getValue(), 2);
        $this->assertEquals($oC->getValue(), 3);
        $this->assertEquals($oD->getValue(), 4);
        $this->assertEquals($oE->getValue(), 5);

        
        // Make Children of A to duplicate all B
        $aEven = $oA->detachDescendants(
            function ($oValue) {
                return $oValue % 2 == 0;
            }
        );

        /*
                1
                 \    
                  3       aEven = [ 2, 4]
                    \
                     5  

        */

        $this->assertEquals($oA->getValue(), 1);
        $this->assertEquals($oB->getValue(), 2);
        $this->assertEquals($oC->getValue(), 3);
        $this->assertEquals($oD->getValue(), 4);
        $this->assertEquals($oE->getValue(), 5);

        $this->assertEquals($aEven->count(), 2);
        $this->assertEquals($oA->getNumberOfNodes(), 3);

        $aValues = $aEven->map(
            function ($oNode) { 
                return $oNode->getValue(); 
            }
        );
        $this->assertEquals($aValues->sum(),   6);
    }
    
    public function testMapFunctionality(): void
    {
        // Let build some tree...
        /*
                1
              /  \    
             2    3
                /   \
               4     5  

        */
        $oA = new TreeNode(1);
        $oB = new TreeNode(2);
        $oC = new TreeNode(3);
        $oD = new TreeNode(4);
        $oE = new TreeNode(5);
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        $this->assertEquals($oA->getValue(), 1);
        $this->assertEquals($oB->getValue(), 2);
        $this->assertEquals($oC->getValue(), 3);
        $this->assertEquals($oD->getValue(), 4);
        $this->assertEquals($oE->getValue(), 5);

        
        // Make Children of A to duplicate all B
        $aMap = $oA->mapDescendants(
            function ($oValue) {
                return $oValue * 2;
            }
        );

        $this->assertEquals($oA->getValue(), 1);
        $this->assertEquals($oB->getValue(), 2);
        $this->assertEquals($oC->getValue(), 3);
        $this->assertEquals($oD->getValue(), 4);
        $this->assertEquals($oE->getValue(), 5);

        $this->assertEquals($aMap->count(), 4);
        $this->assertEquals($aMap->sum(),   2*2 + 3*2 + 4*2 + 5*2);
    }

    public function testFilterFunctionality(): void
    {
        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */
        $oA = new TreeNode('A');
        $oB = new TreeNode('B');
        $oC = new TreeNode('C');
        $oD = new TreeNode('D');
        $oE = new TreeNode('E');
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'E');

        // Find the vocals
        $aVocals = TreeNode::filterSet($oA->getNodes(),
        function ($oValue) : bool {
            return preg_match("/[aAeE]/", $oValue);
        });
        $this->assertEquals($aVocals->count(), 2);
        
        TreeNode::applySet(
            $aVocals, 
            function ($oValue) {
                return strtolower($oValue);
            }
        );
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'e');

        // Make Children of A to duplicate all B
        TreeNode::applySet(
            $oA->filterChildren(
                function ($oValue) : bool {
                    return preg_match("/[Bb]/", $oValue);
                }
            ),
            function ($oValue) {
                return str_repeat($oValue,2);
            }
        );
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'BB');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'e');
    }

    public function testApplyFunctionality(): void
    {
        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */
        $oA = new TreeNode('A');
        $oB = new TreeNode('B');
        $oC = new TreeNode('C');
        $oD = new TreeNode('D');
        $oE = new TreeNode('E');
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        // Apply functions
        $oA->apply( function ($oValue) { 
            return str_repeat($oValue, 3);
        });
        $this->assertEquals($oA->getValue(), 'AAA');

        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */

        // Apply function to all nodes
        $oC->applyNodes( function ($oValue) { 
            return strtolower(substr($oValue,0,1));
        });
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'b');
        $this->assertEquals($oC->getValue(), 'c');
        $this->assertEquals($oD->getValue(), 'd');
        $this->assertEquals($oE->getValue(), 'e');

        // Apply function to children
        $oC->applyChildren( function ($oValue) { 
            return strtoupper($oValue);
        });
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'b');
        $this->assertEquals($oC->getValue(), 'c');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'E');

        // Apply function to children and self
        $oC->applyChildrenAndSelf( function ($oValue) { 
            return str_repeat($oValue,2);
        });
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'b');
        $this->assertEquals($oC->getValue(), 'cc');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Apply function to children
        $oE->applyAncestors( function ($oValue) { 
            return str_repeat($oValue,2);
        });
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'b');
        $this->assertEquals($oC->getValue(), 'cccc');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Apply to single element
        $oC->apply(
            function ($oValue) {
                return strtolower(substr($oValue,0,1));
            }
        );
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'b');
        $this->assertEquals($oC->getValue(), 'c');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Apply to sibling and self
        $oC->applySiblingsAndSelf(
            function ($oValue) {
                return str_repeat($oValue,3);
            }
        );
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'bbb');
        $this->assertEquals($oC->getValue(), 'ccc');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Apply to sibling and self
        $oA->applyDescendants(
            function ($oValue) {
                return str_repeat($oValue,2);
            }
        );
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'bbbbbb');
        $this->assertEquals($oC->getValue(), 'cccccc');
        $this->assertEquals($oD->getValue(), 'DDDD');
        $this->assertEquals($oE->getValue(), 'EEEE');

        // Apply to sibling and self
        $oA->applyDescendantsAndSelf(
            function ($oValue) {
                return strtoupper(substr($oValue,0,1));
            }
        );
        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'E');

        // Apply to sibling and self
        $oE->applyAncestorsAndSelf(
            function ($oValue) {
                return strtolower(str_repeat($oValue,2));
            }
        );
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'cc');
        $this->assertEquals($oD->getValue(), 'D');
        $this->assertEquals($oE->getValue(), 'ee');

        // Apply to sibling and self
        $oE->applySiblings(
            function ($oValue) {
                return strtolower(str_repeat($oValue,3));
            }
        );
        $this->assertEquals($oA->getValue(), 'aa');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'cc');
        $this->assertEquals($oD->getValue(), 'ddd');
        $this->assertEquals($oE->getValue(), 'ee');

        // Apply to sibling and self
        TreeNode::applySet($oE->getNodes(),
            function ($oValue) {
                return strtolower(str_repeat($oValue,3));
            }
        );
        $this->assertEquals($oA->getValue(), 'aaaaaa');
        $this->assertEquals($oB->getValue(), 'bbb');
        $this->assertEquals($oC->getValue(), 'cccccc');
        $this->assertEquals($oD->getValue(), 'ddddddddd');
        $this->assertEquals($oE->getValue(), 'eeeeee');

        $this->assertTrue($oA->hasDescendants());
        $this->assertFalse($oE->hasDescendants());

        try {
            $oFail = new \Ds\Deque();
            $oFail->push('a');
            TreeNode::applySet($oFail,
                function ($oValue) {
                    return strtolower(str_repeat($oValue,3));
                }
            );
            $this->assertTrue(false);
        } catch(\Exception $e) {
            $this->assertTrue(true);
        }
    }


    public function testViewFunctionality(): void
    {
        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */
        $oA = new TreeNode('A');
        $oB = new TreeNode('B');
        $oC = new TreeNode('C');
        $oD = new TreeNode('D');
        $oE = new TreeNode('E');
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        // Checking
        $this->assertEquals($oA->getNumberOfChildren(), 2);
        $this->assertEquals($oA->getNumberOfDescendants(), 4);
        $this->assertEquals($oA->getNumberOfNodes(), 5);


        // Operating on children
        $aChilds = $oC->getChildren();
        $this->assertEquals($aChilds->count(), 2);
        foreach($aChilds as $oChild) {
            $oChild->setValue(str_repeat($oChild->getValue(),2));
        }
        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'B');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Operating on siblings
        $aSiblings = $oC->getSiblings();
        $this->assertEquals($aSiblings->count(), 1);
        foreach($aSiblings as $oSibling) {
            $oSibling->setValue(str_repeat($oSibling->getValue(),3));
        } 
        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'BBB');
        $this->assertEquals($oC->getValue(), 'C');
        $this->assertEquals($oD->getValue(), 'DD');
        $this->assertEquals($oE->getValue(), 'EE');

        // Checking size
        $this->assertEquals($oA->getNumberOfChildren(), 2);
        $this->assertEquals($oA->getNumberOfDescendants(), 4);
        $this->assertEquals($oA->getNumberOfNodes(), 5);

        // Operating on descendant
        $aDescendant = $oA->getDescendants();
        $this->assertEquals($aDescendant->count(), 4);
        foreach($aDescendant as $oDescendant) {
            $oDescendant->setValue(str_repeat($oDescendant->getValue(),2));
        } 
        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'BBBBBB');
        $this->assertEquals($oC->getValue(), 'CC');
        $this->assertEquals($oD->getValue(), 'DDDD');
        $this->assertEquals($oE->getValue(), 'EEEE');

        // Get all nodes
        $aNodes = $oA->getNodes();
        $this->assertEquals($aNodes->count(), 5);
        foreach($aNodes as $oNode) {
            $oNode->setValue(strtolower($oNode->getValue()));
        } 
        $this->assertEquals($oA->getValue(), 'a');
        $this->assertEquals($oB->getValue(), 'bbbbbb');
        $this->assertEquals($oC->getValue(), 'cc');
        $this->assertEquals($oD->getValue(), 'dddd');
        $this->assertEquals($oE->getValue(), 'eeee');

        // Get all anchestors
        $aAnchestors = $oE->getAncestors();
        $this->assertEquals($aAnchestors->count(), 2);
        foreach($aAnchestors as $oAnchestor) {
            $oAnchestor->setValue(strtoupper($oAnchestor->getValue()));
        } 
        $this->assertEquals($oA->getValue(), 'A');
        $this->assertEquals($oB->getValue(), 'bbbbbb');
        $this->assertEquals($oC->getValue(), 'CC');
        $this->assertEquals($oD->getValue(), 'dddd');
        $this->assertEquals($oE->getValue(), 'eeee');
    }

    public function testBasicFunctionality(): void
    {
        // Constructor with builtin class
        $oNode = new TreeNode(1);

        // Get Value
        $this->assertEquals(1, $oNode->getValue());

        // Is Root
        $this->assertTrue($oNode->isRoot());

        // Is Leaf
        $this->assertTrue($oNode->isLeaf());

        // Has Parent
        $this->assertFalse($oNode->hasParent());
        
        // Has Parent
        $this->assertFalse($oNode->hasChildren());

        // Has Siblings
        $this->assertFalse($oNode->hasSiblings());

        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */
        $oA = new TreeNode('A');
        $oB = new TreeNode('B');
        $oC = new TreeNode('C');
        $oD = new TreeNode('D');
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild('E');
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        // Checking
        $this->assertEquals($oA->getDepth(), 0);
        $this->assertEquals($oB->getDepth(), 1);
        $this->assertEquals($oC->getDepth(), 1);
        $this->assertEquals($oD->getDepth(), 2);

        $this->assertEquals($oA->getAltitude(), 2);
        $this->assertEquals($oB->getAltitude(), 0);
        $this->assertEquals($oC->getAltitude(), 1);
        $this->assertEquals($oD->getAltitude(), 0);

        // Checking
        $this->assertEquals($oA->getNumberOfChildren(), 2);
        $this->assertEquals($oA->getNumberOfDescendants(), 4);
        

        // Trying to detach a child, after checking that is disconnected
        $oC->detachChild($oD);
        /*
                A
              /  \    
             B    C
                    \           D
                     E  

        */

        // Is Root
        $this->assertTrue($oD->isRoot());

        // Is Leaf
        $this->assertTrue($oD->isLeaf());

        // Checking size
        $this->assertEquals($oA->getNumberOfDescendants(), 3);
        

        // Let us try to attach it as sibling of C
        /*
                A  ---
              /  \    \
             B    C    D
                    \           
                     E  

        */
        $oC->attachSibling($oD);

        // Checking size
        $this->assertEquals($oA->getNumberOfDescendants(), 4);
        // Checking size
        $this->assertEquals($oA->getNumberOfNodes(), 5);
        
        // Is Root
        $this->assertFalse($oD->isRoot());

        // Is Leaf
        $this->assertTrue($oD->isLeaf());

        // Let us try to detach a sibling of D
        /*
                A  ---
                 \    \
                  C    D      B 
                    \           
                     E  

        */
        $oD->detachSibling($oB);

        // Is Root
        $this->assertTrue($oB->isRoot());

        // Is Leaf
        $this->assertTrue($oB->isLeaf());

        // Testing getRoot
        $oRoot = $oD->getRoot();

        $this->assertEquals($oRoot->getValue(), $oA->getValue());     

        $this->assertTrue(true);

        // Copy constructor

        $oDC = new TreeNode($oD);
        $oDC->setValue(10);

        // Is Root
        $this->assertTrue($oDC->isRoot());

        // Is Leaf
        $this->assertTrue($oDC->isLeaf());

        $this->assertNotEquals($oDC->getValue(), $oD->getValue());

        // Testing that I cannot detach siblings
        $oDC->detachSibling(3);

        // or Childs
        $oDC->detachChild(3);

        $this->assertEquals("┌10\n", (string)$oDC);     

        $o20 = new TreeNode(20);
        $oDC->attachChild($o20);
        $this->assertEquals("┌10\n".
                            "└────20\n", (string)$oDC);  

        // Testing copy
        $oCD = $oDC->copy();
        $oCD->detachChild($o20);
        $this->assertEquals("┌10\n",        (string)$oCD); 
        $this->assertEquals("┌10\n".
                            "└────20\n",    (string)$oDC);  

        // Let build some tree...
        /*
                A
              /  \    
             B    C
                /   \
               D     E  

        */
        $oA = new TreeNode('A');
        $oB = new TreeNode('B');
        $oC = new TreeNode('C');
        $oD = new TreeNode('D');
        $oE = new TreeNode('E');
        
        // Build it...
        $oC->attachChild($oD);
        $oC->attachChild($oE);
        
        $oA->attachChild($oB);
        $oA->attachChild($oC);

        // Check for detach
        $oC->detach();
        /*
                A      C
                |    /   \
                B   D     E  
        */
        $this->assertEquals($oA->getNumberOfNodes(), 2);
        $this->assertEquals($oC->getNumberOfNodes(), 3);
        $this->assertTrue($oA->isRoot());
        $this->assertTrue($oC->isRoot());
        $this->assertTrue($oB->isLeaf());
        $this->assertTrue($oD->isLeaf());
        $this->assertTrue($oE->isLeaf());
        
        // attaching it again
        $oA->attachChild($oC);
        $this->assertEquals($oA->getNumberOfNodes(), 5);
        $this->assertEquals($oA->getNumberOfDescendants(), 4);
        
        $this->assertEquals($oC->getNumberOfNodes(), 5);
        $this->assertEquals($oC->getNumberOfDescendants(), 2);

    }

    public function testAsNodeHandler(): void 
    {
        // Constructor with objects class
        $oA  = new Example(1,'A');
        $oAN = new TreeNode($oA);

        $this->assertEquals($oAN->getValue()->nX, 1);
        $this->assertEquals($oAN->getValue()->szY, 'A');
        
        $oA->nX     = 2;
        $oA->szY    = 'AA';

        $this->assertEquals($oAN->getValue()->nX, 2);
        $this->assertEquals($oAN->getValue()->szY, 'AA');
        
        $oB  = new Example(1,'B');
        $oBN = new TreeNode($oB);
        $this->assertEquals($oBN->getValue()->nX, 1);
        $this->assertEquals($oBN->getValue()->szY, 'B');

        $oAN->attachChild($oB);

        // Checking
        $this->assertEquals($oAN->getNumberOfChildren(), 1);
        $this->assertEquals($oAN->getNumberOfDescendants(), 1);

        // Making a deep copy
        $oDN = $oAN->copy();

        $oA->nX     = 1;
        $oA->szY    = 'A';

        $this->assertEquals($oAN->getValue()->nX, 1);
        $this->assertEquals($oAN->getValue()->szY, 'A');

        $this->assertEquals($oDN->getValue()->nX, 2);
        $this->assertEquals($oDN->getValue()->szY, 'AA');
            
    }

    public function testErrorsFunctionality(): void
    {

        // Cannot get parent of root node...
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->getParent();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // Cannot build an empty object...
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(null);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // Cannot use functions with null...
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->attachChild(null);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // Cannot use unexisting magic functions...
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->applyUnknown(null);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // wrong format
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->applyunknown(null);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // now allowed method
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->sayUnknown(null);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // allowed method with wrong arguments
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->applyChildren('x');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // allowed method with wrong arguments
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->filterChildren('x');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // allowed method with wrong arguments
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->mapChildren('x');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        // allowed method with wrong arguments
        try {
            // Constructor with builtin class
            $oNode = new TreeNode(1);
            $oNode->detachChildren('x');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

    }
}