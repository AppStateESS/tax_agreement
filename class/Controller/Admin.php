<?php

namespace tax_agreement\Controller;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Admin extends \Http\Controller {

    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    public function getHtmlView($data, \Request $request)
    {
        if (!\Current_User::isLogged()) {
            \Current_User::requireLogin();
        }

        $cmd = $request->shiftCommand();


        $template = new \Template;
        $template->setModuleTemplate('tax_agreement', 'Admin/Listing.html');
        return $template;
    }

    public function getController(\Request $request)
    {
        return $this;
    }

}

?>
