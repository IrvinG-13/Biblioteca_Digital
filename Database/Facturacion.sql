USE biblioteca_digital;

ALTER TABLE usuarios ENGINE = InnoDB;
ALTER TABLE libros ENGINE = InnoDB;
ALTER TABLE reservas ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS facturas (
    id INT NOT NULL AUTO_INCREMENT,
    numero_factura VARCHAR(30) NOT NULL,
    usuario_id INT NOT NULL,
    fecha_factura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2) NOT NULL,
    impuesto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,

    metodo_pago ENUM(
        'tarjeta',
        'yappy',
        'transferencia'
    ) NOT NULL,

    referencia_pago VARCHAR(100) NULL,

    estado ENUM(
        'pagada',
        'pendiente',
        'anulada'
    ) NOT NULL DEFAULT 'pagada',

    PRIMARY KEY (id),
    UNIQUE KEY uk_numero_factura (numero_factura),

    CONSTRAINT fk_factura_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS detalle_facturas (
    id INT NOT NULL AUTO_INCREMENT,
    factura_id INT NOT NULL,
    libro_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    dias_acceso INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,

    PRIMARY KEY (id),

    CONSTRAINT fk_detalle_factura
        FOREIGN KEY (factura_id)
        REFERENCES facturas(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_libro
        FOREIGN KEY (libro_id)
        REFERENCES libros(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;