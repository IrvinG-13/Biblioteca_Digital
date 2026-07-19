<?php

require_once __DIR__
    . '/../app/Controllers/FacturaController.php';

$controller = new FacturaController();

$controller->procesarCompra();