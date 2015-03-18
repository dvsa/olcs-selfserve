<?php

/**
 * Application Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Application;

use Olcs\View\Model\LvaOverview;

/**
 * Application Overview View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOverview extends LvaOverview
{
    /**
     * Holds the template
     *
     * @var string
     */
    protected $template = 'overview-application';

    protected $sectionModel = 'Application\\ApplicationOverviewSection';

    /**
     * Set the overview data
     *
     * @param array $data
     * @param array $sections
     */
    public function __construct($data, array $sections = array(), $submissionForm = null)
    {
        $this->setVariable('applicationId', $data['id']);
        $this->setVariable('createdOn', date('d F Y', strtotime($data['createdOn'])));
        $this->setVariable('status', $data['status']['id']);
        $this->setVariable('submissionForm', $submissionForm);
        $this->setVariable('receivedDate', $data['receivedDate']);
        $this->setVariable('completionDate', $data['targetCompletionDate']);
        $this->setVariable(
            'interimStatus',
            ($data['interimStatus']) ? $data['interimStatus']['description'] : null
        );
        $this->setVariable('interimStartDate', $data['interimStart']);

        $completedSections = array_filter(
            $sections,
            function ($section) {
                return isset($section['complete']) && $section['complete'] == true;
            }
        );
        $this->setVariable('progressX', count($completedSections));
        $this->setVariable('progressY', count($sections));

        parent::__construct($data, $sections);
    }
}
