<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class EbsrPackUploadFields
{
    /**
     * @Form\Options({
     *     "label": "Upload type",
     *     "help-block": "Please select an upload type",
     *     "value_options":{
     *          "ebsrt_new":"New Application",
     *          "ebsrt_refresh":"Data Refresh"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"submission_type","placeholder":"", "value":"ebsrt_new"})
     * @Form\Type("Radio")
     */
    public $submissionType;

    /**
     * @Form\Options({
     *      "label": "EBSR pack upload",
     *      "field-attributes" : {
     *          "class":"file-upload"
     *      }
     * })
     * @Form\Type("File")
     * @Form\Input("Zend\InputFilter\FileInput")
     * @Form\Filter({"name": "DecompressUploadToTmp"})
     * @Form\Validator({"name": "FileMimeType", "options":{"mimeType": "application/zip"}})
     */
    public $file;
}
