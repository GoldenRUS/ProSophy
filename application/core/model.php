<?php

class model {

    private $dbh;
    private $DBlist = array();

    /*
     * Метод getTables возвращает двумерный массив.
     * Ключи - названия таблиц в базе данных
     */

    function getTables(){
        return $this->DBlist;
    }

    private function DBcon($DBhost, $DBname, $DBuser, $DBpass){
        $dsn = 'mysql:host='.$DBhost.';dbname='.$DBname.';charset=UTF8';
        $opt = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM
        );
        $this->dbh = new PDO($dsn, $DBuser, $DBpass, $opt);
    }

    function __construct($DBhost, $DBname, $DBuser, $DBpass){
        $this->DBcon($DBhost, $DBname, $DBuser, $DBpass);
        $resTables = $this->dbh->prepare('SHOW TABLES');
        if ($resTables->execute()){
            while($table = $resTables->fetchColumn()){
                $resColumns = $this->dbh->prepare('SHOW COLUMNS FROM '.$table);
                if ($resColumns->execute()){
                    for($i = 1 ; $column = $resColumns->fetchColumn() ; $i++){
                        $this->DBlist[$table][$i] = $column;
                    }
                }
            }
        }
    }

    /*
     * Метод DBarg формирует строку вида "`<название поля>`, `<название поля>`, ..."
     * из переданного ей массива $mod, используя название таблицы $table
     */

    private function DBarg($table, $mod){
        for($i = 0, $str = '' ;$i < sizeof($mod) and $i < sizeof($this->DBlist[(string) $table]); $i++){
            $str .= '`'.$this->DBlist[$table][$mod[$i]].'`';
            $str .= (($i+1) < count($mod) and !empty($this->DBlist[(string) $table][$mod[$i+1]])) ? ', ' : '';
        }

        return $str;
    }

    /*
     * Метод DBwhere формирует строку вида "`<название поля>` = ?<$separator> `<название поля>` = ?, ..."
     * из переданной ей строки $column, где $separator - строка, определяющая разделитель (например, ',' или 'AND')
     */

    private function DBwhere($column, $separator = 'AND'){
        $separator = ' '.trim($separator).' ';

        for($i = 0, $where = '', $column = explode(',', $column) ; $i < sizeof($column) ; $i++){
            $where .= $column[$i].' = ?';
            $where .= $i != count($column)-1 ? $separator : '';
        }

        return $where;
    }

    /*
     * DBinsert - метод, имитирующий запрос INSERT в SQL
     *
     * $arrNumColumns - массив порядковых номеров колонок таблицы $table
     * $arrData - массив данных для вставки в таблицу
     *
     * $this->DBinsert('<название таблицы>', array(1, 2), array('first', 'second'));
     */

    protected function DBinsert($table, $arrNumColumns, $arrData){
        $question = implode(', ', array_fill(0, sizeof($arrNumColumns), '?'));
        $list = $this->DBarg($table, $arrNumColumns);
        $sth = $this->dbh->prepare('INSERT INTO '.$table.' ('.$list.')  VALUES ('.$question.')');

        return $sth->execute($arrData) ? true : false;
    }

    /*
     * BDupdate - метод, имитирующий запрос UPDATE в SQL
     *
     * $arrNumColumns - массив порядковых номеров колонок таблицы $table
     * $column - массив порядковых номеров колонок таблицы для WHERE
     * $arrData - массив данных для вставки в таблицу
     *
     * $this->DBupdate('<название таблицы>', array(1), array(0), array('Аксессуары', '17'));
     */

    protected function DBupdate($table, $arrNumColumns, $column, $arrData){
        $list = $this->DBwhere($this->DBarg($table, $arrNumColumns), ',');
        $where = $this->DBwhere($this->DBarg($table, $column));
        $sth = $this->dbh->prepare('UPDATE '.$table.' SET '.$list.' WHERE '.$where);

        return $sth->execute($arrData) ? true : false;
    }

    /*
     * DBdelete - метод, имитирующий запрос DELETE в SQL
     *
     * $table - название таблицы в БД
     * $column - массив названий полей таблицы (идущих после WHERE)
     * $data - массив значений, содержащихся в полях, указанных в переменной $column
     *
     * Удалить запись
     * $this->DBdelete('<название таблицы>', array(0), array(18));
     *
     */

    protected function DBdelete($table, $column, $data){
        $column = $this->DBarg($table, $column);
        $where = $this->DBwhere($column);
        $sth = $this->dbh->prepare('DELETE FROM '.$table.' WHERE '.$where);

        return $sth->execute($data) ? true : false;
    }

    /*
         * DBselect - метод, имитирующий запрос SELECT в SQL
         *
         * $table - название таблицы в БД
         * $list - массив названий полей таблицы в цифровом виде
         * $column - массив названий полей таблицы (идущих после WHERE)
         * $data - массив значений, содержащихся в полях, указанных в переменной $column
         *
         * Вытащить все записи и все колонки
         * $mas = $this->DBselect('<название таблицы>', 0);
         *
         * Вытащить все записи из таблицы
         * $mas = $this->DBselect('<название таблицы>', array(0, 1, 2));
         *
         * Вытащить определенные записи
         * $mas = $this->DBselect('<название таблицы>', array(0, 1, 2), array(0), array(17));
         *
    */

    protected function DBselect($table, $list, $column = 0, $data = 0){
        $list = $list != 0 ? $this->DBarg($table, $list) : '*';
        if(!empty($column)){
            $column = $this->DBarg($table, $column);
            $where = $this->DBwhere($column);
            $sth = $this->dbh->prepare('SELECT '.$list.' FROM '.$table.' WHERE '.$where);
            $sth->execute($data);
            while ($res = $sth->fetch()){
                $result[] = $res;
            }
        }else{
            $sth = $this->dbh->query('SELECT '.$list.' FROM '.$table);
            while ($res = $sth->fetch()){
                $result[] = $res;
            }
        }

        return !empty($result) ? $result : false;
    }

    /*
     * Метод DBquery выполняет произвольный sql запрос и возвращает экземпляр класса для дальнейшей обработки данных
     *
     * Пример для выборки одного элемента:
     *
     * $sth = $this->DBquery('SELECT * FROM <название таблицы> WHERE id = 3 ');
     * $res = $sth->fetch();
     *
     * Пример для выборки нескольких записей:
     *
     * $sth = $this->DBquery('SELECT * FROM <название таблицы>');
     * $res = $sth->fetchAll();
     */

    protected function DBquery($sql){
        return $this->dbh->query($sql);
    }

}