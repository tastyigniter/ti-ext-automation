<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
                'create' => ['label' => 'lang:admin::lang.button_new', 'class' => 'btn btn-primary', 'href' => 'igniter/automation/automations/create'],
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
            ],
        ],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'igniter/automation/automations/edit/{id}',
                ],
            ],
            'event_name' => [
                'label' => 'lang:igniter.automation::default.column_event',
                'type' => 'text',
                'sortable' => FALSE,
            ],
            'name' => [
                'label' => 'lang:admin::lang.label_name',
                'type' => 'text',
                'searchable' => TRUE,
            ],
            'code' => [
                'label' => 'lang:igniter.automation::default.column_code',
                'type' => 'text',
                'searchable' => TRUE,
            ],
            'status' => [
                'label' => 'lang:admin::lang.label_status',
                'type' => 'switch',
                'searchable' => TRUE,
            ],
            'id' => [
                'label' => 'lang:admin::lang.column_id',
                'invisible' => TRUE,
            ],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'igniter/automation/automations'],
                'save' => [
                    'label' => 'lang:admin::lang.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                ],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'fields' => [
            'event_class' => [
                'label' => 'lang:igniter.automation::default.label_event',
                'type' => 'select',
                'comment' => 'lang:igniter.automation::default.help_event',
            ],
        ],
        'tabs' => [
            'fields' => [
                '_action' => [
                    'tab' => 'Actions',
                    'label' => 'lang:igniter.automation::default.label_actions',
                    'type' => 'select',
                    'context' => ['edit', 'preview'],
                    'placeholder' => 'lang:admin::lang.text_select',
                    'comment' => 'lang:igniter.automation::default.help_actions',
                    'attributes' => [
                        'data-request' => 'onLoadCreateActionForm',
                        'data-request-success' => '$(\'[data-control="connector"]\').connector();',
                    ],
                ],
                'actions' => [
                    'tab' => 'Actions',
                    'type' => 'connector',
                    'context' => ['edit', 'preview'],
                    'formName' => 'lang:igniter.automation::default.text_action_form_name',
                    'popupSize' => 'modal-lg',
                    'sortable' => TRUE,
                    'form' => [],
                ],
                'config_data[condition_match_type]' => [
                    'tab' => 'Conditions',
                    'type' => 'radiolist',
                    'context' => ['edit', 'preview'],
                    'inlineMode' => TRUE,
                    'default' => 'all',
                    'options' => [
                        'all' => 'lang:igniter.automation::default.text_condition_match_all',
                        'any' => 'lang:igniter.automation::default.text_condition_match_any',
                    ],
                ],
                '_condition' => [
                    'tab' => 'Conditions',
                    'label' => 'lang:igniter.automation::default.label_conditions',
                    'type' => 'select',
                    'context' => ['edit', 'preview'],
                    'placeholder' => 'lang:admin::lang.text_select',
                    'comment' => 'lang:igniter.automation::default.help_conditions',
                    'attributes' => [
                        'data-request' => 'onLoadCreateConditionForm',
                        'data-request-success' => '$(\'[data-control="connector"]\').connector();',
                    ],
                ],
                'conditions' => [
                    'tab' => 'Conditions',
                    'type' => 'connector',
                    'context' => ['edit', 'preview'],
                    'formName' => 'lang:igniter.automation::default.text_condition_form_name',
                    'popupSize' => 'modal-lg',
                    'sortable' => TRUE,
                    'form' => [
                        'fields' => [
                            'options' => [
                                'label' => 'lang:igniter.automation::default.label_conditions',
                                'type' => 'repeater',
                                'commentAbove' => 'lang:igniter.automation::default.help_conditions',
                                'sortable' => TRUE,
                                'form' => [
                                    'fields' => [
                                        'priority' => [
                                            'label' => 'lang:igniter.automation::default.column_condition_priority',
                                            'type' => 'hidden',
                                        ],
                                        'attribute' => [
                                            'label' => 'lang:igniter.automation::default.column_condition_attribute',
                                            'type' => 'select',
                                            'options' => 'getAttributeOptions',
                                        ],
                                        'operator' => [
                                            'label' => 'lang:igniter.automation::default.column_condition_operator',
                                            'type' => 'select',
                                            'options' => 'getOperatorOptions',
                                        ],
                                        'value' => [
                                            'label' => 'lang:igniter.automation::default.column_condition_value',
                                            'type' => 'text',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                'name' => [
                    'tab' => 'Settings',
                    'label' => 'lang:admin::lang.label_name',
                    'type' => 'text',
                    'context' => ['edit', 'preview'],
                    'span' => 'left',
                ],
                'code' => [
                    'tab' => 'Settings',
                    'label' => 'lang:igniter.automation::default.label_code',
                    'type' => 'text',
                    'context' => ['edit', 'preview'],
                    'span' => 'right',
                ],
                'description' => [
                    'tab' => 'Settings',
                    'label' => 'lang:admin::lang.label_description',
                    'context' => ['edit', 'preview'],
                    'type' => 'textarea',
                ],
                'status' => [
                    'tab' => 'Settings',
                    'label' => 'lang:admin::lang.label_status',
                    'type' => 'switch',
                    'default' => TRUE,
                    'context' => ['edit', 'preview'],
                ],
            ],
        ],
    ],
];