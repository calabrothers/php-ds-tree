<?php
/*-----------------------------------------------------------------------------
*	File:			TreeBuilder.php
*   Author:         Vincenzo Calabro' <vincenzo@calabrothers.com>
*   Copyright:      Calabrothers Corporation
-----------------------------------------------------------------------------*/

namespace Ds;

class TreeBuilder {
    protected $oTip;
    protected $oConstructor;

    public function __construct(callable $oConstructor) {
        $this->oConstructor = new \ReflectionFunction($oConstructor);
        $this->oTip         = null;
    }

    public function begin() : self {
        $nArguments = func_num_args();
        $nParamsReq = $this->oConstructor->getNumberOfRequiredParameters();
        $nParamsAll = $this->oConstructor->getNumberOfParameters();
        if ($nArguments < $nParamsReq || $nArguments > $nParamsAll) {
            throw new \InvalidArgumentException("Constructor requires [$nParamsReq / $nParamsAll] parameters.");
        }
        
        // Call the factory function
        $oObj   = $this->oConstructor->invokeArgs(func_get_args());
        $oNode  = new TreeNode($oObj);
        if (!isset($this->oTip)) {
            $this->oTip  = $oNode;
        } else {
            $this->oTip->attachChild($oNode);
            $this->oTip = $oNode;
        }
        return $this;
    }

    private function checkForEmptyTree() : self {
        if (!isset($this->oTip)) {
            throw new \OutOfRangeException("Tree is empty.");
        }
        return $this;
    }

    public function end() : self {
        $this->checkForEmptyTree();
        if (!$this->oTip->isRoot()) {
            $this->oTip = $this->oTip->getParent();
        }
        return $this;
    }

    public function resetRoot() : self {
        $this->checkForEmptyTree();
        $this->oTip = $this->oTip->getRoot();
        return $this;
    }

    public function getRoot() : TreeNode {
        $this->checkForEmptyTree();
        return $this->oTip->getRoot();
    }

    public function getTip() : TreeNode {
        $this->checkForEmptyTree();
        return $this->oTip;
    }

    public function __call($szMethod, $aArguments) : self {
        $this->checkForEmptyTree();
        call_user_func_array(array($this->oTip->getValue(), $szMethod), $aArguments);
        return $this;
    }

    public function __toString() : string {
        return (string) $this->getRoot();
    }
}

?>