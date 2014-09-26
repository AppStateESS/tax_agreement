<?php

namespace tax_agreement\Factory;

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
        $id = (int)$id;
        $form = new \tax_agreement\Resource\Form;
        \ResourceFactory::loadByID($form, $id);
        return $form;
    }

}

?>
