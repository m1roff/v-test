<?php
$_subcommand = isset($argv[2]) ? $argv[2] : null;


switch ($_subcommand) {
    default:
    case 'tables':
        migrate_tables();
        exit;
}

function migrate_tables()
{
    require_once(dirname(__FILE__).'/../../config/sys_configs.php');
    
    while( list($alias, $config) = each($GLOBALS['tables']) )
    {
        __show_message(':::::::::  '.$alias.' ::::::::::', 'info', false);
        if( __migrate_create_table($config, $alias) === false ) continue;
    }
}

function __migrate_create_table(&$config, $alias)
{
    if(empty($config['schema']))
    {
        __show_message('no schema configuration', 'info');
        return false;
    } 
    elseif( empty($config['schema']['cols']) )
    {
        __show_message('columns info not exist.', 'info');
        return false;
    }
    require_once(dirname(__FILE__).'/../../lib/db.php');

    $_isTablseExists = db_table_exists($alias);
    var_dump($_isTablseExists);

    __show_message('creating table for "'.$alias.'"...', 'info');
    $_res = db_create_table($alias, $config['schema']['cols']);

    if(  $_res === true )
    {
        __show_message('table for "'.$alias.'" created.', 'success');
    }
    else
    {
        __show_message($_res, 'error');
    }
}