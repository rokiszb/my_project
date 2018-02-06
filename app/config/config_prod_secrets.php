// get the path to the secrects.json file
$secrets = getenv("APP_SECRETS")
if (!$secrets) {
    return;
}

// read the file and decode json to an array
$secrets = json_decode(file_get_contents($secrets), true);

// set database parameters to the container
if (isset($secrets['MYSQL'])) {
    $container->setParameter('database_driver', 'pdo_mysql');
    $container->setParameter('database_host', $secrets['MYSQL']['HOST']);
    $container->setParameter('database_name', $secrets['MYSQL']['DATABASE']);
    $container->setParameter('database_user', $secrets['MYSQL']['USER']);
    $container->setParameter('database_password', $secrets['MYSQL']['PASSWORD']);
}

// check if the Memcache component is present
if (isset($secrets['MEMCACHE'])) {
    $memcache = $secrets['MEMCACHE'];
    $handlers = array();

    foreach (range(1, $memcache['COUNT']) as $num) {
        $handlers[] = $memcache['HOST'.$num].':'.$memcache['PORT'.$num];
    }

    // apply ini settings
    ini_set('session.save_handler', 'memcached');
    ini_set('session.save_path', implode(',', $handlers));

    if ("2" === $memcache['COUNT']) {
        ini_set('memcached.sess_number_of_replicas', 1);
        ini_set('memcached.sess_consistent_hash', 1);
        ini_set('memcached.sess_binary', 1);
    }
}