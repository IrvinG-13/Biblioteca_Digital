'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const datos = window.datosEstadisticas;

    if (
        typeof Chart === 'undefined'
        || !datos
    ) {
        return;
    }

    const movimientoReducido = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const colorRojo = '#8c2d19';
    const colorMarron = '#2d2420';
    const colorProfesor = '#c07b45';
    const colorBorde = '#eadbd4';
    const colorTexto = '#746761';
    const colorBlanco = '#ffffff';

    Chart.defaults.font.family =
        '"Segoe UI", system-ui, sans-serif';

    Chart.defaults.color = colorTexto;

    Chart.defaults.animation.duration =
        movimientoReducido ? 0 : 850;

    /*
    |--------------------------------------------------------------------------
    | Tooltip general
    |--------------------------------------------------------------------------
    */

    const configuracionTooltip = {
        backgroundColor: colorMarron,
        titleColor: colorBlanco,
        bodyColor: colorBlanco,
        borderColor: 'rgba(255, 255, 255, 0.08)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 10,
        displayColors: true,
        usePointStyle: true
    };

    /*
    |--------------------------------------------------------------------------
    | Crear degradado
    |--------------------------------------------------------------------------
    */

    const crearGradiente = (
        contexto,
        colorInicio,
        colorFinal,
        alto = 340
    ) => {
        const gradiente = contexto.createLinearGradient(
            0,
            0,
            0,
            alto
        );

        gradiente.addColorStop(
            0,
            colorInicio
        );

        gradiente.addColorStop(
            1,
            colorFinal
        );

        return gradiente;
    };

    /*
    |--------------------------------------------------------------------------
    | Truncar etiquetas
    |--------------------------------------------------------------------------
    */

    const truncarEtiqueta = (
        etiqueta,
        limite = 25
    ) => {
        const texto = String(etiqueta ?? '');

        return texto.length > limite
            ? `${texto.slice(0, limite)}…`
            : texto;
    };

    /*
    |--------------------------------------------------------------------------
    | Gráfica de distribución
    |--------------------------------------------------------------------------
    */

    const elementoDistribucion =
        document.getElementById(
            'graficaDistribucion'
        );

    if (elementoDistribucion) {
        new Chart(
            elementoDistribucion,
            {
                type: 'doughnut',

                data: {
                    labels: [
                        'Estudiantes',
                        'Profesores'
                    ],

                    datasets: [
                        {
                            data: [
                                datos.distribucion
                                    .estudiantes,

                                datos.distribucion
                                    .profesores
                            ],

                            backgroundColor: [
                                colorRojo,
                                colorProfesor
                            ],

                            borderColor:
                                colorBlanco,

                            borderWidth: 5,

                            hoverBorderWidth: 5,

                            hoverOffset: 10
                        }
                    ]
                },

                options: {
                    responsive: true,

                    maintainAspectRatio: false,

                    cutout: '70%',

                    layout: {
                        padding: 8
                    },

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip:
                            configuracionTooltip
                    },

                    animation: {
                        animateRotate:
                            !movimientoReducido,

                        animateScale:
                            !movimientoReducido,

                        duration:
                            movimientoReducido
                                ? 0
                                : 950,

                        easing:
                            'easeOutQuart'
                    }
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Configuración para rankings
    |--------------------------------------------------------------------------
    */

    const crearGraficaRanking = (
        elemento,
        etiquetas,
        valores,
        color
    ) => {
        if (!elemento) {
            return;
        }

        const contexto =
            elemento.getContext('2d');

        const degradado = contexto
            .createLinearGradient(
                0,
                0,
                elemento.clientWidth || 500,
                0
            );

        degradado.addColorStop(
            0,
            color
        );

        degradado.addColorStop(
            1,
            color === colorRojo
                ? '#b75a44'
                : '#d49a6c'
        );

        new Chart(
            elemento,
            {
                type: 'bar',

                data: {
                    labels: etiquetas,

                    datasets: [
                        {
                            label: 'Reservas',

                            data: valores,

                            backgroundColor:
                                degradado,

                            borderColor:
                                color,

                            borderWidth: 0,

                            borderRadius: 9,

                            borderSkipped: false,

                            barThickness: 18,

                            maxBarThickness: 22
                        }
                    ]
                },

                options: {
                    indexAxis: 'y',

                    responsive: true,

                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'nearest',

                        intersect: false
                    },

                    scales: {
                        x: {
                            beginAtZero: true,

                            ticks: {
                                precision: 0,

                                color: colorTexto
                            },

                            grid: {
                                color: colorBorde,

                                drawTicks: false
                            },

                            border: {
                                display: false
                            }
                        },

                        y: {
                            grid: {
                                display: false
                            },

                            border: {
                                display: false
                            },

                            ticks: {
                                color: colorMarron,

                                font: {
                                    size: 11,

                                    weight: '600'
                                },

                                callback(value) {
                                    const etiqueta =
                                        this.getLabelForValue(
                                            value
                                        );

                                    return truncarEtiqueta(
                                        etiqueta,
                                        25
                                    );
                                }
                            }
                        }
                    },

                    plugins: {
                        legend: {
                            display: false
                        },

                        tooltip: {
                            ...configuracionTooltip,

                            callbacks: {
                                title(contextoTooltip) {
                                    return contextoTooltip[
                                        0
                                    ]?.label ?? '';
                                },

                                label(contextoTooltip) {
                                    return `Reservas: ${
                                        contextoTooltip.raw
                                    }`;
                                }
                            }
                        }
                    },

                    animation: {
                        duration:
                            movimientoReducido
                                ? 0
                                : 850,

                        easing:
                            'easeOutQuart'
                    }
                }
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Ranking de estudiantes
    |--------------------------------------------------------------------------
    */

    crearGraficaRanking(
        document.getElementById(
            'graficaEstudiantes'
        ),
        datos.estudiantes.etiquetas,
        datos.estudiantes.valores,
        colorRojo
    );

    /*
    |--------------------------------------------------------------------------
    | Ranking de profesores
    |--------------------------------------------------------------------------
    */

    crearGraficaRanking(
        document.getElementById(
            'graficaProfesores'
        ),
        datos.profesores.etiquetas,
        datos.profesores.valores,
        colorProfesor
    );

    /*
    |--------------------------------------------------------------------------
    | Actividad diaria
    |--------------------------------------------------------------------------
    */

    const elementoActividad =
        document.getElementById(
            'graficaActividad'
        );

    if (elementoActividad) {
        const contexto =
            elementoActividad.getContext('2d');

        const fondoEstudiantes =
            crearGradiente(
                contexto,
                'rgba(140, 45, 25, 0.30)',
                'rgba(140, 45, 25, 0.01)'
            );

        const fondoProfesores =
            crearGradiente(
                contexto,
                'rgba(192, 123, 69, 0.27)',
                'rgba(192, 123, 69, 0.01)'
            );

        new Chart(
            elementoActividad,
            {
                type: 'line',

                data: {
                    labels:
                        datos.actividad.etiquetas,

                    datasets: [
                        {
                            label:
                                'Estudiantes',

                            data:
                                datos.actividad
                                    .estudiantes,

                            borderColor:
                                colorRojo,

                            backgroundColor:
                                fondoEstudiantes,

                            fill: true,

                            tension: 0.38,

                            borderWidth: 3,

                            pointRadius: 4,

                            pointHoverRadius: 7,

                            pointBackgroundColor:
                                colorBlanco,

                            pointBorderColor:
                                colorRojo,

                            pointBorderWidth: 2
                        },

                        {
                            label:
                                'Profesores',

                            data:
                                datos.actividad
                                    .profesores,

                            borderColor:
                                colorProfesor,

                            backgroundColor:
                                fondoProfesores,

                            fill: true,

                            tension: 0.38,

                            borderWidth: 3,

                            pointRadius: 4,

                            pointHoverRadius: 7,

                            pointBackgroundColor:
                                colorBlanco,

                            pointBorderColor:
                                colorProfesor,

                            pointBorderWidth: 2
                        }
                    ]
                },

                options: {
                    responsive: true,

                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',

                        intersect: false
                    },

                    scales: {
                        x: {
                            grid: {
                                display: false
                            },

                            ticks: {
                                color: colorTexto
                            },

                            border: {
                                display: false
                            }
                        },

                        y: {
                            beginAtZero: true,

                            ticks: {
                                precision: 0,

                                color: colorTexto
                            },

                            grid: {
                                color: colorBorde
                            },

                            border: {
                                display: false
                            }
                        }
                    },

                    plugins: {
                        legend: {
                            position: 'bottom',

                            labels: {
                                usePointStyle: true,

                                pointStyle: 'circle',

                                padding: 22,

                                color: colorTexto
                            }
                        },

                        tooltip:
                            configuracionTooltip
                    },

                    animation: {
                        duration:
                            movimientoReducido
                                ? 0
                                : 950,

                        easing:
                            'easeOutQuart'
                    }
                }
            }
        );
    }
});