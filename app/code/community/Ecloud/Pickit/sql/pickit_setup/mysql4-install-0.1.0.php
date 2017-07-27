<?php
// @var $setup Mage_Eav_Model_Entity_Setup
$setup = $this;

$setup->startSetup();

$setup->run("
    CREATE TABLE IF NOT EXISTS `{$setup->getTable('pickit_order')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
        `id_orden` int(11) NOT NULL,
        `direccion` varchar(255) NOT NULL,
        `localidad` varchar(255) NOT NULL,
        `provincia` varchar(255) NOT NULL,
        `cp_destino` varchar(255) NOT NULL,
        `nombre` varchar(255) NOT NULL,
        `apellido` varchar(255) NOT NULL,
        `telefono` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `precio` float NOT NULL,
        `valor_declarado` float NOT NULL,
        `volumen` float NOT NULL,
        `peso` float NOT NULL,
        `cod_tracking` VARCHAR( 255 ) NOT NULL,
        `estado` VARCHAR( 255 ) NOT NULL,
        `tracking` TEXT NOT NULL,
        `constancia` varchar(600) NOT NULL,
        `order_increment_id` varchar(50) NOT NULL,
        `id_cotizacion` INT(11) NOT NULL,
        `dni` VARCHAR(20) NOT NULL,
        `datos_sucursal` VARCHAR(255) NOT NULL,
        `id_transaccion` INT(11) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$setup->endSetup();