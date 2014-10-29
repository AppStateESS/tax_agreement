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
        $this->setMessage('Tax agreement form saved');
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
                $this->printAgreement($request);
                break;

            default:
                \Error::errorPage(404);
        }

        if (!empty(\Session::getInstance()->tax_message)) {
            $ses = \Session::getInstance();
            $tax_message = $ses->tax_message;
            $message = <<<EOF
<div class="alert alert-warning alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            $tax_message
</div>
EOF;

            $template->add('message', $message);
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
        $form = \tax_agreement\Factory\FormFactory::loadFormById($id,
                        \Current_User::getId());
        if (!\tax_agreement\Factory\FormFactory::allowFormAccess($form)) {
            \Current_User::disallow('Form access not allowed');
        }
        $file = \tax_agreement\Factory\FormFactory::getFormPathForPrinting($form);
        $fullpath = PHPWS_HOME_DIR . 'files/tax_agreement/' . $file;

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Type: application/octetstream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . filesize($fullpath));
        //header("Content-disposition: attachment; filename=\"" . basename($fullpath) . "\"");
        header('Content-disposition: attachment; filename="tax-agreement-form.pdf"');
        readfile($fullpath);
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
        $form->setAction('tax_agreement/user/save');
        $form->appendCSS('bootstrap');
        $form->addSubmit('save', 'Save form');
        $template_data = $form->getInputStringArray();
        $agreements = $this->currentUserAgreementCount();
        $template_data['agreements'] = $agreements ? "($agreements)" : null;

        $template = new \Template($template_data);
        $template->setModuleTemplate('tax_agreement', 'User/Form/form.html');
        return $template;
    }

    private function createForm(\tax_agreement\Resource\Form $agreement)
    {
        $form = $agreement->pullForm();
        return $form;
    }

    private function currentUserAgreementCount()
    {
        $user_id = \Current_User::getId();
        if (empty($user_id)) {
            throw new \Exception('Current user has a zero id');
        }
        $db = \Database::newDB();
        $table = $db->addTable('tax_mainclass');
        $table->addFieldConditional('user_id', $user_id);
        $table->addFieldConditional('approved_date', null, 'is');
        $id = $table->addField('id');
        $id->showCount();
        $count = $db->selectColumn();
        return $count;
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
