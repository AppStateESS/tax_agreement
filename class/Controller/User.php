<?php

namespace tax_agreement\Controller;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class User extends \Http\Controller {

    private $form;

    public function getController(\Request $request)
    {
        $cmd = $request->shiftCommand();

        if (empty($cmd)) {
            $cmd = 'form';
        }

        $form = new User\Form($this->getModule());
        switch ($cmd) {
            case 'form':
                return $form;
        }
    }

}

?>
