<?php

/**
 * SimplifyPHP Framework
 *
 * This file is part of SimplifyPHP Framework.
 *
 * SimplifyPHP Framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimplifyPHP Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */

/**
 *
 * Base class for QueryObjectInterface implementations
 *
 */
class QueryObject implements QueryObjectInterface
{

  const SELECT = 1;

  const UPDATE = 2;

  const INSERT = 4;

  const DELETE = 8;

  const ALL = 15;

  const JOIN_INNER = 'INNER JOIN';

  const JOIN_LEFT = 'LEFT JOIN';

  const JOIN_RIGHT = 'RIGHT JOIN';

  const ORDER_ASC = 'ASC';

  const ORDER_DESC = 'DESC';

  protected $query = 1;

  protected $alias;

  protected $fields = array();

  protected $table = array();

  protected $joins = array();

  protected $groupBy = array();

  protected $having = array();

  protected $where = array();

  protected $orderBy = array();

  protected $limit;

  protected $offset;

  protected $data;

  protected $sql;

  protected $__sql = null;

  /**
   *
   * @return QueryObject
   */
  public static function getInstance()
  {
    return new self();
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::alias()
   */
  public function alias($alias = null)
  {
    if ($this->alias !== $alias) {
      $this->__sql = null;
    }

    if ($alias === true) {
      return $this->alias;
    }

    $this->alias = is_string($alias) ? $alias : null;

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::insert()
   */
  public function insert($table = null, $data = null)
  {
    $this->query = QueryObject::INSERT;

    $this->from($table);
    $this->data($data);

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::update()
   */
  public function update($table = null, $data = null, $where = null)
  {
    $this->query = QueryObject::UPDATE;

    $this->from($table);
    $this->data($data);
    $this->where($where);

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::delete()
   */
  public function delete($table = null, $where = null)
  {
    $this->query = QueryObject::DELETE;

    $this->from($table);
    $this->where($where);

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::data()
   */
  public function data($data = null, $remove = null)
  {
    if ($data === true) {
      return $this->data;
    }
    elseif ($data === false) {
      $this->data = null;
    }
    else {
      $this->data = array_merge((array) $this->data, (array) $data);

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::select()
   */
  public function select($fields = null, $remove = null)
  {
    if ($fields === true) {
      return $this->fields;
    }
    elseif ($fields === false) {
      $this->fields = array();
    }
    elseif (!empty($fields)) {
      $this->query = QueryObject::SELECT;

      $fields = (array) $fields;

      if ($remove) {
        $this->fields = array_diff($this->fields, $fields);
      }
      else {
        $this->fields = array_unique(array_merge($this->fields, $fields));
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::from()
   */
  public function from($table = null, $remove = null)
  {
    if ($table === true) {
      return $this->table;
    }
    elseif ($table === false) {
      $this->table = array();
    }
    elseif (!empty($table)) {
      if (!is_array($table)) {
        $table = array($table);
      }

      if ($remove) {
        $this->table = array_diff($this->table, $table);
      }
      else {
        $this->table = array_unique(array_merge($this->table, $table));
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::join()
   */
  public function join($join = null, $type = null, $remove = null)
  {
    if ($join === true) {
      return $this->joins;
    }
    elseif ($join === false) {
      $this->joins = array();
    }
    elseif (is_array($join)) {
      foreach ($join as $_join) {
        $this->join($_join, $type, $remove);
      }
    }
    elseif (!empty($join)) {
      if (empty($type)) {
        $type = QueryObject::JOIN_INNER;
      }

      $join = array($type, $join);

      if ($remove) {
        if (($i = array_search($join, $this->joins)) !== false) {
          unset($this->joins[$i]);
        }
      }
      else {
        $this->joins[] = $join;
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   *
   * @return QueryObjectInterface
   */
  public function leftJoin($join = null, $remove = null)
  {
    return $this->join($join, self::JOIN_LEFT, $remove);
  }

  /**
   *
   * @return QueryObjectInterface
   */
  public function rightJoin($join = null, $remove = null)
  {
    return $this->join($join, self::JOIN_RIGHT, $remove);
  }

  /**
   *
   * @return QueryObjectInterface
   */
  public function innerJoin($join = null, $remove = null)
  {
    return $this->join($join, self::JOIN_INNER, $remove);
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::groupBy()
   */
  public function groupBy($field = null, $remove = null)
  {
    if ($field === true) {
      return $this->groupBy;
    }
    elseif ($field === false) {
      $this->groupBy = array();
    }
    elseif (!empty($field)) {
      $field = (array) $field;

      if ($remove) {
        $this->groupBy = array_diff($this->groupBy, $field);
      }
      else {
        $this->groupBy = array_unique(array_merge($this->groupBy, $field));
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::having()
   */
  public function having($condition = null, $remove = null)
  {
    if ($condition === true) {
      return $this->having;
    }
    elseif ($condition === false) {
      $this->having = array();
    }
    elseif (!empty($condition)) {
      $condition = (array) $condition;

      if ($remove) {
        $this->having = array_diff($this->having, $condition);
      }
      else {
        $this->having = array_unique(array_merge($this->having, $condition));
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::where()
   */
  public function where($condition = null, $remove = null)
  {
    if ($condition === true) {
      return $this->where;
    }
    elseif ($condition === false) {
      $this->where = array();
    }
    elseif (!empty($condition)) {
      $condition = array($condition);

      if ($remove) {
        $this->where = array_diff($this->where, $condition);
      }
      else {
        $this->where = array_unique(array_merge($this->where, $condition));
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::orderBy()
   */
  public function orderBy($field = null, $direction = null, $remove = null)
  {
    if ($field === true) {
      return $this->orderBy;
    }
    elseif ($field === false) {
      $this->orderBy = array();
    }
    elseif (!empty($field)) {
      if ($remove) {
        if (isset($this->orderBy[$field])) {
          unset($this->orderBy[$field]);
        }
      }
      else {
        $this->orderBy[$field] = $direction;
      }

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::limit()
   */
  public function limit($limit = null)
  {
    if ($limit === true) {
      return $this->limit;
    }
    elseif ($limit === false) {
      $this->limit = null;
    }
    else {
      $this->limit = is_numeric($limit) ? intval($limit) : $limit;

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::offset()
   */
  public function offset($offset = null)
  {
    if ($offset === true) {
      return $this->offset;
    }
    elseif ($offset === false) {
      $this->offset = null;
    }
    else {
      $this->offset = is_numeric($offset) ? intval($offset) : $offset;

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::sql()
   */
  public function sql($sql = null)
  {
    if ($sql === true) {
      return $this->sql;
    }
    elseif ($sql === false) {
      $this->sql = null;
    }
    else {
      $this->sql = $sql;

      $this->__sql = null;
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::buildQuery()
   */
  public function buildQuery()
  {
    if (!$this->__sql) {
      $sql = $this->sql;

      if (empty($sql)) {
        if ($this->accept(QueryObject::SELECT)) {
          /**
           *
           * fields
           *
           */
          $fields = $this->fields;

          if (empty($fields)) {
            $fields = '*';
          }
          else {
            foreach ($fields as &$field) {
              if ($field instanceof QueryObjectInterface) {
                $field = $field->buildQuery();
              }
            }

            $fields = implode(', ', $fields);
          }

          $sql = "SELECT $fields FROM ";
        }
        elseif ($this->accept(QueryObject::INSERT)) {
          $sql = "INSERT INTO ";
        }
        elseif ($this->accept(QueryObject::UPDATE)) {
          $sql = "UPDATE ";
        }
        elseif ($this->accept(QueryObject::DELETE)) {
          $sql = "DELETE FROM ";
        }

        /**
         *
         * tables
         *
         */
        if (empty($this->table)) {
          throw new Exception('No table set for query');
        }
        else {
          $tables = $this->table;

          foreach ($tables as &$table) {
            if ($table instanceof QueryObjectInterface) {
              $table = $table->buildQuery();
            }
          }

          $table = implode(', ', $tables);
        }

        $sql .= "$table ";

        if ($this->accept(QueryObject::UPDATE) && !empty($this->data)) {
          $sql .= 'SET ' . QueryObject::buildUpdate($this->data) . ' ';
        }
        elseif ($this->accept(QueryObject::INSERT) && !empty($this->data)) {
          $sql .= QueryObject::buildInsert($this->data) . ' ';
        }

        if ($this->accept(QueryObject::SELECT)) {
          /**
           *
           * joins
           *
           */
          if (!empty($this->joins)) {
            $joins = $this->joins;

            foreach ($joins as &$join) {
              if ($join[1] instanceof QueryObjectInterface) {
                $join[1] = $join->buildQuery();
              }

              $join = implode(' ', $join);
            }

            $sql .= implode(' ', $joins) . ' ';
          }
        }

        if ($this->accept(QueryObject::ALL ^ QueryObject::INSERT)) {
          /**
           *
           * where
           *
           */
          if (!empty($this->where)) {
            $where = $this->where;

            foreach ($where as &$_where) {
              if ($_where instanceof QueryObjectInterface) {
                $_where = $_where->buildQuery();
              }
            }

            $where = BoolExpr::parse($where);

            $sql .= "WHERE $where ";
          }
        }

        if ($this->accept(QueryObject::SELECT)) {
          /**
           *
           * group by and having
           *
           */
          if (!empty($this->groupBy)) {
            $groupBy = implode(', ', $this->groupBy);

            $sql .= "GROUP BY $groupBy ";

            if (!empty($this->having)) {
              $having = $this->having;

              foreach ($having as &$_having) {
                if ($_having instanceof QueryObjectInterface) {
                  $_having = $_having->buildQuery();
                }
              }

              $having = BoolExpr::parse($having);

              $sql .= "HAVING $having ";
            }
          }
        }

        if ($this->accept(QueryObject::ALL ^ QueryObject::INSERT)) {
          /**
           *
           * order by
           *
           */
          if (!empty($this->orderBy)) {
            $orderBy = $this->orderBy;

            $order = array();

            foreach ($orderBy as $field => $direction) {
              $order[] = implode(' ', array($field, $direction));
            }

            $orderBy = implode(', ', $order);

            $sql .= "ORDER BY $orderBy ";
          }
        }

        if (!is_null($this->limit)) {
          $sql .= "LIMIT {$this->limit} ";
        }

        if (!is_null($this->offset)) {
          $sql .= "OFFSET {$this->offset} ";
        }

        if ($this->alias && $this->accept(QueryObject::SELECT)) {
          $sql = "({$sql}) {$this->alias}";
        }
      }

      $this->__sql = $sql;
    }

    return $this->__sql;
  }

  /**
   * Automagically converts the query object to string
   *
   * @return string
   */
  public function __toString()
  {
    return $this->buildQuery();
  }

  /**
   * (non-PHPdoc)
   * @see QueryObjectInterface::setParams()
   */
  public function setParams($params = null)
  {
    if (!empty($params)) {
      if (isset($params[QueryParameters::SQL])) {
        $this->sql($params[QueryParameters::SQL]);
      }
      else {
        if (isset($params[QueryParameters::SELECT])) {
          call_user_func(array($this, 'select'), $params[QueryParameters::SELECT]);
        }

        if (isset($params[QueryParameters::FROM])) {
          call_user_func(array($this, 'from'), $params[QueryParameters::FROM]);
        }

        if (isset($params[QueryParameters::JOIN])) {
          foreach ($params[QueryParameters::JOIN] as $join) {
            call_user_func_array(array($this, 'join'), (array) $join);
          }
        }

        if (isset($params[QueryParameters::INNER_JOIN])) {
          foreach ((array) $params[QueryParameters::INNER_JOIN] as $join) {
            call_user_func_array(array($this, 'innerJoin'), (array) $join);
          }
        }

        if (isset($params[QueryParameters::GROUP_BY])) {
          call_user_func(array($this, 'groupBy'), $params[QueryParameters::GROUP_BY]);
        }

        if (isset($params[QueryParameters::HAVING])) {
          call_user_func(array($this, 'having'), $params[QueryParameters::HAVING]);
        }

        if (isset($params[QueryParameters::WHERE])) {
          foreach ((array) $params[QueryParameters::WHERE] as $where) {
            call_user_func(array($this, 'where'), $where);
          }
        }

        if (isset($params[QueryParameters::ORDER_BY])) {
          foreach ((array) $params[QueryParameters::ORDER_BY] as $orderBy) {
            call_user_func_array(array($this, 'orderBy'), (array) $orderBy);
          }
        }

        if (isset($params[QueryParameters::LIMIT])) {
          $this->limit($params[QueryParameters::LIMIT]);
        }

        if (isset($params[QueryParameters::OFFSET])) {
          $this->offset($params[QueryParameters::OFFSET]);
        }

        if (isset($params[QueryParameters::DATA])) {
          call_user_func(array($this, 'data'), $params[QueryParameters::DATA]);
        }
      }
    }

    return $this;
  }

  /**
   * Add quotes to non-numeric values.
   *
   * @param mixed $value value
   * @return mixed
   */
  public static function quote($value)
  {
    if (! is_numeric($value)) {
      $value = "'{$value}'";
    }
    return $value;
  }

  /**
   * Build an INSERT's fields/values part of the query
   *
   * @param array $data associative array of field/value pairs
   * @param string|bool|null $wildcard wildcard used for substitution in prepared statements
   * @return string
   */
  public static function buildInsert($data, $wildcard = false)
  {
    $fields = array();
    $values = array();

    foreach ($data as $k => $v) {
      $fields[] = "`$k`";

      if ($wildcard === false) {
        $values[] = self::quote($v);
      }
      elseif (is_string($wildcard)) {
        $values[] = $wildcard;
      }
      else {
        $values[] = ':' . $k;
      }
    }

    return ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ') ';
  }

  /**
   * Build an UPDATE's fields/values part of the query.
   *
   * @param array $data associative array of field/value pairs
   * @param string|bool|null $wildcard wildcard used for substitution in prepared statements
   * @return string
   */
  public static function buildUpdate($data, $wildcard = false)
  {
    $fields = array();

    foreach ($data as $k => $v) {
      if ($wildcard === false) {
        $v = self::quote($v);
        $fields[] = "`$k` = $v";
      }
      elseif (is_string($wildcard)) {
        $fields[] = "`$k` = $wildcard";
      }
      else {
        $fields[] = "`$k` = :$k";
      }
    }

    return ' ' . implode(', ', $fields) . ' ';
  }

  /**
   * Build an equality expression for a single $values or an IN expression for multiple $values
   *
   * @param string $field the field
   * @param mixed $values single or multiple values for the expression
   * @param boolean $not if TRUE, use != or NOT IN instead of = and IN
   * @return string
   */
  public static function buildIn($field, $values, $not = false)
  {
    if (empty($values)) {
      return ' TRUE ';
    }

    $values = (array) $values;

    foreach ($values as &$value) {
      $value = self::quote($value);
    }

    $field = str_replace('.', '`.`', $field);

    $s = "`{$field}`";

    $value = implode(', ', $values);

    if (count($values) > 1) {
      $s .= ($not ? " NOT" : '') . " IN ({$value})";
    }
    else {
      $s .= ($not ? " !" : ' ') . "= {$value}";
    }

    return $s;
  }

  /**
   * Test the bit mask for a given query type
   *
   * @return boolean
   */
  protected function accept($query)
  {
    return ($query & $this->query) == $this->query;
  }

}
