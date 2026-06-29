-- ============================================================
--  BIBLIOTECA DIGITAL
--  Base de datos lista para phpMyAdmin / MySQL / WAMP
-- ============================================================

CREATE DATABASE IF NOT EXISTS biblioteca_digital
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE biblioteca_digital;

-- ------------------------------------------------------------
-- 1. USUARIOS (Irvin)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
  id                INT          NOT NULL AUTO_INCREMENT,
  usuario           VARCHAR(50)  NOT NULL UNIQUE,
  password_hash     VARCHAR(255) NOT NULL,
  rol               ENUM('admin','estudiante') NOT NULL DEFAULT 'admin',
  intentos_fallidos TINYINT      NOT NULL DEFAULT 0,
  bloqueado         TINYINT(1)   NOT NULL DEFAULT 0,
  created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Usuario admin por defecto: admin / root2514
INSERT INTO usuarios (usuario, password_hash, rol)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- IMPORTANTE: ese hash es solo de ejemplo.
-- En PHP genera el hash real con: password_hash('root2514', PASSWORD_BCRYPT)
-- y reemplaza este INSERT antes de entregar el proyecto.

-- ------------------------------------------------------------
-- 2. LOGS DE ACCESO (Irvin)
-- ------------------------------------------------------------
CREATE TABLE logs_acceso (
  id          INT          NOT NULL AUTO_INCREMENT,
  usuario_id  INT              NULL,
  usuario_txt VARCHAR(50)      NULL,
  ip          VARCHAR(45)  NOT NULL,
  fecha_hora  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resultado   ENUM('exitoso','fallido') NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_log_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. CARRERAS (Aaron)
-- ------------------------------------------------------------
CREATE TABLE carreras (
  id     INT          NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO carreras (nombre) VALUES
  ('Ingeniería en Sistemas'),
  ('Enfermería'),
  ('Contabilidad'),
  ('Administración de Empresas'),
  ('Derecho');

-- ------------------------------------------------------------
-- 4. ESTUDIANTES (Aaron)
-- ------------------------------------------------------------
CREATE TABLE estudiantes (
  id               INT         NOT NULL AUTO_INCREMENT,
  cip              VARCHAR(20) NOT NULL UNIQUE,
  primer_nombre    VARCHAR(50) NOT NULL,
  segundo_nombre   VARCHAR(50)     NULL,
  primer_apellido  VARCHAR(50) NOT NULL,
  segundo_apellido VARCHAR(50)     NULL,
  fecha_nacimiento DATE        NOT NULL,
  carrera_id       INT         NOT NULL,
  created_at       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_estudiante_carrera
    FOREIGN KEY (carrera_id) REFERENCES carreras(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. CATEGORIAS (Yov)
-- ------------------------------------------------------------
CREATE TABLE categorias (
  id     INT          NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO categorias (nombre) VALUES
  ('Matemáticas'),
  ('Química'),
  ('Sistemas'),
  ('Salud'),
  ('Estadística'),
  ('Lógica'),
  ('Deporte'),
  ('Revistas Científicas');

-- ------------------------------------------------------------
-- 6. LIBROS (Yov)
-- ------------------------------------------------------------
CREATE TABLE libros (
  id                   INT           NOT NULL AUTO_INCREMENT,
  titulo               VARCHAR(200)  NOT NULL,
  descripcion          TEXT              NULL,
  categoria_id         INT           NOT NULL,
  unidades_totales     INT           NOT NULL DEFAULT 0,
  unidades_disponibles INT           NOT NULL DEFAULT 0,
  imagen               VARCHAR(255)      NULL,
  thumbnail            VARCHAR(255)      NULL,
  firma_digital        VARCHAR(255)      NULL,
  created_at           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_libro_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. RESERVAS (Kevyn)
-- ------------------------------------------------------------
CREATE TABLE reservas (
  id               INT      NOT NULL AUTO_INCREMENT,
  estudiante_id    INT      NOT NULL,
  libro_id         INT      NOT NULL,
  fecha_reserva    DATE     NOT NULL DEFAULT (CURRENT_DATE),
  fecha_devolucion DATE         NULL,
  estado           ENUM('en_prestamo','devuelto','por_vencer') NOT NULL DEFAULT 'en_prestamo',
  firma_digital    VARCHAR(255) NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_reserva_estudiante
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_reserva_libro
    FOREIGN KEY (libro_id) REFERENCES libros(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. SOLICITUDES (Yov / Kevyn)
-- ------------------------------------------------------------
CREATE TABLE solicitudes (
  id               INT          NOT NULL AUTO_INCREMENT,
  estudiante_id    INT          NOT NULL,
  titulo_solicitado VARCHAR(200) NOT NULL,
  area             VARCHAR(100) NOT NULL,
  comentario       TEXT             NULL,
  estado           ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_solicitud_estudiante
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  FIN DEL SCRIPT
-- ============================================================
