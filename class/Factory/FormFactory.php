<?php

namespace tax_agreement\Factory;

require_once PHPWS_SOURCE_DIR . 'mod/tax_agreement/conf/defines.php';

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class FormFactory {

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
        $path = PHPWS_HOME_DIR . 'files/tax_agreement/';
        $file_name = $form->getUserId() . '-' . $form->getId() . '-' . time() . '.pdf';

        $pdf = new \WKPDF(WKPDF_PATH);
        if (USE_XVFB) {
            $pdf->setXVFB(XVFB_PATH);
        }
        $pdf->set_html($content);
        $pdf->render();

        $pdf->output(\WKPDF::$PDF_SAVEFILE, $path . $file_name);
        return $file_name;
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
        $vars['access_date'] = strftime('%B %e, %Y',
                strtotime($vars['access_date']));
        $vars['event_date'] = strftime('%B %e, %Y',
                strtotime($vars['event_date']));
        $template = new \Template($vars);
        $template->setModuleTemplate('tax_agreement', 'agreement.html');
        return $template->get();
    }

}

?>
