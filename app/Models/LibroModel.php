<?php

require_once __DIR__ . '/../Core/Database.php';

class LibroModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Obtiene los libros para la tabla administrativa.
     */
    public function listar(
        string $busqueda,
        int $limite,
        int $offset
    ): array {
        $sql = "
            SELECT
                l.id,
                l.titulo,
                l.autor,
                l.descripcion,
                l.categoria_id,

                l.costo,
                l.tipo_acceso,
                l.precio_acceso,
                l.dias_acceso,

                l.origen,
                l.institucion_origen,
                l.url_externo,

                l.unidades_totales,
                l.unidades_disponibles,

                l.imagen,
                l.thumbnail,
                l.archivo_pdf,

                l.created_at,

                c.nombre AS categoria_nombre

            FROM libros l

            INNER JOIN categorias c
                ON l.categoria_id = c.id

            WHERE
                l.titulo LIKE :busqueda_titulo
                OR l.autor LIKE :busqueda_autor
                OR c.nombre LIKE :busqueda_categoria

            ORDER BY l.titulo ASC

            LIMIT :limite
            OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        $textoBusqueda = "%{$busqueda}%";

        $stmt->bindValue(
            ':busqueda_titulo',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_autor',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_categoria',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':limite',
            $limite,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta los libros encontrados para la paginación.
     */
    public function contar(string $busqueda): int
    {
        $sql = "
            SELECT COUNT(*) AS total

            FROM libros l

            INNER JOIN categorias c
                ON l.categoria_id = c.id

            WHERE
                l.titulo LIKE :busqueda_titulo
                OR l.autor LIKE :busqueda_autor
                OR c.nombre LIKE :busqueda_categoria
        ";

        $stmt = $this->db->prepare($sql);

        $textoBusqueda = "%{$busqueda}%";

        $stmt->execute([
            ':busqueda_titulo' => $textoBusqueda,
            ':busqueda_autor' => $textoBusqueda,
            ':busqueda_categoria' => $textoBusqueda
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Obtiene todos los datos de un libro por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "
            SELECT
                l.*,
                c.nombre AS categoria_nombre

            FROM libros l

            INNER JOIN categorias c
                ON l.categoria_id = c.id

            WHERE l.id = :id

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    /**
     * Registra un libro nuevo.
     */
    public function crear(array $datos): void
    {
        /*
         * Los libros externos no tienen unidades disponibles
         * dentro de nuestra biblioteca.
         */
        $unidadesDisponibles =
            $datos['origen'] === 'propio'
                ? (int)$datos['unidades_totales']
                : 0;

        $sql = "
            INSERT INTO libros (
                titulo,
                autor,
                descripcion,
                categoria_id,

                costo,
                tipo_acceso,
                precio_acceso,
                dias_acceso,

                origen,
                institucion_origen,
                url_externo,

                unidades_totales,
                unidades_disponibles,

                imagen,
                thumbnail,
                archivo_pdf,

                firma_digital
            )
            VALUES (
                :titulo,
                :autor,
                :descripcion,
                :categoria_id,

                :costo,
                :tipo_acceso,
                :precio_acceso,
                :dias_acceso,

                :origen,
                :institucion_origen,
                :url_externo,

                :unidades_totales,
                :unidades_disponibles,

                :imagen,
                :thumbnail,
                :archivo_pdf,

                :firma_digital
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':titulo' => $datos['titulo'],
            ':autor' => $datos['autor'],
            ':descripcion' => $datos['descripcion'],
            ':categoria_id' => $datos['categoria_id'],

            ':costo' => $datos['costo'],
            ':tipo_acceso' => $datos['tipo_acceso'],
            ':precio_acceso' => $datos['precio_acceso'],
            ':dias_acceso' => $datos['dias_acceso'],

            ':origen' => $datos['origen'],
            ':institucion_origen' =>
                $datos['institucion_origen'],
            ':url_externo' => $datos['url_externo'],

            ':unidades_totales' =>
                $datos['unidades_totales'],
            ':unidades_disponibles' =>
                $unidadesDisponibles,

            ':imagen' => $datos['imagen'],
            ':thumbnail' => $datos['thumbnail'],
            ':archivo_pdf' => $datos['archivo_pdf'],

            ':firma_digital' => $datos['firma_digital']
        ]);
    }

    /**
     * Actualiza un libro conservando la cantidad
     * de unidades que se encuentran prestadas.
     */
    public function actualizar(
        int $id,
        array $datos
    ): void {
        try {
            $this->db->beginTransaction();

            /*
             * Se consulta y bloquea temporalmente el registro
             * mientras se calculan las unidades.
             */
            $sqlActual = "
                SELECT
                    unidades_totales,
                    unidades_disponibles

                FROM libros

                WHERE id = :id

                FOR UPDATE
            ";

            $stmtActual = $this->db->prepare($sqlActual);

            $stmtActual->bindValue(
                ':id',
                $id,
                PDO::PARAM_INT
            );

            $stmtActual->execute();

            $libroActual =
                $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$libroActual) {
                throw new RuntimeException(
                    'El libro que intenta actualizar no existe.'
                );
            }

            $totalesAnteriores =
                (int)$libroActual['unidades_totales'];

            $disponiblesAnteriores =
                (int)$libroActual['unidades_disponibles'];

            /*
             * Ejemplo:
             *
             * Totales anteriores: 10
             * Disponibles anteriores: 7
             * Prestados: 3
             */
            $unidadesPrestadas = max(
                0,
                $totalesAnteriores - $disponiblesAnteriores
            );

            $nuevoTotal =
                (int)$datos['unidades_totales'];

            /*
             * Un libro externo no puede tener unidades
             * dentro de nuestra biblioteca.
             */
            if ($datos['origen'] === 'externo') {
                if ($unidadesPrestadas > 0) {
                    throw new RuntimeException(
                        'No puede convertir en externo un libro con unidades prestadas.'
                    );
                }

                $nuevoTotal = 0;
                $nuevasDisponibles = 0;
            } else {
                if ($nuevoTotal < $unidadesPrestadas) {
                    throw new RuntimeException(
                        'El total no puede ser menor que la cantidad de unidades prestadas.'
                    );
                }

                $nuevasDisponibles =
                    $nuevoTotal - $unidadesPrestadas;
            }

            $sql = "
                UPDATE libros

                SET
                    titulo = :titulo,
                    autor = :autor,
                    descripcion = :descripcion,
                    categoria_id = :categoria_id,

                    costo = :costo,
                    tipo_acceso = :tipo_acceso,
                    precio_acceso = :precio_acceso,
                    dias_acceso = :dias_acceso,

                    origen = :origen,
                    institucion_origen = :institucion_origen,
                    url_externo = :url_externo,

                    unidades_totales = :unidades_totales,
                    unidades_disponibles = :unidades_disponibles,

                    imagen = :imagen,
                    thumbnail = :thumbnail,
                    archivo_pdf = :archivo_pdf,

                    firma_digital = :firma_digital

                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':titulo' => $datos['titulo'],
                ':autor' => $datos['autor'],
                ':descripcion' => $datos['descripcion'],
                ':categoria_id' => $datos['categoria_id'],

                ':costo' => $datos['costo'],
                ':tipo_acceso' => $datos['tipo_acceso'],
                ':precio_acceso' =>
                    $datos['precio_acceso'],
                ':dias_acceso' =>
                    $datos['dias_acceso'],

                ':origen' => $datos['origen'],
                ':institucion_origen' =>
                    $datos['institucion_origen'],
                ':url_externo' =>
                    $datos['url_externo'],

                ':unidades_totales' =>
                    $nuevoTotal,
                ':unidades_disponibles' =>
                    $nuevasDisponibles,

                ':imagen' => $datos['imagen'],
                ':thumbnail' => $datos['thumbnail'],
                ':archivo_pdf' =>
                    $datos['archivo_pdf'],

                ':firma_digital' =>
                    $datos['firma_digital'],

                ':id' => $id
            ]);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cuenta las reservas relacionadas con un libro.
     */
    public function contarReservasAsociadas(int $id): int
    {
        $sql = "
            SELECT COUNT(*) AS total

            FROM reservas

            WHERE libro_id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($resultado['total'] ?? 0);
    }

    /**
     * Elimina un libro.
     */
    public function eliminar(int $id): void
    {
        $sql = "
            DELETE FROM libros
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();
    }

    /**
     * Obtiene los libros para la exportación a Excel.
     */
    public function obtenerParaExcel(): array
    {
        $sql = "
            SELECT
                l.titulo,
                l.autor,
                c.nombre AS categoria,
                l.descripcion,

                l.costo,
                l.tipo_acceso,
                l.precio_acceso,
                l.dias_acceso,

                l.origen,
                l.institucion_origen,
                l.url_externo,

                l.unidades_totales,
                l.unidades_disponibles,

                CASE
                    WHEN l.archivo_pdf IS NULL
                         OR l.archivo_pdf = ''
                    THEN 'No'
                    ELSE 'Sí'
                END AS tiene_pdf,

                l.created_at

            FROM libros l

            INNER JOIN categorias c
                ON l.categoria_id = c.id

            ORDER BY
                c.nombre ASC,
                l.titulo ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     /**
     * Obtiene los libros que se mostrarán
     * en el catálogo del estudiante.
     */
    public function listarCatalogo(
        string $busqueda = "",
        int $categoriaId = 0
    ): array {
        $sql = "
            SELECT
                l.*,
                c.nombre AS categoria_nombre

            FROM libros l

            INNER JOIN categorias c
                ON l.categoria_id = c.id

            WHERE (
                l.titulo LIKE :busqueda_titulo

                OR COALESCE(l.autor, '')
                    LIKE :busqueda_autor

                OR c.nombre
                    LIKE :busqueda_categoria
            )
        ";

        /*
         * El filtro de categoría solamente se agrega
         * cuando el estudiante selecciona una.
         */
        if ($categoriaId > 0) {
            $sql .= "
                AND l.categoria_id = :categoria_id
            ";
        }

        $sql .= "
            ORDER BY l.titulo ASC
        ";

        $stmt = $this->db->prepare($sql);

        $textoBusqueda = "%{$busqueda}%";

        $stmt->bindValue(
            ':busqueda_titulo',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_autor',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':busqueda_categoria',
            $textoBusqueda,
            PDO::PARAM_STR
        );

        if ($categoriaId > 0) {
            $stmt->bindValue(
                ':categoria_id',
                $categoriaId,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las cantidades que aparecerán
     * en la parte superior del catálogo.
     */
    public function obtenerResumenCatalogo(): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total_titulos,

                COALESCE(
                    SUM(unidades_disponibles),
                    0
                ) AS total_disponibles

            FROM libros
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resultado) {
            return [
                'total_titulos' => 0,
                'total_disponibles' => 0
            ];
        }

        return [
            'total_titulos' =>
                (int)$resultado['total_titulos'],

            'total_disponibles' =>
                (int)$resultado['total_disponibles']
        ];
    }
}