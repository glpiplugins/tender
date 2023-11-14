CREATE TABLE `glpi_plugin_tender_tenders` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `comment` TEXT,
            `tender_subject` VARCHAR(255) DEFAULT NULL,
            `start_date` DATE,
            `end_date` DATE,
            `submission_date` DATE,
            `users_id` int unsigned DEFAULT NULL,
            `default_locations_id` int unsigned DEFAULT NULL,
            `default_delivery_locations_id` int unsigned DEFAULT NULL,
            `users_id` int unsigned DEFAULT NULL,
            `entities_id` int unsigned NOT NULL default '0',
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `entities_id` (`entities_id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_tendersuppliers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tenders_id` int unsigned DEFAULT NULL,
            `suppliers_id` int unsigned DEFAULT NULL,
            `offer_date` DATE,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_tenderitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `quantity` int unsigned DEFAULT NULL,
            `net_price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `tax` int unsigned DEFAULT NULL,
            `tenders_id` int unsigned DEFAULT NULL,
            `tender_catalogueitems_id` int unsigned DEFAULT NULL,
            `entities_id` int unsigned NOT NULL default '0',
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_distributions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tenderitems_id` int unsigned DEFAULT NULL,
            `quantity` int unsigned DEFAULT NULL,
            `locations_id` int unsigned DEFAULT NULL,
            `delivery_locations_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_catalogueitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `completename` varchar(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `manufacturers_id` int unsigned DEFAULT NULL,
            `manufacturers_reference` varchar(255) DEFAULT NULL,
            `itemtype` varchar(255) DEFAULT NULL,
            `types_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_catalogueitemsuppliers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `catalogueitems_id` int unsigned DEFAULT NULL,
            `suppliers_id` int unsigned DEFAULT NULL,
            `suppliers_reference` varchar(255) DEFAULT NULL,
            `net_price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `suppliers_reference` (`suppliers_reference`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_offeritems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tendersuppliers_id` int unsigned DEFAULT NULL,
            `tenderitems_id` int unsigned DEFAULT NULL,
            `net_price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `tax` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_orders` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tendersuppliers_id` int unsigned DEFAULT NULL,
            `tenders_id` int unsigned DEFAULT NULL,
            `contacts_id` int unsigned DEFAULT NULL,
            `users_id` int unsigned DEFAULT NULL,
            `order_date` DATE,
            `order_reference` varchar(255) DEFAULT NULL,
            `approx_delivery_date` DATE,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_deliveries` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tenders_id` int unsigned DEFAULT NULL,
            `delivery_date` DATE,
            `delivery_reference` varchar(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_deliveryitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `deliveries_id` int unsigned DEFAULT NULL,
            `distributions_id` int unsigned DEFAULT NULL,
            `quantity` int unsigned DEFAULT NULL,
            `delivery_date` DATE,
            `delivery_reference` varchar(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT NULL,
            `date_creation` TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;