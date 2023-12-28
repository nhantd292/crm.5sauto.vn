<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class NestedTable extends AbstractTableGateway {
	
	protected $tableGateway;
	protected $_data;
	protected $_nodeId;
	protected $userInfo;
	
	public function __construct(TableGateway $tableGateway) {
	    $this->tableGateway	= $tableGateway;
	    $this->userInfo	= new \ZendX\System\UserInfo();
	}
	
	public function getNode($arrParam = null, $options = null){
	     
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select -> columns(array('id', 'name', 'status', 'parent', 'level', 'left', 'right'))
	                    -> where->equalTo('id', $arrParam['id']);
	        })->current();
	    }
	    
	    return $result;
	}
	
	public function listNodes($arrParam = null, $options = null){
	    
	    if($options == null) {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select -> columns(array('id', 'name', 'level', 'parent'))
	                    -> order('left ASC')
	                    -> where->greaterThan('level', 0);
	        });
	    }
	    
	    if($options['task'] == 'list-level') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select -> columns(array('id', 'name', 'level', 'parent'))
        	            -> order('left ASC')
        	            -> where->greaterThan('level', 0)
        	            -> where->lessThanOrEqualTo('level', $arrParam['level']);
	        });
	    }
	    
	    if($options['task'] == 'list-branch') {
	        $nodeInfo = $this->getNode($arrParam);
	        
	        $result	= $this->tableGateway->select(function (Select $select) use ($nodeInfo){
	            $select -> columns(array('id', 'name', 'level', 'parent'))
        	            -> order('left ASC')
        	            -> where->greaterThan('level', 0)
        	            -> where->between('left', $nodeInfo->left, $nodeInfo->right);
	        });
	    }
	    
	    if($options['task'] == 'list-breadcrumd') {
	        $nodeInfo = $this->getNode($arrParam);
	         
	        $result	= $this->tableGateway->select(function (Select $select) use ($nodeInfo){
	            $select -> columns(array('id', 'name', 'level', 'parent'))
        	            -> order('left ASC')
        	            -> where->greaterThan('level', 0)
        	            -> where->lessThanOrEqualTo('left', $nodeInfo->left)
        	            -> where->greaterThanOrEqualTo('right', $nodeInfo->right);
	        });
	    }
	    
	    if($options['task'] == 'list-childs') {
	        $result	= $this->tableGateway->select(function (Select $select) use ($arrParam){
	            $select -> columns(array('id', 'name', 'level', 'parent'))
	                    -> order('left ASC')
        	            -> where->equalTo('parent', $arrParam->id);
	        });
	    }
	    
	    if($options['task'] == 'move-up') {
	        $nodeInfo = $this->getNode($arrParam);
	        
	        $result	= $this->tableGateway->select(function (Select $select) use ($nodeInfo){
	            $select -> columns(array('id', 'name', 'status', 'parent', 'level', 'left', 'right'))
        	            -> order('left DESC')
        	            -> limit(1)
        	            -> where->lessThan('right', $nodeInfo->left)
        	            -> where->notEqualTo('id', $nodeInfo->id)
        	            -> where->equalTo('parent', $nodeInfo->parent);
	        })->current();
	    }
	    
	    if($options['task'] == 'move-down') {
	        $nodeInfo = $this->getNode($arrParam);
	         
	        $result	= $this->tableGateway->select(function (Select $select) use ($nodeInfo){
	            $select -> columns(array('id', 'name', 'status', 'parent', 'level', 'left', 'right'))
        	            -> order('left ASC')
        	            -> limit(1)
        	            -> where->greaterThan('left', $nodeInfo->right)
        	            -> where->notEqualTo('id', $nodeInfo->id)
        	            -> where->equalTo('parent', $nodeInfo->parent);
	        })->current();
	    }
	    
		return $result;
	}

	public function insertNode($data, $nodeId, $options) {
	    // Set data
	    $nodeInfo      = $this->getNode(array('id' => $nodeId));
	    $dataLeft      = array('left' => new Expression('(`left` + 2)'));
	    $dataRight     = array('right' => new Expression('(`right` + 2)'));
	    $whereLeft     = new Where();
	    $whereRight    = new Where();
	    
	    switch ($options['position']) {
	        case 'left':
	            $whereLeft->greaterThan('left', $nodeInfo->left);
	            $whereRight->greaterThan('right', $nodeInfo->left);
	            $data['parent']    = $nodeInfo->id;
	            $data['level']     = $nodeInfo->level + 1;
	            $data['left']      = $nodeInfo->left + 1;
	            $data['right']     = $nodeInfo->left + 2;
	            break;
            case 'before':
                $whereLeft->greaterThanOrEqualTo('left', $nodeInfo->left);
        	    $whereRight->greaterThan('right', $nodeInfo->left);
        	    $data['parent']    = $nodeInfo->parent;
        	    $data['level']     = $nodeInfo->level;
        	    $data['left']      = $nodeInfo->left;
        	    $data['right']     = $nodeInfo->left + 1;
                break; 
            case 'after':
                $whereLeft->greaterThan('left', $nodeInfo->right);
        	    $whereRight->greaterThan('right', $nodeInfo->right);
        	    $data['parent']    = $nodeInfo->parent;
        	    $data['level']     = $nodeInfo->level;
        	    $data['left']      = $nodeInfo->right + 1;
        	    $data['right']     = $nodeInfo->right + 2;
                break;
            default:
                $whereLeft->greaterThan('left', $nodeInfo->right);
                $whereRight->greaterThanOrEqualTo('right', $nodeInfo->right);
                $data['parent']    = $nodeInfo->id;
                $data['level']     = $nodeInfo->level + 1;
                $data['left']      = $nodeInfo->right;
                $data['right']     = $nodeInfo->right + 1;
                break;
	    }
	    
	    $this->tableGateway->update($dataLeft, $whereLeft);
	    $this->tableGateway->update($dataRight, $whereRight);
	    $this->tableGateway->insert($data);
	}
	
	public function detachBranch($nodeMoveId, $options = null) {
	    $moveInfo  = $this->getNode(array('id' => $nodeMoveId));
	    $moveLeft  = $moveInfo->left;
	    $moveRight = $moveInfo->right;
	    $totalNode = ($moveInfo->right - $moveInfo->left + 1) / 2;
	    
	    // Cập nhật các Node trên nhánh
	    if($options == null) {
    	    $data    = array(
    	        'left' => new Expression('(`left` - ?)', array($moveLeft)),
    	        'right' => new Expression('(`right` - ?)', array($moveRight))
    	    );
    	    $where   = new Where();
    	    $where->between('left', $moveLeft, $moveRight);
    	    $this->tableGateway->update($data, $where);
	    }
	    
	    if($options['task'] == 'remove-node') {
	        $where   = new Where();
	        $where->between('left', $moveLeft, $moveRight);
	        $this->tableGateway->delete($where);
	    }
	    
	    // Cập nhật các Node trên cây (Left)
	    $data = array('left' => new Expression('(`left` - ?)', array($totalNode * 2)));
	    $where = new Where();
	    $where->greaterThan('left', $moveRight);
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên cây (Right)
	    $data = array('right' => new Expression('(`right` - ?)', array($totalNode * 2)));
	    $where = new Where();
	    $where->greaterThan('right', $moveRight);
	    $this->tableGateway->update($data, $where);
	    
	    return $totalNode;
	}
	
    public function moveNode($nodeMoveId, $nodeSelectionId, $options) {
	    switch ($options['position']) {
	        case 'left':
	            $this->moveLeft($nodeMoveId, $nodeSelectionId);
	            break;
            case 'before':
                $this->moveBefore($nodeMoveId, $nodeSelectionId);
                break; 
            case 'after':
                $this->moveAfter($nodeMoveId, $nodeSelectionId);
                break;
            default:
                $this->moveRight($nodeMoveId, $nodeSelectionId);
                break;
	    }
	}
	
	public function moveRight($nodeMoveId, $nodeSelectionId) {
	    // Tách nhánh
	    $totalNode = $this->detachBranch($nodeMoveId);
	    
	    // Lấy thông tin Node cần di chuyển
	    $nodeMoveInfo = $this->getNode(array('id' => $nodeMoveId));
	    
	    // Lấy thông tin Node gốc mới
	    $nodeSelectionInfo = $this->getNode(array('id' => $nodeSelectionId));
	    
	    // Cập nhật các Node trên cây (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('left', $nodeSelectionInfo->right);
	    $where->greaterThan('right', 0);
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên cây (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThanOrEqualTo('right', $nodeSelectionInfo->right);
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh
	    $where   = new Where();
	    $where->lessThanOrEqualTo('right', 0);
	    
	    // Cập nhật các Node trên nhánh (Level - bắt buộc phải cập nhật trước)
	    $data    = array( 'level' => new Expression('(`level` + ?)', array($nodeSelectionInfo->level - $nodeMoveInfo->level + 1)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($nodeSelectionInfo->right)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($nodeSelectionInfo->right + $totalNode*2 - 1)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Parent)
	    $data    = array( 'parent' => $nodeSelectionInfo->id );
	    $this->tableGateway->update($data, array('id' => $nodeMoveInfo->id));
	}
	
	public function moveLeft($nodeMoveId, $nodeSelectionId) {
	    // Tách nhánh
	    $totalNode = $this->detachBranch($nodeMoveId);
	     
	    // Lấy thông tin Node cần di chuyển
	    $nodeMoveInfo = $this->getNode(array('id' => $nodeMoveId));
	     
	    // Lấy thông tin Node gốc mới
	    $nodeSelectionInfo = $this->getNode(array('id' => $nodeSelectionId));
	     
	    // Cập nhật các Node trên cây (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('left', $nodeSelectionInfo->left);
	    $where->greaterThan('right', 0);
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên cây (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('right', $nodeSelectionInfo->left);
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh
	    $where   = new Where();
	    $where->lessThanOrEqualTo('right', 0);
	     
	    // Cập nhật các Node trên nhánh (Level - bắt buộc phải cập nhật trước)
	    $data    = array( 'level' => new Expression('(`level` + ?)', array($nodeSelectionInfo->level - $nodeMoveInfo->level + 1)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($nodeSelectionInfo->left + 1)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($nodeSelectionInfo->left + 1 + $totalNode*2 - 1)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Parent)
	    $data    = array( 'parent' => $nodeSelectionInfo->id );
	    $this->tableGateway->update($data, array('id' => $nodeMoveInfo->id));
	}
	
	public function moveBefore($nodeMoveId, $nodeSelectionId) {
	    // Tách nhánh
	    $totalNode = $this->detachBranch($nodeMoveId);
	    
	    // Lấy thông tin Node cần di chuyển
	    $nodeMoveInfo = $this->getNode(array('id' => $nodeMoveId));
	    
	    // Lấy thông tin Node gốc mới
	    $nodeSelectionInfo = $this->getNode(array('id' => $nodeSelectionId));
	    
	    // Cập nhật các Node trên cây (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThanOrEqualTo('left', $nodeSelectionInfo->left);
	    $where->greaterThan('right', 0);
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên cây (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('right', $nodeSelectionInfo->left);
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh
	    $where   = new Where();
	    $where->lessThanOrEqualTo('right', 0);
	    
	    // Cập nhật các Node trên nhánh (Level - bắt buộc phải cập nhật trước)
	    $data    = array( 'level' => new Expression('(`level` + ?)', array($nodeSelectionInfo->level - $nodeMoveInfo->level)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($nodeSelectionInfo->left)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($nodeSelectionInfo->left + $totalNode*2 - 1)) );
	    $this->tableGateway->update($data, $where);
	    
	    // Cập nhật các Node trên nhánh (Parent)
	    $data    = array( 'parent' => $nodeSelectionInfo->parent );
	    $this->tableGateway->update($data, array('id' => $nodeMoveInfo->id));
	}
	
	public function moveAfter($nodeMoveId, $nodeSelectionId) {
	    // Tách nhánh
	    $totalNode = $this->detachBranch($nodeMoveId);
	     
	    // Lấy thông tin Node cần di chuyển
	    $nodeMoveInfo = $this->getNode(array('id' => $nodeMoveId));
	     
	    // Lấy thông tin Node gốc mới
	    $nodeSelectionInfo = $this->getNode(array('id' => $nodeSelectionId));
	     
	    // Cập nhật các Node trên cây (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('left', $nodeSelectionInfo->right);
	    $where->greaterThan('right', 0);
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên cây (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($totalNode * 2)) );
	    $where   = new Where();
	    $where->greaterThan('right', $nodeSelectionInfo->right);
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh
	    $where   = new Where();
	    $where->lessThanOrEqualTo('right', 0);
	     
	    // Cập nhật các Node trên nhánh (Level - bắt buộc phải cập nhật trước)
	    $data    = array( 'level' => new Expression('(`level` + ?)', array($nodeSelectionInfo->level - $nodeMoveInfo->level)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Left)
	    $data    = array( 'left' => new Expression('(`left` + ?)', array($nodeSelectionInfo->right + 1)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Right)
	    $data    = array( 'right' => new Expression('(`right` + ?)', array($nodeSelectionInfo->right + $totalNode*2)) );
	    $this->tableGateway->update($data, $where);
	     
	    // Cập nhật các Node trên nhánh (Parent)
	    $data    = array( 'parent' => $nodeSelectionInfo->parent );
	    $this->tableGateway->update($data, array('id' => $nodeMoveInfo->id));
	}
	
	public function moveUp($nodeId, $options = null) {
	    $nodeSelection = $this->listNodes(array('id' => $nodeId), array('task' => 'move-up'));
	    
	    if(!empty($nodeSelection)) {
            $this->moveBefore($nodeId, $nodeSelection->id);
	    }
	}
	
	public function moveDown($nodeId, $options = null) {
        $nodeSelection = $this->listNodes(array('id' => $nodeId), array('task' => 'move-down'));
	    
	    if(!empty($nodeSelection)) {
            $this->moveAfter($nodeId, $nodeSelection->id);
	    }
	}

    public function updateNode($data, $nodeId, $nodeParentId = null, $options = null) {
        if(!empty($nodeParentId)) {
            $nodeInfo = $this->getNode(array('id' => $nodeId));
            $nodeParentInfo = $this->getNode(array('id' => $nodeParentId));
            if(!empty($nodeParentInfo) && $nodeInfo->parent != $nodeParentInfo->id) {
                $this->moveRight($nodeId, $nodeParentId);
            }
        }
        
        $this->tableGateway->update($data, array('id' => $nodeId));
    }
    
    public function removeNode($nodeId, $options = null) {
        switch ($options['type']) {
            case 'only':
                $this->removeNodeOnly($nodeId);
                break;
            case 'branch':
            default:
                $this->removeBranch($nodeId);
                break;
        }
    }

    public function removeBranch($nodeId) {
        $this->detachBranch($nodeId, array('task' => 'remove-node'));
    }
    
    public function removeNodeOnly($nodeId) {
        $nodeInfo = $this->getNode(array('id' => $nodeId));
        $nodes = $this->listNodes($nodeInfo, array('task' => 'list-childs'));
        
        if(!empty($nodes)) {
            foreach ($nodes AS $node) {
                $this->moveRight($node->id, $nodeInfo->parent);
            }
        }
        
        $this->removeBranch($nodeId);
    }
}