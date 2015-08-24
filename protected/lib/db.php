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
function db_select($table, $fields='*', $cond=null, $end=null)
{
    $_q = 'SELECT ';
    
    if( __db_check_all_configs($table) !== true ) // TODO: !!! Может возникнуть exception
    {
        return false;
    }

    if( is_array($fields) ) $_q .= implode(', ', $_q);
        else $_q .= $fields;

    $_q .= ' FROM `'.$GLOBALS['tables'][$table]['db_name'].'`.`'.$GLOBALS['tables'][$table]['table_name'].'`';

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

    // shortcut
    $_host = &$GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ];

    $data = [];

    // if( $_res === false )
    // {
    //     // throw new Exception(mysqli_error($_host['link'])); // TODO:
    //     return [];
    // }
    if( $_res !== false )
    {
        while( $row = mysqli_fetch_assoc($_res) )
        {
            $data[] = $row;
        }
        mysqli_free_result($_res); 
    }

    return $data;
}

function db_insert($table, Array $set)
{
    if( __db_check_all_configs($table) !== true ) // TODO: !!! Может возникнуть exception
    {
        return false;
    }

    $q = 'INSERT INTO `'.$GLOBALS['tables'][$table]['db_name'].'`.`'.$GLOBALS['tables'][$table]['table_name'].'`'
        .' SET';
    $_s = [];
    while( list($field, $value) = each($set) )
    {
        $_s[] = '`'.__db_fields_strip($field).'` = "'.__db_fields_strip($value).'"';
    }
    $q .= ' '.implode(', ', $_s);
    // unset($_s);
    // var_dump($q);
    return __db_run($q, $table);
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


// для локального использования

/**
 * Выполнение запроса
 */
function __db_run($query, $table)
{
    // ПОвторная проверка, может быть вызван отдельно
    if( __db_check_all_configs($table) !== true ) // TODO: !!! Может возникнуть exception
    {
        return fale;
    }
    // shortcut
    $_host = &$GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ];

    if( $_host['link'] === null )
    {
        $_host['link'] = mysqli_connect($_host['host'], $_host['username'], $_host['password']);
        if( mysqli_connect_errno() )
        {
            throw new Exception('Ошибка подключения к БД. ('.mysqli_connect_error().')');  // TODO:
        }
        mysqli_set_charset($_host['link'], "utf8");
    }

    return mysqli_query($_host['link'], $query);
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
function __db_fields_strip($fields)
{
    if( is_array($fields) )
    {
        for($i=0, $max=count($fields); $i<$max; ++$i)
        {
            $fields[$i] = __db_strip($fields[$i]);
        }
        return $fields;
    }
    else
    {
        return __db_strip($fields);
    }
}

/**
 * Для "чистки" строки от потенциальной инъекции
 * Инфо:
 *  Эта ф-я уже была у меня.
 * @param string $str
 * @return string
 */
function __db_strip($str)
{
    $_r = [
        ';' => '',
        ':' => '',
        '/' => '',
        '\'' => '',
        "\t" => '',
        "\n" => '',
        "\\" => '',
    ];
    return strtr($str, $_r);
}