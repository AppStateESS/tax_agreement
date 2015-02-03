<?php

namespace tax_agreement\Controller;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Admin extends \Http\Controller
{

    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    public function post(\Request $request)
    {
        $action = filter_input(INPUT_POST, 'action');
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        switch ($action) {
            case 'delete':
                \tax_agreement\Factory\FormFactory::delete($id);
                exit();

            case 'approve':
                \tax_agreement\Factory\FormFactory::approve($id);
                exit();

            case 'unapprove':
                \tax_agreement\Factory\FormFactory::unapprove($id);
                exit();

            case 'approve_all':
                \tax_agreement\Factory\FormFactory::approveList($request->getVar('agreement'));
                return new \Http\FoundResponse('./tax_agreement/admin/unapproved');
                break;

            case 'unapprove_all':
                \tax_agreement\Factory\FormFactory::unapproveList($request->getVar('agreement'));
                return new \Http\FoundResponse('./tax_agreement/admin/approved');
                break;
        }
    }

    
    protected function getJsonView($data, \Request $request)
    {
        $db = \Database::newDB();
        $t1 = $db->addTable('tax_mainclass');
        $t2 = $db->addTable('users');
        $db->joinResources($t1, $t2, $db->createConditional($t1->getField('user_id'), $t2->getField('id'), '='));
        $t2->addField('email');
        $username_field = $t2->addField('username');

        $cmd = $request->shiftCommand();

        if ($cmd == 'approved') {
            $t1->addFieldConditional('approved_date', null, 'is not');
        } else {
            $t1->addFieldConditional('approved_date', null, 'is');
        }

        $pager = new \DatabasePager($db);
        $pager->setId('agreement-list');
        $pager->setHeaders(array('event_name' => 'Event name', 'event_date' => 'Event date', 'organization_name' => 'Organization', 'username' => 'Submitter'));

        $tbl_head['username'] = $username_field;
        $tbl_head['event_name'] = $t1->getField('event_name');
        $tbl_head['event_date'] = $t1->getField('event_date');
        $tbl_head['organization_name'] = $t1->getField('organization_name');
        $tbl_head['organization_rep_title'] = $t1->getField('organization_rep_title');

        $pager->removeSearchColumn('event_date');
        $pager->addSearchColumn('organization_rep_title');
        $pager->setTableHeaders($tbl_head);
        $pager->setRowIdColumn('id');
        $pager->setCallback(array('tax_agreement\Factory\FormFactory', 'format'));
        $data = $pager->getJson();
        return parent::getJsonView($data, $request);
    }

    public function getHtmlView($data, \Request $request)
    {
        if (!\Current_User::allow('tax_agreement')) {
            \Current_User::disallow();
        }
        $cmd = $request->shiftCommand();
        $template = new \Template;
        if (empty($cmd)) {
            $cmd = 'unapproved';
        }
        switch ($cmd) {
            case 'approved':
                $template = $this->Pager(1);
                break;

            case 'unapproved':
                $template = $this->Pager(0);
                break;
        }
        return $template;
    }

    private function Pager($approved)
    {
        javascript('jquery');
        javascript('jquery_ui');

        $js_file = PHPWS_SOURCE_HTTP . 'mod/tax_agreement/javascript/script.js';
        $script = "<script type='text/javascript' src='$js_file'></script>";
        \Layout::addJSHeader($script, 'tax');
        \Pager::prepare();

        if ($approved) {
            $approval_button = "<button class='btn btn-danger'><i class='fa fa-remove'></i> Unapprove checked</button>";
        } else {
            $approval_button = "<button class='btn btn-success'><i class='fa fa-check'></i> Approve checked</button>";
        }

        $template = new \Template;
        $template->add('approved', $approved);
        $template->add('command', $approved ? 'unapprove_all' : 'approve_all');
        $template->add('approval_button', $approval_button);
        $template->setModuleTemplate('tax_agreement', 'Admin/pager.html');
        return $template;
    }

    public function getController(\Request $request)
    {
        return $this;
    }

}

?>
