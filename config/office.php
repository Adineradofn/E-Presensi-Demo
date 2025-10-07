<?php
return [
    // Bisa isi IP atau CIDR, pisahkan koma di .env
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('OFFICE_ALLOWED_IPS', '')))),
];
