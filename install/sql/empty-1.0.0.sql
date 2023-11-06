CREATE TABLE `glpi_plugin_tender_tenders` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `comment` TEXT,
            `tender_subject` VARCHAR(255) DEFAULT NULL,
            `start_date` DATE,
            `end_date` DATE,
            `users_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;