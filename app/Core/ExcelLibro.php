<?php

class ExcelLibro
{
    /**
     * Escapa los valores para mostrarlos correctamente
     * dentro del archivo generado.
     */
    private static function escapar(mixed $valor): string
    {
        return htmlspecialchars(
            (string)$valor,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Exporta el catálogo completo de libros.
     */
    public static function exportar(array $libros): void
    {
        $nombreArchivo =
            'reporte_libros_' .
            date('Y-m-d_H-i-s') .
            '.xls';

        header(
            'Content-Type: application/vnd.ms-excel; charset=utf-8'
        );

        header(
            'Content-Disposition: attachment; filename="' .
            $nombreArchivo .
            '"'
        );

        header('Cache-Control: max-age=0');
        header('Pragma: public');

        /*
         * Permite que Excel reconozca correctamente
         * las tildes, eñes y demás caracteres.
         */
        echo "\xEF\xBB\xBF";

        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '</head>';

        echo '<body>';

        echo '<h2>Catálogo de Libros - Biblioteca Digital</h2>';

        echo '<p>';
        echo 'Fecha de generación: ';
        echo self::escapar(date('d/m/Y H:i:s'));
        echo '</p>';

        echo '<table border="1" cellpadding="5" cellspacing="0">';

        /*
         * Encabezados.
         */
        echo '<thead>';
        echo '<tr style="font-weight: bold;">';

        echo '<th>Título</th>';
        echo '<th>Autor</th>';
        echo '<th>Categoría</th>';
        echo '<th>Descripción</th>';

        echo '<th>Costo de adquisición</th>';
        echo '<th>Tipo de acceso</th>';
        echo '<th>Precio de acceso</th>';
        echo '<th>Días de acceso</th>';

        echo '<th>Origen</th>';
        echo '<th>Institución de origen</th>';
        echo '<th>URL externa</th>';

        echo '<th>Unidades totales</th>';
        echo '<th>Unidades disponibles</th>';
        echo '<th>Disponibilidad</th>';

        echo '<th>Tiene PDF</th>';
        echo '<th>Fecha de registro</th>';

        echo '</tr>';
        echo '</thead>';

        echo '<tbody>';

        if (empty($libros)) {
            echo '<tr>';
            echo '<td colspan="16">';
            echo 'No existen libros registrados.';
            echo '</td>';
            echo '</tr>';
        } else {
            foreach ($libros as $libro) {
                $origen =
                    $libro['origen'] ?? 'propio';

                $esExterno =
                    $origen === 'externo';

                $tipoAcceso =
                    $libro['tipo_acceso'] ?? 'gratuito';

                $unidadesDisponibles = (int)(
                    $libro['unidades_disponibles'] ?? 0
                );

                /*
                 * Disponibilidad física.
                 */
                if ($esExterno) {
                    $disponibilidad =
                        'Gestionado por biblioteca externa';
                } elseif ($unidadesDisponibles > 0) {
                    $disponibilidad = 'Disponible';
                } else {
                    $disponibilidad = 'No disponible';
                }

                /*
                 * Información del acceso digital.
                 */
                if ($esExterno) {
                    $tipoAccesoTexto =
                        'Gestionado externamente';

                    $precioAccesoTexto =
                        'No aplica';

                    $diasAccesoTexto =
                        'No aplica';
                } elseif ($tipoAcceso === 'pago') {
                    $tipoAccesoTexto =
                        'Pagado';

                    $precioAccesoTexto =
                        number_format(
                            (float)($libro['precio_acceso'] ?? 0),
                            2,
                            '.',
                            ''
                        );

                    $diasAccesoTexto =
                        (string)(
                            (int)($libro['dias_acceso'] ?? 0)
                        );
                } else {
                    $tipoAccesoTexto =
                        'Gratuito';

                    $precioAccesoTexto =
                        '0.00';

                    $diasAccesoTexto =
                        'Permanente';
                }

                echo '<tr>';

                echo '<td>';
                echo self::escapar(
                    $libro['titulo'] ?? ''
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['autor'] ?? ''
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['categoria'] ?? ''
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['descripcion'] ?? ''
                );
                echo '</td>';

                /*
                 * Costo que pagó la biblioteca.
                 */
                echo '<td>';
                echo number_format(
                    (float)($libro['costo'] ?? 0),
                    2,
                    '.',
                    ''
                );
                echo '</td>';

                /*
                 * Acceso digital.
                 */
                echo '<td>';
                echo self::escapar(
                    $tipoAccesoTexto
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $precioAccesoTexto
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $diasAccesoTexto
                );
                echo '</td>';

                /*
                 * Origen.
                 */
                echo '<td>';
                echo self::escapar(
                    $esExterno
                        ? 'Externo'
                        : 'Propio'
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['institucion_origen'] ?? ''
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['url_externo'] ?? ''
                );
                echo '</td>';

                /*
                 * Unidades.
                 */
                echo '<td>';
                echo (int)(
                    $libro['unidades_totales'] ?? 0
                );
                echo '</td>';

                echo '<td>';
                echo $unidadesDisponibles;
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $disponibilidad
                );
                echo '</td>';

                /*
                 * PDF y fecha.
                 */
                echo '<td>';
                echo self::escapar(
                    $libro['tiene_pdf'] ?? 'No'
                );
                echo '</td>';

                echo '<td>';
                echo self::escapar(
                    $libro['created_at'] ?? ''
                );
                echo '</td>';

                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';

        echo '</body>';
        echo '</html>';

        exit;
    }
}