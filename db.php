<?php

class DB
{
    private $DBhost;
    private $DBname;
    private $DBuser;
    private $DBpass;
    //Названия таблиц в БД
	protected $DBmarketP = 'marketplaces';
    protected $DBprod = 'pruduct';
    protected $DBmarketL = 'marketlist';
    protected $DBpriceDate = 'priceofdate';
    protected $DBcat = 'category';
    //End
    private $dbh;
    private $DBlist = array();
    private $Selectors = array();

    /*
     * Метод getTables возвращает двумерный массив.
     * Ключи - названия таблиц в базе данных
     */

    function getTables(){
        return $this->DBlist;
    }

    private function setDBnames($DBhost = 'localhost', $DBname = 'test', $DBuser, $DBpass){
        $this->DBhost = $DBhost;
        $this->DBname = $DBname;
        $this->DBuser = $DBuser;
        $this->DBpass = $DBpass;
    }

    private function DBcon(){
		$this->dbh = new PDO('mysql:host='.$this->DBhost.';dbname='.$this->DBname, $this->DBuser, $this->DBpass);
	}
	
	function __construct($DBhost, $DBname, $DBuser, $DBpass)
	{
		$this->setDBnames($DBhost, $DBname, $DBuser, $DBpass);
        $this->DBcon();
		$stmt = $this->dbh->prepare('SHOW TABLES');
		if ($stmt->execute()) {
		  while($row = $stmt->fetchColumn()) {
			$stmt1 = $this->dbh->prepare('SHOW COLUMNS FROM '.$row);
			if ($stmt1->execute()) {
			  for($i=0;$row1 = $stmt1->fetchColumn();$i++) {
				$this->DBlist[$row][$i] = $row1; 
			  }
			}
		  }
		}
		/*$market = $this->DBselect($this->DBmarketList,$this->DBarg($this->DBmarketList,array(0,2,3)),'','',false);
		if(!empty($market)){
			for($i = 0; $i < count($market); $i++)
				$this->Selectors[$market[$i][0]] = array( 0 => $market[$i][1], 1 => $market[$i][2]);
		}*/
	}

    /*
     * Метод DBarg формирует строку вида "`<название поля>`, `<название поля>`, ..."
     * из переданного ей массива $mod, используя название таблицы $table
     */

	private function DBarg($table, $mod){
		for($i = 0, $str = '' ;$i < count($mod) and $i < count($this->DBlist[(string) $table]); $i++)
		{
			$str .= '`'.$this->DBlist[$table][$mod[$i]].'`';
			if (($i+1) < count($mod) and !empty($this->DBlist[(string) $table][$mod[$i+1]])) $str .= ', ';
		}
		return $str;
	}

    /*
     * Метод DBwhere формирует строку вида "`<название поля>` = ?<$separator> `<название поля>` = ?, ..."
     * из переданной ей строки $column, где $separator - строка, определяющая разделитель (например, ',' или 'AND')
     */

	protected function DBwhere($column, $separator = 'AND'){
		$separator = ' '.trim($separator).' ';

        for($i = 0, $where = '', $column = explode(',', $column) ; $i < count($column) ; $i++)
		{
			$where .= $column[$i].' = ?';
			if($i != count($column)-1) $where .= $separator;
		}
		return $where;
	}

    /*
     * DBinsert - метод, имитирующий запрос INSERT в SQL
     *
     * $table
     * $list
     * $data
     *
     * $this->DBinsert($this->DBcat, array(1, 2), array('first', 'second'));
     */

	protected function DBinsert($table, $list, $data){
		for($i = 0, $count = count($list)-1; $i <= $count;$i++)
		{
			$question .= '?';
			if($i != $count) $question .= ', ';
		}
        $list = $this->DBarg($table, $list);
		$sql = 'INSERT INTO '.$table.' ('.$list.')  VALUES ('.$question.')';
		$sth = $this->dbh->prepare($sql);
		if($sth->execute($data))
			return true;
		else
			return false;
	}

    /*
     * BDupdate - метод, имитирующий запрос UPDATE в SQL
     *
     * $table
     * $list
     * $column
     * $data
     *
     * $this->DBupdate($this->DBcat, array(1), array(0), array('Аксессуары', '17'));
     */

	protected function DBupdate($table, $list, $column, $data){
        $list = $this->DBwhere($this->DBarg($table, $list), ',');
        $where = $this->DBwhere($this->DBarg($table, $column));
		$sql = 'UPDATE '.$table.' SET '.$list.' WHERE '.$where;
		$sth = $this->dbh->prepare($sql);
		if($sth->execute($data))
			return true;
		else
			return false;
	}

    /*
     * DBdelete - метод, имитирующий запрос DELETE в SQL
     *
     * $table - название таблицы в БД
     * $column - массив названий полей таблицы (идущих после WHERE)
     * $data - массив значений, содержащихся в полях, указанных в переменной $column
     *
     * Удалить запись
     * $this->DBdelete($table, array(0), array(18));
     *
     */

	protected function DBdelete($table, $column, $data){
        $column = $this->DBarg($table, $column);
        $where = $this->DBwhere($column);
		$sql = 'DELETE FROM '.$table.' WHERE '.$where;
		$sth = $this->dbh->prepare($sql);
		if($sth->execute($data))
			return true;
		else
			return false;
	}

    /*
         * DBselect - метод, имитирующий запрос SELECT в SQL
         *
         * $table - название таблицы в БД
         * $list - массив названий полей таблицы в цифровом виде
         * $column - массив названий полей таблицы (идущих после WHERE)
         * $data - массив значений, содержащихся в полях, указанных в переменной $column
         *
         * Вытащить все записи из таблицы
         * $mas = $this->DBselect($this->DBcat, array(0, 1, 2));
         *
         * Вытащить определенные записи
         * $mas = $this->DBselect($this->DBcat, array(0, 1, 2), array(0), array(17));
         *
    */

	protected function DBselect($table, $list, $column = 0, $data = 0){
        $list = $this->DBarg($table, $list);
        if(!empty($column)){
            $column = $this->DBarg($table, $column);
			$where = $this->DBwhere($column);
			$sql = 'SELECT '.$list.' FROM '.$table.' WHERE '.$where;
			$sth = $this->dbh->prepare($sql);
			$sth->execute($data);
			while ($res = $sth->fetch(PDO::FETCH_NUM)) {
				$result[] = $res;
			}
		}
		else{
			$sql = 'SELECT '.$list.' FROM '.$table;
			$sth = $this->dbh->query($sql);
			while ($res = $sth->fetch(PDO::FETCH_NUM)) {
				$result[] = $res;
			}
		}
		if(!empty($result))
		{
			return $result;
		}
		else
			return false;
	}
	
	protected function DBselectOrder($table,$list,$column,$data,$where,$order){
		//$mas = $this->DBselect($this->DBwin,$this->DBarg($this->DBwin,array(0,2)),$this->DBarg($this->DBwin,array(1,2,3)),array('lal'),true);
		if($where === true){
			$where = $this->DBwhere($column);
			$sql = 'SELECT '.$list.' FROM '.$table.' WHERE '.$where.' ORDER by '.$order;;
			$sth = $this->dbh->prepare($sql);
			$sth->execute($data);
			while ($res = $sth->fetch(PDO::FETCH_NUM)) {
				$result[] = $res;
			}
		}
		else{
			$sql = 'SELECT '.$list.' FROM '.$table.' ORDER by '.$order;
			$sth = $this->dbh->query($sql);
			while ($res = $sth->fetch(PDO::FETCH_NUM)) {
				$result[] = $res;
			}
		}
		if(!empty($result))
		{
			return $result;
		}
		else
			return false;
	}	
}

?>