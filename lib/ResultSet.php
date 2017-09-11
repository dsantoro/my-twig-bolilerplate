<?php

namespace Lib;

use \Iterator;
use \PDO;

class ResultSet implements Iterator {
	private $table;
	private $offset;
	private $rows;
	private $sort;
	private $order; 
	private $result;
	private $groupby;
	private $position;
	private $conn;
	private $where;
	private $values;
	private $num_rows;
	private $joins;
	private $fields;
	private $language;
	private $debug = false;

	public function __construct($table,$conn,$language) {
		$this->conn = $conn;
		$this->table = $table;
		$this->position = 0;
		$this->sort = null;
		$this->order = null;
		$this->joins = array();
		$this->fields = array();
		$this->language = $language;
	}

	public function __get($name) {
		if ($name == 'length') {
			return $this->getNumRows();
		}
	}

	public function __isset($name) {
		if ($name == 'length') {
			return true;
		}
	}

	public function fetchAs($table) {
		$this->table = $table;
		return $this;
	}

	public function select($field) {
		$this->fields[] = $field;
		return $this;
	}

	public function join($table,$condition = '',$values=array(),$type='INNER') {
		$this->joins[] = (object)array(
			'table' => $table,
			'condition' => $condition,
			'values' => $values,
			'type' => $type
		);
		return $this;
	}

	public function limit($offset=null,$rows=null) {
		$this->offset = $offset;
		$this->rows = $rows;
		return $this;
	}
	public function sort($sort = 'ordenamento',$order = 'desc') {
		$this->sort[] = $sort;
		$this->order[] = $order;
		return $this;
	}
	public function groupby($groupby) {
		$this->groupby = $groupby;
		return $this;
	}
	public function Where($condition,$values=array()) {

		$this->where[] = $condition;
		$this->Values($values);
		return $this;
	}
	public function Values($values = array()) {
		foreach($values as $value) {
			$this->value($value);
		}
		return $this;
	}
	public function value($value) {
		$this->values[] = $value;
		return $this;
	}
	public function getResult() {
		if ($this->result == null) {

			if (count($this->fields) == 0) {
				$this->fields = array("*");
			} 

			$fields = join(',',$this->fields);
			$query = "SELECT {$fields} FROM `{$this->table}` ";

			$values = array();
			$joins = array();
			foreach($this->joins as $join) {
				if ($join->condition == '') $join->condition = '1';
				$joins[] = "{$join->type} JOIN `{$join->table}` ON `{$join->table}`.`deleted` = 0 and ({$join->condition})";
				foreach($join->values as $value) {
					$values[] = $value;
				}
			}

			$query .= join(" ",$joins);

			if (count($this->where) ==  0) {
				$this->where = array('1');
			}
			if (count($this->where)!=0) {
				$where = join(' and ',$this->where);
				$query .= " WHERE `{$this->table}`.`deleted`=0 and ({$where}) ";
			}
			if ($this->groupby !== null) {
				$query.=" GROUP BY {$this->groupby} ";
			}
			if ($this->sort !== null) {

				$orderBy = array();
				foreach($this->sort as $index=>$sort) {
					$order = $this->order[$index];
					$orderBy[] = "{$sort} {$order}";
				}

				$orderBy = join(",",$orderBy);

				$query.=" ORDER BY {$orderBy} ";
			} else {
				$query.=" ORDER BY `{$this->table}`.`ordenamento` DESC";
			}


			if ($this->offset !== null) {
				$query.=" LIMIT {$this->offset} ";
				if ($this->rows !== null) {
					$query .= " , {$this->rows} ";
				}
			}

			
			$stmt = $this->conn->prepare($query);
			/*
			if (!$stmt) {
				throw new \Exception($this->conn->error.' '.$query);
			}
			*/

			if (count($this->values)) {				
				foreach($this->values as $value) {
					$values[] = $value;
				}
			}



			/*
			if (count($values)!=0) {

				$types = str_pad('',count($values),'s');
				$params = array($stmt,$types);
				foreach($values as $index=>$value) {
					$params[] = &$values[$index];
				}
				call_user_func_array('mysqli_stmt_bind_param', $params);
			}
			*/
			
			
			//$stmt->bind_param($types,$this->values);
			$stmt->execute($values);
			//$result = $stmt->get_result();
			$result = $stmt->fetchAll(PDO::FETCH_CLASS);
			//$this->result = $result->fetch_all(MYSQLI_ASSOC);
			$this->result = $result;
			if ($this->debug) {

				var_dump($query);
				var_dump($values);
			}
		}
		return $this->result;
	}
	public function setDebug($debug=false) {
		$this->debug = $debug;
		return $this;
	}
	public function getNumRows() {		
		if ($this->num_rows == null) {
			$result = $this->getResult();
			//$this->num_rows = $result->num_rows;
			$this->num_rows = count($result);
		}
		return $this->num_rows;
	}
	function rewind() {        
        $this->position = 0;
    }

    function current() {
        $result = $this->getResult();
        $num_rows = $this->getNumRows();
        if ($this->position >= $num_rows) return null;
        //$result->data_seek($this->position);
        
        return new ResultSetRow($result[$this->position],$this->table,$this->conn,$this->language);        
    }

    function key() {
        return $this->position;
    }

    function next() {     
    	$num_rows = $this->getNumRows();
  
        ++$this->position;
    }

    function valid() {
    	$num_rows = $this->getNumRows();
    	return $num_rows>$this->position;        
    }
}