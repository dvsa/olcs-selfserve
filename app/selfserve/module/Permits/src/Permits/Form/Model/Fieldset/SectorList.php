<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SectorList")
 */
class SectorList
{

    /**
     * @Form\Name("SectorList")
     * @Form\Options({
     *     "fieldset-attributes": {"id": "sector-list"},
     *     "fieldset-data-group": "sector-list",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "service_name": "Common\Service\Data\Sector",
     *     "category": ""
     * })
     * @Form\Attributes({
     *   "class" : "input--trips",
     *   "id" : "EcmtSectorList",
     *   "aria-labelledby" : "SpecialistHaulage",
     * })
     * @Form\Validator({
     *     "name": "Zend\Validator\NotEmpty",
     *     "options": {
     *         "message": {
     *             "isEmpty": "error.messages.international-journey"
     *         },
     *         "breakchainonfailure": true
     *     },
     * })
     * @Form\Type("DynamicRadioHtml")
     */
    public $SectorList;

}
