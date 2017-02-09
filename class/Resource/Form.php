<?php
namespace tax_agreement\Resource;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Form extends \phpws2\Resource {
    /**
     * Date the student accessed site
     * @var \Variable\DateVar
     */
    protected $access_date;

    /**
     * Date admin approved tax form
     * @var \Variable\DateVar
     */
    protected $approved_date;

    /**
     * Name of student organization
     * @var \Variable\TextOnly
     */
    protected $organization_name;

    /**
     * Name of student organization representative
     * @var \Variable\TextOnly
     */
    protected $organization_rep_name;

    /**
     * Title of student organization representative
     * e.g. President, Treasurer
     * @var \Variable\TextOnly
     */
    protected $organization_rep_title;

    /**
     * Name of event
     * @var \Variable\TextOnly
     */
    protected $event_name;

    /**
     * Date event is occurring
     * @var \Variable\DateVar
     */
    protected $event_date;

    /**
     * Location of event
     * @var \Variable\TextOnly
     */
    protected $event_location;

    /**
     * Location of PDF file
     * @var \Variable\FileVar
     */
    protected $filepath;

    protected $user_id;

    protected $table = 'tax_mainclass';


 public function __construct()
    {
        parent::__construct();
        $this->access_date = new \Variable\DateVar(time(), 'access_date');
        $this->approved_date = new \Variable\DateVar(null, 'approved_date');
        $this->approved_date->allowNull(true);
        $this->organization_name = new \Variable\TextOnly(null, 'organization_name');
        $this->organization_rep_name = new \Variable\TextOnly(null, 'organization_rep_name');
        $this->organization_rep_title = new \Variable\TextOnly(null, 'organization_rep_title');
        $this->event_name = new \Variable\TextOnly(null, 'event_name');
        $this->event_date = new \Variable\DateVar(null, 'event_date');
        $this->event_location = new \Variable\TextOnly(null, 'event_location');
        $this->filepath = new \Variable\FileVar(null, 'filepath');
        $this->filepath->allowNull(true);
        $this->user_id = new \Variable\IntegerVar(0, 'user_id');
    }

    public function getOrganizationName()
    {
        return $this->organization_name->get();
    }

    public function getFilePath()
    {
        return $this->filepath->get();
    }

    public function setFilePath($path)
    {
        $this->filepath->set($path);
    }

    public function setOrganizationName($name)
    {
        $this->organization_name->set($name);
    }

    public function setOrganizationRepName($name)
    {
        $this->organization_rep_name->set($name);
    }

    public function setOrganizationRepTitle($title)
    {
        $this->organization_rep_title->set($title);
    }

    public function setEventName($name)
    {
        $this->event_name->set($name);
    }

    public function setEventDate($date)
    {
        $date_integer = strtotime($date);
        $this->event_date->set($date_integer);
    }

    public function setEventLocation($location)
    {
        $this->event_location->set($location);
    }

    public function getUserId()
    {
        return $this->user_id->get();
    }

    public function setUserId($id)
    {
        $this->user_id = (int) $id;
    }

}

?>
