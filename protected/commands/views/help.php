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
    <?php
    echo "\n";