<?php

return array(
    'variables' => [
        'title' => 'manage-users.table.title.count'
    ],
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => [
                    'label' => 'add-a-user',
                    'class' => 'govuk-button',
                    'id' => 'addUser'
                ],
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Name',
            'type' => 'Action',
            'action' => 'edit',
            'formatter' => function ($row, $column, $sl) {
                $column['formatter'] = 'Name';
                return $this->callFormatter($column, $row['contactDetails']['person']);
            }
        ],
        [
            'title' => 'email-address',
            'formatter' => function ($row) {
                return $row['contactDetails']['emailAddress'];
            }
        ],
        [
            'title' => 'manage-users.table.column.permission.title',
            'formatter' => function ($row, $column, $sm) {
                return implode(
                    ',',
                    array_map(
                        function ($role) use ($sm) {
                            return $sm->get('translator')
                                ->translate('role.' . $role['role']);
                        },
                        $row['roles']
                    )
                );
            }
        ],
        [
            'title' => 'action',
            'type' => 'ActionLinks',
            'isRemoveVisible' => function ($row) {
                /** $var TableBuilder $this */
                return ($row['id'] !== $this->authService->getIdentity()->getUserData()['id']);
            },
            'deleteInputName' => 'action[delete][%d]',
            'dontUseModal' => true,
            'actionClasses' => 'left-aligned action--secondary'
        ],
    ]
);
