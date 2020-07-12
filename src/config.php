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

require_once(INCLUDE_DIR.'/class.forms.php');

class AuthHttpBasicConfig extends PluginConfig
{

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    public function translate()
    {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function($x) { return $x; },
                function($x, $y, $n) { return $n != 1 ? $y : $x; },
            );
        }
        return Plugin::translate('auth-http-basic');
    }

    public function getOptions()
    {
        list($__, $_N) = self::translate();
        return [
            'api' => new SectionBreakField([
                'label' => $__('API'),
                'hint' => $__('API connection and information about the user'),
            ]),
            'api-url' => new TextboxField([
                'label' => $__('URL'),
                'hint' => $__('URL used for access the authentication API'),
                'configuration' => ['size'=>40, 'length'=>100],
                'validators' => [
                    function($self, $val) use ($__) {
                        if (strpos($val, '.') === false) {
                            $self->addError($__('Fully-qualified domain name is expected'));
                        }
                    }
                ],
            ]),
            'api-user-realname' => new TextboxField([
                'label' => $__('Real name'),
                'hint' => $__('Attribute inside the JSON object returned by the API'),
                'configuration' => ['size'=>40, 'length'=>80],
                'validators' => [
                    function($self, $val) use ($__) {
                        if (empty($val)) {
                            $self->addError($__('Non empty value is expected'));
                        }
                    }
                ],
            ]),
            'api-user-username' => new TextboxField([
                'label' => $__('Username'),
                'hint' => $__('Attribute inside the JSON object returned by the API'),
                'configuration' => ['size'=>40, 'length'=>80],
                'validators' => [
                    function($self, $val) use ($__) {
                        if (empty($val)) {
                            $self->addError($__('Non empty value is expected'));
                        }
                    }
                ],
            ]),
            'api-user-email' => new TextboxField([
                'label' => $__('Email'),
                'hint' => $__('Attribute inside the JSON object returned by the API'),
                'configuration' => ['size'=>40, 'length'=>80],
                'validators' => [
                    function($self, $val) use ($__) {
                        if (empty($val)) {
                            $self->addError($__('Non empty value is expected'));
                        }
                    }
                ],
            ]),
            'auth' => new SectionBreakField([
                'label' => $__('Authentication Modes'),
                'hint' => $__('Authentication modes for clients and staff members can be enabled independently.'),
            ]),
            'auth-staff' => new BooleanField([
                'label' => $__('Staff Authentication'),
                'default' => false,
                'configuration' => [
                    'desc' => $__('Enable authentication of staff members')
                ]
            ]),
            'auth-client' => new BooleanField([
                'label' => $__('Client Authentication'),
                'default' => true,
                'configuration' => [
                    'desc' => $__('Enable authentication and discovery of clients')
                ]
            ]),
        ];
    }

    public function pre_save(&$config, &$errors)
    {
        global $msg;
        list($__, $_N) = self::translate();
        if (!$errors) {
            $msg = $__('Configuration updated successfully');
        }
        return true;
    }

}
