<?php

namespace Olcs\Controller;

use Laminas\View\Model\ViewModel;

trait InteractsWithViewsTrait
{
    /**
     * Creates a new page view model.
     *
     * @param string $templateName
     * @param array $params
     * @return ViewModel
     */
    protected function newPageView(string $templateName, array $params): ViewModel
    {
        $content = new ViewModel($params);
        $content->setTemplate($templateName);
        $view = new ViewModel();
        $view->setTemplate('layout/layout')
            ->setTerminal(true)
            ->addChild($content, 'content');
        return $view;
    }
}