<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Form\Model\Form\Surrender\CommunityLicence;
use Common\Data\Mapper\Licence\Surrender\CommunityLicence as Mapper;

/**
 * Class CommunityLicenceController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class CommunityLicenceController extends AbstractSurrenderController
{
    use ReviewRedirect;

    protected $formConfig = [
        'default' => [
            'communityLicenceForm' => [
                'formClass' => CommunityLicence::class,
                'mapper' => [
                    'class' => Mapper::class
                ],
                'dataSource' => 'surrender'
            ]
        ]
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-licence-documents'
    ];

    protected $conditionalDisplayConfig = [
        'default' => [
            'surrender' => [
                'key' => 'isInternationalLicence',
                'value' => true,
                'route' => 'licence/surrender/review/GET'
            ]
        ]
    ];

    public function indexAction()
    {
         return $this->createView();
    }

    public function submitAction()
    {

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);
        $validForm = $this->form->isValid();
        if ($validForm) {
            $data = $this->getServiceLocator()->get(Mapper::class)->mapFromForm($formData);
            if ($this->updateSurrender(RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE, $data)) {
                $routeName = 'licence/surrender/review/GET';
                $this->nextStep($routeName);
            }
        }
        return $this->createView();
    }

    public function alterForm($form)
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $form->get('form-actions')->get('submit')->setLabel($translator->translate('lva.external.save_and_continue.button'));
        return $form;
    }


    /**
     * @return array
     */
    protected function getViewVariables(): array
    {
        return [
            'pageTitle' => 'licence.surrender.community_licence.heading',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'backUrl' => $this->getLink('licence/surrender/operator-licence/GET'),
            'returnLinkText' => 'licence.surrender.community_licence.return_to_operator.licence.link',
            'returnLink' => $this->getLink('licence/surrender/operator-licence/GET'),
        ];
    }
}
