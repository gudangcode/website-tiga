<?php

return [
    'services' => [
        [
            'id' => 'kickbox.io',
            'name' => 'Kickbox',
            'uri' => 'https://api.kickbox.io/v2/verify?email={EMAIL}&apikey={API_KEY}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.result',
            'result_map' => [ 'deliverable' => 'deliverable', 'undeliverable' => 'undeliverable', 'risky' => 'risky', 'unknown' => 'unknown' ]
        ], [
            'id' => 'thechecker.co',
            'name' => 'TheChecker',
            'uri' => 'https://api.thechecker.co/v1/verify?email={EMAIL}&api_key={API_KEY}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.result',
            'result_map' => [ 'deliverable' => 'deliverable', 'undeliverable' => 'undeliverable', 'risky' => 'risky', 'unknown' => 'unknown' ]
        ], [
            'id' => 'verify-email.org',
            'name' => 'verify-email.org (deprecated)',
            'uri' => 'http://api.verify-email.org/api.php?usr={USERNAME}&pwd={PASSWORD}&check={EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'username', 'password' ],
            'result_xpath' => '$.authentication_status',
            'result_map' => [ '1' => 'deliverable', '0' => 'undeliverable' ]
        ], [
            'id' => 'verify-email.org',
            'name' => 'verify-email.org',
            'uri' => 'https://app.verify-email.org/api/v1/{API_KEY}/verify/{EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.status',
            'result_map' => [ '1' => 'deliverable', '0' => 'undeliverable', '-1' => 'unknown' ]
        /*
        ], [
            'id' => 'proofy.io',
            'name' => 'proofy.io',
            'uri' => 'https://api.proofy.io/verifyaddr?aid={USERNAME}&key={API_KEY}&email={EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'username', 'api_key' ],
            'result_xpath' => '$.mail.statusName',
            'result_map' => [ 'deliverable' => 'deliverable', 'undeliverable' => 'undeliverable', 'risky' => 'risky' ]
        */
        ], [
            'id' => 'everifier.org',
            'name' => 'everifier.org',
            'uri' => 'https://api.everifier.org/v1/{API_KEY}/verify/{EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.*.status',
            'result_map' => [ '1' => 'deliverable', '0' => 'undeliverable', '-1' => 'risky' ]
        ], [
            'id' => 'verifyre.co',
            'name' => 'verifyre.co',
            'uri' => 'https://www.verifyre.co/app/check?id={USERNAME}&key={API_KEY}&mail={EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'username', 'api_key' ],
            'result_xpath' => '$.mail.status',
            'result_map' => [ '1' => 'deliverable', '2' => 'risky', '3' => 'undeliverable' ]
        ], [
            'id' => 'localmail.io',
            'name' => 'localmail.io',
            'uri' => 'https://api.localmail.io/v1/mail/verify?key={API_KEY}&email={EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.result',
            'result_map' => [ 'deliverable' => 'deliverable', 'unknown' => 'unknown', 'risky' => 'risky', 'undeliverable' => 'undeliverable' ]
        ], [
            'id' => 'debounce.io',
            'name' => 'debounce.io',
            'uri' => 'https://api.debounce.io/v1/?api={API_KEY}&email={EMAIL}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.debounce.result',
            'result_map' => [ 'Safe to Send' => 'deliverable', 'Unknown' => 'unknown', 'Risky' => 'risky', 'Invalid' => 'undeliverable' ]
        ], [
            'id' => 'emailchecker.com',
            'name' => 'emailchecker.com',
            'uri' => 'https://api.emailverifyapi.com/v3/lookups/json?email={EMAIL}&key={API_KEY}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.deliverable',
            'result_map' => [ 'true' => 'deliverable', 'false' => 'undeliverable' ]
        ],[
            'id' => 'cloudvision.io',
            'name' => 'Cloud Vision',
            'uri' => 'https://dev-marketing.cloudvision.io/api/v1/verify?email={EMAIL}&api_token={API_KEY}',
            'request_type' => 'GET',
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.result',
            'result_map' => [ 'deliverable' => 'deliverable', 'undeliverable' => 'undeliverable' ]
        ],
        /*[
            'id' => 'mailcheck.co',
            'name' => 'mailcheck.co',
            'uri' => 'https://api.mailcheck.co/v1/singleEmail:check',
            'request_type' => 'POST',
            'post_data' => '{ "email": "{EMAIL}" }',
            'post_headers' => [ 'Authorization' => 'Bearer {API_KEY}' ],
            'fields' => [ 'api_key' ],
            'result_xpath' => '$.deliverable',
            'result_map' => [ 'true' => 'deliverable', 'false' => 'undeliverable' ],
        ],*/
    ]
];
