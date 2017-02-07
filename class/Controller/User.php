<?php

namespace tax_agreement\Controller;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class User extends \Http\Controller {

    private $form;

    public function getController(\Canopy\Request $request)
    {
        $form = new User\Form($this->getModule());
        return $form;
    }

}

?>
