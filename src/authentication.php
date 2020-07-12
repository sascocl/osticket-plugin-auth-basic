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

////////////////////////////////////////////////////////////////////////////////
// API PARA CONSUMIR LOS SERVICIOS WEB DE UNA APP CON HTTP AUTH BASIC         //
////////////////////////////////////////////////////////////////////////////////

/**
 * Clase que realiza la autenticación con una API que usa HTTP Auth Basic
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-06-01
 */
class AuthHttpBasicAuthentication
{

    const LOGIN_TYPE_USER = 1;
    const LOGIN_TYPE_STAFF = 2;

    private $config;
    private $type = self::LOGIN_TYPE_USER;

    public function __construct($config, $type = self::LOGIN_TYPE_USER)
    {
        $this->config = $config;
        $this->type = $type;
    }

    public function authenticate($username, $password)
    {
        // si no se pasaron credenciales error
        if (empty($username) or empty($password)) {
            return null;
        }
        // realizar solicitud
        $url = $this->config->get('api-url');
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic '.base64_encode($username.':'.$password),
            ],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $user = json_decode($response);
        // si es un string ocurrió un error
        if (is_string($user) or !is_object($user)) {
            return null;
        }
        // si no es un string, es un objeto, entonces vienen los datos del usuario
        return $this->lookupAndSync($user);
    }

    private function lookupAndSync($user)
    {
        if ($this->type == self::LOGIN_TYPE_USER) {
            return $this->lookupAndSyncUser($user);
        } else if ($this->type == self::LOGIN_TYPE_STAFF) {
            return $this->lookupAndSyncStaff($user);
        }
    }

    private function getUserInfo($user)
    {
        $name = new PersonsName($user->{$this->config->get('api-user-realname')});
        $first = $name->getFirst();
        $last = $name->getLast();
        return [
            'username' => $user->{$this->config->get('api-user-username')},
            'first' => $first,
            'last' => $last,
            'name' => $name,
            'email' => $user->{$this->config->get('api-user-email')},
            'phone' => null,
            'mobile' => null,
        ];
    }

    private function lookupAndSyncUser($user)
    {
        $acct = ClientAccount::lookupByUsername($user->{$this->config->get('api-user-email')});
        if (!$acct) {
            $info = $this->getUserInfo($user);
            return new ClientCreateRequest($this, $user->{$this->config->get('api-user-username')}, $info);
        }
        if (($client = new ClientSession(new EndUser($acct->getUser()))) && !$client->getId()) {
            return;
        }
        return $client;
    }

    private function lookupAndSyncStaff($user)
    {
        if (($staff = StaffSession::lookup($user->{$this->config->get('api-user-email')})) && $staff->getId()) {
            if (!$staff instanceof StaffSession) {
                // osTicket <= v1.9.7 or so
                $staff = new StaffSession($staff->getId());
            }
            return $staff;
        }
    }

}

////////////////////////////////////////////////////////////////////////////////
// CLASES QUE REALIZAN LA AUTENTICACIÓN EN OSTICKET                           //
////////////////////////////////////////////////////////////////////////////////

require_once(INCLUDE_DIR.'class.auth.php');

/**
 * Clase que realiza la autenticación de los agentes del Staff
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-06-01
 */
class AuthHttpBasicStaffAuthenticationBackend extends StaffAuthenticationBackend
{

    public static $name = 'HTTP Auth Basic'; // trans
    public static $id = 'auth-http-basic.staff';
    private static $_config; // configuración del plugin
    private $_client; // cliente para la autenticación

    public function __construct($config) {
        $this->_config = $config;
        $this->_client = new AuthHttpBasicAuthentication($this->_config, AuthHttpBasicAuthentication::LOGIN_TYPE_STAFF);
    }

    public function authenticate($username, $password = false, array $errors = []) {
        return $this->_client->authenticate($username, $password);
    }

    public function getName()
    {
        $config = $this->_config;
        list($__, $_N) = $config::translate();
        return $__(static::$name);
    }

}

/**
 * Clase que realiza la autenticación de los usuarios (clientes)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-06-01
 */
class AuthHttpBasicUserAuthenticationBackend extends UserAuthenticationBackend
{

    public static $name = 'HTTP Auth Basic'; // trans
    public static $id = 'auth-http-basic.user';
    private static $_config; // configuración del plugin
    private $_client; // cliente para la autenticación

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_client = new AuthHttpBasicAuthentication($this->_config, AuthHttpBasicAuthentication::LOGIN_TYPE_USER);
        if (!empty($config->get('api-url'))) {
            self::$name .= sprintf(' (%s)', $config->get('api-url'));
        }
    }

    public function getName()
    {
        $config = $this->_config;
        list($__, $_N) = $config::translate();
        return $__(static::$name);
    }

    function authenticate($username, $password = false, array $errors = [])
    {
        $object = $this->_client->authenticate($username, $password);
        if ($object instanceof ClientCreateRequest) {
            $object->setBackend($this);
        }
        return $object;
    }

}

////////////////////////////////////////////////////////////////////////////////
// DEFINICIÓN DEL PLUGIN                                                      //
////////////////////////////////////////////////////////////////////////////////

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

/**
 * Clase que realiza la autenticación de los agentes del Staff
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-06-01
 */
class AuthHttpBasicPlugin extends Plugin
{
    public $config_class = 'AuthHttpBasicConfig';

    public function bootstrap()
    {
        $config = $this->getConfig();
        if ($config->get('auth-staff')) {
            StaffAuthenticationBackend::register(new AuthHttpBasicStaffAuthenticationBackend($config));
        }
        if ($config->get('auth-client')) {
            UserAuthenticationBackend::register(new AuthHttpBasicUserAuthenticationBackend($config));
        }
    }

}
