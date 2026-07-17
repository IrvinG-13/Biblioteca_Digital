<?php

class ExcelReserva
{
    /**
     * Escapa valores para escribirlos en Excel.
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
     * Formatea una fecha.
     */
    private static function formatearFecha(
        mixed $fecha,
        string $vacio = 'No aplica'
    ): string {
        $fecha = trim((string)$fecha);

        if ($fecha === '') {
            return $vacio;
        }

        try {
            return (new DateTime($fecha))->format('d/m/Y');
        } catch (Throwable $e) {
            return $fecha;
        }
    }

    /**
     * Convierte el estado interno en un texto visible.
     */
    private static function nombreEstado(
        string $estado
    ): string {
        $estados = [
            'reservado' => 'Reservado',
            'en_prestamo' => 'En préstamo',
            'por_vencer' => 'Por vencer',
            'devuelto' => 'Devuelto',
            'cancelado' => 'Cancelado'
        ];

        return $estados[$estado]
            ?? ucfirst(
                str_replace('_', ' ', $estado)
            );
    }

    /**
     * Exporta el reporte de reservas.
     */
    public static function exportar(
        array $reservas,
        array $filtros,
        array $resumen
    ): void {
        $nombreArchivo =
            'reporte_reservas_' .
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
         * Permite mostrar correctamente tildes y eñes.
         */
        echo "\xEF\xBB\xBF";

        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';

        echo '<style>';

        echo '
            body {
                font-family: Arial, sans-serif;
                color: #1d2922;
            }

            h2 {
                color: #183126;
                margin-bottom: 6px;
            }

            .subtitulo {
                color: #5f6d65;
                margin-top: 0;
            }

            .resumen td {
                padding: 6px 12px;
                border: 1px solid #cfd8d1;
            }

            .resumen strong {
                color: #183126;
            }

            table.datos {
                border-collapse: collapse;
                margin-top: 16px;
            }

            table.datos th {
                padding: 8px;
                border: 1px solid #9cab9f;
                background: #183126;
                color: #ffffff;
            }

            table.datos td {
                padding: 7px;
                border: 1px solid #cfd8d1;
                vertical-align: top;
            }

            table.datos tr:nth-child(even) {
                background: #f4f6f2;
            }
        ';

        echo '</style>';

        echo '</head>';
        echo '<body>';

        echo '<h2>';
        echo 'Reporte de Reservas - Biblioteca Digital';
        echo '</h2>';

        echo '<p class="subtitulo">';

        echo 'Fecha de generación: ';

        echo self::escapar(
            date('d/m/Y H:i:s')
        );

        echo '</p>';

        echo '<p>';

        if (
            ($filtros['fecha_desde'] ?? '') !== ''
            || ($filtros['fecha_hasta'] ?? '') !== ''
            || ($filtros['estado'] ?? '') !== ''
        ) {
            echo '<strong>Filtros aplicados:</strong> ';

            $partes = [];

            if (($filtros['fecha_desde'] ?? '') !== '') {
                $partes[] =
                    'Desde ' .
                    self::formatearFecha(
                        $filtros['fecha_desde']
                    );
            }

            if (($filtros['fecha_hasta'] ?? '') !== '') {
                $partes[] =
                    'Hasta ' .
                    self::formatearFecha(
                        $filtros['fecha_hasta']
                    );
            }

            if (($filtros['estado'] ?? '') !== '') {
                $partes[] =
                    'Estado: ' .
                    self::nombreEstado(
                        (string)$filtros['estado']
                    );
            }

            echo self::escapar(
                implode(' | ', $partes)
            );
        } else {
            echo '<strong>';
            echo 'Filtros aplicados:';
            echo '</strong> Ninguno';
        }

        echo '</p>';

        echo '<table class="resumen" cellspacing="0">';

        echo '<tr>';

        echo '<td>';
        echo '<strong>Total:</strong> ';
        echo (int)($resumen['total'] ?? 0);
        echo '</td>';

        echo '<td>';
        echo '<strong>Activas:</strong> ';
        echo (int)($resumen['activas'] ?? 0);
        echo '</td>';

        echo '<td>';
        echo '<strong>Devueltas:</strong> ';
        echo (int)($resumen['devueltas'] ?? 0);
        echo '</td>';

        echo '<td>';
        echo '<strong>Canceladas:</strong> ';
        echo (int)($resumen['canceladas'] ?? 0);
        echo '</td>';

        echo '</tr>';

        echo '</table>';

        echo '<table class="datos" cellspacing="0">';

        echo '<thead>';

        echo '<tr>';

        echo '<th>ID de reserva</th>';
        echo '<th>Usuario</th>';
        echo '<th>Tipo de usuario</th>';
        echo '<th>Nombre del estudiante</th>';
        echo '<th>CIP</th>';

        echo '<th>Libro</th>';
        echo '<th>Autor</th>';
        echo '<th>Categoría</th>';

        echo '<th>Fecha de reserva</th>';
        echo '<th>Fecha de vencimiento</th>';
        echo '<th>Fecha de devolución</th>';
        echo '<th>Estado</th>';

        echo '<th>Tipo de acceso</th>';
        echo '<th>Precio de acceso</th>';
        echo '<th>Días de acceso</th>';

        echo '<th>Observación</th>';
        echo '<th>Fecha de registro</th>';

        echo '</tr>';

        echo '</thead>';

        echo '<tbody>';

        if (empty($reservas)) {
            echo '<tr>';

            echo '<td colspan="17">';
            echo 'No se encontraron reservas con los filtros seleccionados.';
            echo '</td>';

            echo '</tr>';
        } else {
            foreach ($reservas as $reserva) {
                $tipoAcceso =
                    (string)(
                        $reserva['tipo_acceso']
                        ?? 'gratuito'
                    );

                if ($tipoAcceso === 'pago') {
                    $tipoAccesoTexto = 'Pago';

                    $precioTexto = number_format(
                        (float)(
                            $reserva['precio_acceso']
                            ?? 0
                        ),
                        2,
                        '.',
                        ''
                    );

                    $diasTexto = (string)(
                        (int)(
                            $reserva['dias_acceso']
                            ?? 0
                        )
                    );
                } else {
                    $tipoAccesoTexto = 'Gratuito';
                    $precioTexto = '0.00';
                    $diasTexto = 'Permanente';
                }

                $nombreEstudiante = trim(
                    (string)(
                        $reserva['estudiante_nombre']
                        ?? ''
                    )
                );

                if ($nombreEstudiante === '') {
                    $nombreEstudiante =
                        (string)(
                            $reserva['usuario']
                            ?? ''
                        );
                }

                echo '<tr>';

                echo '<td>';
                echo (int)($reserva['id'] ?? 0);
                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $reserva['usuario'] ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    ucfirst(
                        (string)(
                            $reserva['tipo_usuario']
                            ?? ''
                        )
                    )
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $nombreEstudiante
                );

                echo '</td>';

                /*
                 * Conserva los guiones y ceros del CIP.
                 */
                echo '<td style="mso-number-format:\'\@\';">';

                echo self::escapar(
                    $reserva['cip'] ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $reserva['titulo'] ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $reserva['autor'] ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $reserva['categoria_nombre']
                    ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    self::formatearFecha(
                        $reserva['fecha_reserva']
                        ?? ''
                    )
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    self::formatearFecha(
                        $reserva['fecha_vencimiento']
                        ?? '',
                        'Sin fecha límite'
                    )
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    self::formatearFecha(
                        $reserva['fecha_devolucion']
                        ?? ''
                    )
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    self::nombreEstado(
                        (string)(
                            $reserva['estado']
                            ?? ''
                        )
                    )
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $tipoAccesoTexto
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $precioTexto
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $diasTexto
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    $reserva['observacion']
                    ?? ''
                );

                echo '</td>';

                echo '<td>';

                echo self::escapar(
                    self::formatearFecha(
                        $reserva['created_at']
                        ?? ''
                    )
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