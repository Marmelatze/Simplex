<?php
$matrix = array(
	array(0, -0.1, -0.15, -0.2, -0.02),
	array(0.75, 1, 1, 0, 0),
	array(-0.2, 1, 0, -1, 0),
	array(-0.8, -1, -1, -1, 0),
	array(-0.05, 0, 0, 0, -1),
	array(1, 1, 1, 1, 1)
);
/*
$matrix = array(
	array(0,-4, -3),
	array(6,0,1),
	array(7,1,1),
	array(18,3,2)
);*/

$tableu = new Tableu($matrix);
$tableu->locked = 5;
new Simplex($tableu);


class Simplex{
	public static $twig;
	public $tableus = array();
	public function __construct($startTableu){
		$this->run($startTableu);
		require_once 'Twig/Autoloader.php';
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem('templates');
		self::$twig = new Twig_Environment($loader);
		$tableus = array($startTableu);
							
		echo self::$twig->render('index.html', array('tableus' => $this->tableus));
	}
	
	public function run(Tableu $tableu){
		$this->tableus[] = $tableu;
		$result = $tableu->getPivot();
		$runs = 0;
		while($result !== false){
			$newTableu = $tableu->getNext();
			$this->tableus[] = $newTableu;
			#$this->run($newTableu);
			$result = $newTableu->getPivot();
			$tableu = $newTableu;
			$runs++;
			if($runs>5){
				break;
			}
		}
	}
}

class Tableu{
	private $matrix = array();
	public $num = 0;

	public $rows = array();
	public $cols = array();
	public $locked = null;
	/**
	 *
	 * @var Cell
	 */
	public $pivot = null;
	public function __construct($matrix = array()){
		#$this->matrix = $matrix;
		$rowCount = 0;
		/*
		foreach($matrix as $rowKey => $row){
			if($rowCount == 0){
				$colCount = 0;
			}
			foreach($row as $colKey => $col){
				if($rowCount == 0){
					$this->xNames[] = "X".($colCount+1);
					$colCount++;
				}
				$cell = new Cell($col);
				$this->matrix[$rowKey][$colKey] = $cell;
			}
			$this->xCount = $colCount;

			$this->yNames[] = "Y".($rowCount+1);
			
			$rowCount++;
			$this->yCount = $rowCount;
		}*/
		$rowCount = 0;
		foreach($matrix as $rowKey => $row){
			$rowClass = new Row();
			if($rowKey == 0){
				
				$rowClass->isBase = true;
				$rowClass->name = "Z";
			}else{
				$rowClass->name = "Y".($rowCount+1);
				$rowCount++;
			}
			$this->rows[$rowKey] = $rowClass;
			foreach($row as $colKey => $col){
				if($rowKey == 0){
					$colClass = new Col();
					$this->cols[$colKey] = $colClass;
					if($colKey == 0){
						$colClass->isBase = true;
						$colClass->name = "Z";
						
					}else{
						$colClass->name = "X".($colKey);
					}
				}else{
					$colClass = $this->cols[$colKey];
				}
				
				$cell = new Cell($col);
				$colClass->addCell($cell);
				$rowClass->addCell($cell);
				$this->matrix[$rowKey][$colKey] = $cell;
			}
		}
		
		#var_dump($this->matrix);
	}
	
	public function getPivot(){
				
		if($this->locked != null){
			$row = $this->getRow($this->locked);
			$row->locked = true;
			$cells = $row->getCells();
			unset($cells[0]);
			#$cell = $cells[array_rand($cells)];
			$cell = $cells[1];
			$cell->isPivot = true;
		}else{
			
			$cells = $this->cols[0]->getCells();
			$pivot = null;
			foreach($cells as $key => $cell){
				if($key==0){
					continue;
				}
				if($cell->value < 0){
					$pivot = $cell;
					break;
				}
			}
			if($pivot!==null){
				$pivotCell = null;
				foreach($pivot->row->getCells() as $key => $cell){
					if($key==0  OR $cell->col->locked){
						continue;
					}
					if($cell->value < 0){
						$pivotCell = $cell;
						break;
					}
				}
				if(empty($pivotCell)){
					echo "Keine Lösung";
					return false;
				}
				$cell = $pivotCell;
				$cell->isPivot = true;
			}else{
				$cells = $this->getRow(0)->getCells();
				$min = null;
				$minKey = null;
				foreach($cells as $key=>$cell){
					if($key == 0){
						continue;
					}
					if($cell->value < 0 AND ($min == null OR $cell->value < $min)){
						$min = $cell->value;
						$minKey = $key;
					}
				}
				if($min == null){
					return false;
				}
				$cell = $cells[$minKey];
				$row = $cell->row;
				$row->isPivot = true;
				$baseCells = $this->cols[0]->getCells();
				$col = $cell->col;
				$cols = $col->getCells();
				
				$min = null;
				$minKey = null;
				
				foreach($baseCells as $key=>$newCell){
					if($key==0 OR $cols[$key]->value == 0){
						continue;
					}
					$val = $newCell->value / $cols[$key]->value;
					if($val > 0 AND ($min == null OR $val < $min)){
						$min = $val;
						$minKey = $key;
					}
				}
				if($min == null){
					echo "fehler";
					return false;
				}
				$cell = $cols[$minKey];
				$cell->isPivot = true;
			}
		}
		$cell->row->isPivot = true;
		$cell->col->isPivot = true;
		$this->pivot = $cell;
		return true;
	}
	/**
	 *
	 * @param integer $num
	 * @return Row
	 */
	public function getRow($num){
		#return $this->matrix[$num];
		return $this->rows[$num];
	}
	
	public function build(){
		return Simplex::$twig->render("tableu.html",array('tableu' => $this));
	}
	
	public function getNext(){
		$newTableu = $this->deepClone();
		$newTableu->num++;
		$pivot = $newTableu->pivot;
		$pivot->col->isPivot = false;
		$pivot->row->isPivot = false;
		$pivot->col->swapped = true;
		$pivot->col->name = $this->pivot->row->name;
		$pivot->row->name = $this->pivot->col->name;
		if($pivot->row->locked){
			$pivot->col->locked = true;
			$pivot->row->locked = false;
		}
		$colCells = $pivot->col->getCells();
		$rowCells = $pivot->row->getCells();
		
		foreach($newTableu->rows as $key=>$row){
			if($row === $pivot->row){
				continue;
			}
			foreach($row->getCells() as $key2 => $cell){
				if($cell->isPivot OR $cell->col === $pivot->col){
					continue;
				}
				$colCell = $rowCells[$key2];
				$rowCell = $colCells[$key];
				$colValue = $colCell->value;
				$rowValue = $rowCell->value;
				$cell->value = $cell->value - ($colValue * $rowValue)/$pivot->value;
			}
		}
		
		
		foreach($colCells as $cell){
			if($cell != $pivot){
				$cell->value = $cell->value/$pivot->value*-1;
			}
		}
		
		foreach($rowCells as $cell){
			if($cell != $pivot){
				$cell->value = $cell->value/$pivot->value;
			}
		}
		$pivot->value = 1/$pivot->value;
		$pivot->isPivot = false;
		$pivot->col->isPivot = false;
		$pivot->row->isPivot = false;
		$newTableu->locked = null;
		return $newTableu;
	}
	/**
	 *
	 * @return Tableu
	 */
	public function deepClone(){
		return unserialize(serialize($this));
	}

	
}

class Row{
	public $name = "";
	public $isBase = false;
	private $cells = array();
	private $cols = array();
	public $isPivot = false;
	public $locked = false;
	#public $lock
	public function __construct($name = ""){
		$this->name = $name;
	}
	public function addCell(Cell $cell){
		if(!empty($cell->row)){
			throw new Exception("Cell alredy assigened to row");
		}
		$cell->row = $this;
		$this->cells[] = $cell;
	}
	public function getCells(){
		return $this->cells;
	}
	public function addCol(Col $col){
		$this->cols[] = $col;
	}
	public function getCols(){
		return $this->cols;
	}
}

class Col{
	public $isBase = false;
	private $cells = array();
	public $name = "";
	private $rows;
	public $isPivot = false;
	public $swapped = false;
	public $locked = false;
	public function __construct($name = ""){
		$this->name = $name;
	}
	public function addCell(Cell $cell){
		if(!empty($cell->col)){
			throw new Exception("Cell alredy assigened to col");
		}
		$cell->col = $this;
		$this->cells[] = $cell;
	}
	public function getCells(){
		return $this->cells;
	}
	public function addRow(Row $row){
		$this->rows[] = $row;
	}
	public function getRows(){
		return $this->rows();
	}
}

class Cell{
	public $value;
	/**
	 *
	 * @var Col
	 */
	public $col = null;
	/**
	 *
	 * @var Row
	 */
	public $row = null;
	public $isPivot = false;
	public function __construct($value){
		$this->value = $value;
	}
	
	public function asFraction(){
		$number = $this->value;
		if($number==0){
			return $number;
		}
		$negative = false;
		if($number < 0){
			$negative = true;
			$number*=-1;
		}
		$fraction = $this->getFraction($number);
		if($fraction === false){
			return round($number,3);
		}else{
			if($negative){
				$fraction = "-".$fraction;
			}
			return $fraction;
		}
		
	}
	private function getFraction($number){
		$numerator = 1;
		$denominator = 0;
		for(; $numerator < 1000; $numerator++){
			$temp = $numerator / $number;
			if(ceil($temp) - $temp == 0){
				$denominator = $temp;
				break;
			}
		}
		return ($denominator > 0) ? $numerator . '/' . $denominator : false;
	}
}

?>