SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `product`;

CREATE TABLE `product`
(
    `id`     int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`   varchar(255)     NOT NULL,
    `cost`   int(8) unsigned  NOT NULL,
    `markup` int(8) unsigned  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_name` (`name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `discount`;

CREATE TABLE `discount`
(
    `id`         int(10) unsigned    NOT NULL AUTO_INCREMENT,
    `product_id` int(10) unsigned    NOT NULL,
    `name`       varchar(255)        NOT NULL,
    `type`       tinyint(1) unsigned NOT NULL,
    `value`      int(8) unsigned     NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

SET FOREIGN_KEY_CHECKS = 1;
