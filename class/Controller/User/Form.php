<?php

namespace tax_agreement\Controller\User;

require_once PHPWS_SOURCE_DIR . 'mod/tax_agreement/conf/defines.php';

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Form extends \Http\Controller
{

    public function get(\Canopy\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        if (is_a($view, '\Http\NotAcceptableResponse')) {
            return $view;
        }
        $response = new \Canopy\Response($view);
        return $response;
    }

    public function post(\Canopy\Request $request)
    {
        $command = $request->shiftCommand();

        switch ($command) {
            case 'save':
                $this->savePost($request);
                $response = new \Http\SeeOtherResponse(\Canopy\Server::getSiteUrl() . 'tax_agreement/user/form/list');
                break;
        }

        return $response;
    }

    private function savePost($request)
    {
        $form = new \tax_agreement\Resource\Form;
        \tax_agreement\Factory\FormFactory::postForm($form, $request);
        $form->setUserId(\Current_User::getId());
        \phpws2\ResourceFactory::saveResource($form);
        $this->setMessage('Tax agreement form saved');
    }

    public function getHtmlView($data, \Canopy\Request $request)
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
                // if printAgreement fails, an error message is returned via the returned template
                // otherwise, the download headers are sent and the script exits.
                $template = $this->printAgreement($request);
                break;

            default:
                \Error::errorPage(404);
        }

        if (!empty(\phpws2\Session::getInstance()->tax_message)) {
            $ses = \phpws2\Session::getInstance();
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

    private function printAgreement(\Canopy\Request $request)
    {
        $id = $request->shiftCommand();
        if (!is_numeric($id)) {
            throw new \Exception('Bad id passed to function');
        }
        $form = \tax_agreement\Factory\FormFactory::loadFormById($id, \Current_User::getId());
        if (!\tax_agreement\Factory\FormFactory::allowFormAccess($form)) {
            \Current_User::disallow('Form access not allowed');
        }
        try {
            $file = \tax_agreement\Factory\FormFactory::getFormPathForPrinting($form);
        } catch (\Exception $e) {
            if ($e->getCode() == 666) {
                if (\Current_User::allow('tax_agreement')) {
                    $message = '<p>WKPDF can not be accessed on this server.</p>';
                } else {
                    $message = '<p>Printing can not be accessed at this time. Please check back later.</p>';
                }
                $view = new \phpws2\View\HtmlView($message);
                return $view;
            } else {
                throw $e;
            }
        }
        $fullpath = PHPWS_HOME_DIR . 'files/tax_agreement/' . $file;

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Type: application/octetstream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . filesize($fullpath));
        header('Content-disposition: attachment; filename="tax-agreement-form.pdf"');
        readfile($fullpath);
        exit();
    }

    private function setMessage($message)
    {
        $ses = \phpws2\Session::getInstance();
        $ses->tax_message = $message;
    }

    private function newForm(\Canopy\Request $request)
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

        $template = new \phpws2\Template($template_data);
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
        $db = \phpws2\Database::newDB();
        $table = $db->addTable('tax_mainclass');
        $table->addFieldConditional('user_id', $user_id);
        $table->addFieldConditional('approved_date', null, 'is');
        $id = $table->addField('id');
        $id->showCount();
        $count = $db->selectColumn();
        return $count;
    }

    private function listing(\Canopy\Request $request)
    {
        $db = \phpws2\Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('user_id', \Current_User::getId());
        $result = $db->select();
        $rows = array();
        if ($result) {
            foreach ($result as $i) {
                $i['event_date'] = strftime('%e %b, %Y', $i['event_date']);
                if (empty($i['approved_date'])) {
                    $i['approved_date'] = 'Not approved';
                } else {
                    $i['approved_date'] = strftime('%e %b, %Y', $i['access_date']);
                }
                $rows[] = $i;
            }
        }

        $tpl['rows'] = $rows;

        $template = new \phpws2\Template($tpl);
        $template->setModuleTemplate('tax_agreement', 'User/Form/list.html');
        return $template;
    }

}

?>
