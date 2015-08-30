Использование:
    <?php echo $argv[0] ?> <команда> [параметры]

Доступные команды:
    adduser <username> <password> <type> [<name>] - Добавление пользователя
        Где:
            <type> - может быть:
                admin
                customer
                performer

    passwd <username> <new password> - Изменение пароля

    migrate <subcommand> - Запуск миграции с указанной подкомандой
        Где:
            <subcommand> - по умолчанию tables
                tables - устанавливает таблицы
    <?php
    echo "\n";