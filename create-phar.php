#!/usr/bin/php
<?php

/**
 * Plugin osTicket para autenticación usando HTTP Auth Basic
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// ¡IMPORTANTE!
// Para poder crear el archivo phar la opción phar.readonly en php.ini debe ser 0

// nombre del archivo phar que se generará
$pharFile = 'auth-http-basic.phar';

// se borran los archivos phar si existían previamente
if (file_exists($pharFile)) {
    unlink($pharFile);
}
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// crear el objeto phar
$p = new Phar($pharFile);

// crear el plugin con todo el contenido de src
$p->buildFromDirectory('src/');

// entregar comprimido como GZIP
$p->compress(Phar::GZ);

// todo ok
echo $pharFile,' archivo phar generado',"\n";
