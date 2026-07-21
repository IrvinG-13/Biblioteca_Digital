'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const movimientoReducido = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    /*
     * Agrega una clase de animación a un elemento.
     */
    const prepararElemento = (
        elemento,
        tipoEntrada = '',
        retraso = 0
    ) => {
        if (!(elemento instanceof HTMLElement)) {
            return;
        }

        elemento.classList.add('elemento-aparece');

        if (tipoEntrada !== '') {
            elemento.classList.add(tipoEntrada);
        }

        elemento.style.setProperty(
            '--retraso-animacion',
            `${retraso}ms`
        );
    };

    /*
     * Encabezados principales de cada sección.
     */
    document
        .querySelectorAll('.encabezado-seccion')
        .forEach((encabezado) => {
            prepararElemento(encabezado);
        });

    /*
     * Primera sección.
     */
    prepararElemento(
        document.querySelector('.texto-inicio'),
        'aparece-desde-izquierda'
    );

    prepararElemento(
        document.querySelector('.panel-servicios-destacados'),
        'aparece-desde-derecha',
        120
    );

    /*
     * Tarjetas de servicios.
     * Cada tarjeta aparece con un pequeño retraso.
     */
    document
        .querySelectorAll('.lista-servicios')
        .forEach((lista) => {
            Array.from(lista.children).forEach(
                (tarjeta, indice) => {
                    prepararElemento(
                        tarjeta,
                        'aparece-con-escala',
                        indice * 90
                    );
                }
            );
        });

    /*
     * Tarjetas de beneficios.
     */
    document
        .querySelectorAll('.lista-beneficios')
        .forEach((lista) => {
            Array.from(lista.children).forEach(
                (tarjeta, indice) => {
                    prepararElemento(
                        tarjeta,
                        '',
                        indice * 90
                    );
                }
            );
        });

    /*
     * Tarjetas de tecnologías.
     */
    document
        .querySelectorAll('.lista-tecnologias')
        .forEach((lista) => {
            Array.from(lista.children).forEach(
                (tarjeta, indice) => {
                    prepararElemento(
                        tarjeta,
                        'aparece-con-escala',
                        indice * 70
                    );
                }
            );
        });

    /*
     * Sección de contacto.
     */
    prepararElemento(
        document.querySelector('.texto-contacto'),
        'aparece-desde-izquierda'
    );

    prepararElemento(
        document.querySelector('.panel-contacto'),
        'aparece-desde-derecha',
        120
    );

    const elementosAnimados = document.querySelectorAll(
        '.elemento-aparece'
    );

    /*
     * Cuando el usuario ha desactivado las animaciones,
     * todos los elementos se muestran inmediatamente.
     */
    if (movimientoReducido) {
        elementosAnimados.forEach((elemento) => {
            elemento.classList.add('elemento-visible');
        });

        return;
    }

    /*
     * Alternativa para navegadores antiguos.
     */
    if (!('IntersectionObserver' in window)) {
        elementosAnimados.forEach((elemento) => {
            elemento.classList.add('elemento-visible');
        });

        return;
    }

    /*
     * Detecta cuándo un elemento entra en la pantalla.
     */
    const observador = new IntersectionObserver(
        (entradas) => {
            entradas.forEach((entrada) => {
                if (!entrada.isIntersecting) {
                    return;
                }

                entrada.target.classList.add(
                    'elemento-visible'
                );

                /*
                 * La animación ocurre una sola vez.
                 */
                observador.unobserve(entrada.target);
            });
        },
        {
            threshold: 0.12,
            rootMargin: '0px 0px -8% 0px'
        }
    );

    elementosAnimados.forEach((elemento) => {
        observador.observe(elemento);
    });
});