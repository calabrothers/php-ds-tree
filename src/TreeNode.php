<?php
/*-----------------------------------------------------------------------------
*	File:           TreeNode.php
*   Author:         Vincenzo Calabro' <vincenzo@calabrothers.com>
*   Copyright:      Calabrothers Corporation
-----------------------------------------------------------------------------*/

namespace Ds;

class TreeNode {
    protected $oValue;
    protected $oParent;
    protected $aChildren;

    // Static functions
    private static function isValidNodeObject($oValue) : bool {
        $bValid = false;
        if (is_object($oValue)) {
            $oThis      = new \ReflectionClass(TreeNode::class);
            $oObj       = new \ReflectionObject($oValue);
            $szClass    = $oObj->getName();                
            if ($oThis->isInstance($oValue)    || 
                $oThis->isSubclassOf($szClass)) 
            {
                $bValid = true;
            } 
        }  
        return $bValid;
    }

    private static function validateInput($oValue)  : void{
        if (!isset($oValue)) {
            throw new \UnexpectedValueException("Unable to initialize node with null value");
        }
    }

    private static function getInputNode($oValue) : TreeNode {
        self::validateInput($oValue);
        return self::isValidNodeObject($oValue) ? $oValue : new TreeNode($oValue);
    }

    private static function checkNodeSet(\Ds\Deque $oSet) : void {
        $aValidFlags = $oSet->map(
            function($oNode) : bool {
                return self::isValidNodeObject($oNode);
            }
        );
        $bValid = true;
        foreach($aValidFlags as $bValidElement) {
            $bValid = $bValid && $bValidElement;
        }
        if (!$bValid) {
            throw new \InvalidArgumentException('The argument is not a valid set of Nodes.');
        }
    }


    // Constructor
    public function __construct($oValue) {
        $this->oParent      = null;
        $this->aChildren    = new \Ds\Deque();
        $this->setValue($oValue);
    }

    // Copy method
    public function copy() : TreeNode {
        if (is_object($this->oValue)) { 
            $oThisClone = new TreeNode(clone $this->oValue);
        } else {
            $oThisClone = new TreeNode($this->oValue);
        }        // We should perform a copy of the child
        $oThisClone->aChildren = $this->aChildren->map(
            function ($oChild) use ($oThisClone) { 
                $oClone = $oChild->copy();
                $oClone->setParent($oThisClone);
                return $oClone;
            }
        );
        return $oThisClone;
    }

    // Object methods
    public function setValue($oValue) : self {
        self::validateInput($oValue);

        if (self::isValidNodeObject($oValue)) {
            $this->oValue   = $oValue->oValue;
            return $this;
        }     
        $this->oValue   = $oValue;
        return $this;
    }

    public function getValue() { 
        return $this->oValue;
    }

    public function isRoot() : bool {
        return !isset($this->oParent);
    }

    public function isLeaf() : bool {
        return $this->aChildren->count() == 0;
    }

    public function hasParent() : bool {
        return !$this->isRoot();
    }

    public function hasChildren() : bool {
        return $this->getNumberOfChildren() > 0;
    }

    public function hasDescendants() : bool {
        return $this->getNumberOfDescendants() > 0;
    }

    public function getNumberOfChildren() : int {
        return $this->aChildren->count();
    }

    public function hasSiblings() : bool {
        return $this->getNumberOfSiblings() > 0;
    }

    public function getNumberOfSiblings() : int {
        if ($this->isRoot()) { return 0; }
        return $this->getParent()->getNumberOfChildren() - 1;
    }

    public function getParent() : TreeNode {
        if ($this->isRoot()) { 
            throw new \LogicException("Root node has no parent."); 
        }
        return $this->oParent;
    }

    protected function setParent($oParent) : self {
        $this->oParent = $oParent;
        return $this;
    }

    public function getRoot() : TreeNode {
        $oNode = $this;
        while (!$oNode->isRoot()) {
            $oNode = $oNode->getParent();
        }
        return  $oNode;
    }

    public function detach() : self {
        if ($this->hasParent()) {
            $this->getParent()->detachChild($this);
        } 
        return $this;
    }

    public function getDepth() : int {
        $nDepth = 0;
        $oNode = $this;
        while (!$oNode->isRoot()) {
            $nDepth++;
            $oNode = $oNode->getParent();
        }
        return $nDepth;
    }

    public function getAltitude() : int {
        if ($this->isLeaf()) { return 0; }
        $aAltitude = $this->aChildren->map(
            function ($oChild) { 
                return 1 + $oChild->getAltitude();
            }
        );
        $aAltitude->sort();
        return $aAltitude->last();
    }

    public function getNumberOfDescendants() : int {
        if ($this->isLeaf()) { return 0; }
        $aChildNumber = $this->aChildren->map(
            function ($oChild) { 
                return 1 + $oChild->getNumberOfDescendants();
            }
        );
        $nCumulative = 0;
        foreach($aChildNumber as $oValue) {
            $nCumulative += $oValue;
        }
        return $nCumulative;
    }

    public function getNumberOfNodes() : int {
        return 1 + $this->getRoot()->getNumberOfDescendants();
    }

    public function attachSibling($oChild) : self {
        $oChild = self::getInputNode($oChild);

        if ($this->isRoot()) { return $this; }

        // Register the sibling
        $this->getParent()->attachChild($oChild);

        return $this;
    } 

    public function attachChild($oChild) : self {
        $oChild = self::getInputNode($oChild);

        // Register the parent
        $oChild->setParent($this);

        // Add to the child list
        $this->aChildren->push($oChild);
        return $this;
    }

    public function detachSibling($oChild) : self {
        $oChild = self::getInputNode($oChild);
        if (!$this->hasSiblings()) {
            return $this;
        }
        // Remove the sibling
        $this->getParent()->detachChild($oChild);
        return $this;
    }

    public function detachChild($oChild) : self {
        $oChild = self::getInputNode($oChild);
        if (!$this->hasChildren()) {
            return $this;
        }
        // Get che nodes matching the value
        $aMatch =  $this->aChildren->filter(
            function ($oNode) use ($oChild) {
                return $oNode == $oChild;
            }
        );
        
        // Removing these nodes from child list
        $this->aChildren = $this->aChildren->filter(
            function ($oNode) use ($oChild) {
                return $oNode != $oChild;
            }
        );
        // Detach nodes
        foreach($aMatch as $oNode) {
            $oNode->setParent(null);
        }
        return $this;
    }

    // Set group functions
    public function getChildren() : \Ds\Deque {
        return $this->aChildren->copy();
    }

    public function getChildrenAndSelf() : \Ds\Deque {
        $aSet = $this->getChildren();
        $aSet->push($this);
        return $aSet;
    }

    public function getSiblings() : \Ds\Deque {
        if ($this->isRoot()) { return new \Ds\Deque(); }
        $oThis = $this;
        return $this->oParent->aChildren->filter(
            function ($oNode) use ($oThis) {
                return $oNode != $oThis;
            }
        );
    }

    public function getSiblingsAndSelf() : \Ds\Deque {
        $aSet = $this->getSiblings();
        $aSet->push($this);
        return $aSet;
    }

    public function getDescendants() : \Ds\Deque {
        $aDescendants = new \Ds\Deque();
        if (!$this->hasChildren())  { return $aDescendants; }
        // Recursion
        $aChildren = $this->getChildren();
        foreach($aChildren as $oChild) {
            $aDescendants->push($oChild);
            $aDescendants = $aDescendants->merge($oChild->getDescendants());
        }
        return $aDescendants;
    }

    public function getLeaves() : \Ds\Deque {
        $aLeaves = new \Ds\Deque();
        if ($this->isLeaf()) {
            $aLeaves->push($this); 
            return $aLeaves; 
        }
        // Recursion
        $aChildren = $this->getChildren();
        foreach($aChildren as $oChild) {
            $aLeaves = $aLeaves->merge($oChild->getLeaves());
        }
        return $aLeaves;
    }

    public function getDescendantsAndSelf() : \Ds\Deque {
        $aSet = $this->getDescendants();
        $aSet->push($this);
        return $aSet;
    }

    public function getAncestors() : \Ds\Deque {
        if ($this->isRoot()) { return new \Ds\Deque(); }
        $oParent    = $this->getParent();
        $aAncestors = new \Ds\Deque();
        $aAncestors->push($oParent);
        return $aAncestors->merge($oParent->getAncestors());
    }

    public function getAncestorsAndSelf() : \Ds\Deque {
        $aSet = $this->getAncestors();
        $aSet->push($this);
        return $aSet;
    }

    public function getNodes() : \Ds\Deque {
        $oRoot      = $this->getRoot();
        $aAllNodes  = new \Ds\Deque();
        $aAllNodes->push($oRoot);
        return $aAllNodes->merge($oRoot->getDescendants());
    }

    // Apply function
    public static function applySet(\Ds\Deque $oSet, callable $oFunction) : void {
        self::checkNodeSet($oSet);
        $oSet->apply(
            function($oNode) use ($oFunction) {
                return $oNode->apply($oFunction); 
            }
        );
    }

    // Filter function
    public static function filterSet(\Ds\Deque $oSet, callable $oFunction) : \Ds\Deque {
        self::checkNodeSet($oSet);
        return $oSet->filter(
            function($oNode) use ($oFunction) {
                return $oFunction($oNode->getValue()); 
            }
        );
    }

    // Filter function
    public static function mapSet(\Ds\Deque $oSet, callable $oFunction) : \Ds\Deque {
        self::checkNodeSet($oSet);
        return $oSet->map(
            function($oNode) use ($oFunction) {
                return $oFunction($oNode->getValue()); 
            }
        );
    }

    // Detach function
    public static function detachSet(\Ds\Deque $oSet, callable $oFunction) : \Ds\Deque {
        $aMatch = self::filterSet($oSet, $oFunction);
        $aMatch->apply(
            function($oNode) use ($oFunction) {
                return $oNode->detach(); 
            }
        );
        return $aMatch;
    }

    public function __call($szMethod, $aParams) {
        $aszAllowedMethods    = ['apply', 'filter', 'map', 'detach'];
        if (!preg_match('/([a-z]+)([A-Z]\w+)/', $szMethod, $aMatch)) {
            throw new \InvalidArgumentException('method now allowed. Valid are: '.implode('|',$aszAllowedMethods));
        }
        $szAction = $aMatch[1];
        if (!in_array($szAction, $aszAllowedMethods)) {
            throw new \InvalidArgumentException('method now allowed. Valid are: '.implode('|',$aszAllowedMethods));
        }
        $szSet          = $aMatch[2];
        $szSetFunction  = 'get'.$szSet;
        $oClass = new \ReflectionClass(TreeNode::class);
        if (!$oClass->hasMethod($szSetFunction)) {
            throw new \InvalidArgumentException("method $szSetFunction does not exist.");
        }
        $oSetMethod = new \ReflectionMethod(TreeNode::class, $szSetFunction);
        switch ($szAction) {
            case 'filter': {
                // This requires one parameter
                if (count($aParams) != 1 || !(is_callable($aParams[0]))) {
                    throw new \InvalidArgumentException("method $szSetFunction requires one callable argument");
                }
                return self::filterSet($oSetMethod->invoke($this), $aParams[0]);
            break;
            }

            case 'map': {
                // This requires one parameter
                if (count($aParams) != 1 || !(is_callable($aParams[0]))) {
                    throw new \InvalidArgumentException("method $szSetFunction requires one callable argument");
                }
                return self::mapSet($oSetMethod->invoke($this), $aParams[0]);
            break;
            }

            case 'detach': {
                // This requires one parameter
                if (count($aParams) != 1 || !(is_callable($aParams[0]))) {
                    throw new \InvalidArgumentException("method $szSetFunction requires one callable argument");
                }
                return self::detachSet($oSetMethod->invoke($this), $aParams[0]);
            break;
            }

            default: 
            case 'apply': {
                // This requires one parameter
                if (count($aParams) != 1 || !(is_callable($aParams[0]))) {
                    throw new \InvalidArgumentException("method $szSetFunction requires one callable argument");
                }
            break;
            }

        }
        
        // Handling apply (default) case here to achieve proper code coverage.
        self::applySet($oSetMethod->invoke($this), $aParams[0]);
        return $this;
    }

    public function apply(callable $oFunction) : self {
        $this->setValue($oFunction($this->getValue()));
        return $this;
    }

    // Utility functions
    private static function ToString(TreeNode $oNode, $nDepth = 0) : string {
        $szSymbol       = $oNode->isRoot() ? "┌" :"└";
        $szPrePadding   = $nDepth > 0 ? str_repeat(" ", 4*($nDepth-1)) : "";
        $szPostPadding  = $nDepth > 0 ? str_repeat("─", 4) : "";
        $szContent      = $szPrePadding.$szSymbol.$szPostPadding.$oNode->getValue()."\n"; 

        if ($oNode->hasChildren()) {
            $aszContent = $oNode->aChildren->map(
                function ($oChild) use ($nDepth) {
                    return self::ToString($oChild, $nDepth+1);
                }
            );
            foreach ($aszContent as $szChildContent) {
                $szContent .= $szChildContent;
            } 
        } 

        return $szContent;
    }

    public function __toString() : string {
            return self::ToString($this->getRoot());
    }

}
?>