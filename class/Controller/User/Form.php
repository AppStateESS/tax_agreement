<?php

namespace tax_agreement\Controller\User;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Form extends \Http\Controller {

    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    public function getHtmlView($data, \Request $request)
    {
        $cmd = $request->shiftCommand();

        if (empty($cmd)) {
            $cmd = 'new';
        }

        switch ($cmd) {
            case 'new':
                $template = $this->newForm($request);
                break;
        }

        if (!empty(\Session::getInstance()->tax_message)) {
            $ses = \Session::getInstance();
            $template->add('message', $ses->tax_message);
            unset($ses->tax_message);
        }
        return $template;
    }

    private function newForm(\Request $request)
    {
        $agreement = new \tax_agreement\Resource\Form;
        $form = $this->createForm($agreement);
        $form->appendCSS('bootstrap');
        $template_data = $form->getInputStringArray();
        $template = new \Template($template_data);
        $template->setModuleTemplate('tax_agreement', 'User/Form/form.html');
        return $template;
    }

    private function createForm(\tax_agreement\Resource\Form $agreement)
    {
        $form = $agreement->pullForm();
        return $form;
    }

    private function listing(\Request $request)
    {
        $template = new \Template(array());
        $template->setModuleTemplate('tax_agreement', 'User/Form/list.html');
        return $template;
    }

}

?>
