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

    public function post(\Request $request)
    {
        $command = $request->shiftCommand();

        switch ($command) {
            case 'save':
                $this->savePost($request);
                $response = new \Http\SeeOtherResponse(\Server::getSiteUrl() . 'tax_agreement/user/form/list');
                break;
        }

        return $response;
    }

    private function savePost($request)
    {
        $form = new \tax_agreement\Resource\Form;
        \tax_agreement\Factory\FormFactory::postForm($form, $request);
        $form->setUserId(\Current_User::getId());
        \ResourceFactory::saveResource($form);
    }

    public function getHtmlView($data, \Request $request)
    {
        $cmd = $request->shiftCommand();

        if (empty($cmd)) {
            $cmd = 'form';
        }

        switch ($cmd) {
            case 'form':
                $template = $this->newForm($request);
                break;

            case 'list':
                $template = $this->listing($request);
                break;

            case 'print':
                $template = $this->printAgreement($request);
                break;

            default:
                \Error::errorPage(404);
        }

        if (!empty(\Session::getInstance()->tax_message)) {
            $ses = \Session::getInstance();
            $template->add('message', $ses->tax_message);
            unset($ses->tax_message);
        }
        return $template;
    }

    private function printAgreement(\Request $request)
    {
        $id = $request->shiftCommand();

        if (!is_numeric($id)) {
            throw new \Exception('Bad id passed to function');
        }
        $form = \tax_agreement\Factory\FormFactory::loadFormById($id);
        $vars = $form->getStringVars();
        $vars['access_date'] = strftime('%B %e, %Y', strtotime($vars['access_date']));
        $vars['event_date'] = strftime('%B %e, %Y', strtotime($vars['event_date']));
        $template = new \Template($vars);
        $template->setModuleTemplate('tax_agreement', 'agreement.html');
        return $template;
    }

    private function setMessage($message)
    {
        $ses = \Session::getInstance();
        $ses->tax_message = $message;
    }

    private function newForm(\Request $request)
    {
        $today = strftime('%Y-%m-%d', time());
        $agreement = new \tax_agreement\Resource\Form;
        $form = $this->createForm($agreement);
        current($form->getInput('organization_name'))->setRequired();
        current($form->getInput('event_name'))->setRequired();
        current($form->getInput('event_location'))->setRequired();
        $i4 = current($form->getInput('event_date'));
        $i4->setValue($today);
        $i4->setRequired();
        $i4->setMin($today);
        current($form->getInput('organization_rep_name'))->setRequired();
        current($form->getInput('organization_rep_title'))->setRequired();
        $form->setAction('tax_agreement/user/form/save');
        $form->appendCSS('bootstrap');
        $form->addSubmit('save', 'Save form');
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
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('user_id', \Current_User::getId());
        $result = $db->select();
        $rows = array();
        if ($result) {
            foreach ($result as $i) {
                $i['event_date'] = strftime('%c', $i['event_date']);
                $i['access_date'] = strftime('%c', $i['access_date']);
                if (empty($i['approved_date'])) {
                    $i['approved_date'] = 'Not approved';
                } else {

                    $i['approved_date'] = strftime('%c', $i['access_date']);
                }
                $rows[] = $i;
            }
        }

        $tpl['rows'] = $rows;

        $template = new \Template($tpl);
        $template->setModuleTemplate('tax_agreement', 'User/Form/list.html');
        return $template;
    }

}

?>
