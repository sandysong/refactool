<?php
/**
 * 重命名
 */
class Refactool_Rename extends PHPParser_NodeVisitorAbstract {
	private $_option;
	private $_printer;
	public function __construct(){
		$this->_printer = new PHPParser_PrettyPrinter_Zend();
	}
	public function setOption($option){
		$this->_option = $option;
	}
	public function beforeTraverse(array $nodes) {
	}
	public function afterTraverse(array $nodes) {
		//generate file
		$otherStmts = array();
		foreach ($nodes as $node) {
			if ($node instanceof PHPParser_Node_Stmt_Class
					|| $node instanceof PHPParser_Node_Stmt_Interface) {
				$filename = $this->getFilename($node->name);
				$dir = $this->_option[1].DIRECTORY_SEPARATOR.dirname($filename);
				if (!file_exists($dir)) {
					mkdir($dir, 0755, true);
				}
				$code = "<?php\n".$this->_printer->prettyPrint(array($node));
				file_put_contents($this->_option[1].DIRECTORY_SEPARATOR.$filename, $code);
			} else {
				$otherStmts[] = $node;
			}
		}
		if ($otherStmts) {
			throw new Exception("file has ".count($otherStmts).' other statments');
		}
	}
	public function enterNode(PHPParser_Node $node) {
	}
	public function leaveNode(PHPParser_Node $node) {
		if ($this->_option['target'] == 'class') {
			$this->replaceClassName($node);
		} elseif ($this->_option['target'] == 'method') {
			$this->replaceMethodName($node);
		}
	}
	public function replaceClassName($node) {
		$pattern = $this->_option['pattern'];
		$replace = $this->_option['replace'];
		if ($node instanceof PHPParser_Node_Stmt_Class || $node instanceof PHPParser_Node_Stmt_Interface ) {
			$name = preg_replace($pattern, $replace, $node->name);
			if ($name) {
				$node->name = $this->formatName($name);
			}
			if ($node->extends) {
				$name = preg_replace($pattern, $replace, $node->extends->parts[0]);
				if ($name) {
					$node->extends->parts[0] = $this->formatName($name);
				}
			}
			if ($node->implements) {
				foreach ($node->implements as $k => $v) {
					$name = preg_replace($pattern, $replace, $v->parts[0]);
					if ($name) {
						$node->implements[$k]->parts[0] = $this->formatName($name);
					}
				}
			}
		}
		if ($node instanceof PHPParser_Node_Expr_StaticCall) {
			$name = preg_replace($pattern, $replace, $node->class->parts[0]);
			if ($name) {
				$node->class->parts[0] = $this->formatName($name);
			}
		}

	}
	public function replaceMethodName($node) {
		$pattern = $this->_option['pattern'];
		$replace = $this->_option['replace'];
		if ($node instanceof PHPParser_Node_Stmt_ClassMethod) {
			$name = preg_replace($pattern, $replace, $node->name);
			if ($name) {
				$node->name = $name;
			}
		}
		if ($node instanceof PHPParser_Node_Expr_MethodCall || $node instanceof PHPParser_Node_Expr_StaticCall) {
			if (is_string($node->name)) {
				$name = preg_replace($pattern, $replace, $node->name);
				if ($name) {
					$node->name = $name;
				}
			}
		}
	}
	public function getFilename($class) {
		$standard = $this->_option['standard']?:'psr0';
		switch ($standard) {
			case 'yaf_controller':
				$parts = explode('_', preg_replace('/Controller$/','',$class));
				$filename = implode(DIRECTORY_SEPARATOR, $parts).'.php';
			break;
			case 'psr0':
			default:
				$parts = explode('_', $class);
				$filename = implode(DIRECTORY_SEPARATOR, $parts).'.php';
			break;
		}
		return $filename;
	}
	public function formatName($name) {
		if ($this->_option['standard'] == 'yaf_controller') {
			$parts = explode('_', $name);
			$parts = array_map('strtolower', $parts);
			$parts = array_map('ucfirst', $parts);
			$name = implode('_', $parts).'Controller';
		}
		return $name;
	}
}
