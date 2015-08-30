<?php
/**
 * Набор функций для работы с БД.
 * 
 * Доп.инфо:
 *  префикс "__" у названий методов - типа protected ;)
 */
require_once(dirname(__FILE__).'/../config/sys_configs.php');

/**
 * Тоже самое что и {@link db_select()}, 
 * Но, всегда возвращает только первую запись
 */
function db_select_row($table, $fields='*', $cond=null, $end=null)
{
    $_res = db_select($table, $fields, $cond, $end.' LIMIT 1');
    if($_res)
    {
        return $_res[0];
    }
    return [];
}

/**
 * Выполнение Select запроса
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @param mixed $fields Поля для показа
 * @param mixed $cond Условие для выборки
 * @param mixed $end Добавление в конец запроса
 * @return Array
 */
function db_select($table, $fields='*', $cond=null, $end=null, Array $join=array())
{
    $_q = 'SELECT ';
    
    if( is_array($fields) ) $_q .= implode(', ', $_q);
        else $_q .= $fields;

    $_q .= ' FROM '.__db_get_table_name($table).' as `'.$GLOBALS['tables'][$table]['table_name'].'` ';

    if( $join !== array() )
    {
        $_q .= __db_join_join($join);
    }

    if( $cond !== null )
    {
        $_q .= ' WHERE ('.$cond.')';
    }
    if( $end !== null )
    {
        $_q .= ' '.$end;
    }
    $_q .= ';';
    $_res = __db_run($_q, $table);


    $data = [];

    if( $_res !== false )
    {
        while( $row = mysqli_fetch_assoc($_res) )
        {
            $data[] = $row;
        }
        mysqli_free_result($_res); 
    }
    // if(!__db_is_transaction($table)) mysqli_close(__db_get_host_link($table));
    return $data;
}

function db_update($table, Array $upd, $cond, Array $cond_params=array())
{

    if($cond_params!=array())
    {
        foreach($cond_params as &$value)
        {
            $value = '"'.__db_strip($table, $value).'"';
        }
        $cond = strtr($cond, $cond_params );
    }

    $q = 'UPDATE '.__db_get_table_name($table).' `'.$table.'` SET '
        .' '.__db_str_value($table, $upd)
        .' WHERE '.$cond; //TODO;
    $_res = __db_run($q, $table);

    // if(!__db_is_transaction($table)) mysqli_close(__db_get_host_link($table));

    return (bool)mysqli_affected_rows(__db_get_host_link($table));
}

// TODO: mv
function __db_str_value($table, Array $fields)
{
    $_s = [];
    while( list($field, $value) = each($fields) )
    {
        $field = __db_fields_strip($table, $field);
        $field = strtr($field, array('.'=>'`.`'));
        $_s[] = '`'.$field.'` = "'.__db_fields_strip($table, $value).'"';
    }
    return implode(', ', $_s);
}

function db_insert($table, Array $set, $id=false)
{
    $q = 'INSERT INTO '.__db_get_table_name($table).' SET';

        // TODO: __db_str_value
    $_s = [];
    while( list($field, $value) = each($set) )
    {
        $_s[] = '`'.__db_fields_strip($table, $field).'` = "'.__db_fields_strip($table, $value).'"';
    }
    $q .= ' '.implode(', ', $_s);
    unset($_s);
    $_res = __db_run($q, $table);

    if($_res && $id===true)
    {
        $_host = &$GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ];
        return mysqli_insert_id($_host['link']);
    }


    // if(!__db_is_transaction($table))  mysqli_close(__db_get_host_link($table));
    
    return $_res;
}

function db_get_error($table)
{
    // shortcut
    $_host = &$GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ];
    if( $_host['link'] === null )
    {
        throw new Exception('Подключение к БД отсутсвует. Невозможно получить ошибку БД.');  // TODO:
    }
    $_e = mysqli_error($_host['link']);
    if(empty($_e))
    {
        return false;
    }
    else
    {
        return $_e;
    }
}

function db_transaction_commit()
{
    __db_action_transaction('mysqli_commit');
}

function db_transaction_rollback()
{
    __db_action_transaction('mysqli_rollback');
}

function db_transaction_start()
{
    if(empty($GLOBALS['mysql']['transaction']))
    {
        $GLOBALS['mysql'] = [
            'transaction' => true,
            'tables'      => [],
        ];
    }
    else
    {
        $GLOBALS['mysql']['transaction'] = true;
        $GLOBALS['mysql']['tables']      = [];
    }
}



// ============================== для локального использования

function __db_get_table_name($table)
{
    __db_check_all_configs($table); // TODO: !!! exception
    return '`'.$GLOBALS['tables'][$table]['db_name'].'`.`'.$GLOBALS['tables'][$table]['table_name'].'`';
}

/**
 * Выполнение запроса
 */
function __db_run($query, $table)
{
    // ПОвторная проверка, может быть вызван отдельно
    if( __db_check_all_configs($table) !== true ) // TODO: !!! Может возникнуть exception
    {
        return false;
    }

    __db_start_transaction($table);
    return mysqli_query(__db_get_host_link($table), $query);
}

function __db_start_transaction($table)
{
    if( !empty($GLOBALS['mysql']['transaction']) && $GLOBALS['mysql']['transaction'] === true )
    {
        mysqli_begin_transaction(__db_get_host_link($table));
        mysqli_autocommit(__db_get_host_link($table), FALSE);
        $GLOBALS['mysql']['tables'][] = $table;
    }
}

function __db_is_transaction($table)
{
    return !empty($GLOBALS['mysql']['transaction']) && $GLOBALS['mysql']['transaction'] === true;
}

function __db_action_transaction($action)
{
    if(!empty($GLOBALS['mysql']['transaction']) && $GLOBALS['mysql']['transaction']===true )
    {
        for($i=count($GLOBALS['mysql']['tables'])-1; $i>=0; --$i)
        {
            $_res = $action(__db_get_host_link($GLOBALS['mysql']['tables'][$i]));
        }
        // mysqli_close(__db_get_host_link($table));
        unset($GLOBALS['mysql']['transaction'], $GLOBALS['mysql']['tables']);
    }
}

/**
 * Проверка всех необходимых конфигураций для таблицы через алиас
 * 
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @return bool
 */
function __db_check_all_configs($table)
{
    if( !isset($GLOBALS['tables'][$table]) )
    {
        throw new Exception('Указанной таблицы не существует.'); // TODO:
    }

    if( !isset( $GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ] ) )
    {
        throw new Exception('Указанной таблицы не существует.'); // TODO:
    }
    return true;
}

/**
 * "Очистка" полей
 * @param mixed $fields
 * @return mixed
 */
function __db_fields_strip($table, $fields)
{
    if( is_array($fields) )
    {
        for($i=0, $max=count($fields); $i<$max; ++$i)
        {
            $fields[$i] = __db_strip($table, $fields[$i]);
        }
        return $fields;
    }
    else
    {
        return __db_strip($table, $fields);
    }
}

/**
 * Для "чистки" строки от потенциальной инъекции
 * Инфо:
 *  Эта ф-я уже была у меня.
 * @param string $str
 * @return string
 */
function __db_strip($table, $str)
{
    return mysqli_real_escape_string(__db_get_host_link($table), $str);
}

function __db_get_host_link($table)
{
    $_host = &$GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ];
    if( $_host['link'] === null )
    {
        $_host['link'] = mysqli_connect($_host['host'], $_host['username'], $_host['password']);

        if( mysqli_connect_errno() || !$_host['link'] )
        {
            throw new Exception('Ошибка подключения к БД. ('.mysqli_connect_error().')');  // TODO:
        }
        mysqli_set_charset($_host['link'], "utf8");
    }
    return $_host['link'];
}

function __db_join_join(Array $join)
{
    $_q = '';
    $_joinType = 'LEFT';
    foreach($join as $tableAlias => $params)
    {
        if(!isset($params['on']))
        {
            throw new Exception('Необходимо указать данные для связи таблицы.');
        }

        if(isset($params['type'])) $_joinType=$params['type'];

        $_q .= ' '.$_joinType.' JOIN '.__db_get_table_name($tableAlias).' as '.$GLOBALS['tables'][$tableAlias]['table_name'];
        $_q .= ' ON ('.$params['on'].')';
    }
    return $_q;
}