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
 * Но, всегда возвращает только одну(первую) запись
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
 * Для формирования запроса использует {@link __db_create_query_select()}
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @param mixed $fields Поля для показа
 * @param mixed $cond Условие для выборки
 * @param mixed $end Добавление в конец запроса
 * @return Array
 */
function db_select($table, $fields='*', $cond=null, $end=null, Array $join=array())
{
    $_q = __db_create_query_select($table, $fields, $cond, $end, $join);
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

/**
 * Выполнение UPDATE запроса по указанной таблице
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @param Array $upd Поля для обновления. Ключ - название поля, Значение - Значение поля.
 *                      Можно в Значение передать еще массив по правилу 
 *                      для формирования подзапроса, подробнее см.{@link __db_str_value()}
 * @param string $cond Строка для WHERE (без кл.слова WHERE)
 * @param Array $cond_params Значения безопасно подключаемые для $cond
 * @return bool
 */
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

/**
 * Выполнение INSERT запроса
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @param Array $set Ключ - поле, Значение - значение для поля
 * @param bool $id Нужно ли возвращать ключ созданной записи
 * @return mixed
 */
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

/**
 * Получить ошибку возникшую при выполнении запроса
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @return mixed
 */
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

/**
 * Выполнить транзакцию
 */
function db_transaction_commit()
{
    __db_action_transaction('mysqli_commit');
}

/**
 * Отказтить транзакцию
 */
function db_transaction_rollback()
{
    __db_action_transaction('mysqli_rollback');
}

/**
 * Начать транзакцию
 * Ставит только отметки.
 * Инициализация транзакции происходит в {@__db_run}
 */
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

/**
 * Преобразование названия таблицы из алаиса вместе в БД
 * @param string $table Алиас таблицы
 * @return string
 */
function __db_get_table_name($table)
{
    __db_check_all_configs($table); // TODO: !!! exception
    return '`'.$GLOBALS['tables'][$table]['db_name'].'`.`'.$GLOBALS['tables'][$table]['table_name'].'`';
}

/**
 * Выполнение запроса
 * @param string $query Подготовленный запрос
 * @param string $table Алиас таблицы
 * @return mixed см.http://php.net/manual/ru/mysqli.query.php
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

/**
 * Старт транзакции
 * Работает относительно хоста
 * @param string $table Алиас таблицы
 */
function __db_start_transaction($table)
{
    if( 
            !empty($GLOBALS['mysql']['transaction']) 
            && $GLOBALS['mysql']['transaction'] === true 
            && !in_array($GLOBALS['tables'][$table]['db_host'], $GLOBALS['mysql']['hosts'])
        )
    {
        mysqli_begin_transaction(__db_get_host_link($table));
        mysqli_autocommit(__db_get_host_link($table), FALSE);
        $GLOBALS['mysql']['hosts'][] = $GLOBALS['tables'][$table]['db_host'];
    }
}

/**
 * Проверка, работает ли транзакцию
 * @return bool
 */
function __db_is_transaction()
{
    return !empty($GLOBALS['mysql']['transaction']) && $GLOBALS['mysql']['transaction'] === true;
}

/**
 * Действие с открытой транзакцией
 * Вызов только из (!!!) {@link db_transaction_commit()}, {@link db_transaction_rollback()}
 * @param string $action Действие. Ф-ия mysqli
 */
function __db_action_transaction($action)
{
    if(!empty($GLOBALS['mysql']['transaction']) && $GLOBALS['mysql']['transaction']===true )
    {
        for($i=count($GLOBALS['mysql']['hosts'])-1; $i>=0; --$i)
        {
            $_res = $action( $GLOBALS['db_hosts'][$GLOBALS['mysql']['hosts'][$i]]['link'] );
        }
        // mysqli_close(__db_get_host_link($table));
        unset($GLOBALS['mysql']['transaction'], $GLOBALS['mysql']['hosts']);
    }
}

/**
 * Проверка всех необходимых конфигураций для таблицы через алиас
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @return bool
 */
function __db_check_all_configs($table)
{
    if( !isset($GLOBALS['tables'][$table]) )
    {
        throw new Exception('Указанной таблицы не существует. ('.$table.')'); // TODO:
    }

    if( !isset( $GLOBALS['db_hosts'][ $GLOBALS['tables'][$table]['db_host'] ] ) )
    {
        throw new Exception('Указанной таблицы не существует. ('.$table.')'); // TODO:
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
 * @param string $str
 * @return string
 */
function __db_strip($table, $str)
{
    return mysqli_real_escape_string(__db_get_host_link($table), $str);
}

/**
 * Получит линк соединения от алиаса таблицы
 * Если линка нет, то создает
 * @param string $table
 * @return Object см.http://php.net/manual/ru/mysqli.construct.php
 */
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

/**
 * Формирует join строку
 * @todo Требует доработки
 * @param Array $join вид кофиги:
 *      array(
 *          {TABLE ALIAS} => array(
 *              'on' => {Условие при котором подключается таблица},
 *              ['type' => {LEFT|RIGHT|INNER}]
 *          ),
 *          ...
 *      )
 * @return string
 */
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

/**
 * Формирование SELECT запроса
 * Параметры см.{@link db_select()}
 * @todo Безопасные $fields
 * @todo Добавить params, обработка $cond
 * @return string;
 */
function __db_create_query_select($table, $fields='*', $cond=null, $end=null, Array $join=array())
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
    return $_q;
}

/**
 * Формирование строки присваивания значения для {@link db_update()}
 * @todo Доработать и Описать подробнее параметр $fields
 * @param string $table Алиас таблицы. Ключ массива $GLOBALS['tables']
 * @param Array $fields см.параметр $upd у {@link db_update()}
 * @return string
 */
function __db_str_value($table, Array $fields)
{
    $_s = [];
    while( list($field, $value) = each($fields) )
    {
        $_asValue = null;
        $field = __db_fields_strip($table, $field);
        $field = strtr($field, array('.'=>'`.`'));
        if(is_array($value))
        {
            // INFO: Можно расширять!
            if(isset($value['select'])) // Вложенный SELECT
            {
                // TODO: Проверка значений в $value
                $_tableAlias = $value['select']['from'];
                $_join       = empty($value['select']['join']) ? array() : $value['select']['join'];
                $_pref       = empty($value['select']['pref']) ? null : $value['select']['pref'];
                $_fields     = empty($value['select']['fields']) ? '*' : $value['select']['fields'];
                $_cond       = empty($value['select']['cond']) ? '' : $value['select']['cond'];
                $_end        = empty($value['select']['end']) ? '' : $value['select']['end'];
                $_asValue = $_pref.'('.__db_create_query_select($_tableAlias, $_fields, $_cond, $_end, $_join).')';
            }
        }
        else
        {
             $_asValue = '"'.__db_fields_strip($table, $value).'"';
        }
        $_s[] = '`'.$field.'` ='.$_asValue;
    }
    return implode(', ', $_s);
}