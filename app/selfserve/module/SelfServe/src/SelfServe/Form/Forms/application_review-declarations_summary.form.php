<?php

$formConfig = array(
    'application_review-declarations_summary' => array(
        'name' => 'application_review-declarations_summary',
        'disabled' => true,
        'fieldsets' => array(
            array(
                'name' => 'title',
                'elements' => array(
                    'title' => array(
                        'type' => 'hidden',
                        'label' => '<h2>1. Type of licence</h2>',
                    )
                )
            )
        )
    )
);

$ignoreFieldsetTypes = array(
    'journey-buttons'
);

$forms = array(
    'application_type-of-licence_operator-location',
    'application_type-of-licence_operator-type',
    'application_type-of-licence_licence-type',
    'application_previous-history_financial-history'
);

foreach ($forms as $form) {

    $config = include(__DIR__ . '/' . $form . '.form.php');
    $fieldsets = $config[$form]['fieldsets'];

    $i = 1;

    foreach ($fieldsets as $fieldset) {

        if (isset($fieldset['type']) && in_array($fieldset['type'], $ignoreFieldsetTypes)) {
            continue;
        }

        $fieldset['name'] = $form . '-' . $i;

        $formConfig['application_review-declarations_summary']['fieldsets'][] = $fieldset;

        $i++;
    }
}

$formConfig['application_review-declarations_summary']['fieldsets'][] = array(
    'type' => 'journey-buttons'
);

return $formConfig;
