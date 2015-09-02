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

    fillorders <amount> - Создание рэндомных заказов
        Где:
            <amount> - Кол-во заказов.
    
    truncatedb - Обнулить БД. Удаляет все заказы, обнуляет баланс у всех пользователей.
    <?php
    echo "\n";