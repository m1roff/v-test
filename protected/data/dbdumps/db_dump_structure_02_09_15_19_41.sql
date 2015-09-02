
--
-- База данных: `v_test`
--
CREATE DATABASE IF NOT EXISTS `v_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `v_test`;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id_orders` int(10) unsigned NOT NULL,
  `id_customer` int(10) unsigned NOT NULL,
  `id_performer` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '0',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int(10) unsigned NOT NULL,
  `type` enum('admin','customer','performer') NOT NULL DEFAULT 'performer',
  `login` varchar(100) NOT NULL,
  `password` char(60) NOT NULL COMMENT 'password_hash',
  `name` varchar(100) NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_orders`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_performer` (`id_performer`),
  ADD KEY `status` (`status`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `type` (`type`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id_orders` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_performer`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE;
