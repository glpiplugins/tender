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
            `plugin_tender_tendertypes_id` int unsigned DEFAULT NULL,
            `plugin_tender_statuses_id` int unsigned DEFAULT NULL,
            `entities_id` int unsigned NOT NULL default '0',
            `estimated_net_total` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `entities_id` (`entities_id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_tendersuppliers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `suppliers_id` int unsigned DEFAULT NULL,
            `offer_date` DATE,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `plugin_tender_catalogueitems_id` int unsigned DEFAULT NULL,
            `plugin_tender_measures_id` int unsigned DEFAULT NULL,
            `entities_id` int unsigned NOT NULL default '0',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_distributions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tenderitems_id` int unsigned DEFAULT NULL,
            `quantity` int unsigned DEFAULT NULL,
            `percentage` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `plugin_tender_financials_id` int unsigned DEFAULT NULL,
            `locations_id` int unsigned DEFAULT NULL,
            `delivery_locations_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
            `models_id` int unsigned DEFAULT NULL,
            `itemtype` varchar(255) DEFAULT NULL,
            `types_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_catalogueitemsuppliers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_catalogueitems_id` int unsigned DEFAULT NULL,
            `suppliers_id` int unsigned DEFAULT NULL,
            `suppliers_reference` varchar(255) DEFAULT NULL,
            `net_price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `suppliers_reference` (`suppliers_reference`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_offeritems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tendersuppliers_id` int unsigned DEFAULT NULL,
            `plugin_tender_tenderitems_id` int unsigned DEFAULT NULL,
            `net_price` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `tax` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_orders` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tendersuppliers_id` int unsigned DEFAULT NULL,
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `contacts_id` int unsigned DEFAULT NULL,
            `users_id` int unsigned DEFAULT NULL,
            `order_date` DATE,
            `order_reference` varchar(255) DEFAULT NULL,
            `approx_delivery_date` DATE,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_deliveries` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `delivery_date` DATE,
            `name` varchar(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_deliveryitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_deliveries_id` int unsigned DEFAULT NULL,
            `plugin_tender_distributions_id` int unsigned DEFAULT NULL,
            `quantity` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`),
            FOREIGN KEY (`deliveries_id`)
            REFERENCES `glpi_plugin_tender_deliveries`(`id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
        

CREATE TABLE `glpi_plugin_tender_tendertypes` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `min_value` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `max_value` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;        


CREATE TABLE `glpi_plugin_tender_tendertypeoptions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tendertypes_id` int unsigned DEFAULT NULL,
            `stage` int unsigned DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  


CREATE TABLE `glpi_plugin_tender_financials` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `reference` VARCHAR(255) DEFAULT NULL,
            `entities_id` int unsigned NOT NULL default '0',
            `plugin_tender_costcenters_id` int unsigned DEFAULT NULL,
            `plugin_tender_accounts_id` int unsigned DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `entities_id` (`entities_id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  


CREATE TABLE `glpi_plugin_tender_costcenters` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  

CREATE TABLE `glpi_plugin_tender_accounts` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  

CREATE TABLE `glpi_plugin_tender_financialitems` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_financials_id` int unsigned DEFAULT NULL,
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `type` int unsigned NOT NULL default '0',
            `year` DATE,
            `value` decimal(20,4) NOT NULL DEFAULT '0.0000',
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `plugin_tender_financials_id` (`plugin_tender_financials_id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  

CREATE TABLE `glpi_plugin_tender_invoices` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_tender_tenders_id` int unsigned DEFAULT NULL,
            `invoice_date` DATE,
            `name` varchar(255) DEFAULT NULL,
            `internal_reference` varchar(255) DEFAULT NULL,
            `posting_text` VARCHAR(255) DEFAULT NULL,
            `due_date` DATE,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`),
            FOREIGN KEY (`tenders_id`)
            REFERENCES `glpi_plugin_tender_tenders`(`id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_invoiceitems` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `plugin_tender_invoices_id` INT UNSIGNED DEFAULT NULL,
            `plugin_tender_distributions_id` INT UNSIGNED DEFAULT NULL,
            `quantity` INT UNSIGNED DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`),
            FOREIGN KEY (`plugin_tender_invoices_id`)
            REFERENCES `glpi_plugin_tender_invoices`(`id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_tenderstatuses` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;   


CREATE TABLE `glpi_plugin_tender_configs` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `value` VARCHAR(5000) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  


CREATE TABLE `glpi_plugin_tender_measures` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) DEFAULT NULL,
        `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `name` (`name`),
        KEY `date_mod` (`date_mod`),
        KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  

CREATE TABLE `glpi_plugin_tender_measureitems` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `plugin_tender_measures_id` INT UNSIGNED DEFAULT NULL,
        `plugin_tender_costcenters_id` INT UNSIGNED DEFAULT NULL,
        `value` decimal(20,4) NOT NULL DEFAULT '0.0000',
        `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `date_mod` (`date_mod`),
        KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `glpi_plugin_tender_tendersubjects` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` VARCHAR(255) DEFAULT NULL,
            `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `date_mod` (`date_mod`),
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;   

CREATE TABLE `glpi_plugin_tender_documenttemplates` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) DEFAULT NULL,
        `itemtype` VARCHAR(255) DEFAULT NULL,
        `template_path` VARCHAR(255) DEFAULT NULL,
        `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `name` (`name`),
        KEY `date_mod` (`date_mod`),
        KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  

CREATE TABLE `glpi_plugin_tender_documenttemplate_parameters` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `plugin_tender_documenttemplates_id` INT UNSIGNED DEFAULT NULL,
        `name` VARCHAR(255) DEFAULT NULL,
        `type` VARCHAR(255) DEFAULT NULL,
        `value` VARCHAR(255) DEFAULT NULL,
        `date_mod` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `name` (`name`),
        KEY `date_mod` (`date_mod`),
        KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;  


INSERT INTO `glpi_fieldunicities` 
    (
        `name`,
        `itemtype`,
        `fields`,
        `is_active`,
        `action_refuse`,
        `action_notify`
    ) VALUES (
        'Tender name',
        'GlpiPlugin\\Tender\\Tender',
        'name',
        1,
        1,
        1
    );

INSERT INTO `glpi_displaypreferences` 
    (
        `itemtype`,
        `num`,
        `rank`,
        `users_id`
    ) VALUES (
        'GlpiPlugin\\Tender\\Tender',
        4,
        1,
        0
    ), (
        'GlpiPlugin\\Tender\\Tender',
        8,
        2,
        0
    ), (
        'GlpiPlugin\\Tender\\Tender',
        9,
        3,
        0
    ), (
        'GlpiPlugin\\Tender\\Tender',
        10,
        4,
        0
    );

INSERT INTO `glpi_plugin_tender_documenttemplates` 
    (
        `id`,
        `name`,
        `itemtype`
    ) VALUES (
        1,
        'Invoice',
        'invoice'
    );