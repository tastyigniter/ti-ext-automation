<?php

return [
    'text_title' => 'Automations',
    'text_form_name' => 'Automation',
    'text_action_form_name' => 'Automation Rule Action',
    'text_condition_form_name' => 'Automation Rule Condition',
    'text_tab_general' => 'General',
    'text_empty' => 'No added automations',
    'text_condition_match_any' => 'Match ANY of the below',
    'text_condition_match_all' => 'Match ALL of the below',

    'column_event' => 'Event',
    'column_code' => 'Code',
    'column_status' => 'Status',
    'column_condition_priority' => 'Priority',
    'column_condition_attribute' => 'Attribute',
    'column_condition_operator' => 'Operator',
    'column_condition_value' => 'Value',

    'label_code' => 'Code',
    'label_event' => 'Event',
    'label_actions' => 'Actions',
    'label_conditions' => 'Conditions',
    'label_status' => 'Status',

    'label_assign_to_staff' => 'Assign To Staff',
    'label_assign_to_staff_group' => 'Assign To Staff Group',

    'help_event' => 'This rule is triggered by the selected system event',
    'help_actions' => 'Choose one or more actions to perform when this automation is triggered',
    'help_conditions' => 'Only process the actions when ALL the conditions are TRUE.',

    'webhook' => [
        'label_webhooks' => 'Webhooks',
        'label_url' => 'Url',

        'help_webhooks' => 'Webhooks allow you to set up integrations, which triggers when certain events occur within TastyIgniter. When an event is triggered, a HTTP POST payload is sent to the webhook\'s URL. Webhooks can be used to push new orders to your POS.',
        'help_url' => 'A POST request will be sent to the URL with details of the subscribed events. Data format will be JSON',
    ],
];