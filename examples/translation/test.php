<?php

use Larmias\Translation\Translator;

require '../bootstrap.php';

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

/** @var \Larmias\Contracts\ConfigInterface $config */
$config = $container->get(\Larmias\Contracts\ConfigInterface::class);

$config->set('translation', [
    'path' => './languages',
    'use_group' => true,
]);

/** @var \Larmias\Contracts\TranslatorInterface $translator */
$translator = $container->get(Translator::class);

$name = $translator->trans('zh_CN.name');
println($name);