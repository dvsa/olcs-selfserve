<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Generate link from the change answer page, back to the correct page
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ChangeAnswerLink extends AbstractHelper
{
    private $linkTemplate = '<a href="%s">%s<span class="govuk-visually-hidden">%s</span></a>';

    /**
     * Link to move from the change answer page to the answer being changed
     *
     * @param string      $route Route
     * @param string|null $label translated label
     *
     * @return string
     */
    public function __invoke(string $route, string $context, ?string $label = 'common.link.change.label'): string
    {
        $label = $this->view->escapeHtml($this->view->translate($label));
        $url = $this->view->url($route, [], [], true);
        return sprintf($this->linkTemplate, $url, $label, $context);
    }
}
