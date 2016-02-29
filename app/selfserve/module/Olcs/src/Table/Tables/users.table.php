<?php

return array(
    'variables' => [
        'title' => 'Users'
    ],
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'label' => 'manage-users.action.add'),
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
            'title' => 'Email address',
            'formatter' => function ($row) {
                return $row['contactDetails']['emailAddress'];
            }
        ],
        [
            'title' => 'Permission',
            'formatter' => function ($row, $column, $sm) {
                return implode(
                    ',',
                    array_map(
                        function ($role) use ($sm) {
                            return $sm->get('translator')
                                ->translate('role.'.$role['role']);
                        },
                        $row['roles']
                    )
                );
            }
        ],
        [
            'title' => 'Remove',
            'type' => 'Action',
            'action' => 'delete',
            'class' => 'remove',
            'formatter' => function () {
                return 'Remove';
            }
        ],
    ]
);
