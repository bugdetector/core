<?php

namespace CoreDB\Kernel\Database;

abstract class SelectQueryPreparerAbstract extends QueryPreparerAbstract
{
    public array $tables = [];
    public $fields;
    protected QueryCondition $condition;
    protected $orderBy;
    protected $groupBy;
    protected $limit;
    protected $offset;
    protected $quote;
    protected $distinct = "";
    protected $having;
    
    public function __construct(string $table_name, string $alias = "", bool $quote = true)
    {
        $this->quote = $quote;
        $this->tables[] = [
            "tableName" => $table_name,
            "alias" => $alias
        ];
        $this->condition = new QueryCondition($this);
    }

    /**
     * Select distinct results
     * @return $this
     */
    abstract public function distinct();
    
    /**
     * Join a table
     * @param string $table_name
     *  Table name
     * @param string $alias
     *  Alias
     * @param string $on
     *  Join condition
     * @param string $join
     *  Join type. Default: INNER
     */
    public function join(string $table_name, string $alias = "", string $on = "", string $join = "INNER")
    {
        array_push($this->tables, array(
            "tableName" => $table_name,
            "alias" => $alias,
            "join" => $join,
            "on" => $on
        ));
        return $this;
    }

    /**
     * Left join a table
     * @param string $table_name
     *  Table name
     * @param string $alias
     *  Alias
     * @param string $on
     *  Join condition
     * @param string $join
     *  Join type. Default: LEFT
     * @return $this
     *  Chaining
     */
    public function leftjoin(string $table_name, string $alias = "", string $on = "")
    {
        $this->join($table_name, $alias, $on, "LEFT");
        return $this;
    }

    /**
     * Right join a table
     * @param string $table_name
     *  Table name
     * @param string $alias
     *  Alias
     * @param string $on
     *  Join condition
     * @param string $join
     *  Join type. Default: RIGHT
     * @return $this
     *  Chaining
     */
    public function rightjoin(string $table_name, string $alias = "", string $on = "")
    {
        $this->join($table_name, $alias, $on, "RIGHT");
        return $this;
    }
    
    /**
     * Add fields to result.
     * If fields is null query will select all columns
     * @param string $table
     *  Table name
     * @param array $fields
     *  Fields in table
     * @return $this
     *  Chaining
     */
    abstract public function select(?string $table, array $fields);
    
    /**
     * Add field without a table alias and quote
     * @param array $function
     *  Database functions
     * @return $this
     *  Chaining
     */
    public function selectWithFunction(array $functions): SelectQueryPreparerAbstract
    {
        if (!$this->fields) {
            $this->fields = array();
        }
        foreach ($functions as $function) {
            array_push($this->fields, $function);
        }
        return $this;
    }
    
    /**
     * Set order direction and column
     * @param $orderBy
     *  Order sentence
     * @return $this
     *  Chaining
     */
    public function orderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Group results
     * @param $groupBy
     *  Group sentence
     * @return $this
     *  Chaining
     */
    public function groupBy(string $groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * Set having result
     * @param string $having
     *  Having sentence
     * @return $this
     *  Chaining
     */
    public function having(string $having)
    {
        $this->having = $having;
        return $this;
    }
    

    /**
     * Add condition to query
     * @param string $column
     *  Column name.
     * @param mixed $value
     *  Matching value.
     * @param string $operator
     *  Matching operator.
     * @param string $connect
     *  AND - OR vs. Default: AND
     * @return SelectQueryPreparerAbstract
     *  Self
     */
    abstract public function condition(
        $column,
        $value = null,
        string $operator = null,
        string $connect = "AND"
    ): SelectQueryPreparerAbstract;
    
    /**
     * Range results
     * @param int $limit
     *  Result limit
     * @param string $offset
     *  Start from
     * @return $this
     *  Chaining
     */
    public function limit(int $limit, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
}
