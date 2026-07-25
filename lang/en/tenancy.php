<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant resolution language lines
    |--------------------------------------------------------------------------
    |
    | The unknown / suspended / expired / provisioning keys are used both as the
    | message for ResolveTenant's JSON responses and as the text source for the
    | standalone HTML pages in resources/views/tenancy/.
    |
    */

    'unknown'      => 'This workspace does not exist.',
    'suspended'    => 'This workspace is temporarily suspended.',
    'expired'      => 'Your subscription has expired.',
    'provisioning' => 'Your workspace is being prepared.',

    // "workspace not found" page (404)
    'unknown_title' => 'Workspace not found',
    'unknown_body'  => 'The address you entered does not match any customer workspace. Check the spelling of the subdomain and try again.',

    // "suspended" page (503)
    'suspended_title' => 'Workspace suspended',
    'suspended_body'  => 'Access to this workspace has been temporarily suspended. If you believe this is a mistake, please contact support.',

    // "expired" page (503)
    'expired_title' => 'Subscription expired',
    'expired_body'  => 'The subscription for this workspace has expired. Renew your subscription to restore access.',

    // "provisioning" page (503)
    'provisioning_title' => 'Workspace being prepared',
    'provisioning_body'  => 'We are finishing setting up your workspace. This usually takes only a few moments. Please try again shortly.',

    // Shared elements
    'contact_support' => 'Contact support',
    'retry'           => 'Try again',
    'need_help'       => 'Need help?',

];
