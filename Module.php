<?php

namespace tax_agreement;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Module extends \Module
{

    public function getController(\Canopy\Request $request)
    {
        if (!\Current_User::isLogged()) {
            \Current_User::requireLogin();
        }


        $cmd = $request->shiftCommand();
        if ($cmd == 'admin' && \Current_User::allow('tax_agreement')) {
            $admin = new \tax_agreement\Controller\Admin($this);
            $controller = $admin->getController($request);
        } else {
            $user = new \tax_agreement\Controller\User($this);
            $controller = $user->getController($request);
        }
        return $controller;
    }

}

?>
