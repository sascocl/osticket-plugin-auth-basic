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

return [
    'id'            => 'auth:http-basic', # notrans
    'version'       => '1.0.0',
    'name'          => /* trans */ 'HTTP Auth Basic',
    'author'        => 'Esteban De La Fuente Rubio',
    'description'   => /* trans */ 'Allows perform the authentication of the user with the API of a application that uses HTTP Auth Basic. osTicket will match the username from the API to a username defined internally.',
    'url'           => 'https://github.com/sascocl/osticket-plugin-auth-basic',
    'plugin'        => 'authentication.php:AuthHttpBasicPlugin'
];
