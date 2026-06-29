<?php

interface ErrorHandlerInterface
{
    public function manejar(string $mensaje): void;
}