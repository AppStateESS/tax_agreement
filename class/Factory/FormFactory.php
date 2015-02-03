<?php

namespace tax_agreement\Factory;

require_once PHPWS_SOURCE_DIR . 'mod/tax_agreement/conf/defines.php';

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class FormFactory
{

    public static function postForm(\tax_agreement\Resource\Form $form, \Request $request)
    {
        $vars = $request->getRequestVars();
        $form->setId($vars['id']);
        $form->setOrganizationName($vars['organization_name']);
        $form->setEventName($vars['event_name']);
        $form->setEventLocation($vars['event_location']);
        $form->setEventDate($vars['event_date']);
        $form->setOrganizationRepName($vars['organization_rep_name']);
        $form->setOrganizationRepTitle($vars['organization_rep_title']);
    }

    public static function loadFormById($id)
    {
        $id = (int) $id;
        $form = new \tax_agreement\Resource\Form;
        \ResourceFactory::loadByID($form, $id);
        return $form;
    }

    public static function allowFormAccess(\tax_agreement\Resource\Form $form)
    {
        return (\Current_User::allow('tax_agreement') || $form->getUserId() == \Current_User::getId());
    }

    public static function createPDF($form)
    {
        require_once PHPWS_SOURCE_DIR . 'mod/tax_agreement/class/WKPDF.php';

        $content = self::createHTML($form);
        $path = self::getFileDirectory();
        $file_name = $form->getUserId() . '-' . $form->getId() . '-' . time() . '.pdf';

        if (!is_executable(WKPDF_PATH)) {
            throw new \Exception('WKPDF is not installed or executable', 666);
        }

        $pdf = new \WKPDF(WKPDF_PATH);
        if (USE_XVFB) {
            $pdf->setXVFB(XVFB_PATH);
        }
        $pdf->set_html($content);
        $pdf->render();

        $pdf->output(\WKPDF::$PDF_SAVEFILE, $path . $file_name);
        return $file_name;
    }

    public static function getFileDirectory()
    {
        return PHPWS_HOME_DIR . 'files/tax_agreement/';
    }

    public static function getFormPathForPrinting(\tax_agreement\Resource\Form $form)
    {
        $filepath = $form->getFilepath();
        if (empty($filepath)) {
            $filepath = self::createPDF($form);
            $form->setFilePath($filepath);
            \ResourceFactory::saveResource($form);
        }

        return $filepath;
    }

    public static function createHTML(\tax_agreement\Resource\Form $form)
    {
        $vars = $form->getStringVars();
        $vars['access_date'] = strftime('%B %e, %Y', strtotime($vars['access_date']));
        $vars['event_date'] = strftime('%B %e, %Y', strtotime($vars['event_date']));
        $template = new \Template($vars);
        $template->setModuleTemplate('tax_agreement', 'agreement.html');
        return $template->get();
    }

    public static function format($data)
    {
        extract($data);
        $info_date = strftime('%b. %e, %Y', $event_date);
        $data['event_date'] = strftime('%Y/%m/%d', $event_date);
        $data['xcheckbox'] = '<input type="checkbox" class="agreement-checkbox" name="agreement[]" value="' . $data['id'] . '" />';

        $data['username'] = "<a href='mailto:$email'>$username</a>";

        $approved_date = empty($approved_date) ? null : '<tr><th>Approved on</th><td>' . strftime('%B %e, %Y', $approved_date) . '</td></tr>';
        $title = $data['event_name'] . ' - ' . $info_date;
        $content = <<<EOF
<table class="table table-concise">
                $approved_date
                <tr>
                    <th>Event</th><td>$title</td>
                </tr>
                <tr>
                    <th>Location</th><td>$event_location</td>
                </tr>
                <tr>
                    <th>Organization</th><td>$organization_name</td>
                </tr>
                <tr>
                    <th>Organizer</th><td> $organization_rep_name, $organization_rep_title</td>
                </tr>
</table>
EOF;
        $ent_content = htmlentities($content);

        $options[] = "<i class='fa fa-info-circle fa-lg help tax-info' role='button' data-content='$ent_content'></i>";
        $options[] = "<a style='color:inherit' title='Print' target='_blank' href='./tax_agreement/user/print/$id'><i class='fa fa-print fa-lg'></i></a>";

        if (empty($approved_date)) {
            $options[] = "<i class='tax-approve fa fa-check fa-lg pointer' data-id='$id' title='Approve agreement'></i>";
        } else {
            $options[] = "<i class='tax-unapprove fa fa-remove fa-lg pointer' data-id='$id' title='Unapprove agreement'></i>";
        }

        $data['options'] = implode(' ', $options);
        $data['delete'] = "<i class='fa fa-trash-o fa-lg pointer tax-delete' data-id='$id' title='Delete'></i>";

        return $data;
    }

    public static function approve($id)
    {
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('id', $id);
        $t->addValue('approved_date', time());
        $db->setLimit(1);
        $db->update();
    }

    public static function unapprove($id)
    {
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('id', $id);
        $t->addValue('approved_date', null);
        $db->setLimit(1);
        $db->update();
    }

    public static function delete($id)
    {
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('id', $id);
        $db->setLimit(1);
        $row = $db->selectOneRow();
        $pdf = self::getFileDirectory() . $row['filepath'];
        if (is_file($pdf)) {
            unlink($pdf);
        }

        $db->delete();
    }

    public static function approveList($checked)
    {
        $checked = filter_var_array($checked, FILTER_SANITIZE_NUMBER_INT);
        if (empty($checked)) {
            return;
        }
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('id', $checked, 'in');
        $t->addValue('approved_date', time());
        $db->update();
    }

    public static function unapproveList($checked)
    {
        $checked = filter_var_array($checked, FILTER_SANITIZE_NUMBER_INT);
        if (empty($checked)) {
            return;
        }
        $db = \Database::newDB();
        $t = $db->addTable('tax_mainclass');
        $t->addFieldConditional('id', $checked, 'in');
        $t->addValue('approved_date', null);
        $db->update();
    }

}

?>
