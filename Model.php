<?php

// father class
class Model
{
    private $host;
    private $user;
    private $pwd;
    private $dbname;
    private $charset;
    private $prefix;
    private $link;
    private $tablename;
    private $sql;
    private $options;

    public function __construct(array $config)
    {
        $this->host = $config['DB_HOST'];
        $this->user = $config['DB_USER'];
        $this->pwd = $config['DB_PWD'];
        $this->dbname = $config['DB_NAME'];
        $this->charset = $config['DB_CHARSET'];
        $this->prefix = $config['DB_PREFIX'];

        $this->link = $this->connect();

        $this->tablename = $this->getTableName();

        $this->initOptions();
    }

    public function __destruct()
    {
        mysqli_close($this->link);
    }

    private function connect()
    {
        $link = mysqli_connect($this->host, $this->user, $this->pwd);

        if (!$link) {
            die('数据库连接失败');
        }

        mysqli_select_db($link, $this->dbname);
        mysqli_set_charset($link, $this->charset);

        return $link;
    }

    private function getTableName(): string
    {
        if (!empty($this->tablename)) {
            return $this->prefix . $this->tablename;
        }

        $className = get_class($this);
        $table = strtolower(substr($className, 0, -5));
        return $this->prefix . $table;
    }

    private function initOptions(): void
    {
        $arr = ['where', 'table', 'field', 'order', 'group', 'having', 'limit'];

        foreach ($arr as $value) {
            $this->options[$value] = '';
            if ($value == 'table') {
                $this->options[$value] = $this->tablename;
            }
        }
    }

    function field($field)
    {
        if (!empty($field)) {
            if (is_string($field)) {
                $this->options['field'] = $field;
            } else if (is_array($field)) {
                $this->options['field'] = join(',', $field);
            }
        }
        return $this;
    }

    function table($table)
    {
        if (!empty($table)) {
            $this->options['table'] = $table;
        }
        return $this;
    }

    function where($where)
    {
        if (!empty($where)) {
            $this->options['where'] = 'where ' . $where;
        }
        return $this;
    }

    function group($group)
    {
        if (!empty($group)) {
            $this->options['group'] = 'group by ' . $group;
        }
        return $this;
    }

    function having($having)
    {
        if (!empty($having)) {
            $this->options['having'] = 'having' . $having;
        }
        return $this;
    }

    function order($order)
    {
        if (!empty($order)) {
            $this->options['order'] = 'order by ' . $order;
        }
        return $this;
    }

    function limit($limit)
    {
        if (!empty($limit)) {
            if (is_string($limit)) {
                $this->options['limit'] = 'limit ' . $limit;
            } elseif (is_array($limit)) {
                $this->options['limit'] = 'limit ' . join(',', $limit);
            }
        }
        return $this;
    }

    function select()
    {
        $sql = 'select %FIELD% from %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';

        $sql = str_replace(
            ['%FIELD%', '%TABLE%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
            [$this->options['field'], $this->options['table'], $this->options['where'], $this->options['group'],
                $this->options['having'], $this->options['order'], $this->options['limit']], $sql);

        $this->sql = $sql;

        return $this->query($sql);
    }

    function query(array|string $sql): array
    {
        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link)) {
            while ($data = mysqli_fetch_assoc($result)) {
                $newData[] = $data;
            }
        }
        return $newData;
    }

    public function __get(string $name)
    {
        if ($name == 'sql') {
            return $this->sql;
        }
        if ($name == 'tablename') {
            return $this->tablename;
        }
        return false;
    }

    function insert(array $data)
    {
        $data = $this->parseValue($data);

        $keys = array_keys($data);
        $values = array_values($data);

        $sql = 'insert into %TABLE%(%FIELD%) values(%VALUES%)';
        $sql = str_replace(['%TABLE%', '%FIELD%', '%VALUES%'], [$this->options['table'], join(',', $keys), join(',', $values)], $sql);
        $this->sql = $sql;
        return $this->exec($sql, true);
    }

    private function parseValue(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
            }
            $newData[$key] = $value;
        }
        return $newData;
    }

    private function exec(array|string $sql, $isInsert = false): false|int|string
    {
        $result = mysqli_query($this->link, $sql);
        if ($result && mysqli_affected_rows($this->link)) {
            if ($isInsert) {
                return mysqli_insert_id($this->link);
            } else {
                return mysqli_affected_rows($this->link);
            }
        }
        return false;
    }

    function delete(): false|int|string
    {
        $sql = 'delete from %TABLE% %WHERE%';
        $sql = str_replace(['%TABLE%', '%WHERE%'], [$this->options['table'], $this->options['where']], $sql);
        $this->sql = $sql;
        return $this->exec($sql);
    }

    function update($data): false|int|string
    {
        $data = $this->parseValue($data);
        $value = $this->parseUpdate($data);

        $sql = 'update %TABLE% set %VALUE% %WHERE%';
        $sql = str_replace(['%TABLE%', '%VALUE%','%WHERE%'], [$this->options['table'], $value,$this->options['where']], $sql);

        $this->sql = $sql;
        return $this->exec($sql);
    }

    private function parseUpdate(array $data): string
    {
        foreach ($data as $key => $value) {
            $newData[] = $key . '=' . $value;
        }
        return join(',', $newData);
    }

}

//test
$config = include 'config.php';
$m = new Model($config);

/*$data = ['age' => 30, 'name' => 'jackchen', 'money' => 2000];
$insertId = $m->table('user')->insert($data);
var_dump($insertId);*/
/*$m->limit('0', '5')->table('user')->field('age,name')->order('money desc')->where('id>1')->select();

var_dump($m->sql);*/

/*var_dump($m->field('name')->table('user')->limit('0,1')->where('id>0')->order('age desc')->select());

var_dump($m->tablename);*/
